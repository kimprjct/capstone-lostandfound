<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Organization;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first organization or create one if none exists
        $organization = Organization::first();
        if (!$organization) {
            $this->call(OrganizationSeeder::class);
            $organization = Organization::first();
        }

        // Create a tenant user for the organization if it doesn't exist
        if (!User::where('email', 'tenant@lostfound.com')->exists()) {
            User::create([
                'first_name' => 'Tenant',
                'middle_name' => '',
                'last_name' => 'User',
                'address' => 'Tenant Address',
                'phone_number' => '9876543210',
                'email' => 'tenant@lostfound.com',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'organization_id' => $organization->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
