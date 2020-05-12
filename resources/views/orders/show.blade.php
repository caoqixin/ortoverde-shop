@extends('layouts.app')
@section('title', '查看订单')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    <h4>
                        <span>订单详情</span>
                        <button class="btn btn-success float-right btn-back"><i class="fa fa-backward"></i> 返回</button>
                    </h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>商品信息</th>
                            <th class="text-center">单价</th>
                            <th class="text-center">数量</th>
                            <th class="text-right item-amount">小计</th>
                        </tr>
                        </thead>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td class="product-info">
                                    <div class="preview">
                                        <a href="{{ route('products.show', [$item->product_id]) }}" target="_blank">
                                            <img src="{{ $item->product->image_url }}" alt="">
                                        </a>
                                    </div>
                                    <div>
                                        <span class="product-title"><a
                                                    href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a></span>
                                        <span class="sku-title">{{ $item->productSku->title }}</span>
                                    </div>
                                </td>
                                <td class="sku-price text-center vertical-middle">&euro; {{ $item->price }}</td>
                                <td class="sku-amount text-center vertical-middle">{{ $item->amount }}</td>
                                <td class="item-amount text-right vertical-middle">
                                    &euro; {{ number_format($item->price * $item->amount, 2, '.', '') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4"></td>
                        </tr>
                    </table>
                    <div class="order-bottom">
                        <div class="order-info">
                            <div class="line">
                                <div class="line-label">收货地址:</div>
                                <div class="line-value">{{ join(' ', $order->address) }}</div>
                            </div>
                            <div class="line">
                                <div class="line-label">订单备注:</div>
                                <div class="line-value">{{ $order->remark ?? '-' }}</div>
                            </div>
                            <div class="line">
                                <div class="line-label">订单编号:</div>
                                <div class="line-value">{{ $order->no }}</div>
                            </div>
                        </div>
                        <div class="order-summary text-right">
                            <div class="total-amount">
                                <span>订单总价: </span>
                                <div class="value">&euro; {{ $order->total_amount }}</div>
                            </div>
                            <div>
                                <span>订单状态: </span>
                                <div class="value">
                                    @if($order->paid_at)
                                        @if($order->payment_method == 'delivery')
                                            已确认
                                        @else
                                            @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                                已支付
                                            @else
                                                {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                            @endif
                                        @endif
                                    @elseif($order->closed)
                                        已关闭
                                    @else
                                        未支付|未确认
                                    @endif
                                </div>
                            </div>
                            <!--支付开始-->
                            @if(!$order->paid_at && !$order->closed)
                                <div class="payment-buttons">
                                    <button class="btn btn-info btn-sm btn-delivery">货到付款</button>
                                    {{--                                    <a class="btn btn-info btn-sm" href="{{ route('payment.alipay', ['order' => $order->id]) }}">支付宝支付</a>--}}
                                </div>
                        @endif
                        <!--支付结束-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.btn-back').click(function () {
                let last_url = document.referrer;

                if (last_url === `{!! config('app.url') !!}/orders`) {
                    history.back();
                } else {
                    location.href = `{!! config('app.url') !!}/orders`;
                }
            });

            $('.btn-delivery').click(function () {
                axios.post('{{ route('payment.received', ['order' => $order->id]) }}').then(function () {
                    location.reload();
                },function (error) {
                    // 请求失败
                    if (error.response.status === 401) {
                        // http 401 表示用户未登录
                        swal('请先登录再试', '', 'error');
                    } else if (error.response.status === 422) {
                        // http 状态码 422 表示用户出入校验失败
                        var html = '<div>';
                        _.each(error.response.data.errors, function (errors) {
                            _.each(errors, function (error) {
                                html += error + '<br>';
                            })
                        });

                        html += '</div>';
                        swal({content: $(html)[0], icon: 'error'});
                    } else {
                        swal('系统错误', '', 'error');
                    }
                });
            });
        });
    </script>
@endsection