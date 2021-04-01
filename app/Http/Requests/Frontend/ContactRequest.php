<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'mobile' => 'nullable|numeric',
            'title' => 'required|min:5|max:20',
            'message' => 'required|min:10',
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
