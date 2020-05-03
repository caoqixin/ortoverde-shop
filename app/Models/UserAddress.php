<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    // 批量赋值
    protected $fillable = [
        'region',
        'province',
        'town',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_used_at',
    ];

    protected $dates = ['last_used_at'];

    // 创建user表关联 一对多

    /**
     * 一个 User 可以有多个 UserAddress，一个 UserAddress 只能属于一个 User
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute()
    {
        return "{$this->address},{$this->town},{$this->province}";
    }
}
