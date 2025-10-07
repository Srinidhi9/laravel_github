<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin1@example.com',
            'password' => Hash::make('admin@123'),
            'role' => 'admin',
            'phone_number' => '9999999999',
            'created_by' => null,
        ]);
    }
}
