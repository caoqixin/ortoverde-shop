<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'region' => 'required',
            'province' => 'required',
            'town' => 'required',
            'address' => 'required',
            'zip' => 'required',
            'contact_name' => 'required',
            'contact_phone' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'region' => '大区',
            'province' => '省份',
            'town' => '市镇',
            'address' => '详细地址',
            'zip' => '邮编',
            'contact_name' => '姓名',
            'contact_phone' => '电话',
        ];
    }
}
