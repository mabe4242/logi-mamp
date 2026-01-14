<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\InboundPlan;
use App\Models\InboundPlanLine;
use App\Models\Location;
use App\Models\Product;
use App\Models\PutawayLine;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PutawayControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 入庫待ち一覧が表示できる
     */
    public function admin_can_view_putaway_index()
    {
        $this->actingAsAdmin();

        InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        InboundPlan::factory()->create(['status' => 'RECEIVING']); // これは表示されない

        $response = $this->get('/putaway');
        $response->assertStatus(200);
        $response->assertViewIs('wms.putaway.index');
    }

    /**
     * @test
     * 入庫画面が表示できる
     */
    public function admin_can_view_putaway_show()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $product = Product::factory()->create();
        InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'product_id' => $product->id,
            'received_qty' => 10,
        ]);
        Location::factory()->create();

        $response = $this->get("/putaway/{$plan->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.putaway.show');
    }

    /**
     * @test
     * WAITING_PUTAWAY以外のステータスでは入庫画面にアクセスできない
     */
    public function cannot_access_putaway_show_for_non_waiting_putaway_status()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);

        $response = $this->get("/putaway/{$plan->id}");

        $response->assertRedirect('/putaway');
        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 明細IDが未入力の場合、バリデーションエラーが表示される
     */
    public function line_id_is_required_when_storing_putaway()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $location = Location::factory()->create();

        $response = $this->post("/putaway/{$plan->id}/store", [
            'line_id' => '',
            'location_id' => $location->id,
            'qty' => 5,
        ]);

        $response->assertSessionHasErrors(['line_id']);
    }

    /**
     * @test
     * ロケーションIDが未入力の場合、バリデーションエラーが表示される
     */
    public function location_id_is_required_when_storing_putaway()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'received_qty' => 10,
        ]);

        $response = $this->post("/putaway/{$plan->id}/store", [
            'line_id' => $line->id,
            'location_id' => '',
            'qty' => 5,
        ]);

        $response->assertSessionHasErrors(['location_id']);
    }

    /**
     * @test
     * 数量が未入力の場合、バリデーションエラーが表示される
     */
    public function qty_is_required_when_storing_putaway()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'received_qty' => 10,
        ]);
        $location = Location::factory()->create();

        $response = $this->post("/putaway/{$plan->id}/store", [
            'line_id' => $line->id,
            'location_id' => $location->id,
            'qty' => '',
        ]);

        $response->assertSessionHasErrors(['qty']);
    }

    /**
     * @test
     * 入庫可能数量を超える場合、エラーが表示される
     */
    public function cannot_store_putaway_exceeding_remaining_qty()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'received_qty' => 10,
            'putaway_qty' => 5, // 入庫残は5
        ]);
        $location = Location::factory()->create();

        $response = $this->post("/putaway/{$plan->id}/store", [
            'line_id' => $line->id,
            'location_id' => $location->id,
            'qty' => 10, // 入庫残を超える
        ]);

        $response->assertSessionHasErrors(['qty']);
    }

    /**
     * @test
     * WAITING_PUTAWAY以外のステータスでは入庫できない
     */
    public function cannot_store_putaway_when_status_is_not_waiting_putaway()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
        ]);
        $location = Location::factory()->create();

        $response = $this->post("/putaway/{$plan->id}/store", [
            'line_id' => $line->id,
            'location_id' => $location->id,
            'qty' => 5,
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 別の入荷予定の明細は入庫できない
     */
    public function cannot_store_putaway_for_line_from_different_plan()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan1 = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $plan2 = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan1->id,
            'received_qty' => 10,
        ]);
        $location = Location::factory()->create();

        $response = $this->post("/putaway/{$plan2->id}/store", [
            'line_id' => $line->id,
            'location_id' => $location->id,
            'qty' => 5,
        ]);

        $response->assertStatus(404);
    }

    /**
     * @test
     * 入庫を実行できる
     */
    public function admin_can_store_putaway()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $product = Product::factory()->create();
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'product_id' => $product->id,
            'received_qty' => 10,
            'putaway_qty' => 0,
        ]);
        $location = Location::factory()->create();

        $response = $this->post("/putaway/{$plan->id}/store", [
            'line_id' => $line->id,
            'location_id' => $location->id,
            'qty' => 5,
        ]);

        $response->assertRedirect("/putaway/{$plan->id}");
        $response->assertSessionHas('success');

        // 明細の入庫済数が更新されている
        $line->refresh();
        $this->assertEquals(5, $line->putaway_qty);

        // 入庫実績が作成されている
        $this->assertDatabaseHas('putaway_lines', [
            'inbound_plan_id' => $plan->id,
            'inbound_plan_line_id' => $line->id,
            'location_id' => $location->id,
            'qty' => 5,
            'putaway_by_admin_id' => $admin->id,
        ]);

        // 在庫が増加している
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'location_id' => $location->id,
            'on_hand_qty' => 5,
        ]);
    }

    /**
     * @test
     * 全明細が入庫済みの場合、自動的にCOMPLETEDになる
     */
    public function inbound_plan_status_becomes_completed_when_all_lines_putaway()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        $product = Product::factory()->create();
        $line = InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'product_id' => $product->id,
            'received_qty' => 10,
            'putaway_qty' => 0,
        ]);
        $location = Location::factory()->create();

        // 全数量を入庫
        $this->post("/putaway/{$plan->id}/store", [
            'line_id' => $line->id,
            'location_id' => $location->id,
            'qty' => 10,
        ]);

        $plan->refresh();
        $this->assertEquals('COMPLETED', $plan->status);
    }

    /**
     * @test
     * 入庫残がある場合、入庫完了できない
     */
    public function cannot_complete_putaway_with_remaining_qty()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'received_qty' => 10,
            'putaway_qty' => 5, // 入庫残あり
        ]);

        $response = $this->post("/putaway/{$plan->id}/complete");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * WAITING_PUTAWAY以外のステータスでは入庫完了できない
     */
    public function cannot_complete_putaway_when_status_is_not_waiting_putaway()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'RECEIVING']);

        $response = $this->post("/putaway/{$plan->id}/complete");

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * @test
     * 入庫完了できる
     */
    public function admin_can_complete_putaway()
    {
        $this->actingAsAdmin();

        $plan = InboundPlan::factory()->create(['status' => 'WAITING_PUTAWAY']);
        InboundPlanLine::factory()->create([
            'inbound_plan_id' => $plan->id,
            'received_qty' => 10,
            'putaway_qty' => 10, // 全数量入庫済み
        ]);

        $response = $this->post("/putaway/{$plan->id}/complete");

        $response->assertRedirect('/putaway');
        $response->assertSessionHas('success', '入庫を完了しました');

        $plan->refresh();
        $this->assertEquals('COMPLETED', $plan->status);
    }
}
