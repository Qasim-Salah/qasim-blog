<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'url' => 'nullable|url',
            'comment' => 'required|min:10|max:140',
        ];
    }


    public function messages(){

        return [
            'required'  => 'This field is required',
            'min'  => 'This field is short',
            'max'  => 'This field is long',
            'email.email' => 'The email format is incorrect',

        ];
    }

}
