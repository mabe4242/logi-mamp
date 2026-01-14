<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentPlanLineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'planned_qty' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'planned_qty.required' => '予定数量を入力してください',
            'planned_qty.integer' => '予定数量は数値で入力してください',
            'planned_qty.min' => '予定数量は1以上で入力してください',
        ];
    }
}
