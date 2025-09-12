<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Only create if no organizations exist yet
        if (Organization::count() === 0) {
            Organization::create([
                'name' => 'Sample Organization',
                'address' => '123 Main Street, Sample City',
                'color_theme' => 'indigo',
                'sidebar_bg' => 'default',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
