<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::create([
            'name' => 'Electonics',
            'info' => '',
        ]);
        Category::create([
            'name' => 'Clothes',
            'info' => '',
        ]);
        Category::create([
            'name' => 'Auto',
            'info' => '',
        ]);
        Category::create([
            'name' => 'Furniture',
            'info' => '',
        ]);
        Category::create([
            'name' => 'Books',
            'info' => '',
        ]);
        Category::create([
            'name' => 'Construction',
            'info' => '',
        ]);
    }
}
