<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CouponCode;
use Faker\Generator as Faker;

$factory->define(CouponCode::class, function (Faker $faker) {
    // 随机去一个类型
    $type = $faker->randomElement(array_keys(CouponCode::$typeMap));

    // 根据取得的类型生成对应折扣
    if ($type === CouponCode::TYPE_FIXED) {
        $value  = random_int(1, 200);
    } else if ($type === CouponCode::TYPE_PERCENT) {
        $value = random_int(1, 50);
    } else {
        $value =  -1;
    }

    // 如果是固定金额, 则最低订单金额要比优惠券高20元
    if ($type === CouponCode::TYPE_FIXED) {
        $minAmount = $value + 20;
    } elseif ($type === CouponCode::TYPE_PERCENT) {
        // 如果是百分比则扣, 有 50% 概率不需要最低订单金额
        if (random_int(0, 100) < 50) {
            $minAmount = 0;
        } else {
            $minAmount = random_int(100, 1000);
        }
    } else {
        // 如果免运费的话, (目标距离5公里以内免费( 以后写)) 100 元免运费
        $minAmount = 100;
    }

    return [
        'name' => join(' ', $faker->words),
        'code' => CouponCode::findAvailableCode(),
        'type' => $type,
        'value' => $value,
        'total' => 100,
        'used' => 0,
        'min_amount' => $minAmount,
        'not_before' => null,
        'not_after' => null,
        'enabled' => true,
    ];
});
