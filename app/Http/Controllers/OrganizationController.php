<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $organizations = Organization::withCount(['users as staff_count' => function($query) {
            $query->where('role', 'tenant');
        }])
        ->withCount('lostItems', 'foundItems')
        ->latest()
        ->paginate(12);
        
        return view('organizations.index', compact('organizations'));
    }

    /**
     * Display the specified organization.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $organization = Organization::with([
            'users' => function($query) {
                $query->where('role', 'tenant');
            },
            'lostItems' => function($query) {
                $query->latest()->take(5);
            },
            'foundItems' => function($query) {
                $query->latest()->take(5);
            }
        ])->findOrFail($id);
        
        return view('organizations.show', compact('organization'));
    }
}
