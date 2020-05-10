<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;

use App\Models\CartItem;
use App\Models\ProductSku;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;
    // 利用 laravel 的自动解析功能注入 CartService 类
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // 显示购物车页
    public function index(Request $request)
    {
        $cartItems = $this->cartService->get();

        $addresses = $request->user()->address()->orderBy('last_used_at', 'desc')->get();

        return view('cart.index', [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
        ]);
    }


    // 添加购物车
    public function add(AddCartRequest $request)
    {
        $this->cartService->add($request->input('sku_id'), $request->input('amount'));

        return [];
    }

    // 商品从购物车移除
    public function remove(ProductSku $sku, Request $request)
    {
        $this->cartService->remove($sku->id);

        return [];
    }
}
