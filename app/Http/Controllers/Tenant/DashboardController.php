<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Claim;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display the tenant dashboard with key metrics and recent activities.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Get count of lost and found items for this tenant's organization
        $user = auth()->user();
        if (!$user || !$user->organization_id) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $organizationId = $user->organization_id;
        
        $lostItemsCount = LostItem::where('organization_id', $organizationId)->count();
        $foundItemsCount = FoundItem::where('organization_id', $organizationId)->count();
        
        // Get claims related to found items belonging to this organization
        $foundItemIds = FoundItem::where('organization_id', $organizationId)->pluck('id')->toArray();
        $pendingClaimsCount = Claim::whereIn('found_item_id', $foundItemIds)
            ->where('status', 'pending')
            ->count();
        $staffCount = User::where('organization_id', $organizationId)
            ->where('role', 'user')
            ->count();
            
        // Get recent lost items
        $recentLostItems = LostItem::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get recent found items
        $recentFoundItems = FoundItem::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get recent claims for found items in this organization
        $recentClaims = Claim::whereIn('found_item_id', $foundItemIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('tenant.dashboard', compact(
            'lostItemsCount', 
            'foundItemsCount', 
            'pendingClaimsCount', 
            'staffCount',
            'recentLostItems',
            'recentFoundItems',
            'recentClaims'
        ));
    }
    
    /**
     * Display the tenant organization settings page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function settings()
    {
        $user = auth()->user();
        if (!$user || !$user->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $organization = $user->organization;
        
        return view('tenant.settings', compact('organization'));
    }
    
    /**
     * Update tenant organization settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $organization = $user->organization;
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color_theme' => 'required|string|in:indigo,blue,green,red,purple,pink,yellow,gray',
            'sidebar_bg' => 'required|string|in:default,gradient,pattern-dots,pattern-lines,pattern-grid',
        ]);
        
        $organization->name = $validated['name'];
        $organization->address = $validated['address'];
        $organization->color_theme = $validated['color_theme'];
        $organization->sidebar_bg = $validated['sidebar_bg'];
        
        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('organization-logos', 'public');
            $organization->logo = $logoPath;
        }
        
        $organization->save();
        
        return redirect()->route('tenant.settings')
            ->with('success', 'Organization settings updated successfully.');
    }
}