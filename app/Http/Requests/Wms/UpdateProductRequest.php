<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $productId = $this->route('product')->id;

        return [
            'sku' => 'required|string|max:255|unique:products,sku,' . $productId,
            'barcode' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'sku.required' => 'SKUを入力してください',
            'sku.unique' => 'このSKUは既に登録されています',
            'name.required' => '商品名を入力してください',
            'unit.required' => '単位を入力してください',
        ];
    }
}
