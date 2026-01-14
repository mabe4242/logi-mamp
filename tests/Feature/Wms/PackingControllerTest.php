<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use App\Models\ShippingLog;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackingControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 出荷作業対象の出荷予定一覧が表示できる
     */
    public function admin_can_view_packing_index()
    {
        $this->actingAsAdmin();

        ShipmentPlan::factory()->create(['status' => 'PACKING']);
        ShipmentPlan::factory()->create(['status' => 'PICKING']); // これは表示されない

        $response = $this->get('/packing');
        $response->assertStatus(200);
        $response->assertViewIs('wms.packing.index');
    }

    /**
     * @test
     * 出荷作業画面が表示できる
     */
    public function admin_can_view_packing_show()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PACKING']);
        $product = Product::factory()->create();
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
        ]);

        $response = $this->get("/packing/{$plan->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.packing.show');
    }

    /**
     * @test
     * PACKING以外のステータスでは出荷作業画面にアクセスできない
     */
    public function cannot_access_packing_show_for_non_packing_status()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);

        $response = $this->get("/packing/{$plan->id}");

        $response->assertRedirect('/packing');
        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 送り状番号が未入力の場合、バリデーションエラーが表示される
     */
    public function tracking_no_is_required_when_scanning_label()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PACKING']);

        $response = $this->post("/packing/{$plan->id}/scan-label", [
            'tracking_no' => '',
        ]);

        $response->assertSessionHasErrors(['tracking_no']);
    }

    /**
     * @test
     * PACKING以外のステータスでは送り状スキャンできない
     */
    public function cannot_scan_label_when_status_is_not_packing()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);

        $response = $this->post("/packing/{$plan->id}/scan-label", [
            'tracking_no' => '1234567890',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 送り状番号を登録できる
     */
    public function admin_can_scan_label()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PACKING']);

        $response = $this->post("/packing/{$plan->id}/scan-label", [
            'tracking_no' => '1234567890',
        ]);

        $response->assertRedirect("/packing/{$plan->id}");
        $response->assertSessionHas('success', '送り状番号を登録しました');

        $plan->refresh();
        $this->assertEquals('1234567890', $plan->tracking_no);
    }

    /**
     * @test
     * 運送会社が未入力の場合、バリデーションエラーが表示される
     */
    public function carrier_is_required_when_setting_carrier()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PACKING']);

        $response = $this->post("/packing/{$plan->id}/set-carrier", [
            'carrier' => '',
        ]);

        $response->assertSessionHasErrors(['carrier']);
    }

    /**
     * @test
     * PACKING以外のステータスでは運送会社設定できない
     */
    public function cannot_set_carrier_when_status_is_not_packing()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PICKING']);

        $response = $this->post("/packing/{$plan->id}/set-carrier", [
            'carrier' => 'ヤマト',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 運送会社を設定できる
     */
    public function admin_can_set_carrier()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PACKING']);

        $response = $this->post("/packing/{$plan->id}/set-carrier", [
            'carrier' => 'ヤマト',
        ]);

        $response->assertRedirect("/packing/{$plan->id}");
        $response->assertSessionHas('success', '運送会社を設定しました');

        $plan->refresh();
        $this->assertEquals('ヤマト', $plan->carrier);
    }

    /**
     * @test
     * 送り状番号が未登録の場合、出荷完了できない
     */
    public function cannot_ship_without_tracking_no()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = ShipmentPlan::factory()->create([
            'status' => 'PACKING',
            'tracking_no' => null,
            'carrier' => 'ヤマト',
        ]);

        $response = $this->post("/packing/{$plan->id}/ship");

        $response->assertSessionHasErrors(['tracking_no']);
    }

    /**
     * @test
     * 運送会社が未設定の場合、出荷完了できない
     */
    public function cannot_ship_without_carrier()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = ShipmentPlan::factory()->create([
            'status' => 'PACKING',
            'tracking_no' => '1234567890',
            'carrier' => null,
        ]);

        $response = $this->post("/packing/{$plan->id}/ship");

        $response->assertSessionHasErrors(['carrier']);
    }

    /**
     * @test
     * PACKING以外のステータスでは出荷完了できない
     */
    public function cannot_ship_when_status_is_not_packing()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = ShipmentPlan::factory()->create([
            'status' => 'PICKING',
            'tracking_no' => '1234567890',
            'carrier' => 'ヤマト',
        ]);

        $response = $this->post("/packing/{$plan->id}/ship");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 出荷完了できる
     */
    public function admin_can_ship()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = ShipmentPlan::factory()->create([
            'status' => 'PACKING',
            'tracking_no' => '1234567890',
            'carrier' => 'ヤマト',
        ]);
        $product = Product::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
            'picked_qty' => 10,
            'shipped_qty' => 0,
        ]);

        // 十分な在庫を作成（予約済み含む）
        Stock::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'on_hand_qty' => 20,
            'reserved_qty' => 10,
        ]);

        $response = $this->post("/packing/{$plan->id}/ship");

        $response->assertRedirect('/packing');
        $response->assertSessionHas('success', '出荷完了しました（在庫を減算しました）');

        $plan->refresh();
        $this->assertEquals('SHIPPED', $plan->status);

        // 明細の出荷済数が更新されている
        $line->refresh();
        $this->assertEquals(10, $line->shipped_qty);

        // 出荷ログが作成されている
        $this->assertDatabaseHas('shipping_logs', [
            'shipment_plan_id' => $plan->id,
            'carrier' => 'ヤマト',
            'tracking_no' => '1234567890',
            'shipped_by_admin_id' => $admin->id,
        ]);

        // 在庫が減算されている
        $stock = Stock::where('product_id', $product->id)->first();
        $this->assertEquals(10, $stock->on_hand_qty); // 20 - 10 = 10
        $this->assertEquals(0, $stock->reserved_qty); // 10 - 10 = 0
    }
}
