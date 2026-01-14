<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * ロケーション一覧が表示できる
     */
    public function admin_can_view_locations_index()
    {
        $this->actingAsAdmin();

        Location::factory()->count(3)->create();

        $response = $this->get('/locations');
        $response->assertStatus(200);
        $response->assertViewIs('wms.locations.index');
    }

    /**
     * @test
     * ロケーション作成画面が表示できる
     */
    public function admin_can_view_location_create_form()
    {
        $this->actingAsAdmin();

        $response = $this->get('/locations/create');
        $response->assertStatus(200);
        $response->assertViewIs('wms.locations.create');
    }

    /**
     * @test
     * ロケーションコードが未入力の場合、バリデーションエラーが表示される
     */
    public function code_is_required_when_creating_location()
    {
        $this->actingAsAdmin();

        $response = $this->post('/locations', [
            'code' => '',
            'name' => 'テストロケーション',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * ロケーションコードが重複している場合、バリデーションエラーが表示される
     */
    public function code_must_be_unique_when_creating_location()
    {
        $this->actingAsAdmin();

        Location::factory()->create(['code' => 'A-01']);

        $response = $this->post('/locations', [
            'code' => 'A-01',
            'name' => 'テストロケーション',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * 正しい情報でロケーションを作成できる
     */
    public function admin_can_create_location()
    {
        $this->actingAsAdmin();

        $formData = [
            'code' => 'A-01',
            'name' => 'テストロケーション',
            'note' => 'テスト用ロケーション',
        ];

        $response = $this->post('/locations', $formData);

        $response->assertRedirect('/locations');
        $response->assertSessionHas('success', 'ロケーションを登録しました');

        $this->assertDatabaseHas('locations', [
            'code' => 'A-01',
            'name' => 'テストロケーション',
        ]);
    }

    /**
     * @test
     * ロケーション詳細が表示できる
     */
    public function admin_can_view_location_detail()
    {
        $this->actingAsAdmin();

        $location = Location::factory()->create();

        $response = $this->get("/locations/{$location->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.locations.show');
        $response->assertViewHas('location', $location);
    }

    /**
     * @test
     * ロケーション編集画面が表示できる
     */
    public function admin_can_view_location_edit_form()
    {
        $this->actingAsAdmin();

        $location = Location::factory()->create();

        $response = $this->get("/locations/{$location->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('wms.locations.edit');
        $response->assertViewHas('location', $location);
    }

    /**
     * @test
     * ロケーションコードが未入力の場合、更新時にバリデーションエラーが表示される
     */
    public function code_is_required_when_updating_location()
    {
        $this->actingAsAdmin();

        $location = Location::factory()->create();

        $response = $this->put("/locations/{$location->id}", [
            'code' => '',
            'name' => '更新されたロケーション名',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * ロケーションコードが他のロケーションと重複している場合、更新時にバリデーションエラーが表示される
     */
    public function code_must_be_unique_when_updating_location()
    {
        $this->actingAsAdmin();

        $location1 = Location::factory()->create(['code' => 'A-01']);
        $location2 = Location::factory()->create(['code' => 'A-02']);

        $response = $this->put("/locations/{$location2->id}", [
            'code' => 'A-01',
            'name' => '更新されたロケーション名',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * 正しい情報でロケーションを更新できる
     */
    public function admin_can_update_location()
    {
        $this->actingAsAdmin();

        $location = Location::factory()->create([
            'code' => 'A-01',
            'name' => '元のロケーション名',
        ]);

        $formData = [
            'code' => 'A-01',
            'name' => '更新されたロケーション名',
            'note' => '更新された備考',
        ];

        $response = $this->put("/locations/{$location->id}", $formData);

        $response->assertRedirect("/locations/{$location->id}");
        $response->assertSessionHas('success', 'ロケーションを更新しました');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => '更新されたロケーション名',
        ]);
    }

    /**
     * @test
     * ロケーションを削除できる（論理削除）
     */
    public function admin_can_delete_location()
    {
        $this->actingAsAdmin();

        $location = Location::factory()->create();

        $response = $this->delete("/locations/{$location->id}");

        $response->assertRedirect('/locations');
        $response->assertSessionHas('success', 'ロケーションを削除しました');

        $this->assertSoftDeleted('locations', [
            'id' => $location->id,
        ]);
    }

    /**
     * @test
     * ロケーション一覧で検索ができる
     */
    public function admin_can_search_locations_by_keyword()
    {
        $this->actingAsAdmin();

        Location::factory()->create(['code' => 'A-01', 'name' => 'エリアA']);
        Location::factory()->create(['code' => 'B-01', 'name' => 'エリアB']);

        $response = $this->get('/locations?keyword=A-01');

        $response->assertStatus(200);
        $response->assertViewIs('wms.locations.index');
    }
}
