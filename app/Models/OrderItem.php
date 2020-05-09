<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    // 批量增加
    protected $fillable = [
        'amount',
        'price',
        'rating',
        'review',
        'reviewed_ar',
    ];
    protected $dates = ['reviewed_at'];
    public $timestamps = false;


    // 关联 products 表
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 关联 product_skus 表
    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }

    // 关联 orders 表
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
