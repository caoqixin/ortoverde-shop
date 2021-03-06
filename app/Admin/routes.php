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

    // 优惠券添加
    $router->get('coupon_codes/create', 'CouponCodesController@create');
    $router->post('coupon_codes', 'CouponCodesController@store');

    // 优惠券修改
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit');
    $router->put('coupon_codes/{id}', 'CouponCodesController@update');
    // 优惠券删除
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy');

    // 商品类目
    $router->get('categories', 'CategoriesController@index');
    $router->get('categories/create', 'CategoriesController@create');
    $router->get('categories/{id}/edit', 'CategoriesController@edit');
    $router->post('categories', 'CategoriesController@store');
    $router->put('categories/{id}', 'CategoriesController@update');
    $router->delete('categories/{id}', 'CategoriesController@destroy');
    $router->get('api/categories', 'CategoriesController@apiIndex');
});
