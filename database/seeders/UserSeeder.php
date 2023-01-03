<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin9999'),
            'phone' => null
        ]);
        $admin->roles()->attach([1, 2]);

        $blogger = User::create([
            'name' => 'blogger',
            'email' => 'blogger@gmail.com',
            'password' => Hash::make('blogger9999'),
            'phone' => null
        ]);

        $blogger->roles()->attach([2]);

        $viewer = User::create([
            'name' => 'viewer',
            'email' => 'viewer@gmail.com',
            'password' => Hash::make('viewer9999'),
            'phone' => null
        ]);
        $viewer->roles()->attach([3]);
    }
}
