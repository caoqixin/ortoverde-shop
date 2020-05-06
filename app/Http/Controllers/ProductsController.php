<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);

        // 判断是否有提交search参数, 如果有就给 $search 赋值
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            // 模糊搜索商品标题, 商品详情, sku 标题, sku 描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        // 是否有提交 order 参数
        // order 参数用来控制商品排序
        if ($order = $request->input('order', '')) {
            // 是否以_asc 或 _desc结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是三个字符串之一, 说明是合法的
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $builder->paginate(16);


        return view('products.index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'order' => $order,
            ]
        ]);
    }

    /**
     * 商品列表页
     */
    public function show(Product $product, Request $request)
    {
        // 判断商品是否上架, 如果没上架则抛出异常
        if (!$product->on_sale) {
            throw new \Exception('商品未上架');
        }
        // 判断是否已经收藏
        $favored = false;
        // 用户未登录时返回null, 以登陆时返回对应的用户对象
        if ($user = $request->user()) {
            // 当前用户以收藏的商品中搜索id 为当前商品id 的商品
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        return view('products.show', [
            'product' => $product,
            'favored' => $favored,
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