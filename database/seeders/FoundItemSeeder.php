<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FoundItem;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;

class FoundItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all organizations
        $organizations = Organization::all();
        
        // For each organization, create 5 found items
        foreach ($organizations as $organization) {
            // Get staff users from this organization
            $staffUsers = User::where('organization_id', $organization->id)->get();
            
            if ($staffUsers->isEmpty()) {
                continue;
            }
            
            // Create 5 found items for each organization
            $categories = ['Electronics', 'Clothing', 'Accessories', 'Documents', 'Keys', 'Other'];
            $locations = ['Library', 'Cafeteria', 'Classroom 101', 'Main Hall', 'Parking Lot', 'Reception', 'Lobby'];
            $statuses = ['found', 'claimed', 'archived'];
            
            for ($i = 1; $i <= 5; $i++) {
                $user = $staffUsers->random();
                $date = Carbon::now()->subDays(rand(1, 30));
                
                FoundItem::create([
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                    'title' => 'Found Item ' . $i . ' - ' . $organization->name,
                    'description' => 'This is a description for found item ' . $i . ' in ' . $organization->name . '. The item was found and reported by staff.',
                    'category' => $categories[array_rand($categories)],
                    'location' => $locations[array_rand($locations)],
                    'date_found' => $date,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }
}
