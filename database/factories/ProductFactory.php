<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    $image = $faker->randomElement([
        'images/5b696f37N3f9db92b.png',
        'images/9464004bc0077138c62a2e3e6e7d4a58.jpg',
        'images/151573386.jpg',
        'images/1115959353.jpg',
        'images/1515584406.jpg',
        'images/11152317685.jpg',
        'images/19143749201.jpg',
        'images/20170824034129887.jpg',
        'images/FswacoUml-FyL2IrR7lvDN9uURik.jpg',
    ]);
    return [
        'title' => $faker->word,
        'description' => $faker->sentence,
        'image' => $image,
        'on_sale' => true,
        'rating' => $faker->numberBetween(0,5),
        'sold_count' => 0,
        'review_count' => 0,
        'price' => 0,
    ];
});
