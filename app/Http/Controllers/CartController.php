<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;

use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class CartController extends Controller
{

    // 显示购物车页
    public function index(Request $request)
    {
        $cartItems = $request->user()->cartItems()->with(['productSku.product'])->get();

        $addresses = $request->user()->address()->orderBy('last_used_at', 'desc')->get();

        return view('cart.index', [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
        ]);
    }


    // 添加购物车
    public function add(AddCartRequest $request)
    {
        // 当前用户
        $user = $request->user();
        $skuId = $request->input('sku_id');
        $amount = $request->input('amount');

        // 从数据库中查询该商品是否已经在购物车里
        if ($cart = $user->CartItems()->where('product_sku_id', $skuId)->first()) {
            // 如果存在则直接叠加商品数量
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        } else {
            // 否则创建一个新的购物车记录
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($skuId);
            $cart->save();
        }

        return [];
    }

    // 商品从购物车移除
    public function remove(ProductSku $sku, Request $request)
    {
        $request->user()->cartItems()->where('product_sku_id', $sku->id)->delete();

        return [];
    }
}
