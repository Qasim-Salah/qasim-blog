<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RolesRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'permissions' => 'required|array|min:1',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'هذا الحقل مطلوب ',
        ];

    }


}
