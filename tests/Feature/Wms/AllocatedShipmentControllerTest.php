<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllocatedShipmentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 引当済み出荷予定一覧が表示できる
     */
    public function admin_can_view_allocated_shipments_index()
    {
        $this->actingAsAdmin();

        // ALLOCATEDステータスの出荷予定を作成
        ShipmentPlan::factory()->count(3)->create(['status' => 'ALLOCATED']);
        // 他のステータスは表示されない
        ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        ShipmentPlan::factory()->create(['status' => 'PICKING']);

        $response = $this->get('/allocated-shipments');
        $response->assertStatus(200);
        $response->assertViewIs('wms.allocated_shipments.index');
    }

    /**
     * @test
     * 引当済み出荷予定詳細が表示できる
     */
    public function admin_can_view_allocated_shipment_detail()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'ALLOCATED']);
        $product = Product::factory()->create();
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
        ]);

        $response = $this->get(route('allocated-shipments.show', $plan));
        $response->assertStatus(200);
        $response->assertViewIs('wms.allocated_shipments.show');
        $response->assertViewHas('shipment_plan', $plan);
    }

    /**
     * @test
     * ALLOCATED以外のステータスでは詳細表示できない
     */
    public function cannot_view_non_allocated_shipment_detail()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']); // ALLOCATED以外

        $response = $this->get(route('allocated-shipments.show', $plan));
        $response->assertStatus(404);
    }

    /**
     * @test
     * 納品書が表示できる
     */
    public function admin_can_view_invoice()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'ALLOCATED']);
        $product = Product::factory()->create();
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
        ]);

        $response = $this->get(route('allocated-shipments.invoice', $plan));
        $response->assertStatus(200);
        $response->assertViewIs('wms.allocated_shipments.invoice');
        $response->assertViewHas('shipment_plan', $plan);
    }

    /**
     * @test
     * ALLOCATED以外のステータスでは納品書を表示できない
     */
    public function cannot_view_invoice_for_non_allocated()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']); // ALLOCATED以外

        $response = $this->get(route('allocated-shipments.invoice', $plan));
        $response->assertStatus(404);
    }

    /**
     * @test
     * 送り状が表示できる
     */
    public function admin_can_view_label()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'ALLOCATED']);
        $product = Product::factory()->create();
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
        ]);

        $response = $this->get(route('allocated-shipments.label', $plan));
        $response->assertStatus(200);
        $response->assertViewIs('wms.allocated_shipments.label');
        $response->assertViewHas('shipment_plan', $plan);
    }

    /**
     * @test
     * ALLOCATED以外のステータスでは送り状を表示できない
     */
    public function cannot_view_label_for_non_allocated()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']); // ALLOCATED以外

        $response = $this->get(route('allocated-shipments.label', $plan));
        $response->assertStatus(404);
    }

    /**
     * @test
     * ピッキング開始できる
     */
    public function admin_can_start_picking()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'ALLOCATED']);

        $response = $this->post(route('allocated-shipments.start-picking', $plan));

        $response->assertRedirect(route('allocated-shipments.index'));
        $response->assertSessionHas('success', 'ピッキングを開始しました（ピッキング開始へ）');

        // ステータスがPICKINGに変更されたことを確認
        $this->assertDatabaseHas('shipment_plans', [
            'id' => $plan->id,
            'status' => 'PICKING',
        ]);
    }

    /**
     * @test
     * ALLOCATED以外のステータスではピッキング開始できない
     */
    public function cannot_start_picking_for_non_allocated()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']); // ALLOCATED以外

        $response = $this->post(route('allocated-shipments.start-picking', $plan));
        $response->assertStatus(404);

        // ステータスが変更されていないことを確認
        $this->assertDatabaseHas('shipment_plans', [
            'id' => $plan->id,
            'status' => 'PLANNED',
        ]);
    }

    /**
     * @test
     * 引当済み出荷予定一覧で検索ができる（キーワード）
     */
    public function admin_can_search_allocated_shipments_by_keyword()
    {
        $this->actingAsAdmin();

        $customer1 = Customer::factory()->create(['name' => 'テスト出荷先']);
        $customer2 = Customer::factory()->create(['name' => 'サンプル出荷先']);

        ShipmentPlan::factory()->create([
            'customer_id' => $customer1->id,
            'status' => 'ALLOCATED',
        ]);
        ShipmentPlan::factory()->create([
            'customer_id' => $customer2->id,
            'status' => 'ALLOCATED',
        ]);

        $response = $this->get('/allocated-shipments?keyword=テスト');

        $response->assertStatus(200);
        $response->assertViewIs('wms.allocated_shipments.index');
    }

    /**
     * @test
     * 引当済み出荷予定一覧で検索ができる（出荷予定日）
     */
    public function admin_can_search_allocated_shipments_by_date()
    {
        $this->actingAsAdmin();

        ShipmentPlan::factory()->create([
            'planned_ship_date' => '2023-01-10',
            'status' => 'ALLOCATED',
        ]);
        ShipmentPlan::factory()->create([
            'planned_ship_date' => '2023-01-15',
            'status' => 'ALLOCATED',
        ]);

        $response = $this->get('/allocated-shipments?planned_ship_date=2023-01-10');

        $response->assertStatus(200);
        $response->assertViewIs('wms.allocated_shipments.index');
    }
}
