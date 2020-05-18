@extends('layouts.app')
@section('title', '购物车')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">我的购物车</div>
                @if(!$cartItems->isEmpty())
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>商品信息</th>
                                <th>单价</th>
                                <th>数量</th>
                                <th>总价</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody class="product_list">
                            @foreach($cartItems as $item)
                                <tr data-id="{{ $item->productSku->id }}">
                                    <td>
                                        <input type="checkbox" name="select"
                                               value="{{ $item->productSku->id }}" {{ $item->productSku->product->on_sale ? 'checked' : 'disable'}}>
                                    </td>
                                    <td class="product_info">
                                        <div class="preview">
                                            <a target="_blank"
                                               href="{{ route('products.show', [$item->productSku->product_id]) }}">
                                                <img src="{{ $item->productSku->product->image_url }}">
                                            </a>
                                        </div>
                                        <div @if(!$item->productSku->product->on_sale) class="not_on_sale" @endif>
                                    <span class="product_title">
                                        <a target="_blank"
                                           href="{{ route('products.show', [$item->productSku->product_id]) }}">{{ $item->productSku->product->title }}</a>
                                    </span>
                                            <span class="sku_title">{{ $item->productSku->title }}</span>
                                            @if(!$item->productSku->product->on_sale)
                                                <span class="warning">该商品已下架</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td><span class="price">&euro;{{ $item->productSku->price }}</span></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm amount"
                                               @if(!$item->productSku->product->on_sale) disabled @endif name="amount"
                                               value="{{ $item->amount }}">
                                    </td>
                                    <td>
                                        <span class="price">&euro;{{ $item->productSku->price * $item->amount}}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger btn-remove">移除</button>
                                    </td>
                                </tr>

                            @endforeach
                            </tbody>
                        </table>

                        <!-- 订单开始 -->
                        <div>
                            <form id="order-form" class="form-horizontal" role="form">
                                <div class="form-group row">
                                    <label class="col-form-label col-sm-3 text-md-right">选择收货地址</label>
                                    <div class="col-sm-9 col-md-7">
                                        <select name="address" class="form-control">
                                            @foreach($addresses as $address)
                                                <option value="{{ $address->id }}">{{ $address->full_address }}
                                                    - {{ $address->contact_name }}
                                                    - {{ $address->contact_phone }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-sm-3 text-md-right">备注</label>
                                    <div class="col-sm-9 col-md-7">
                                        <textarea name="remark" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                                {{--                                优惠码开始--}}
                                <div class="form-group row">
                                    <label class="col-form-label col-sm-3 text-md-right">优惠码</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" name="coupon_code">
                                        <span class="form-text text-muted" id="coupon_desc"></span>
                                    </div>
                                    <div class="col-sm-3">
                                        <button class="btn btn-success" id="btn-check-coupon" type="button">检查</button>
                                        <button type="button" class="btn btn-danger" id="btn-cancel-coupon"
                                                style="display: none">取消
                                        </button>
                                    </div>
                                </div>
                                {{--                                优惠码结束--}}

                                <div class="form-group">
                                    <div class="offset-sm-3 col-sm-3">
                                        <button type="button" class="btn btn-primary btn-create-order">提交订单</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- 订单结束 -->
                    </div>
                @else
                    <div class="card-body text-center">
                        <h1>购物车中还没有商品, 请去主页添加吧</h1>
                        <a href="{{ route('root') }}" class="btn btn-primary">Shopping</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            // 监听 移除 按钮的点击事件
            $('.btn-remove').click(function () {
                // $(this) 可以获取当前点击的 移除 按钮的对象
                // closest() 可以获取当前匹配选择器的第一个祖先元素, 相对于 移除按钮 就是tr
                // sku id
                var id = $(this).closest('tr').data('id');

                swal({
                    title: "确认要将该商品删除吗?",
                    icon: 'warning',
                    buttons: ['取消', '确认'],
                    dangerMode: true,
                }).then(function (willDelete) {
                    // 用户点击 确认按钮 willDelete 的值时true
                    if (!willDelete) {
                        return;
                    }

                    axios.delete('/cart/' + id).then(function () {
                        location.reload();
                    })
                });
            });

            // 全选/取消全选
            $('#select-all').change(function () {
                // 获取单选框的选中的状态
                // prop() 方法可以知道标签中是否包含某个属性, 当单选框被勾选时, 对应的标签就会新增一个 checked 属性
                var checked = $(this).prop('checked');
                // 获取所有 name=select 并且不带 disabled 属性的勾选框
                // 对于已经下架的商品我们不希望被选中, 因此要加上 :not([disabled])
                $('input[name=select][type=checkbox]:not([disabled])').each(function () {
                    // 将其勾选状态设为与目标框一致
                    $(this).prop('checked', checked);
                });
            });


            // 监听创建订单按钮的事件
            $('.btn-create-order').click(function () {
                // 构建请求参数, 将用户选择的地址的id和备注内容写入请求参数
                var req = {
                    address_id: $('#order-form').find('select[name=address]').val(),
                    items: [],
                    remark: $('#order-form').find('textarea[name=remark]').val(),
                    coupon_code: $('input[name=coupon_code]').val(),
                };

                // 遍历 <table> 标签内的所有带 data-id 属性的 <tr> 标签, 每个购物车的商品 sku
                $('table tr[data-id]').each(function () {
                    // 获取当前行的单选框
                    var $checkbox = $(this).find('input[name=select][type=checkbox]');

                    // 如果单选框禁用或者没有被选中则跳过
                    if ($checkbox.prop('disabled') || !$checkbox.prop('checked')) {
                        return;
                    }

                    // 获取当前行中输入的数量
                    var $input = $(this).find('input[name=amount]');
                    // 如果用户将数量设为0或者不是一个数字跳过
                    if ($input.val() == 0 || isNaN($input.val())) {
                        return;
                    }

                    // 把 sku id 的数量存入请求参数数组中
                    req.items.push({
                        sku_id: $(this).data('id'),
                        amount: $input.val(),
                    })
                });

                axios.post('{{ route('orders.store') }}', req).then(function (response) {
                    swal('订单提交成功', '', 'success').then(() => {
                        location.href = '/orders/' + response.data.id;
                    });
                }, function (err) {
                    if (err.response.status == 422) {
                        // http 状态码为 422 代表用户输入校验失败
                        var html = '<div>';
                        _.each(err.response.data.errors, function (errors) {
                            _.each(errors, function (erros) {
                                html += error + '<br>';
                            });
                        });
                        html += '</div>';

                        swal({content: $(html)[0], icon: 'error'});
                    } else if (error.response.status === 403) { // 这里判断状态 403
                        swal(error.response.data.msg, '', 'error');
                    } else {
                        // 其他情况应该是系统挂了
                        swal('系统错误', '', 'error');
                    }
                });
            });

            // 优惠码检查
            $('#btn-check-coupon').click(function () {
                // 获取用户输入的优惠码
                var code = $('input[name=coupon_code]').val();
                // 如果没有输入则弹框提示
                if (!code) {
                    swal('请输入优惠码', '', 'warning');
                    return;
                }

                // 调用检查接口
                axios.get('/coupon_codes/' + encodeURIComponent(code)).then(function (response) {
                    // then 方法第一个参数是个回调, 请求成功会被调用
                    $('#coupon_desc').text(response.data.description); // 输出优惠信息
                    $('input[name=coupon_code]').prop('readonly', true); // 禁用输入框
                    $('#btn-cancel-coupon').show();
                    $('#btn-check-coupon').hide();
                }, function (error) {
                    // 如果放回404 说明优惠券不存在
                    if (error.response.status === 404) {
                        swal('优惠码不存在', '', 'error');
                    } else if (error.response.status === 403) {
                        swal(error.response.data.msg, '', 'error');
                    } else {
                        swal('系统出错', '', 'error');
                    }

                });
            });

            // 隐藏 按钮点击事件
            $('#btn-cancel-coupon').click(function () {
                $('#coupon_desc').text(''); // 隐藏优惠信息
                $('input[name=coupon_code]').prop('readonly', false);
                $('#btn-cancel-coupon').hide();
                $('#btn-check-coupon').show();
            });
        });
    </script>
@endsection