<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">订单号: {{ $order->no }}</h3>
        <div class="box-tools">
            <div class="btn-group float-right" style="margin-right: 10px">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-default"><i
                            class="fa fa-list"></i>列表</a>
            </div>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td>买家:</td>
                <td>{{ $order->user->name }}</td>
                <td>支付时间:</td>
                <td>{{ $order->paid_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <td>支付方式:</td>
                <td>{{ $order->payment_method }}</td>
                <td>支付订单号:</td>
                <td>{{ $order->payment_no }}</td>
            </tr>
            <tr>
                <td>收货地址</td>
                <td colspan="3">{{ $order->address['address'] }} {{ $order->address['zip'] }} {{ $order->address['contact_name'] }} {{ $order->address['contact_phone'] }}</td>
            </tr>
            <tr>
                <td rowspan="{{ $order->items->count() + 1 }}">商品列表</td>
                <td>商品名称</td>
                <td>单价</td>
                <td>数量</td>
            </tr>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->title }} {{ $item->productSku->title }}</td>
                    <td>&euro; {{ $item->price }}</td>
                    <td>{{ $item->amount }}</td>
                </tr>
            @endforeach
            <tr>
                <td>订单金额:</td>
                <td> &euro; {{ $order->total_amount }}</td>
                <td>发货状态:</td>
                <td class="bg-success">{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</td>
            </tr>
            {{--            订单开始发货--}}
            {{--            如果订单未发货, 显示发货表单--}}
            @if($order->ship_status === \App\Models\Order::SHIP_STATUS_PENDING)
                <tr>
                    <td colspan="4">
                        <form action="{{ route('admin.orders.ship', ['order' => $order->id]) }}" method="post" class="form-inline">
                            {{ csrf_field() }}
                            <div class="form-group {{ $errors->has('contact_name') ? 'has-error' : ''}} ">
                                <label for="contact_name" class="control-label">送货人姓名</label>
                                <input type="text" id="contact_name" name="contact_name" value="" class="form-control"
                                       placeholder="送货人姓名">
                                @if($errors->has('contact_name'))
                                    @foreach($errors->get('contact_name') as $msg)
                                        <span class="help-block">{{ $msg }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="form-group {{ $errors->has('contact_phone') ?'has-error':'' }}">
                                <label for="contact_phone" class="control-label">送货人联系电话</label>
                                <input type="text" id="contact_phone" name="contact_phone" value="" class="form-control"
                                       placeholder="输入送货人联系方式">
                                @if($errors->has('contact_phone'))
                                    @foreach($errors->get('contact_phone') as $msg)
                                        <span class="help-block">{{ $msg }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <button type="submit" class="btn btn-success" id="ship-btn">发货</button>
                        </form>
                    </td>
                </tr>
            @else
                {{--                否则展示送货人联系方式--}}
                <tr>
                    <td>送货人姓名:</td>
                    <td class="bg-primary">{{ $order->ship_data['contact_name'] }}</td>
                    <td>送货人联系方式:</td>
                    <td class="bg-info">{{ $order->ship_data['contact_phone'] }}</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>