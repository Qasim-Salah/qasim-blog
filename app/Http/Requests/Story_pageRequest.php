<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Story_pageRequest extends FormRequest
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
            'category_id'   => 'required',
            'images.*' => 'nullable|mimes:jpg,jpeg,png,gif|max:20000',


        ];
    }


    public function messages(){

        return [
            'required'  => 'This field is required',
            'min'  => 'This field is short',
            'images.*'=>'The image size is large',




        ];
    }

}
