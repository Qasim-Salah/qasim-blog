<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class Story_postRequest extends FormRequest
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
            'title'         => 'required',
            'description'   => 'required|min:50',
            'status'        => 'required',
            'comment_able'  => 'required',
            'category_id'   => 'required',
            'image.*' => 'nullable|mimes:jpg,jpeg,png,gif|max:20000',


        ];
    }


    public function messages(){

        return [
            'required'  => 'This field is required',
            'min'  => 'This field is short',
            'image.*'=>'The image size is large',




        ];
    }

}
