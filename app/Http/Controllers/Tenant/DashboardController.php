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
        $user = auth()->user();
        if (!$user || !$user->organization_id) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
    
        $organizationId = $user->organization_id;
    
        // Count Lost and Found Items
        $lostItemsCount = LostItem::where('organization_id', $organizationId)->count();
        $foundItemsCount = FoundItem::where('organization_id', $organizationId)->count();
    
        // Claims - Include both lost and found items
        $foundItemIds = FoundItem::where('organization_id', $organizationId)->pluck('id')->toArray();
        $lostItemIds = LostItem::where('organization_id', $organizationId)->pluck('id')->toArray();
        
        // Count pending claims for both found and lost items
        $pendingClaimsCount = Claim::where(function($query) use ($foundItemIds, $lostItemIds) {
            $query->whereIn('found_item_id', $foundItemIds)
                  ->orWhereIn('lost_item_id', $lostItemIds);
        })->where('status', 'pending')->count();
    
        // Unclaimed Lost Items
            $unclaimedLost = \App\Models\LostItem::where('organization_id', $organizationId)
            ->whereDoesntHave('claims', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->count();

            // Unclaimed Found Items
            $unclaimedFound = \App\Models\FoundItem::where('organization_id', $organizationId)
            ->whereDoesntHave('claims', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->count();

            // ✅ Total Unclaimed Items
            $unclaimedItemsCount = $unclaimedLost + $unclaimedFound;

    
    
        // Recent Lost Items
        $recentLostItems = LostItem::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    
        // Recent Found Items
        $recentFoundItems = FoundItem::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    
        // Recent Claims - Show only approved claims
        $recentClaims = Claim::where(function($query) use ($foundItemIds, $lostItemIds) {
            $query->whereIn('found_item_id', $foundItemIds)
                  ->orWhereIn('lost_item_id', $lostItemIds);
        })->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    
        return view('tenant.dashboard', compact(
            'lostItemsCount',
            'foundItemsCount',
            'pendingClaimsCount',
            'recentLostItems',
            'recentFoundItems',
            'recentClaims',
            'unclaimedItemsCount' // 👈 Added here
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
            'claim_location' => 'nullable|string|max:255',
            'office_hours' => 'nullable|string|max:255',
        ]);
        
        $organization->name = $validated['name'];
        $organization->address = $validated['address'];
        $organization->color_theme = $validated['color_theme'];
        $organization->sidebar_bg = $validated['sidebar_bg'];
        $organization->claim_location = $validated['claim_location'];
        $organization->office_hours = $validated['office_hours'];
        
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
