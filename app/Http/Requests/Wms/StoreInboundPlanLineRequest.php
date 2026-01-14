<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class StoreInboundPlanLineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'planned_qty' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => '商品を選択してください',
            'product_id.exists' => '選択された商品が存在しません',
            'planned_qty.required' => '予定数量を入力してください',
            'planned_qty.integer' => '予定数量は数値で入力してください',
            'planned_qty.min' => '予定数量は1以上で入力してください',
        ];
    }
}
