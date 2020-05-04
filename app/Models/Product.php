<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'on_sale',
        'rating',
        'sold_count',
        'review_count',
        'price',
    ];

    protected $casts = [
        'on_sale' => 'boolean', // on_sale是一个布尔字段
    ];

    // 与商品sku关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    // 转化图片地址
    public function getImageUrlAttribute()
    {
        // 如果 image 字段本身就时完整的url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://','https://'])) {
            return $this->attributes['image'];
        }

        return \Storage::disk('shop')->url($this->attributes['image']);
    }
}
