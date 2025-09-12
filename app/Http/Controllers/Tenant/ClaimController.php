<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\FoundItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $organization = auth()->user()->organization;
        $claims = Claim::where('organization_id', $organization->id)
            ->with(['user', 'foundItem'])
            ->latest()
            ->paginate(10);
            
        return view('tenant.claims.index', compact('claims'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        $claim = Claim::where('id', $id)
            ->where('organization_id', $organization->id)
            ->with(['user', 'foundItem', 'foundItem.user'])
            ->firstOrFail();
            
        return view('tenant.claims.show', compact('claim'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    /**
     * Approve a claim request
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function approve($id)
    {
        $claim = \App\Models\Claim::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();
            
        // Update claim status
        $claim->status = 'approved';
        $claim->resolved_at = now();
        $claim->resolved_by = auth()->id();
        $claim->save();
        
        // Update found item status
        $foundItem = $claim->foundItem;
        $foundItem->status = 'claimed';
        $foundItem->save();
        
        // Reject other pending claims for this item
        \App\Models\Claim::where('found_item_id', $foundItem->id)
            ->where('id', '!=', $claim->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'resolved_at' => now(),
                'resolved_by' => auth()->id()
            ]);
        
        return redirect()->back()->with('success', 'Claim has been approved successfully.');
    }
    
    /**
     * Reject a claim request
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function reject($id)
    {
        $claim = \App\Models\Claim::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();
            
        $claim->status = 'rejected';
        $claim->resolved_at = now();
        $claim->resolved_by = auth()->id();
        $claim->save();
        
        return redirect()->back()->with('success', 'Claim has been rejected successfully.');
    }
}
