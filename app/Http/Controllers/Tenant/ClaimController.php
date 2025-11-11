<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\UserDevice;
use App\Services\ExpoPushService;
use App\Services\NotificationService;

class ClaimController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index()
    {
        $organization = auth()->user()->organization;

        $query = Claim::where('organization_id', $organization->id)
            ->with(['user', 'foundItem', 'lostItem', 'resolvedBy'])
            ->latest();

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('type')) {
            if (request('type') === 'found') {
                $query->whereNotNull('found_item_id');
            } elseif (request('type') === 'lost') {
                $query->whereNotNull('lost_item_id');
            }
        }

        if (request('q')) {
            $q = '%' . request('q') . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('claim_reason', 'like', $q)
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('email', 'like', $q)
                          ->orWhere('first_name', 'like', $q)
                          ->orWhere('last_name', 'like', $q);
                    })
                    ->orWhereHas('foundItem', function ($fi) use ($q) {
                        $fi->where('title', 'like', $q);
                    })
                    ->orWhereHas('lostItem', function ($li) use ($q) {
                        $li->where('title', 'like', $q);
                    });
            });
        }

        $claims = $query->paginate(10);

        return view('tenant.claims.index', compact('claims'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'claim_reason'   => 'required|string|max:1000',
            'photo'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'claim_datetime' => 'required|date',
            'location'       => 'required|string|max:255',
            'found_item_id'  => 'nullable|exists:found_items,id',
            'lost_item_id'   => 'nullable|exists:lost_items,id',
        ]);

        $data = $request->only([
            'claim_reason',
            'found_item_id',
            'lost_item_id',
            'claim_datetime',
            'location',
        ]);

        $data['user_id'] = Auth::id();
        $data['organization_id'] = Auth::user()->organization_id;
        $data['status'] = 'pending';

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('claims', 'public');
        }

        $claim = Claim::create($data);

        // Send notification to organization admin about new claim
        try {
            $this->notificationService->notifyNewClaim($claim);
        } catch (\Exception $e) {
            Log::error('Failed to send notification for new claim: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Your claim has been submitted successfully.');
    }

    public function show($id)
    {
        $organization = auth()->user()->organization;
        $claim = Claim::where('id', $id)
            ->where('organization_id', $organization->id)
            ->with(['user', 'foundItem', 'foundItem.user', 'lostItem'])
            ->firstOrFail();

        return view('tenant.claims.show', compact('claim'));
    }

    public function update(Request $request, $id)
{
    $claim = Claim::where('id', $id)
        ->where('organization_id', Auth::user()->organization_id)
        ->firstOrFail();

    $request->validate([
        'claim_reason'   => 'required|string|max:1000',
        'photo'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'claim_datetime' => 'required|date',
        'location'       => 'required|string|max:255',
        'found_item_id'  => 'nullable|exists:found_items,id',
        'lost_item_id'   => 'nullable|exists:lost_items,id',
    ]);

    $data = $request->only([
        'claim_reason',
        'claim_datetime',
        'location',
    ]);

    if ($request->hasFile('photo')) {
        if ($claim->photo && Storage::disk('public')->exists($claim->photo)) {
            Storage::disk('public')->delete($claim->photo);
        }
        $data['photo'] = $request->file('photo')->store('claims', 'public');
    }

    $claim->update($data);

    return redirect()->back()->with('success', 'Claim has been updated successfully.');
}


    public function approve($id)
    {
        $claim = Claim::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->with(['user', 'foundItem', 'lostItem', 'organization'])
            ->firstOrFail();

        // Generate claim code if not exists
        if (!$claim->claim_code) {
            $claim->claim_code = Claim::generateClaimCode();
        }

        $claim->status = 'approved';
        $claim->resolved_at = now();
        $claim->resolved_by = auth()->id();
        $claim->save();

        // Send notification using NotificationService
        try {
            $this->notificationService->notifyClaimStatusUpdate($claim, 'approved');
        } catch (\Exception $e) {
            Log::error('Failed to send claim approval notification: ' . $e->getMessage());
        }

        // Also send push notification for mobile app
        try {
            $expo = app(ExpoPushService::class);
            $tokens = UserDevice::where('user_id', $claim->user_id)->pluck('expo_push_token')->all();
            $itemTitle = optional($claim->foundItem)->title ?? optional($claim->lostItem)->title ?? 'Item';
            $expo->send($tokens, 'Claim approved', "Your claim for {$itemTitle} has been approved. Please visit the office to retrieve your item.", [
                'type' => 'claim_approved', 
                'claim_id' => $claim->id,
                'claim_code' => $claim->claim_code,
            ]);
        } catch (\Throwable $e) { 
            Log::warning('Expo notify approve failed: '.$e->getMessage()); 
        }

        $foundItem = $claim->foundItem;
        if ($foundItem) {
            $foundItem->status = 'claimed';
            $foundItem->save();

            // Notify the original reporter that their item has been claimed
            try {
                $this->notificationService->notifyItemClaimed($foundItem, $claim);
            } catch (\Exception $e) {
                Log::error('Failed to send item claimed notification: ' . $e->getMessage());
            }

            // Notify organization that item has been completed
            try {
                $this->notificationService->notifyItemCompleted($foundItem, $claim);
            } catch (\Exception $e) {
                Log::error('Failed to send item completed notification: ' . $e->getMessage());
            }

            Claim::where('found_item_id', $foundItem->id)
                ->where('id', '!=', $claim->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);
        }

        $lostItem = $claim->lostItem;
        if ($lostItem) {
            $lostItem->status = 'returned';
            $lostItem->save();

            // Notify the original reporter that their item has been returned
            try {
                $this->notificationService->notifyItemClaimed($lostItem, $claim);
            } catch (\Exception $e) {
                Log::error('Failed to send item returned notification: ' . $e->getMessage());
            }

            // Notify organization that item has been completed
            try {
                $this->notificationService->notifyItemCompleted($lostItem, $claim);
            } catch (\Exception $e) {
                Log::error('Failed to send item completed notification: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Claim has been approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $claim = Claim::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();

        $claim->status = 'rejected';
        $claim->resolved_at = now();
        $claim->resolved_by = auth()->id();
        $claim->rejection_reason = $request->input('rejection_reason');
        $claim->save();

        // Send notification using NotificationService
        try {
            $this->notificationService->notifyClaimStatusUpdate($claim, 'rejected');
        } catch (\Exception $e) {
            Log::error('Failed to send claim rejection notification: ' . $e->getMessage());
        }

        // Also send push notification for mobile app
        try {
            $expo = app(ExpoPushService::class);
            $tokens = UserDevice::where('user_id', $claim->user_id)->pluck('expo_push_token')->all();
            $itemTitle = optional($claim->foundItem)->title ?? optional($claim->lostItem)->title ?? 'Item';
            $expo->send($tokens, 'Claim rejected', "Your claim for {$itemTitle} was rejected after verification.", [
                'type' => 'claim_rejected', 'claim_id' => $claim->id,
            ]);
        } catch (\Throwable $e) { Log::warning('Expo notify reject failed: '.$e->getMessage()); }

        return redirect()->back()->with('success', 'Claim has been rejected successfully.');
    }

    /**
     * Review claim details for modal
     */
    public function review($id)
    {
        $organization = auth()->user()->organization;
        $claim = Claim::where('id', $id)
            ->where('organization_id', $organization->id)
            ->with(['user', 'foundItem', 'foundItem.user', 'lostItem', 'lostItem.user', 'resolvedBy'])
            ->firstOrFail();

        // Additional validation to ensure we have either foundItem or lostItem
        if (!$claim->foundItem && !$claim->lostItem) {
            return redirect()->back()->with('error', 'Invalid claim: No associated item found.');
        }

        return view('tenant.claims.review', compact('claim'));
    }


}
