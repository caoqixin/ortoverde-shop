<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('users', 'UsersController@index');
    // 显示商品
    $router->get('products', 'ProductsController@index');
    // 创建商品
    $router->get('products/create', 'ProductsController@create');
    $router->post('products', 'ProductsController@store');
    // 编辑商品
    $router->get('products/{id}/edit', 'ProductsController@edit');
    $router->put('products/{id}', 'ProductsController@update');
    $router->delete('products/{id}', 'ProductsController@destroy');

    // 订单显示
    $router->get('orders', 'OrdersController@index')->name('admin.orders.index');
    // 订单详情
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show');
    // 订单发货
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');

    // 优惠券页面
    $router->get('coupon_codes', 'CouponCodesController@index');
});
