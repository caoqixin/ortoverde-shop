<?php

use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 创建商品
        $products = factory(\App\Models\Product::class, 100)->create();

        foreach ($products as $product) {
            // 创建商品的sku, 并且每个 sku 的`product_id` 字段都设为当前循环的商品 id
            $skus = factory(\App\Models\ProductSku::class, 3)->create(['product_id' => $product->id]);
            // 找到价格最低 SKU 价格, 把商品价格设置为该价格
            $product->update(['price' => $skus->min('price')]);
        }
    }
}
