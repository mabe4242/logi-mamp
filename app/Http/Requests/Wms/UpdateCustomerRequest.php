<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $customerId = $this->route('customer')->id;

        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:customers,code,' . $customerId,
            'postal_code' => 'nullable|string|max:20',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact_name' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '出荷先名を入力してください',
            'code.unique' => 'このコードは既に登録されています',
            'email.email' => 'メールアドレスは正しい形式で入力してください',
        ];
    }
}
