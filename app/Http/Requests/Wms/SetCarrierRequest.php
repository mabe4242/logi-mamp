<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class SetCarrierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'carrier' => 'required|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'carrier.required' => '運送会社を選択してください',
        ];
    }
}
