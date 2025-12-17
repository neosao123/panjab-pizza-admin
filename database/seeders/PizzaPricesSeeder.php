<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PizzaPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pizza_prices')->insert([
            [
                'size' => 'Small',
                'price' => 14.00,
                'isActive' => 1,
                'isDelete' => 0,
                'shortcode' => 'SM',
                'order_column' => 1,
            ],
            [
                'size' => 'Medium',
                'price' => 17.00,
                'isActive' => 1,
                'isDelete' => 0,
                'shortcode' => 'MD',
                'order_column' => 2,
            ],
            [
                'size' => 'Large',
                'price' => 20.50,
                'isActive' => 1,
                'isDelete' => 0,
                'shortcode' => 'LG',
                'order_column' => 3,
            ],
            [
                'size' => 'Xtra Large',
                'price' => 26.50,
                'isActive' => 1,
                'isDelete' => 0,
                'shortcode' => 'XL',
                'order_column' => 4,
            ]
        ]);
    }
}
