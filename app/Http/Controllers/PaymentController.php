<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    // 货到付款
    public function payWhenReceived(Order $order)
    {

        // 判断订单是否属于当前用户\
        $this->authorize('own', $order);
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        $order->update([
            'paid_at' => Carbon::now(), // 支付时间
            'payment_method' => 'delivery', // 支付方式
            'payment_no' => $this->payNo(), // 支付宝订单号
        ]);

        $this->afterPaid($order);

        return [];
    }

    public function payByAlipay(Order $order, Request $request)
    {

        // 判断订单是否属于当前用户\
        $this->authorize('own', $order);
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 调用支付宝的网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no, //订单编号, 需保证在商户端不重复
            'total_amount' => $order->total_amount, //订单金额
            'subject' => '支付订单' . $order->no,
        ]);
    }

    // 前端回调
    public function alipayReturn()
    {
        // 校验提交的参数是否合法
        try{
            app('alipay')->verify();
        }catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '支付成功']);
    }

    // 服务器回调
    public function alipayNotify()
    {
        // 校验输入参数
        $data = app('alipay')->verify();
        // 如果订单状态不是成功或者结束, 则不走后续逻辑
        if (!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }

        // $data->out_trade_no 拿到订单流水号, 并在数据库中查询
        $order = Order::where('no', $data->out_trade_no)->first();

        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if (!$order) {
            return 'fail';
        }

        // 如果这笔订单的状态已经是已支付
        if ($order->paid_at) {
            // 返回数据给支付宝
            return app('alipay')->success();
        }

        $order->update([
            'paid_at' => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no' => $data->trade_no, // 支付宝订单号
        ]);

        $this->afterPaid($order);

        return app('alipay')->success();
    }


    // 生成支付流水号
    protected function payNo()
    {
        $prefix = date('YmdHis');

        $no = $prefix . uniqid();

        return $no;
    }

    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
}
