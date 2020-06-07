<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Route;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        // 代码重构 elasticsearch 搜索
        $page = $request->input('page', 1);
        $perPage = 16;

        // 构建查询
        $params = [
            'index' => 'products',
            'body' => [
                'from' => ($page - 1) * $perPage, // 通过当前页数与每页数量计算偏移量
                'size' => $perPage, // 每页显示数量
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['on_sale' => true]],
                        ]
                    ],
                ],
            ],
        ];


        // 是否有提交 order 参数, 如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
//            是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一, 说明是合法的排序规则
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $params['body']['sort'] = [[$m[1] => $m[2]]];
                }
            }
        }

        // 类目搜索
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            if ($category->is_directory) {
                // 如果是个父类目, 则使用 category_path 来筛选
                $params['body']['query']['bool']['filter'][] = [
                    'prefix' => ['category_path' => $category->path . $category->id . '-'],
                ];
            } else {
                // 否则直接通过 category_id 筛选
                $params['body']['query']['bool']['filter'][] = [
                    'term' => ['category_id' => $category->id],
                ];
            }
        }

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            // 将搜索词根据空格 拆分成数组, 并过滤掉空项
            $keywords = array_filter(explode(' ', $search));

            $params['body']['query']['bool']['must'] = [];
            // 遍历搜索词数组，分别添加到 must 查询中
            foreach ($keywords as $keyword) {
                $params['body']['query']['bool']['must'] = [
                    [
                        'multi_match' => [
                            'query' => $keyword,
                            'fields' => [
                                'title^3',
                                'long_title^2',
                                'category^2',
                                'description',
                                'skus_title',
                                'skus_description',
                                'properties_value',
                            ],
                        ]
                    ]
                ];
            }

        }
        $result = app('es')->search($params);

        // 通过 collect 函数将返回结果转为集合,并通过集合的 pluck 方法取到返回的商品 ID 数组
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        // 通过 whereIn 方法从数据库中读取商品数据
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $productIds)))
            ->get();

        // 返回一个 LengthAwarePaginator 对象
        $pager = new LengthAwarePaginator($products, $result['hits']['total']['value'], $perPage, $page, [
            'path' => route('products.index', false),
        ]);

        return view('products.index', [
            'products' => $pager,
            'filters' => [
                'search' => $search,
                'order' => $order,
            ],
            'category' => $category ?? null,
//            'categoryTree' => $categoryService->getCategoryTree(),
        ]);
    }

    /**
     * 商品列表页
     */
    public function show(Product $product, Request $request)
    {
        // 判断商品是否上架, 如果没上架则抛出异常
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }
        // 判断是否已经收藏
        $favored = false;
        // 用户未登录时返回null, 以登陆时返回对应的用户对象
        if ($user = $request->user()) {
            // 当前用户以收藏的商品中搜索id 为当前商品id 的商品
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }


        // 用户评价
        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku'])
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)
            ->get();

        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews,
        ]);
    }

    // 新增收藏
    public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }

    // 取消收藏
    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);

        return [];
    }


    // 收藏列表
    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', [
            'products' => $products,
        ]);
    }
}
