<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title',
        'long_title',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // 转化图片地址
    public function getImageUrlAttribute()
    {
        // 如果 image 字段本身就时完整的url 就直接返回
        if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
            return $this->attributes['image'];
        }

        return \Storage::disk('shop')->url($this->attributes['image']);
    }


    // 加入属性 关联关系
    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }

    // 聚合商品属性
    public function getGroupedPropertiesAttribute()
    {
        return $this->properties
            // 按照属性名聚合, 返回的集合 key 是属性名, value 是包含该属性的多有属性集合
            ->groupBy('name')
            ->map(function ($properties) {
                // 使用 map 方法将属性集合变为属性值集合
                return $properties->pluck('value')->all();
            });
    }
}
