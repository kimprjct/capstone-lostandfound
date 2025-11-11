<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organizations = [
            [
                'name' => 'Surigao Del Norte State University',
                'address' => 'Surigao City, Surigao del Norte',
                'color_theme' => 'blue',
                'sidebar_bg' => 'default',
                'claim_location' => 'Main Campus, Surigao City',
                'office_hours' => '8:00 AM - 5:00 PM',
                'admin_email' => 'snsuadmin@gmail.com',
                'admin_name' => 'SNDSU Admin',
            ],
            [
                'name' => 'NEMCO',
                'address' => 'Surigao City, Surigao del Norte',
                'color_theme' => 'green',
                'sidebar_bg' => 'default',
                'claim_location' => 'NEMCO Office, Surigao City',
                'office_hours' => '8:00 AM - 5:00 PM',
                'admin_email' => 'nemcoadmin@gmail.com',
                'admin_name' => 'NEMCO Admin',
            ],
            [
                'name' => 'SEC',
                'address' => 'Surigao City, Surigao del Norte',
                'color_theme' => 'purple',
                'sidebar_bg' => 'default',
                'claim_location' => 'SEC Office, Surigao City',
                'office_hours' => '8:00 AM - 5:00 PM',
                'admin_email' => 'secadmin@gmail.com',
                'admin_name' => 'SEC Admin',
            ],
            [
                'name' => 'Gaisano',
                'address' => 'Surigao City, Surigao del Norte',
                'color_theme' => 'red',
                'sidebar_bg' => 'default',
                'claim_location' => 'Gaisano Mall, Surigao City',
                'office_hours' => '9:00 AM - 9:00 PM',
                'admin_email' => 'gaisanoadmin@gmail.com',
                'admin_name' => 'Gaisano Admin',
            ],
        ];

        foreach ($organizations as $orgData) {
            // Check if organization already exists, if not create it
            $organization = Organization::where('name', $orgData['name'])->first();
            
            if (!$organization) {
                // Create organization
                $organization = Organization::create([
                    'name' => $orgData['name'],
                    'address' => $orgData['address'],
                    'color_theme' => $orgData['color_theme'],
                    'sidebar_bg' => $orgData['sidebar_bg'],
                    'claim_location' => $orgData['claim_location'],
                    'office_hours' => $orgData['office_hours'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Always create or update admin user for the organization
            $existingUser = User::where('email', $orgData['admin_email'])->first();
            
            if (!$existingUser) {
                // Create new admin user
                User::create([
                    'first_name' => explode(' ', $orgData['admin_name'])[0],
                    'middle_name' => '',
                    'last_name' => explode(' ', $orgData['admin_name'])[1] ?? '',
                    'phone_number' => '',
                    'email' => $orgData['admin_email'],
                    'password' => Hash::make($orgData['admin_email']), // Password same as email
                    'role' => 'admin',
                    'organization_id' => $organization->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Update existing user to admin role and assign to organization
                $existingUser->update([
                    'role' => 'admin',
                    'organization_id' => $organization->id,
                    'password' => Hash::make($orgData['admin_email']), // Update password to match email
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
