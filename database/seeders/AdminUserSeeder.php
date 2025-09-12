<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if admin user already exists
        if (!User::where('email', 'admin@lostfound.com')->exists()) {
            User::create([
                'first_name' => 'Admin',
                'middle_name' => '',
                'last_name' => 'User',
                'address' => 'Admin Address',
                'phone_number' => '1234567890',
                'email' => 'admin@lostfound.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]);
        }
    }
}
