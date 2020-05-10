<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
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
    public function store(OrderRequest $request)
    {
        $user = $request->user();

        // 开启一个数据库事务
        $order = \DB::transaction(function () use ($user, $request) {
            $address = UserAddress::find($request->input('address_id'));
            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);

            // 创建一个订单
            $order = new Order([
                'address' => [
                    // 将地址信息放入订单
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0,
            ]);

            // 订单关联到当前用户
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;

            $items = $request->input('items');

            // 遍历用户提交的 sku
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                // 创建一个OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);

                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];

                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            // 更新总订单金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();


            // 触发 CloseOrder 关闭订单
            $this->dispatch(new CloseOrder($order, config('app.order_ttl')));

            return $order;
        });

        return $order;
    }
}