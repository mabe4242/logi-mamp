<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\InboundPlan;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 入荷予定一覧が表示できる
     */
    public function admin_can_view_inbound_plans_index()
    {
        $this->actingAsAdmin();

        InboundPlan::factory()->count(3)->create();

        $response = $this->get('/inbound-plans');
        $response->assertStatus(200);
        $response->assertViewIs('wms.inbound_plans.index');
    }

    /**
     * @test
     * 入荷予定作成画面が表示できる
     */
    public function admin_can_view_inbound_plan_create_form()
    {
        $this->actingAsAdmin();

        Supplier::factory()->count(2)->create();

        $response = $this->get('/inbound-plans/create');
        $response->assertStatus(200);
        $response->assertViewIs('wms.inbound_plans.create');
    }

    /**
     * @test
     * 仕入先が未選択の場合、バリデーションエラーが表示される
     */
    public function supplier_id_is_required_when_creating_inbound_plan()
    {
        $this->actingAsAdmin();

        $response = $this->post('/inbound-plans', [
            'supplier_id' => '',
            'planned_date' => '2024-01-01',
        ]);

        $response->assertSessionHasErrors(['supplier_id']);
    }

    /**
     * @test
     * 存在しない仕入先IDの場合、バリデーションエラーが表示される
     */
    public function supplier_id_must_exist_when_creating_inbound_plan()
    {
        $this->actingAsAdmin();

        $response = $this->post('/inbound-plans', [
            'supplier_id' => 99999,
            'planned_date' => '2024-01-01',
        ]);

        $response->assertSessionHasErrors(['supplier_id']);
    }

    /**
     * @test
     * 正しい情報で入荷予定を作成できる
     */
    public function admin_can_create_inbound_plan()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $supplier = Supplier::factory()->create();

        $formData = [
            'supplier_id' => $supplier->id,
            'planned_date' => '2024-01-15',
            'note' => 'テスト用入荷予定',
        ];

        $response = $this->post('/inbound-plans', $formData);

        $response->assertRedirect();
        $response->assertSessionHas('success', '入荷予定を作成しました。次に明細（商品）を追加してください。');

        $this->assertDatabaseHas('inbound_plans', [
            'supplier_id' => $supplier->id,
            'planned_date' => '2024-01-15',
            'status' => 'DRAFT',
            'created_by_admin_id' => $admin->id,
        ]);
    }

    /**
     * @test
     * 入荷予定詳細が表示できる
     */
    public function admin_can_view_inbound_plan_detail()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create();

        $response = $this->get("/inbound-plans/{$plan->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.inbound_plans.show');
        $response->assertViewHas('inbound_plan');
    }

    /**
     * @test
     * 入荷予定編集画面が表示できる
     */
    public function admin_can_view_inbound_plan_edit_form()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create();

        $response = $this->get("/inbound-plans/{$plan->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('wms.inbound_plans.edit');
        $response->assertViewHas('inbound_plan', $plan);
    }

    /**
     * @test
     * 正しい情報で入荷予定を更新できる
     */
    public function admin_can_update_inbound_plan()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create([
            'status' => 'DRAFT',
        ]);
        $newSupplier = Supplier::factory()->create();

        $formData = [
            'supplier_id' => $newSupplier->id,
            'planned_date' => '2024-02-01',
            'note' => '更新された備考',
            'status' => 'DRAFT',
        ];

        $response = $this->put("/inbound-plans/{$plan->id}", $formData);

        $response->assertRedirect("/inbound-plans/{$plan->id}");
        $response->assertSessionHas('success', '入荷予定を更新しました');

        $this->assertDatabaseHas('inbound_plans', [
            'id' => $plan->id,
            'supplier_id' => $newSupplier->id,
            'planned_date' => '2024-02-01',
        ]);
    }

    /**
     * @test
     * 入荷予定を削除できる（論理削除）
     */
    public function admin_can_delete_inbound_plan()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create();

        $response = $this->delete("/inbound-plans/{$plan->id}");

        $response->assertRedirect('/inbound-plans');
        $response->assertSessionHas('success', '入荷予定を削除しました');

        $this->assertSoftDeleted('inbound_plans', [
            'id' => $plan->id,
        ]);
    }

    /**
     * @test
     * 明細がない状態で確定しようとするとエラーが表示される
     */
    public function cannot_confirm_inbound_plan_without_lines()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create([
            'status' => 'DRAFT',
        ]);

        $response = $this->post("/inbound-plans/{$plan->id}/confirm");

        $response->assertSessionHasErrors(['lines']);
    }

    /**
     * @test
     * 明細がある状態で入荷予定を確定できる
     */
    public function admin_can_confirm_inbound_plan_with_lines()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create([
            'status' => 'DRAFT',
        ]);
        $product = Product::factory()->create();
        $plan->lines()->create([
            'product_id' => $product->id,
            'planned_qty' => 10,
        ]);

        $response = $this->post("/inbound-plans/{$plan->id}/confirm");

        $response->assertRedirect("/inbound-plans/{$plan->id}");
        $response->assertSessionHas('success', '入荷予定を確定しました（入荷作業中へ）');

        $this->assertDatabaseHas('inbound_plans', [
            'id' => $plan->id,
            'status' => 'RECEIVING',
        ]);
    }

    /**
     * @test
     * 既に確定済みの入荷予定は再度確定できない
     */
    public function cannot_confirm_already_confirmed_inbound_plan()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create([
            'status' => 'RECEIVING',
        ]);

        $response = $this->post("/inbound-plans/{$plan->id}/confirm");

        $response->assertSessionHas('success', 'すでに確定済みです');
    }

    /**
     * @test
     * 入荷予定一覧で検索ができる
     */
    public function admin_can_search_inbound_plans_by_keyword()
    {
        $this->actingAsAdmin();

        $supplier1 = Supplier::factory()->create(['name' => 'テスト仕入先']);
        $supplier2 = Supplier::factory()->create(['name' => 'サンプル仕入先']);

        InboundPlan::factory()->create(['supplier_id' => $supplier1->id]);
        InboundPlan::factory()->create(['supplier_id' => $supplier2->id]);

        $response = $this->get('/inbound-plans?keyword=テスト');

        $response->assertStatus(200);
        $response->assertViewIs('wms.inbound_plans.index');
    }
}
