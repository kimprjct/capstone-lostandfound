<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\Claim;
use Illuminate\Http\Request;

class FoundItemController extends Controller
{
    /**
     * Display a listing of found items.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $foundItems = FoundItem::where('status', 'available')
            ->latest()
            ->paginate(10);
            
        return view('user.found-items.index', compact('foundItems'));
    }

    /**
     * Display the specified found item.
     *
     * @param  \App\Models\FoundItem  $foundItem
     * @return \Illuminate\Contracts\View\View
     */
    public function show(FoundItem $foundItem)
    {
        return view('user.found-items.show', compact('foundItem'));
    }

    /**
     * Claim a found item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FoundItem  $foundItem
     * @return \Illuminate\Http\RedirectResponse
     */
    public function claim(Request $request, FoundItem $foundItem)
    {
        $request->validate([
            'description' => 'required|string|min:10',
            'contact_info' => 'required|string',
        ]);

        $claim = new Claim();
        $claim->user_id = auth()->id();
        $claim->found_item_id = $foundItem->id;
        $claim->description = $request->description;
        $claim->contact_info = $request->contact_info;
        $claim->status = 'pending';
        $claim->save();

        return redirect()->route('user.claims.index')
            ->with('success', 'Your claim has been submitted and is pending review.');
    }
}
