<?php

namespace Modules\Basics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'username' => 'required|min:2|max:20',
            'password' => 'required|min:6|max:20',
            'captcha' => ['required'],
        ];
    }

    public function messages(){
        return [
            'required' => ':attribute为必填项',
            'min' => ':attribute长度不符合要求',
            'captcha.required' => '验证码不能为空',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
