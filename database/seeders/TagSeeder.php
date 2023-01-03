<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tag::create([
            'name' => 'Exsponsive'
        ]);
        Tag::create([
            'name' => 'Cheap'
        ]);
        Tag::create([
            'name' => 'Phone'
        ]);
        Tag::create([
            'name' => 'Laptop'
        ]);
        Tag::create([
            'name' => 'PC'
        ]);
        Tag::create([
            'name' => 'Comfortable'
        ]);
        Tag::create([
            'name' => 'Free'
        ]);
        Tag::create([
            'name' => 'Public'
        ]);
        Tag::create([
            'name' => 'Popular'
        ]);
        Tag::create([
            'name' => 'New'
        ]);
        Tag::create([
            'name' => 'Economical'
        ]);
        Tag::create([
            'name' => 'Ecological'
        ]);
    }
}
