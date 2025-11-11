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
        // Keep existing users
        $existingUsers = [
            [
                'first_name' => 'Ma. Norielle',
                'middle_name' => 'P.',
                'last_name' => 'Serato',
                'address' => 'Surigao City',
                'phone_number' => '09094729802',
                'email' => 'lala@gmail.com',
                'password' => 'lala@gmail.com',
            ],
            [
                'first_name' => 'Rena',
                'middle_name' => 'B.',
                'last_name' => 'Rabe',
                'address' => 'Surigao City',
                'phone_number' => '09094729801',
                'email' => 'renarabe@gmail.com',
                'password' => 'renarabe@gmail.com',
            ],
            [
                'first_name' => 'Kimby',
                'middle_name' => 'A.',
                'last_name' => 'Pareja',
                'address' => 'Surigao City',
                'phone_number' => '09094729803',
                'email' => 'kimby@gmail.com',
                'password' => 'kimby@gmail.com',
            ],
            [
                'first_name' => 'Mark Gerald',
                'middle_name' => 'F.',
                'last_name' => 'Arena',
                'address' => 'Surigao City',
                'phone_number' => '09094729804',
                'email' => 'jemboy@gmail.com',
                'password' => 'jemboy@gmail.com',
            ],
        ];

        // Create existing users
        foreach ($existingUsers as $userData) {
            $existingUser = User::where('email', $userData['email'])->first();
            
            if (!$existingUser) {
                User::create([
                    'first_name' => $userData['first_name'],
                    'middle_name' => $userData['middle_name'],
                    'last_name' => $userData['last_name'],
                    'address' => $userData['address'],
                    'phone_number' => $userData['phone_number'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'UserTypeID' => 3, // User type
                    'email_verified_at' => now(), // Mark as verified
                ]);
            }
        }

        // Generate 20 additional users using Faker
        $faker = \Faker\Factory::create();
        
        // Philippine cities for addresses
        $philippineCities = [
            'Manila', 'Quezon City', 'Cebu City', 'Davao City', 'Makati',
            'Taguig', 'Pasig', 'Mandaluyong', 'San Juan', 'Muntinlupa',
            'Las Piñas', 'Parañaque', 'Marikina', 'Caloocan', 'Valenzuela',
            'Surigao City', 'Butuan City', 'Cagayan de Oro', 'Iloilo City', 'Bacolod City'
        ];

        $userCount = 0;
        $targetCount = 20;

        while ($userCount < $targetCount) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $email = $faker->unique()->safeEmail;
            
            // Check if user already exists
            $existingUser = User::where('email', $email)->first();
            
            if (!$existingUser) {
                User::create([
                    'first_name' => $firstName,
                    'middle_name' => $faker->optional(0.7)->randomLetter . '.', // 70% chance of having middle name
                    'last_name' => $lastName,
                    'address' => $faker->streetAddress . ', ' . $faker->randomElement($philippineCities),
                    'phone_number' => '09' . $faker->numerify('#########'), // Philippine mobile format
                    'email' => $email,
                    'password' => Hash::make('password123'), // Default password for all seeded users
                    'UserTypeID' => 3, // User type (regular user)
                    'email_verified_at' => now(), // Mark as verified
                ]);
                
                $userCount++;
            }
        }

        $this->command->info("Successfully created {$userCount} new users!");
    }
}
