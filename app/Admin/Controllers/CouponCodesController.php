<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CouponCodesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '优惠券';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode);
        $grid->disableExport();
        // 默认按创建时间排序
        $grid->model()->orderBy('created_at', 'desc');
        $grid->id('ID')->sortable();
        $grid->name('名称');
        $grid->code('优惠码');

        $grid->description('描述');
        $grid->column('usage', '用量')->display(function ($value) {
            return "{$this->used} / {$this->total}";
        });


//        $grid->type('类型')->display(function ($value) {
//            return CouponCode::$typeMap[$value];
//        });
//
//        // 根据不同的则扣类型用对应的方式展示
//        $grid->value('折扣')->display(function ($value) {
//            if ($this->type === CouponCode::TYPE_FIXED) {
//                return '€' . $value;
//            } else if ($this->type === CouponCode::TYPE_PERCENT) {
//                return $value . '%';
//            } else {
//                return '免运费';
//            }
//        });

//        $grid->min_amount('最低使用金额');
//        $grid->total('总数');
//        $grid->used('已用数量');
        $grid->enabled('是否启用')->display(function ($value) {
            return $value ? '是' : '否';
        });

        $grid->created_at('创建时间');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CouponCode::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('type', __('Type'));
        $show->field('value', __('Value'));
        $show->field('total', __('Total'));
        $show->field('used', __('Used'));
        $show->field('min_amount', __('Min amount'));
        $show->field('not_before', __('Not before'));
        $show->field('not_after', __('Not after'));
        $show->field('enabled', __('Enabled'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CouponCode);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });

        $form->display('id', 'ID');
        $form->text('name', '名称')->rules('required');
        $form->text('code', '优惠码')->rules(function ($form) {
            // 如果 $form->model()->id 不为空, 代表 编辑操作
            if ($id = $form->model()->id) {
                return 'nullable|unique:coupon_codes,code,' . $id . ',id';
            } else {
                return 'nullable|unique:coupon_codes';
            }
        });
        $form->radio('type', '类型')->options(CouponCode::$typeMap)->rules('required')->default(CouponCode::TYPE_FIXED);
        $form->text('value', '折扣')->rules(function ($form) {
            if (request()->input('type') === CouponCode::TYPE_PERCENT) {
                // 如果选择百分比的则扣, 那么折扣范围只能是1~99
                return 'required|numeric|between:1,99';
            } elseif (request()->input('type') === CouponCode::TYPE_SHIPPING_FREE) {
                // 如果是免运费, 则可以不用填, 自动生成
                return 'nullable';
            } else {
                // 大于 0.01 即可
                return 'required|numeric|min:0.01';
            }
        });
        $form->text('total', '总数')->rules('required|numeric|min:0');
        $form->text('min_amount', '最低金额')->rules('required|numeric|min:0');
        $form->datetime('not_before', '开始时间');
        $form->datetime('not_after', '结束时间');
        $form->radio('enabled', '启用')->options(['1' => '是', '0' => '否']);

        $form->saving(function (Form $form) {
            if (!$form->code) {
                $form->code = CouponCode::findAvailableCode();
            }

            if (!$form->value) {
                $form->value = -1;
            }
        });

        return $form;
    }
}
