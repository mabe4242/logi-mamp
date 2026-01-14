<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 出荷予定一覧が表示できる
     */
    public function admin_can_view_shipment_plans_index()
    {
        $this->actingAsAdmin();

        ShipmentPlan::factory()->count(3)->create();

        $response = $this->get('/shipment-plans');
        $response->assertStatus(200);
        $response->assertViewIs('wms.shipment_plans.index');
    }

    /**
     * @test
     * 出荷予定作成画面が表示できる
     */
    public function admin_can_view_shipment_plan_create_form()
    {
        $this->actingAsAdmin();

        Customer::factory()->count(2)->create();

        $response = $this->get('/shipment-plans/create');
        $response->assertStatus(200);
        $response->assertViewIs('wms.shipment_plans.create');
    }

    /**
     * @test
     * 出荷先が未選択の場合、バリデーションエラーが表示される
     */
    public function customer_id_is_required_when_creating_shipment_plan()
    {
        $this->actingAsAdmin();

        $response = $this->post('/shipment-plans', [
            'customer_id' => '',
            'planned_ship_date' => '2024-01-01',
        ]);

        $response->assertSessionHasErrors(['customer_id']);
    }

    /**
     * @test
     * 正しい情報で出荷予定を作成できる
     */
    public function admin_can_create_shipment_plan()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $customer = Customer::factory()->create();

        $formData = [
            'customer_id' => $customer->id,
            'planned_ship_date' => '2024-01-15',
            'note' => 'テスト用出荷予定',
        ];

        $response = $this->post('/shipment-plans', $formData);

        $response->assertRedirect();
        $response->assertSessionHas('success', '出荷予定を作成しました。');

        $this->assertDatabaseHas('shipment_plans', [
            'customer_id' => $customer->id,
            'planned_ship_date' => '2024-01-15',
            'status' => 'PLANNED',
            'created_by_admin_id' => $admin->id,
        ]);
    }

    /**
     * @test
     * 出荷予定詳細が表示できる
     */
    public function admin_can_view_shipment_plan_detail()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create();

        $response = $this->get("/shipment-plans/{$plan->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.shipment_plans.show');
        $response->assertViewHas('shipment_plan');
    }

    /**
     * @test
     * 出荷予定編集画面が表示できる
     */
    public function admin_can_view_shipment_plan_edit_form()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create();

        $response = $this->get("/shipment-plans/{$plan->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('wms.shipment_plans.edit');
        $response->assertViewHas('shipment_plan', $plan);
    }

    /**
     * @test
     * 正しい情報で出荷予定を更新できる
     */
    public function admin_can_update_shipment_plan()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create([
            'status' => 'PLANNED',
        ]);
        $newCustomer = Customer::factory()->create();

        $formData = [
            'customer_id' => $newCustomer->id,
            'planned_ship_date' => '2024-02-01',
            'note' => '更新された備考',
        ];

        $response = $this->put("/shipment-plans/{$plan->id}", $formData);

        $response->assertRedirect("/shipment-plans/{$plan->id}");
        $response->assertSessionHas('success', '更新しました');

        $this->assertDatabaseHas('shipment_plans', [
            'id' => $plan->id,
            'customer_id' => $newCustomer->id,
            'planned_ship_date' => '2024-02-01',
        ]);
    }

    /**
     * @test
     * 出荷予定を削除できる（論理削除）
     */
    public function admin_can_delete_shipment_plan()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create();

        $response = $this->delete("/shipment-plans/{$plan->id}");

        $response->assertRedirect('/shipment-plans');
        $response->assertSessionHas('success', '削除しました');

        $this->assertSoftDeleted('shipment_plans', [
            'id' => $plan->id,
        ]);
    }

    /**
     * @test
     * PLANNED以外のステータスでは在庫割り当てできない
     */
    public function cannot_allocate_when_status_is_not_planned()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'ALLOCATED']);

        $response = $this->post("/shipment-plans/{$plan->id}/allocate");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 在庫が不足している場合、在庫割り当てできない
     */
    public function cannot_allocate_when_stock_is_insufficient()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $product = Product::factory()->create();
        ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 100,
        ]);

        // 在庫を5しか作らない（必要100に対して不足）
        Stock::factory()->create([
            'product_id' => $product->id,
            'on_hand_qty' => 5,
            'reserved_qty' => 0,
        ]);

        $response = $this->post("/shipment-plans/{$plan->id}/allocate");

        $response->assertSessionHasErrors();
    }

    /**
     * @test
     * 在庫割り当てできる
     */
    public function admin_can_allocate_stock()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $product = Product::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
        ]);

        // 十分な在庫を作成
        Stock::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'on_hand_qty' => 20,
            'reserved_qty' => 0,
        ]);

        $response = $this->post("/shipment-plans/{$plan->id}/allocate");

        $response->assertRedirect("/shipment-plans/{$plan->id}");
        $response->assertSessionHas('success', '在庫を引当しました');

        $plan->refresh();
        $this->assertEquals('ALLOCATED', $plan->status);

        // 在庫が予約されている
        $stock = Stock::where('product_id', $product->id)->first();
        $this->assertEquals(10, $stock->reserved_qty);
    }

    /**
     * @test
     * ALLOCATED以外のステータスでは引当解除できない
     */
    public function cannot_deallocate_when_status_is_not_allocated()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);

        $response = $this->post("/shipment-plans/{$plan->id}/deallocate");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 引当解除できる
     */
    public function admin_can_deallocate_stock()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'ALLOCATED']);
        $product = Product::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
        ]);

        // 予約済み在庫を作成
        $stock = Stock::factory()->create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'on_hand_qty' => 20,
            'reserved_qty' => 10,
        ]);

        $response = $this->post("/shipment-plans/{$plan->id}/deallocate");

        $response->assertRedirect("/shipment-plans/{$plan->id}");
        $response->assertSessionHas('success', '在庫引当を解除しました');

        $plan->refresh();
        $this->assertEquals('PLANNED', $plan->status);

        // 予約が解除されている
        $stock->refresh();
        $this->assertEquals(0, $stock->reserved_qty);
    }

    /**
     * @test
     * 出荷予定一覧で検索ができる
     */
    public function admin_can_search_shipment_plans_by_keyword()
    {
        $this->actingAsAdmin();

        $customer1 = Customer::factory()->create(['name' => 'テスト出荷先']);
        $customer2 = Customer::factory()->create(['name' => 'サンプル出荷先']);

        ShipmentPlan::factory()->create(['customer_id' => $customer1->id]);
        ShipmentPlan::factory()->create(['customer_id' => $customer2->id]);

        $response = $this->get('/shipment-plans?keyword=テスト');

        $response->assertStatus(200);
        $response->assertViewIs('wms.shipment_plans.index');
    }
}
