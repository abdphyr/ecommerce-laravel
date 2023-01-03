<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $lenovo = Product::create([
            'user_id' => 1,
            'category_id' => 1,
            'price' => 29,
            'name' => 'Lenovo',
            'info' => 'This is Lenovo laptop for programmers and gamers'
        ]);
        $lenovo->tags()->attach([2, 4]);

        $malibu = Product::create([
            'user_id' => 1,
            'category_id' => 3,
            'price' => 20,
            'name' => 'Malibu',
            'info' => 'This automobile is produced by GM Uzbekistan'
        ]);
        $malibu->tags()->attach([9, 10]);
    }
}
