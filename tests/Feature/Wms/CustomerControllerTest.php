<?php

namespace Tests\Feature\Wms;

use App\Models\Admin;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        $admin = Admin::factory()->create();
        return $this->actingAs($admin, 'admin');
    }

    /**
     * @test
     * 出荷先一覧が表示できる
     */
    public function admin_can_view_customers_index()
    {
        $this->actingAsAdmin();

        Customer::factory()->count(3)->create();

        $response = $this->get('/customers');
        $response->assertStatus(200);
        $response->assertViewIs('wms.customers.index');
    }

    /**
     * @test
     * 出荷先作成画面が表示できる
     */
    public function admin_can_view_customer_create_form()
    {
        $this->actingAsAdmin();

        $response = $this->get('/customers/create');
        $response->assertStatus(200);
        $response->assertViewIs('wms.customers.create');
    }

    /**
     * @test
     * 出荷先名が未入力の場合、バリデーションエラーが表示される
     */
    public function name_is_required_when_creating_customer()
    {
        $this->actingAsAdmin();

        $response = $this->post('/customers', [
            'name' => '',
            'code' => 'CUST001',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * @test
     * 出荷先コードが重複している場合、バリデーションエラーが表示される
     */
    public function code_must_be_unique_when_creating_customer()
    {
        $this->actingAsAdmin();

        Customer::factory()->create(['code' => 'CUST001']);

        $response = $this->post('/customers', [
            'name' => 'テスト出荷先',
            'code' => 'CUST001',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * メールアドレスが不正な形式の場合、バリデーションエラーが表示される
     */
    public function email_must_be_valid_format_when_creating_customer()
    {
        $this->actingAsAdmin();

        $response = $this->post('/customers', [
            'name' => 'テスト出荷先',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * @test
     * 正しい情報で出荷先を作成できる
     */
    public function admin_can_create_customer()
    {
        $this->actingAsAdmin();

        $formData = [
            'name' => 'テスト出荷先',
            'code' => 'CUST001',
            'postal_code' => '123-4567',
            'address1' => '東京都渋谷区',
            'address2' => 'テストビル1F',
            'phone' => '03-1234-5678',
            'email' => 'test@example.com',
            'contact_name' => '山田太郎',
            'shipping_method' => 'ヤマト',
            'note' => 'テスト用出荷先',
        ];

        $response = $this->post('/customers', $formData);

        $response->assertRedirect('/customers');
        $response->assertSessionHas('success', '出荷先を登録しました');

        $this->assertDatabaseHas('customers', [
            'name' => 'テスト出荷先',
            'code' => 'CUST001',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * @test
     * 出荷先詳細が表示できる
     */
    public function admin_can_view_customer_detail()
    {
        $this->actingAsAdmin();

        $customer = Customer::factory()->create();

        $response = $this->get("/customers/{$customer->id}");
        $response->assertStatus(200);
        $response->assertViewIs('wms.customers.show');
        $response->assertViewHas('customer', $customer);
    }

    /**
     * @test
     * 出荷先編集画面が表示できる
     */
    public function admin_can_view_customer_edit_form()
    {
        $this->actingAsAdmin();

        $customer = Customer::factory()->create();

        $response = $this->get("/customers/{$customer->id}/edit");
        $response->assertStatus(200);
        $response->assertViewIs('wms.customers.edit');
        $response->assertViewHas('customer', $customer);
    }

    /**
     * @test
     * 出荷先名が未入力の場合、更新時にバリデーションエラーが表示される
     */
    public function name_is_required_when_updating_customer()
    {
        $this->actingAsAdmin();

        $customer = Customer::factory()->create();

        $response = $this->put("/customers/{$customer->id}", [
            'name' => '',
            'code' => 'CUST001',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * @test
     * 出荷先コードが他の出荷先と重複している場合、更新時にバリデーションエラーが表示される
     */
    public function code_must_be_unique_when_updating_customer()
    {
        $this->actingAsAdmin();

        $customer1 = Customer::factory()->create(['code' => 'CUST001']);
        $customer2 = Customer::factory()->create(['code' => 'CUST002']);

        $response = $this->put("/customers/{$customer2->id}", [
            'name' => '更新された出荷先名',
            'code' => 'CUST001',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    /**
     * @test
     * 正しい情報で出荷先を更新できる
     */
    public function admin_can_update_customer()
    {
        $this->actingAsAdmin();

        $customer = Customer::factory()->create([
            'name' => '元の出荷先名',
            'code' => 'CUST001',
        ]);

        $formData = [
            'name' => '更新された出荷先名',
            'code' => 'CUST001',
            'email' => 'updated@example.com',
            'note' => '更新された備考',
        ];

        $response = $this->put("/customers/{$customer->id}", $formData);

        $response->assertRedirect("/customers/{$customer->id}");
        $response->assertSessionHas('success', '出荷先を更新しました');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => '更新された出荷先名',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * @test
     * 出荷先を削除できる（論理削除）
     */
    public function admin_can_delete_customer()
    {
        $this->actingAsAdmin();

        $customer = Customer::factory()->create();

        $response = $this->delete("/customers/{$customer->id}");

        $response->assertRedirect('/customers');
        $response->assertSessionHas('success', '出荷先を削除しました');

        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    /**
     * @test
     * 出荷先一覧で検索ができる
     */
    public function admin_can_search_customers_by_keyword()
    {
        $this->actingAsAdmin();

        Customer::factory()->create(['name' => 'テスト出荷先1', 'code' => 'CUST001']);
        Customer::factory()->create(['name' => 'サンプル出荷先', 'code' => 'CUST002']);

        $response = $this->get('/customers?keyword=テスト');

        $response->assertStatus(200);
        $response->assertViewIs('wms.customers.index');
    }
}
