<?php

namespace App\Admin\Controllers;

use App\Events\DoShip;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class OrdersController extends AdminController
{

    // 使用 validate
    use ValidatesRequests;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);

        // 只显示以支付|已确认的订单, 并且默认按支付时间倒序排序
        $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');

        $grid->no('订单号');
        // 展示关联关系的字段时, 使用 column 方法
        $grid->column('user.name', '客人');
        $grid->total_amount('总金额')->sortable();
        $grid->paid_at('支付时间')->sortable();
        $grid->ship_status('物流')->display(function ($value) {
            return Order::$shipStatusMap[$value];
        });
//        $grid->refund_status('退款状态')->display(function ($value) {
//            return Order::$refundStatusMap[$value];
//        });

        // 禁止创建按钮
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 禁用删除和编辑按钮
            $actions->disableDelete();
            $actions->disableEdit();
        });

        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });


        return $grid;
    }

    /**
     *
     * 这样的效果就是页面顶部和左侧都还是 Laravel-Admin 原本的菜单，而页面主要内容就变成了我们这个模板视图渲染的内容了。
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        // body 方法可以接受 Laravel 的视图作为参数
        return $content->header('查看订单')->body(view('admin.orders.show', [
            'order' => Order::find($id),
        ]));
    }

    // 订单发货
    public function ship(Order $order, Request $request)
    {
        // 判断当前订单是否已经支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单为付款或者为确认');
        }

        // 判断当前订单发货状态是否为未发货
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已发货');
        }

        // Laravel 5.5 之后validate方法可以返回校验过的值
        $data = $this->validate($request, [
            'contact_name' => ['required'],
            'contact_phone' => ['required'],
        ],[], [
            'contact_name' => '送货员姓名',
            'contact_phone' => '送货员电话',
        ]);

        // 将订单发货状态改为已发货, 并存入物流信息
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是一个数组
            // 因此这里可以直接把数组传过去
            'ship_data' => $data,
        ]);

        $this->afterDoShip($order);

        // 返回上一页
        return redirect()->back();
    }


    public function afterDoShip(Order $order)
    {
        event(new DoShip($order));
    }
}
