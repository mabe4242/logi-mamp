<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
use App\Models\Product;
use App\Models\ReceivingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundReceiveControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 検品対象の入荷予定一覧が表示できる
     */
    public function admin_can_view_receiving_index()
    {
        $this->actingAsAdmin();

        InboundPlan::factory()->create(['status' => 'RECEIVING']);
        InboundPlan::factory()->create(['status' => 'DRAFT']); // これは表示されない

        $response = $this->get('/receiving');
        $response->assertStatus(200);
        $response->assertViewIs('wms.receiving.index');
    }

    /**
     * @test
     * 検品画面が表示できる
     */
    public function admin_can_view_receiving_show()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $product = Product::factory()->create();
        InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'product_id' => $product->id,
        ]);

        $response = $this->get("/receiving/{$plan->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.receiving.show');
    }

    /**
     * @test
     * RECEIVING以外のステータスでは検品画面にアクセスできない
     */
    public function cannot_access_receiving_show_for_non_receiving_status()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);

        $response = $this->get("/receiving/{$plan->id}");

        $response->assertRedirect('/receiving');
        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * バーコードが未入力の場合、バリデーションエラーが表示される
     */
    public function code_is_required_when_scanning()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);

        $response = $this->post("/receiving/{$plan->id}/scan", [
            'code' => '',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * 予定に存在しない商品のバーコードをスキャンするとエラーが表示される
     */
    public function scanning_non_existent_product_shows_error()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $product = Product::factory()->create(['sku' => 'TEST001', 'barcode' => '1234567890123']);
        // 明細には追加しない

        $response = $this->post("/receiving/{$plan->id}/scan", [
            'code' => 'TEST001',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * 予定数まで検品済みの商品をスキャンするとエラーが表示される
     */
    public function scanning_already_received_product_shows_error()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $product = Product::factory()->create(['sku' => 'TEST001']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
            'received_qty' => 10, // 既に検品済み
        ]);

        $response = $this->post("/receiving/{$plan->id}/scan", [
            'code' => 'TEST001',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * RECEIVING以外のステータスでは検品スキャンできない
     */
    public function cannot_scan_when_status_is_not_receiving()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $product = Product::factory()->create(['sku' => 'TEST001']);
        InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'product_id' => $product->id,
        ]);

        $response = $this->post("/receiving/{$plan->id}/scan", [
            'code' => 'TEST001',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * SKUコードで検品スキャンできる
     */
    public function admin_can_scan_by_sku()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $product = Product::factory()->create(['sku' => 'TEST001']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
            'received_qty' => 0,
        ]);

        $response = $this->post("/receiving/{$plan->id}/scan", [
            'code' => 'TEST001',
        ]);

        $response->assertRedirect("/receiving/{$plan->id}");
        $response->assertSessionHas('success');

        $line->refresh();
        $this->assertEquals(1, $line->received_qty);

        $this->assertDatabaseHas('receiving_logs', [
            'inbound_plan_id' => $plan->id,
            'inbound_plan_line_id' => $line->id,
            'scanned_code' => 'TEST001',
            'scanned_by_admin_id' => $admin->id,
        ]);
    }

    /**
     * @test
     * バーコードで検品スキャンできる
     */
    public function admin_can_scan_by_barcode()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $product = Product::factory()->create(['barcode' => '1234567890123']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
            'received_qty' => 0,
        ]);

        $response = $this->post("/receiving/{$plan->id}/scan", [
            'code' => '1234567890123',
        ]);

        $response->assertRedirect("/receiving/{$plan->id}");
        $response->assertSessionHas('success');

        $line->refresh();
        $this->assertEquals(1, $line->received_qty);
    }

    /**
     * @test
     * 検品済み数量が0の場合、検品完了できない
     */
    public function cannot_finish_receiving_without_any_received_items()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'received_qty' => 0,
        ]);

        $response = $this->post("/receiving/{$plan->id}/finish");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * RECEIVING以外のステータスでは検品完了できない
     */
    public function cannot_finish_receiving_when_status_is_not_receiving()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);

        $response = $this->post("/receiving/{$plan->id}/finish");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 検品完了できる
     */
    public function admin_can_finish_receiving()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'received_qty' => 5,
        ]);

        $response = $this->post("/receiving/{$plan->id}/finish");

        $response->assertRedirect('/receiving');
        $response->assertSessionHas('success', '検品を完了しました（入庫待ちへ移動）');

        $plan->refresh();
        $this->assertEquals('WAITING_PUTAWAY', $plan->status);
    }
}
