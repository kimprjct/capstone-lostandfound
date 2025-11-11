<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ClaimController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $claims = Claim::with(['user', 'foundItem', 'lostItem', 'foundItem.organization', 'lostItem.organization'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.claims.index', compact('claims'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Claim $claim)
    {
        $claim->load(['user', 'foundItem', 'lostItem', 'foundItem.organization', 'lostItem.organization']);
        
        return view('admin.claims.show', compact('claim'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Claim $claim)
    {
        return view('admin.claims.edit', compact('claim'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Claim $claim)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed',
            'admin_notes' => 'nullable|string',
        ]);

        $oldStatus = $claim->status;
        $newStatus = $request->status;
        
        $claim->update($request->all());

        // Send notification if status changed
        if ($oldStatus !== $newStatus) {
            try {
                $this->notificationService->notifyClaimStatusUpdate($claim, $newStatus);
            } catch (\Exception $e) {
                Log::error('Failed to send claim status update notification: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.claims.show', $claim)
            ->with('success', 'Claim updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Claim $claim)
    {
        $claim->delete();

        return redirect()->route('admin.claims.index')
            ->with('success', 'Claim deleted successfully.');
    }
}
