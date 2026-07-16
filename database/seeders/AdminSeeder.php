<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cbt.com'],
            [
                'role' => 'admin',
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'isActive' => true,
            ]
        );
    }
}
