<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use Illuminate\Http\Request;

class FoundItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $foundItems = FoundItem::with(['user', 'organization'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.found-items.index', compact('foundItems'));
    }

    /**
     * Display the specified resource.
     */
    public function show(FoundItem $foundItem)
    {
        $foundItem->load(['user', 'organization', 'photos']);
        
        return view('admin.found-items.show', compact('foundItem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FoundItem $foundItem)
    {
        return view('admin.found-items.edit', compact('foundItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FoundItem $foundItem)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'location' => 'required|string',
            'date_found' => 'required|date',
            'status' => 'required|in:unclaimed,claimed,returned',
        ]);

        $foundItem->update($request->all());

        return redirect()->route('admin.found-items.show', $foundItem)
            ->with('success', 'Found item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FoundItem $foundItem)
    {
        $foundItem->delete();

        return redirect()->route('admin.found-items.index')
            ->with('success', 'Found item deleted successfully.');
    }
}
