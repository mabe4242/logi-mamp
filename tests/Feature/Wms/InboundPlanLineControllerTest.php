<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundPlanLineControllerTest extends TestCase
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

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);

        $response = $this->post("/inbound-plans/{$plan->id}/lines", [
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

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $product = Product::factory()->create();

        $response = $this->post("/inbound-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => '',
        ]);

        $response->assertSessionHasErrors(['planned_qty']);
    }

    /**
     * @test
     * 予定数量が1未満の場合、バリデーションエラーが表示される
     */
    public function planned_qty_must_be_at_least_1_when_creating_line()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $product = Product::factory()->create();

        $response = $this->post("/inbound-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => 0,
        ]);

        $response->assertSessionHasErrors(['planned_qty']);
    }

    /**
     * @test
     * 確定済みの入荷予定には明細を追加できない
     */
    public function cannot_add_line_to_confirmed_inbound_plan()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $product = Product::factory()->create();

        $response = $this->post("/inbound-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => 10,
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 正しい情報で明細を追加できる
     */
    public function admin_can_create_inbound_plan_line()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $product = Product::factory()->create();

        $formData = [
            'product_id' => $product->id,
            'planned_qty' => 10,
            'note' => 'テスト用明細',
        ];

        $response = $this->post("/inbound-plans/{$plan->id}/lines", $formData);

        $response->assertRedirect("/inbound-plans/{$plan->id}");
        $response->assertSessionHas('success', '明細を追加しました');

        $this->assertDatabaseHas('inbound_plan_lines', [
            'inbound_plan_id' => $plan->id,
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

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $product = Product::factory()->create();

        // 最初の明細追加
        $this->post("/inbound-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => 10,
        ]);

        // 同じ商品で再度追加（数量を変更）
        $response = $this->post("/inbound-plans/{$plan->id}/lines", [
            'product_id' => $product->id,
            'planned_qty' => 20,
        ]);

        $response->assertRedirect("/inbound-plans/{$plan->id}");

        // 明細は1件のみで、数量が更新されている
        $this->assertDatabaseHas('inbound_plan_lines', [
            'inbound_plan_id' => $plan->id,
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

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
        ]);

        $response = $this->patch("/inbound-plans/{$plan->id}/lines/{$line->id}", [
            'planned_qty' => '',
        ]);

        $response->assertSessionHasErrors(['planned_qty']);
    }

    /**
     * @test
     * 確定済みの入荷予定の明細は編集できない
     */
    public function cannot_update_line_of_confirmed_inbound_plan()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
        ]);

        $response = $this->patch("/inbound-plans/{$plan->id}/lines/{$line->id}", [
            'planned_qty' => 20,
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 正しい情報で明細を更新できる
     */
    public function admin_can_update_inbound_plan_line()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'planned_qty' => 10,
        ]);

        $formData = [
            'planned_qty' => 20,
            'note' => '更新された備考',
        ];

        $response = $this->patch("/inbound-plans/{$plan->id}/lines/{$line->id}", $formData);

        $response->assertRedirect("/inbound-plans/{$plan->id}");
        $response->assertSessionHas('success', '明細を更新しました');

        $this->assertDatabaseHas('inbound_plan_lines', [
            'id' => $line->id,
            'planned_qty' => 20,
        ]);
    }

    /**
     * @test
     * 別の入荷予定の明細は更新できない
     */
    public function cannot_update_line_from_different_inbound_plan()
    {
        $this->actingAsAdmin();

        $plan1 = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $plan2 = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan1->id,
        ]);

        $response = $this->patch("/inbound-plans/{$plan2->id}/lines/{$line->id}", [
            'planned_qty' => 20,
        ]);

        $response->assertStatus(404);
    }

    /**
     * @test
     * 確定済みの入荷予定の明細は削除できない
     */
    public function cannot_delete_line_of_confirmed_inbound_plan()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
        ]);

        $response = $this->delete("/inbound-plans/{$plan->id}/lines/{$line->id}");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 明細を削除できる
     */
    public function admin_can_delete_inbound_plan_line()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
        ]);

        $response = $this->delete("/inbound-plans/{$plan->id}/lines/{$line->id}");

        $response->assertRedirect("/inbound-plans/{$plan->id}");
        $response->assertSessionHas('success', '明細を削除しました');

        $this->assertSoftDeleted('inbound_plan_lines', [
            'id' => $line->id,
        ]);
    }

    /**
     * @test
     * 別の入荷予定の明細は削除できない
     */
    public function cannot_delete_line_from_different_inbound_plan()
    {
        $this->actingAsAdmin();

        $plan1 = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $plan2 = InboundPlan::factory()->create(['status' => 'DRAFT']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan1->id,
        ]);

        $response = $this->delete("/inbound-plans/{$plan2->id}/lines/{$line->id}");

        $response->assertStatus(404);
    }
}
