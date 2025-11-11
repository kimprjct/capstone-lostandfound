<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'first_name' => 'Ma. Norielle',
                'middle_name' => 'P.',
                'last_name' => 'Serato',
                'address' => 'Surigao City',
                'phone_number' => '09094729802',
                'email' => 'lala@gmail.com',
                'password' => 'lala@gmail.com',
                'role' => 'user',
            ],
            [
                'first_name' => 'Rena',
                'middle_name' => 'B.',
                'last_name' => 'Rabe',
                'address' => 'Surigao City',
                'phone_number' => '09094729801',
                'email' => 'renarabe@gmail.com',
                'password' => 'renarabe@gmail.com',
                'role' => 'user',
            ],
            [
                'first_name' => 'Kimby',
                'middle_name' => 'A.',
                'last_name' => 'Pareja',
                'address' => 'Surigao City',
                'phone_number' => '09094729803',
                'email' => 'kimby@gmail.com',
                'password' => 'kimby@gmail.com',
                'role' => 'user',
            ],
            [
                'first_name' => 'Mark Gerald',
                'middle_name' => 'F.',
                'last_name' => 'Arena',
                'address' => 'Surigao City',
                'phone_number' => '09094729804',
                'email' => 'jemboy@gmail.com',
                'password' => 'jemboy@gmail.com',
                'role' => 'user',
            ],
        ];

        foreach ($users as $userData) {
            // Check if user already exists
            $existingUser = User::where('email', $userData['email'])->first();
            
            if (!$existingUser) {
                // Create new user with hashed password
                User::create([
                    'first_name' => $userData['first_name'],
                    'middle_name' => $userData['middle_name'],
                    'last_name' => $userData['last_name'],
                    'address' => $userData['address'],
                    'phone_number' => $userData['phone_number'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']), // Hash the password
                    'role' => $userData['role'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Update existing user's password if needed
                $existingUser->update([
                    'password' => Hash::make($userData['password']), // Update with hashed password
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
