<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CouponCode extends Model
{


    // 用常量的方式总以支持的优惠券类型
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';
    const TYPE_SHIPPING_FREE = 'shipping_free';

    public static $typeMap = [
        self::TYPE_FIXED => '固定金额',
        self::TYPE_PERCENT => '比例',
        self::TYPE_SHIPPING_FREE => '运费全免',
    ];

    // 批量字段
    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    // 指定日期格式的字段
    protected $dates = ['not_before', 'not_after'];

    protected $appends = ['description'];

    // 创建优惠码
    public static function findAvailableCode($length = 16)
    {
        do {
            // 生成一个指定长度的随机字符串
            $code = strtoupper(Str::random($length));
            // 如果生成的码已存在就继续循环
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }


    public function getDescriptionAttribute()
    {
        $str = '';
        if ($this->min_amount > 0) {
            $str = '满' . str_replace('.00', '', $this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str . '优惠' . str_replace('.00', '', $this->value) . '%';
        }

        if ($this->type === self::TYPE_SHIPPING_FREE) {
            return $str . '免运费';
        }

        return $str . '减' . str_replace('.00', '', $this->value);
    }


    // 检查优惠券是否可用
    // 添加$user参数
    public function checkAvailable(User $user, $orderAmount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑玩');
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }

        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }


        $used = Order::where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('paid_at')
                        ->where('closed', false);
                })->orWhere(function ($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', '!=', Order::REFUND_STATUS_SUCCESS);
                });
            })->exists();
        /**
         * select * from orders where user_id = xx and coupon_code_id = xx
        and (
        ( paid_at is null and closed = 0 )
        or ( paid_at is not null and refund_status != 'success' )
        )
         */

        if ($used) {
            throw new CouponCodeUnavailableException('你已经使用过这张优惠券了');
        }
    }


    // 计算优惠后的金额
    public function getAdjustedPrice($orderAmount)
    {
        // 固定金额
        if ($this->type === self::TYPE_FIXED) {
            // 为了保证系统健壮性, 订单金额需要最少为 0.01
            return max(0.01, $orderAmount - $this->value);
        } elseif ($this->type === self::TYPE_SHIPPING_FREE) {
            // 免运费 ***
            return $orderAmount;
        }

        // 百分比
        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }


    // 用户使用优惠券是优惠券的增减
    public function changeUsed($increase = true)
    {
        // 传入true 代表新增用, 否则减少
        if ($increase) {
            // 检查当前用来是否已经超量
            return $this->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement();
        }
    }
}