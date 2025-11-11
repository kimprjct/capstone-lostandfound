<?php

namespace App\Http\Controllers;

use App\Models\LostItem;   
use App\Models\FoundItem;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations.
     */
    public function index()
    {
        $organizations = Organization::withCount([
                'users as staff_count' => function ($query) {
                    $query->where('role', 'tenant');
                }
            ])
            ->withCount('lostItems', 'foundItems')
            ->latest()
            ->paginate(12);

        return view('organizations.index', compact('organizations'));
    }

    /**
     * Display the specified organization.
     */
    public function show($id)
    {
        $organization = Organization::with([
            'users' => function ($query) {
                $query->where('role', 'tenant');
            },
            'lostItems' => function ($query) {
                $query->with('user')->latest()->take(5);
            },
            'foundItems' => function ($query) {
                $query->with('user')->latest()->take(5);
            }
        ])->findOrFail($id);

        // pass lostItems and foundItems separately for Blade
        $lostItems = LostItem::with('user')->where('organization_id', $organization->id)->get();
$foundItems = FoundItem::with('user')->where('organization_id', $organization->id)->get();

return view('organizations.show', compact('organization', 'lostItems', 'foundItems'));

    }
}
