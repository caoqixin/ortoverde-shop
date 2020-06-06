<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
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

    /*
     * "properties" : {
        "category" : {
          "type" : "keyword"
        },
        "category_id" : {
          "type" : "integer"
        },
        "category_path" : {
          "type" : "keyword"
        },
        "desciption" : {
          "type" : "text",
          "analyzer" : "ik_smart"
        },
        "long_title" : {
          "type" : "text",
          "analyzer" : "ik_smart"
        },
        "on_sale" : {
          "type" : "boolean"
        },
        "price" : {
          "type" : "scaled_float",
          "scaling_factor" : 100.0
        },
        "properties" : {
          "type" : "nested",
          "properties" : {
            "name" : {
              "type" : "keyword"
            },
            "value" : {
              "type" : "keyword"
            }
          }
        },
        "rating" : {
          "type" : "float"
        },
        "review_count" : {
          "type" : "integer"
        },
        "skus" : {
          "type" : "nested",
          "properties" : {
            "description" : {
              "type" : "text",
              "analyzer" : "ik_smart"
            },
            "price" : {
              "type" : "scaled_float",
              "scaling_factor" : 100.0
            },
            "title" : {
              "type" : "text",
              "analyzer" : "ik_smart"
            }
          }
        },
        "sold_count" : {
          "type" : "integer"
        },
        "title" : {
          "type" : "text",
          "analyzer" : "ik_smart"
        },
        "type" : {
          "type" : "keyword"
        }
      }
     */
    // 将商品数据写入ElasticSearch 需要把商品模型转化成符合上述字段格式的数组
    public function toESArray()
    {
        // 只取出需要的字段
        $arr = Arr::only($this->toArray(), [
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);

        // 如果商品有类目, 则 category 字段为类目名数组, 否则为空字符串
        $arr['category'] = $this->categoty ? explode(' - ', $this->category->full_name) : '';
        // 类目 path 字段
        $arr['category_path'] = $this->category ? $this->category->path : '';
        // strip_tags 函数可以将 html 标签去除
        $arr['description'] = strip_tags($this->description);
        // 取出需要的 sku 字段
        $arr['skus'] = $this->skus->map(function (ProductSku $sku) {
            return Arr::only($sku->toArray(), ['title', 'description', 'price']);
        });

        // 取出需要的 商品属性 字段
        $arr['properties'] = $this->properties->map(function (ProductProperty $property) {
            return Arr::only($property->toArray(), ['name', 'value']);
        });

        return $arr;
    }

}
