<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentPlanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'planned_ship_date' => 'nullable|date',
            'note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'customer_id.required' => '出荷先を選択してください',
            'customer_id.exists' => '選択された出荷先が存在しません',
            'planned_ship_date.date' => '出荷予定日は正しい日付形式で入力してください',
        ];
    }
}
