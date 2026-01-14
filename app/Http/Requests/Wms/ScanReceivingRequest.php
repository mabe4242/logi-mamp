<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class ScanReceivingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'バーコードまたはSKUを入力してください',
        ];
    }
}
