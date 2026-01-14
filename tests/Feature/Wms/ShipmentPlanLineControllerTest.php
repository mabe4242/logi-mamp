<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentPlanLineControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 商品が未選択の場合、バリデーションエラーが表示される
     */
    public function product_id_is_required_when_creating_line()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);

        $response = $this->post("/shipment-plans/{$plan->id}/lines", [
            'product_id' => '',
            'planned_qty' => 10,
        ]);

        $response->assertSessionHasErrors(['product_id']);
    }

    /**
     * @test
     * 予定数量が未入力の場合、バリデーションエラーが表示される
     */
    public function planned_qty_is_required_when_creating_line()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $product = Product::factory()->create();

        $response = $this->post("/shipment-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => '',
        ]);

        $response->assertSessionHasErrors(['planned_qty']);
    }

    /**
     * @test
     * PLANNED以外のステータスでは明細を追加できない
     */
    public function cannot_add_line_when_status_is_not_planned()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'ALLOCATED']);
        $product = Product::factory()->create();

        $response = $this->post("/shipment-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => 10,
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 正しい情報で明細を追加できる
     */
    public function admin_can_create_shipment_plan_line()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $product = Product::factory()->create();

        $formData = [
            'product_id' => $product->id,
            'planned_qty' => 10,
        ];

        $response = $this->post("/shipment-plans/{$plan->id}/lines", $formData);

        $response->assertRedirect();
        $response->assertSessionHas('success', '明細を追加しました');

        $this->assertDatabaseHas('shipment_plan_lines', [
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 10,
        ]);
    }

    /**
     * @test
     * 同じ商品の明細を追加すると更新される（updateOrCreate）
     */
    public function adding_same_product_line_updates_existing_line()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $product = Product::factory()->create();

        // 最初の明細追加
        $this->post("/shipment-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => 10,
        ]);

        // 同じ商品で再度追加（数量を変更）
        $response = $this->post("/shipment-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => 20,
        ]);

        $response->assertRedirect();

        // 明細は1件のみで、数量が更新されている
        $this->assertDatabaseHas('shipment_plan_lines', [
            'shipment_plan_id' => $plan->id,
            'product_id' => $product->id,
            'planned_qty' => 20,
        ]);

        $this->assertEquals(1, $plan->lines()->count());
    }

    /**
     * @test
     * 予定数量が未入力の場合、更新時にバリデーションエラーが表示される
     */
    public function planned_qty_is_required_when_updating_line()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
        ]);

        $response = $this->patch("/shipment-plans/{$plan->id}/lines/{$line->id}", [
            'planned_qty' => '',
        ]);

        $response->assertSessionHasErrors(['planned_qty']);
    }

    /**
     * @test
     * 正しい情報で明細を更新できる
     */
    public function admin_can_update_shipment_plan_line()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
            'planned_qty' => 10,
        ]);

        $formData = [
            'planned_qty' => 20,
        ];

        $response = $this->patch("/shipment-plans/{$plan->id}/lines/{$line->id}", $formData);

        $response->assertRedirect();
        $response->assertSessionHas('success', '明細を更新しました');

        $this->assertDatabaseHas('shipment_plan_lines', [
            'id' => $line->id,
            'planned_qty' => 20,
        ]);
    }

    /**
     * @test
     * 明細を削除できる
     */
    public function admin_can_delete_shipment_plan_line()
    {
        $this->actingAsAdmin();

        $plan = ShipmentPlan::factory()->create(['status' => 'PLANNED']);
        $line = ShipmentPlanLine::factory()->create([
            'shipment_plan_id' => $plan->id,
        ]);

        $response = $this->delete("/shipment-plans/{$plan->id}/lines/{$line->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success', '明細を削除しました');

        $this->assertSoftDeleted('shipment_plan_lines', [
            'id' => $line->id,
        ]);
    }
}
