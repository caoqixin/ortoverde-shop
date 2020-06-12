<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\SearchBuilders\ProductSearchBuilder;
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

        // 新建查询构造器对象，设置只搜索上架商品，设置分页
        $builder = (new ProductSearchBuilder())->onSale()->paginate($perPage, $page);



        // 是否有提交 order 参数, 如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
//            是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一, 说明是合法的排序规则
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        // 类目搜索
        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            // 调用查询构造器的类目筛选
            $builder->category($category);
        }

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            // 将搜索词根据空格 拆分成数组, 并过滤掉空项
            $keywords = array_filter(explode(' ', $search));

            // 调用查询构造器的关键词筛选
            $builder->keywords($keywords);

        }


        // 只有当用户有输入搜索此或者使用类目筛选时开启聚合
        if ($search || isset($category)) {
            // 调用查询构造器的分面搜索
            $builder->aggregateProperties();
        }

        // 定义一个数组
        $propertyFilters = [];
        // 支持按属性筛选，并且要支持多对的属性值筛选
        // 从用户请求参数获取 filters
        if ($filterString = $request->input('filters')) {
            // 将获取到的字符串用符号 | 拆分成数组
            $filterArray = explode('|', $filterString);
            foreach ($filterArray as $filter) {
                // 将字符串用符号 : 拆分成两部分并且分别赋值给 $name 和 $value 两个变量
                list($name, $value) = explode(':', $filter);
                // 将用户筛选的属性添加到数组中
                $propertyFilters[$name] = $value;

                // 添加到 filter 类型中
                // 调用查询构造器的属性筛选
                $builder->propertyFilter($name, $value);
            }

        }
        $result = app('es')->search($builder->getParams());

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


        $properties = [];
        // 如果返回结果有 aggregation 字段, 说明做了奋勉搜索
        if (isset($result['aggregations'])) {
            // 使用 collect 函数将返回值转为集合
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])->map(function ($buckets) {
                return [
                    'key' => $buckets['key'],
                    'values' => collect($buckets['value']['buckets'])->pluck('key')->all(),
                ];
            })->filter(function ($property) use ($propertyFilters) {
                // 过滤掉只剩下一个值 或者 已经在筛选条件里的属性
                return count($property['values']) > 1 && !isset($propertyFilters[$property['key']]);
            });
        }
//        dd($properties);
        return view('products.index', [
            'products' => $pager,
            'filters' => [
                'search' => $search,
                'order' => $order,
            ],
            'category' => $category ?? null,
            'properties' => $properties,
            'propertyFilters' => $propertyFilters,
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
