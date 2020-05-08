@extends('layouts.app')
@section('title', '购物车')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">我的购物车</div>
                @if($cartItems)
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
        });
    </script>
@endsection