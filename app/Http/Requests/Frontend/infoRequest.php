<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class infoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required|numeric',
            'bio' => 'nullable|min:10',
            'receive_email' => 'required',
            'image' => 'nullable|image|max:20000,mimes:jpeg,jpg,png'
        ];
    }


    public function messages(){

        return [
            'required'  => 'This field is required',
            'min'  => 'This field is short',
            'max'  => 'This field is long',
            'email.email' => 'The email format is incorrect',
            'mobile.numeric' => 'The field must be assigned numbers',


        ];
    }

}
