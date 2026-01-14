<?php

namespace App\Http\Requests\Wms;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:255|unique:locations,code',
            'name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'ロケーションコードを入力してください',
            'code.unique' => 'このロケーションコードは既に登録されています',
        ];
    }
}
