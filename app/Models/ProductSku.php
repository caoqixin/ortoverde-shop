<?php

namespace App\Models;

use App\Exceptions\InternalException;
use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'stock',
    ];

    // 关联products
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    // 减库存
    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不可少于0');
        }

        return $this->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    // 增加库存
    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('增加库存不可小于0');
        }

        $this->increment('stock', $amount);
    }
}
