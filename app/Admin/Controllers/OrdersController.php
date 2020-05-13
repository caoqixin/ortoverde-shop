<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class OrdersController extends AdminController
{
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
}
