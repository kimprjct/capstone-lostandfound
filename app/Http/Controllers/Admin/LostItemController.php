<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use Illuminate\Http\Request;

class LostItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lostItems = LostItem::with(['user', 'organization'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.lost-items.index', compact('lostItems'));
    }

    /**
     * Display the specified resource.
     */
    public function show(LostItem $lostItem)
    {
        $lostItem->load(['user', 'organization', 'photos']);
        
        return view('admin.lost-items.show', compact('lostItem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LostItem $lostItem)
    {
        return view('admin.lost-items.edit', compact('lostItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LostItem $lostItem)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'location' => 'required|string',
            'date_lost' => 'required|date',
            'status' => 'required|in:unresolved,resolved,returned',
        ]);

        $lostItem->update($request->all());

        return redirect()->route('admin.lost-items.show', $lostItem)
            ->with('success', 'Lost item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LostItem $lostItem)
    {
        $lostItem->delete();

        return redirect()->route('admin.lost-items.index')
            ->with('success', 'Lost item deleted successfully.');
    }
}
