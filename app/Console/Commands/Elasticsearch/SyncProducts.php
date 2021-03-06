<?php

namespace App\Console\Commands\Elasticsearch;

use App\Models\Product;
use Illuminate\Console\Command;

//当创建新的商品或者修改了商品信息时能够自动同步到 Elasticsearch
class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:sync-products {--index=products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将商品数据同步到 Elasticsearch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 获取 Elasticsearch 对象
        $es = app('es');
        Product::query()
            // 预加载 SKU 和 商品属性数据, 避免 N + 1 问题
            ->with(['skus', 'properties'])
            // 使用 chunkById 避免一次性加载过多数据
            ->chunkById(100, function ($products) use ($es) {
                $this->info(sprintf('正在同步 ID 范围为 %s 至 %s 的商品', $products->first()->id, $products->last()->id));


                // 初始化请求体
                $request = ['body' => []];
                foreach ($products as $product) {
                    // 将商品模型转为 Elasticsearch 所用的数组
                    $data = $product->toESArray();

                    $request['body'][] = [
                        'index' => [
                            // 从参数中读取索引名称
                            '_index' => $this->option('index'),
                            '_id' => $data['id'],
                        ],
                    ];

                    $request['body'][] = $data;
                }

                try {
                    // 使用 bulk 方法批量创建
                    $es->bulk($request);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            });

        $this->info('同步完成');
    }
}
