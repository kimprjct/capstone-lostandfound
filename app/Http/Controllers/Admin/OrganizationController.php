<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Claim;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $organizations = Organization::latest()->paginate(10);
        return view('admin.organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'logo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('logo');
        
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('organization_logos', 'public');
        }

        Organization::create($data);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $organization = Organization::findOrFail($id);

        // Lost Items for this organization
        $lostItems = LostItem::where('organization_id', $id)
            ->with(['user', 'photos'])
            ->latest()
            ->paginate(10);

        // Found Items for this organization
        $foundItems = FoundItem::where('organization_id', $id)
            ->with(['user', 'photos'])
            ->latest()
            ->paginate(10);

        // Claims for this organization
        $claims = Claim::where('organization_id', $id)
            ->with(['user', 'foundItem'])
            ->latest()
            ->paginate(10);

        // Users under this organization
        $users = User::where('organization_id', $id)
            ->latest()
            ->paginate(10);

        return view('admin.organizations.show', compact(
            'organization',
            'lostItems',
            'foundItems',
            'claims',
            'users'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $organization = Organization::findOrFail($id);
        return view('admin.organizations.edit', compact('organization'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);
        
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'logo'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('logo');
        
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }
            
            $data['logo'] = $request->file('logo')->store('organization_logos', 'public');
        }

        $organization->update($data);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);

        // Delete logo if exists
        if ($organization->logo) {
            Storage::disk('public')->delete($organization->logo);
        }
        
        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully!');
    }
}
