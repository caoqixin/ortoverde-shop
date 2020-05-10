<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdersController extends Controller
{

    // 用户显示订单页面
    public function index(Request $request)
    {
        $orders = Order::query()
            // 使用 with 方法预加载 ,避免 n+ 1 问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }


    // 订单详情页
    public function show(Order $order, Request $request)
    {
        $this->authorize('own', $order);
        return view('orders.show', [
            'order' => $order->load([
                'items.productSku',
                'items.product',
            ]),
        ]);
    }


    // 创建订单
    // 利用 Laravel 的自动解析功能注入 CartService 类
    public function store(OrderRequest $request, OrderService $orderService)
    {
        // 获取当前用户
        $user = $request->user();
        // 获取地址
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));


    }
}
