<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class ScanLabelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tracking_no' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'tracking_no.required' => '送り状番号を入力してください',
        ];
    }
}
