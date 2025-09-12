<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\Organization;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoundItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->organization_id) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $organizationId = $user->organization_id;
        
        // Get all found items for this organization
        $foundItems = FoundItem::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Debug information
        \Log::info('Found Items Count: ' . $foundItems->count());
        \Log::info('Organization ID: ' . $organizationId);
            
        return view('tenant.found-items.index', compact('foundItems'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('tenant.found-items.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date_found' => 'required|date|before_or_equal:today',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $foundItem = new \App\Models\FoundItem();
        $foundItem->title = $request->title;
        $foundItem->description = $request->description;
        $foundItem->category = $request->category;
        $foundItem->location = $request->location;
        $foundItem->date_found = $request->date_found;
        $foundItem->status = 'found';
        $foundItem->user_id = auth()->id();
        $foundItem->organization_id = auth()->user()->organization_id;
        
        if ($request->hasFile('image')) {
            $foundItem->image = $request->file('image')->store('found-items', 'public');
        }
        
        $foundItem->save();
        
        return redirect()->route('tenant.found-items.index')
            ->with('success', 'Found item has been reported successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $organization = auth()->user()->organization;
        $foundItem = \App\Models\FoundItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->with(['user'])
            ->firstOrFail();
            
        return view('tenant.found-items.show', compact('foundItem'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $organization = auth()->user()->organization;
        $foundItem = \App\Models\FoundItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();
            
        return view('tenant.found-items.edit', compact('foundItem'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $organization = auth()->user()->organization;
        $foundItem = \App\Models\FoundItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();
            
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date_found' => 'required|date|before_or_equal:today',
            'status' => 'required|in:found,claimed,archived',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $foundItem->title = $request->title;
        $foundItem->description = $request->description;
        $foundItem->category = $request->category;
        $foundItem->location = $request->location;
        $foundItem->date_found = $request->date_found;
        $foundItem->status = $request->status;
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($foundItem->image) {
                \Storage::disk('public')->delete($foundItem->image);
            }
            $foundItem->image = $request->file('image')->store('found-items', 'public');
        }
        
        // Handle remove image checkbox
        if ($request->has('remove_image') && $foundItem->image) {
            \Storage::disk('public')->delete($foundItem->image);
            $foundItem->image = null;
        }
        
        $foundItem->save();
        
        return redirect()->route('tenant.found-items.show', $foundItem->id)
            ->with('success', 'Found item has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        $organization = auth()->user()->organization;
        $foundItem = \App\Models\FoundItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();
            
        // Delete the image if it exists
        if ($foundItem->image) {
            \Storage::disk('public')->delete($foundItem->image);
        }
        
        $foundItem->delete();
        
        return redirect()->route('tenant.found-items.index')
            ->with('success', 'Found item has been deleted successfully.');
    }
}
