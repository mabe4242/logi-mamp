<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use App\Models\PickingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PickingControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * ピッキング対象の出荷予定一覧が表示できる
     */
    public function admin_can_view_picking_index()
    {
        $this->actingAsAdmin();

        ShipmentPlan::factory()->create(['status' => 'PICKING']);
        ShipmentPlan::factory()->create(['status' => 'PLANNED']); // これは表示されない

        $response = $this->get('/picking');
        $response->assertStatus(200);
        $response->assertViewIs('wms.picking.index');
    }

    /**
     * @test
     * ピッキング画面が表示できる
     */
    public function admin_can_view_picking_show()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);
        $product = Product::factory()->create();
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
        ]);

        $response = $this->get("/picking/{$plan->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.picking.show');
    }

    /**
     * @test
     * PICKING以外のステータスではピッキング画面にアクセスできない
     */
    public function cannot_access_picking_show_for_non_picking_status()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);

        $response = $this->get("/picking/{$plan->id}");

        $response->assertRedirect('/picking');
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

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);

        $response = $this->post("/picking/{$plan->id}/scan", [
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

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);
        $product = Product::factory()->create(['sku' => 'TEST001']);
        // 明細には追加しない

        $response = $this->post("/picking/{$plan->id}/scan", [
            'code' => 'TEST001',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * 予定数までピッキング済みの商品をスキャンするとエラーが表示される
     */
    public function scanning_already_picked_product_shows_error()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);
        $product = Product::factory()->create(['sku' => 'TEST001']);
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
            'picked_qty' => 10, // 既にピッキング済み
        ]);

        $response = $this->post("/picking/{$plan->id}/scan", [
            'code' => 'TEST001',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * PICKING以外のステータスではピッキングスキャンできない
     */
    public function cannot_scan_when_status_is_not_picking()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $product = Product::factory()->create(['sku' => 'TEST001']);
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
        ]);

        $response = $this->post("/picking/{$plan->id}/scan", [
            'code' => 'TEST001',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * SKUコードでピッキングスキャンできる
     */
    public function admin_can_scan_by_sku()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);
        $product = Product::factory()->create(['sku' => 'TEST001']);
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
            'picked_qty' => 0,
        ]);

        $response = $this->post("/picking/{$plan->id}/scan", [
            'code' => 'TEST001',
        ]);

        $response->assertRedirect("/picking/{$plan->id}");
        $response->assertSessionHas('success');

        $line->refresh();
        $this->assertEquals(1, $line->picked_qty);

        $this->assertDatabaseHas('picking_logs', [
            'shipment_plan_id' => $plan->id,
            'shipment_plan_line_id' => $line->id,
            'scanned_code' => 'TEST001',
            'picked_by_admin_id' => $admin->id,
        ]);
    }

    /**
     * @test
     * バーコードでピッキングスキャンできる
     */
    public function admin_can_scan_by_barcode()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);
        $product = Product::factory()->create(['barcode' => '1234567890123']);
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
            'picked_qty' => 0,
        ]);

        $response = $this->post("/picking/{$plan->id}/scan", [
            'code' => '1234567890123',
        ]);

        $response->assertRedirect("/picking/{$plan->id}");
        $response->assertSessionHas('success');

        $line->refresh();
        $this->assertEquals(1, $line->picked_qty);
    }

    /**
     * @test
     * ピッキング残がある場合、ピッキング完了できない
     */
    public function cannot_finish_picking_with_remaining_qty()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'planned_qty' => 10,
            'picked_qty' => 5, // ピッキング残あり
        ]);

        $response = $this->post("/picking/{$plan->id}/finish");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * PICKING以外のステータスではピッキング完了できない
     */
    public function cannot_finish_picking_when_status_is_not_picking()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);

        $response = $this->post("/picking/{$plan->id}/finish");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * ピッキング完了できる
     */
    public function admin_can_finish_picking()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'planned_qty' => 10,
            'picked_qty' => 10, // 全数量ピッキング済み
        ]);

        $response = $this->post("/picking/{$plan->id}/finish");

        $response->assertRedirect('/picking');
        $response->assertSessionHas('success', 'ピッキングを完了しました（出荷作業へ）');

        $plan->refresh();
        $this->assertEquals('PACKING', $plan->status);
    }
}
