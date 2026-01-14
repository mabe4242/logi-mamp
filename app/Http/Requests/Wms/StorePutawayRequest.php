<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class StorePutawayRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'line_id' => 'required|integer|exists:inbound_plan_lines,id',
            'location_id' => 'required|integer|exists:locations,id',
            'qty' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'line_id.required' => '明細を選択してください',
            'line_id.exists' => '選択された明細が存在しません',
            'location_id.required' => 'ロケーションを選択してください',
            'location_id.exists' => '選択されたロケーションが存在しません',
            'qty.required' => '数量を入力してください',
            'qty.integer' => '数量は数値で入力してください',
            'qty.min' => '数量は1以上で入力してください',
        ];
    }
}
