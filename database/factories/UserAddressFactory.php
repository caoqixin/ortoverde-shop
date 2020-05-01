<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserAddress;
use Faker\Generator as Faker;

$factory->define(UserAddress::class, function (Faker $faker) {
    $addresses = [
        ["TOSCANA", "FIRENZE", "CAMPI BISENZIO"],
        ["VENETO", "PADOVA", "UNKOWN"],
        ["LOMBARDIA", "MILANO", "###"],
    ];

    $address = $faker->randomElement($addresses);
    return [
        'region' => $address[0],
        'province' => $address[1],
        'town' => $address[2],
        'address' => sprintf('via vingone %d', $faker->randomNumber(2)),
        'zip' => $faker->postcode,
        'contact_name' => $faker->name,
        'contact_phone' => $faker->phoneNumber,
    ];
});
