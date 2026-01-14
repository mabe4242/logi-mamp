<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 商品一覧が表示できる
     */
    public function admin_can_view_products_index()
    {
        $this->actingAsAdmin();

        Product::factory()->count(3)->create();

        $response = $this->get('/products');
        $response->assertStatus(200);
        $response->assertViewIs('wms.products.index');
    }

    /**
     * @test
     * 商品作成画面が表示できる
     */
    public function admin_can_view_product_create_form()
    {
        $this->actingAsAdmin();

        $response = $this->get('/products/create');
        $response->assertStatus(200);
        $response->assertViewIs('wms.products.create');
    }

    /**
     * @test
     * SKUが未入力の場合、バリデーションエラーが表示される
     */
    public function sku_is_required_when_creating_product()
    {
        $this->actingAsAdmin();

        $response = $this->post('/products', [
            'sku' => '',
            'name' => 'テスト商品',
            'unit' => '個',
        ]);

        $response->assertSessionHasErrors(['sku']);
    }

    /**
     * @test
     * 商品名が未入力の場合、バリデーションエラーが表示される
     */
    public function name_is_required_when_creating_product()
    {
        $this->actingAsAdmin();

        $response = $this->post('/products', [
            'sku' => 'TEST001',
            'name' => '',
            'unit' => '個',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * @test
     * 単位が未入力の場合、バリデーションエラーが表示される
     */
    public function unit_is_required_when_creating_product()
    {
        $this->actingAsAdmin();

        $response = $this->post('/products', [
            'sku' => 'TEST001',
            'name' => 'テスト商品',
            'unit' => '',
        ]);

        $response->assertSessionHasErrors(['unit']);
    }

    /**
     * @test
     * SKUが重複している場合、バリデーションエラーが表示される
     */
    public function sku_must_be_unique_when_creating_product()
    {
        $this->actingAsAdmin();

        Product::factory()->create(['sku' => 'TEST001']);

        $response = $this->post('/products', [
            'sku' => 'TEST001',
            'name' => 'テスト商品',
            'unit' => '個',
        ]);

        $response->assertSessionHasErrors(['sku']);
    }

    /**
     * @test
     * 正しい情報で商品を作成できる
     */
    public function admin_can_create_product()
    {
        $this->actingAsAdmin();

        $formData = [
            'sku' => 'TEST001',
            'barcode' => '1234567890123',
            'name' => 'テスト商品',
            'unit' => '個',
            'note' => 'テスト用商品',
        ];

        $response = $this->post('/products', $formData);

        $response->assertRedirect('/products');
        $response->assertSessionHas('success', '商品を登録しました');

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST001',
            'name' => 'テスト商品',
            'unit' => '個',
        ]);
    }

    /**
     * @test
     * 商品詳細が表示できる
     */
    public function admin_can_view_product_detail()
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create();

        $response = $this->get("/products/{$product->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.products.show');
        $response->assertViewHas('product', $product);
    }

    /**
     * @test
     * 商品編集画面が表示できる
     */
    public function admin_can_view_product_edit_form()
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create();

        $response = $this->get("/products/{$product->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('wms.products.edit');
        $response->assertViewHas('product', $product);
    }

    /**
     * @test
     * SKUが未入力の場合、更新時にバリデーションエラーが表示される
     */
    public function sku_is_required_when_updating_product()
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create();

        $response = $this->put("/products/{$product->id}", [
            'sku' => '',
            'name' => '更新された商品名',
            'unit' => '個',
        ]);

        $response->assertSessionHasErrors(['sku']);
    }

    /**
     * @test
     * SKUが他の商品と重複している場合、更新時にバリデーションエラーが表示される
     */
    public function sku_must_be_unique_when_updating_product()
    {
        $this->actingAsAdmin();

        $product1 = Product::factory()->create(['sku' => 'TEST001']);
        $product2 = Product::factory()->create(['sku' => 'TEST002']);

        $response = $this->put("/products/{$product2->id}", [
            'sku' => 'TEST001',
            'name' => '更新された商品名',
            'unit' => '個',
        ]);

        $response->assertSessionHasErrors(['sku']);
    }

    /**
     * @test
     * 正しい情報で商品を更新できる
     */
    public function admin_can_update_product()
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create([
            'sku' => 'TEST001',
            'name' => '元の商品名',
            'unit' => '個',
        ]);

        $formData = [
            'sku' => 'TEST001',
            'name' => '更新された商品名',
            'unit' => '箱',
            'note' => '更新された備考',
        ];

        $response = $this->put("/products/{$product->id}", $formData);

        $response->assertRedirect("/products/{$product->id}");
        $response->assertSessionHas('success', '商品を更新しました');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => '更新された商品名',
            'unit' => '箱',
        ]);
    }

    /**
     * @test
     * 商品を削除できる（論理削除）
     */
    public function admin_can_delete_product()
    {
        $this->actingAsAdmin();

        $product = Product::factory()->create();

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect('/products');
        $response->assertSessionHas('success', '商品を削除しました');

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * @test
     * 商品一覧で検索ができる
     */
    public function admin_can_search_products_by_keyword()
    {
        $this->actingAsAdmin();

        Product::factory()->create(['sku' => 'TEST001', 'name' => 'テスト商品1']);
        Product::factory()->create(['sku' => 'TEST002', 'name' => 'サンプル商品']);
        Product::factory()->create(['sku' => 'OTHER001', 'name' => 'その他商品']);

        $response = $this->get('/products?keyword=TEST');

        $response->assertStatus(200);
        $response->assertViewIs('wms.products.index');
    }
}
