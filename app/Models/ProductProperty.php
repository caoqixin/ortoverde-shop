<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductProperty extends Model
{
    protected $fillable = ['name', 'value'];
    // 没有 created_at 和 updated_at 字段
    public $timestamps = false;

    // 关联 products 表
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
