<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 仕入先一覧が表示できる
     */
    public function admin_can_view_suppliers_index()
    {
        $this->actingAsAdmin();

        Supplier::factory()->count(3)->create();

        $response = $this->get('/suppliers');
        $response->assertStatus(200);
        $response->assertViewIs('wms.suppliers.index');
    }

    /**
     * @test
     * 仕入先作成画面が表示できる
     */
    public function admin_can_view_supplier_create_form()
    {
        $this->actingAsAdmin();

        $response = $this->get('/suppliers/create');
        $response->assertStatus(200);
        $response->assertViewIs('wms.suppliers.create');
    }

    /**
     * @test
     * 仕入先名が未入力の場合、バリデーションエラーが表示される
     */
    public function name_is_required_when_creating_supplier()
    {
        $this->actingAsAdmin();

        $response = $this->post('/suppliers', [
            'name' => '',
            'code' => 'SUPP001',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * @test
     * 仕入先コードが重複している場合、バリデーションエラーが表示される
     */
    public function code_must_be_unique_when_creating_supplier()
    {
        $this->actingAsAdmin();

        Supplier::factory()->create(['code' => 'SUPP001']);

        $response = $this->post('/suppliers', [
            'name' => 'テスト仕入先',
            'code' => 'SUPP001',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * メールアドレスが不正な形式の場合、バリデーションエラーが表示される
     */
    public function email_must_be_valid_format_when_creating_supplier()
    {
        $this->actingAsAdmin();

        $response = $this->post('/suppliers', [
            'name' => 'テスト仕入先',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * @test
     * 正しい情報で仕入先を作成できる
     */
    public function admin_can_create_supplier()
    {
        $this->actingAsAdmin();

        $formData = [
            'name' => 'テスト仕入先',
            'code' => 'SUPP001',
            'postal_code' => '123-4567',
            'address1' => '東京都渋谷区',
            'address2' => 'テストビル1F',
            'phone' => '03-1234-5678',
            'email' => 'test@example.com',
            'contact_name' => '山田太郎',
            'note' => 'テスト用仕入先',
        ];

        $response = $this->post('/suppliers', $formData);

        $response->assertRedirect('/suppliers');
        $response->assertSessionHas('success', '仕入先を登録しました');

        $this->assertDatabaseHas('suppliers', [
            'name' => 'テスト仕入先',
            'code' => 'SUPP001',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * @test
     * 仕入先詳細が表示できる
     */
    public function admin_can_view_supplier_detail()
    {
        $this->actingAsAdmin();

        $supplier = Supplier::factory()->create();

        $response = $this->get("/suppliers/{$supplier->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.suppliers.show');
        $response->assertViewHas('supplier', $supplier);
    }

    /**
     * @test
     * 仕入先編集画面が表示できる
     */
    public function admin_can_view_supplier_edit_form()
    {
        $this->actingAsAdmin();

        $supplier = Supplier::factory()->create();

        $response = $this->get("/suppliers/{$supplier->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('wms.suppliers.edit');
        $response->assertViewHas('supplier', $supplier);
    }

    /**
     * @test
     * 仕入先名が未入力の場合、更新時にバリデーションエラーが表示される
     */
    public function name_is_required_when_updating_supplier()
    {
        $this->actingAsAdmin();

        $supplier = Supplier::factory()->create();

        $response = $this->put("/suppliers/{$supplier->id}", [
            'name' => '',
            'code' => 'SUPP001',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * @test
     * 仕入先コードが他の仕入先と重複している場合、更新時にバリデーションエラーが表示される
     */
    public function code_must_be_unique_when_updating_supplier()
    {
        $this->actingAsAdmin();

        $supplier1 = Supplier::factory()->create(['code' => 'SUPP001']);
        $supplier2 = Supplier::factory()->create(['code' => 'SUPP002']);

        $response = $this->put("/suppliers/{$supplier2->id}", [
            'name' => '更新された仕入先名',
            'code' => 'SUPP001',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * 正しい情報で仕入先を更新できる
     */
    public function admin_can_update_supplier()
    {
        $this->actingAsAdmin();

        $supplier = Supplier::factory()->create([
            'name' => '元の仕入先名',
            'code' => 'SUPP001',
        ]);

        $formData = [
            'name' => '更新された仕入先名',
            'code' => 'SUPP001',
            'email' => 'updated@example.com',
            'note' => '更新された備考',
        ];

        $response = $this->put("/suppliers/{$supplier->id}", $formData);

        $response->assertRedirect("/suppliers/{$supplier->id}");
        $response->assertSessionHas('success', '仕入先を更新しました');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => '更新された仕入先名',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * @test
     * 仕入先を削除できる（論理削除）
     */
    public function admin_can_delete_supplier()
    {
        $this->actingAsAdmin();

        $supplier = Supplier::factory()->create();

        $response = $this->delete("/suppliers/{$supplier->id}");

        $response->assertRedirect('/suppliers');
        $response->assertSessionHas('success', '仕入先を削除しました');

        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    /**
     * @test
     * 仕入先一覧で検索ができる
     */
    public function admin_can_search_suppliers_by_keyword()
    {
        $this->actingAsAdmin();

        Supplier::factory()->create(['name' => 'テスト仕入先1', 'code' => 'SUPP001']);
        Supplier::factory()->create(['name' => 'サンプル仕入先', 'code' => 'SUPP002']);

        $response = $this->get('/suppliers?keyword=テスト');

        $response->assertStatus(200);
        $response->assertViewIs('wms.suppliers.index');
    }
}
