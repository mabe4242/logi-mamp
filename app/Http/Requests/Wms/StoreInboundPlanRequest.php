<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class StoreInboundPlanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'planned_date' => 'nullable|date',
            'note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'supplier_id.required' => '仕入先を選択してください',
            'supplier_id.exists' => '選択された仕入先が存在しません',
            'planned_date.date' => '入荷予定日は正しい日付形式で入力してください',
        ];
    }
}
