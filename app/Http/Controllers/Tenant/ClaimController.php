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
use App\Services\SmsService;

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

        // Keep status as 'pending' - claimant needs to visit office for verification
        // Status will change to 'approved' or 'rejected' after in-person verification
        // Do NOT update item status yet - that happens after successful in-person verification
        $claim->save();

        // Send notification to claimant to visit office (status still pending)
        try {
            $this->notificationService->notifyClaimStatusUpdate($claim, 'approved');
        } catch (\Exception $e) {
            Log::error('Failed to send claim approval notification: ' . $e->getMessage());
        }

        // Send SMS to claimant with detailed info about visiting office
        try {
            /** @var SmsService $sms */
            $sms = app(SmsService::class);
            $itemTitle = optional($claim->foundItem)->title ?? optional($claim->lostItem)->title ?? 'your item';
            $organization = $claim->organization ?? optional($claim->foundItem)->organization ?? optional($claim->lostItem)->organization;
            $location = optional($organization)->claim_location ?? ($claim->location ?? optional($claim->foundItem)->location ?? optional($claim->lostItem)->location ?? 'the office');
            $officeHours = optional($organization)->office_hours ?? 'office hours';
            $requirements = 'Valid ID and proof of ownership';
            $code = $claim->claim_code ?: '';
            $message = "FoundU - Your claim for {$itemTitle} has been approved for verification!\nPlease visit the office for in-person verification.\nLocation: {$location}\nOffice Hours: {$officeHours}\nBring: {$requirements}" . ($code ? "\nReference Code: {$code}" : '');
            
            // Ensure user relationship is loaded
            if (!$claim->relationLoaded('user')) {
                $claim->load('user');
            }
            
            $recipient = $claim->user ? $claim->user->phone_number : null;
            
            if ($recipient) {
                Log::info('Sending SMS notification for claim approval', [
                    'claim_id' => $claim->id,
                    'user_id' => $claim->user_id,
                    'phone_number' => $recipient,
                ]);
                
                $smsResult = $sms->send($recipient, $message);
                
                if ($smsResult) {
                    Log::info('SMS notification sent successfully for claim approval', [
                        'claim_id' => $claim->id,
                        'user_id' => $claim->user_id,
                    ]);
                } else {
                    Log::warning('SMS notification failed to send for claim approval', [
                        'claim_id' => $claim->id,
                        'user_id' => $claim->user_id,
                        'phone_number' => $recipient,
                    ]);
                }
            } else {
                Log::warning('Cannot send SMS for claim approval: user phone number is missing', [
                    'claim_id' => $claim->id,
                    'user_id' => $claim->user_id,
                    'user_exists' => $claim->user !== null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('SMS claimant approve failed', [
                'claim_id' => $claim->id,
                'user_id' => $claim->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Also send push notification for mobile app
        try {
            $expo = app(ExpoPushService::class);
            $tokens = UserDevice::where('user_id', $claim->user_id)->pluck('expo_push_token')->all();
            $itemTitle = optional($claim->foundItem)->title ?? optional($claim->lostItem)->title ?? 'Item';
            $expo->send($tokens, 'Visit office for verification', "Your claim for {$itemTitle} has been approved. Please visit the office for in-person verification.", [
                'type' => 'claim_approved', 
                'claim_id' => $claim->id,
                'claim_code' => $claim->claim_code,
            ]);
        } catch (\Throwable $e) { 
            Log::warning('Expo notify approve failed: '.$e->getMessage()); 
        }

        // Do NOT update item status yet - that happens after successful in-person verification via "Claim" button

        return redirect()->back()->with('success', 'Claim has been approved. Claimant will be notified to visit the office for verification.');
    }

    public function reject(Request $request, $id)
    {
        // Require rejection reason
        $request->validate([
            'rejection_reason' => 'required|string|min:3',
        ]);
        $claim = Claim::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->with(['user', 'foundItem', 'lostItem'])
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

        // Send SMS to claimant about rejection
        try {
            /** @var SmsService $sms */
            $sms = app(SmsService::class);
            $itemTitle = optional($claim->foundItem)->title ?? optional($claim->lostItem)->title ?? 'your item';
            
            // Ensure user relationship is loaded
            if (!$claim->relationLoaded('user')) {
                $claim->load('user');
            }
            
            $recipient = $claim->user ? $claim->user->phone_number : null;
            
            if ($recipient) {
                $reason = $claim->rejection_reason ?: 'No reason provided';
                $message = "FoundU - Your claim for {$itemTitle} has been rejected. Reason: {$reason}";
                
                Log::info('Sending SMS notification for claim rejection', [
                    'claim_id' => $claim->id,
                    'user_id' => $claim->user_id,
                    'phone_number' => $recipient,
                ]);
                
                $smsResult = $sms->send($recipient, $message);
                
                if ($smsResult) {
                    Log::info('SMS notification sent successfully for claim rejection', [
                        'claim_id' => $claim->id,
                        'user_id' => $claim->user_id,
                    ]);
                } else {
                    Log::warning('SMS notification failed to send for claim rejection', [
                        'claim_id' => $claim->id,
                        'user_id' => $claim->user_id,
                        'phone_number' => $recipient,
                    ]);
                }
            } else {
                Log::warning('Cannot send SMS for claim rejection: user phone number is missing', [
                    'claim_id' => $claim->id,
                    'user_id' => $claim->user_id,
                    'user_exists' => $claim->user !== null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('SMS claimant rejection failed', [
                'claim_id' => $claim->id,
                'user_id' => $claim->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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

    /**
     * Mark claim as successfully verified and released (in-person verification successful)
     */
    public function markClaimed(Request $request, $id)
    {
        $claim = Claim::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->with(['foundItem', 'lostItem'])
            ->firstOrFail();

        if ($claim->status !== 'pending') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Only pending claims can be marked as claimed.'], 422);
            }
            return redirect()->back()->with('error', 'Only pending claims can be marked as claimed.');
        }

        $claim->status = 'approved';
        $claim->resolved_at = now();
        $claim->resolved_by = auth()->id();
        $claim->save();

        // Update item status - item is now claimed/returned
        if ($claim->foundItem) {
            $claim->foundItem->status = \App\Models\FoundItem::STATUS_CLAIMED;
            $claim->foundItem->save();

            // Notify the original reporter that their item has been claimed
            try {
                $this->notificationService->notifyItemClaimed($claim->foundItem, $claim);
            } catch (\Exception $e) {
                Log::error('Failed to send item claimed notification: ' . $e->getMessage());
            }

            // Send SMS to the user who reported the FOUND item
            try {
                /** @var SmsService $sms */
                $sms = app(SmsService::class);
                $recipient = optional($claim->foundItem->user)->phone_number;
                if ($recipient) {
                    $sms->send($recipient, 'FoundU - The item you reported as FOUND has been successfully claimed by its rightful owner. Thank you for your honesty and cooperation!');
                }
            } catch (\Throwable $e) {
                Log::warning('SMS found reporter notify failed: '.$e->getMessage());
            }

            // Reject other pending claims for the same item
            Claim::where('found_item_id', $claim->foundItem->id)
                ->where('id', '!=', $claim->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);
        }

        if ($claim->lostItem) {
            $claim->lostItem->status = \App\Models\LostItem::STATUS_CLAIMED;
            $claim->lostItem->save();

            // Notify the original reporter that their item has been returned
            try {
                $this->notificationService->notifyItemClaimed($claim->lostItem, $claim);
            } catch (\Exception $e) {
                Log::error('Failed to send item returned notification: ' . $e->getMessage());
            }
        }

        // Notify claimant of successful verification
        try {
            $this->notificationService->notifyClaimStatusUpdate($claim, 'approved');
        } catch (\Exception $e) {
            Log::error('Failed to send claim completion notification: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Claim has been verified and item released successfully.');
    }

    /**
     * Mark claim as rejected after in-person verification (failed verification)
     */
    public function rejectInPerson(Request $request, $id)
    {
        $claim = Claim::where('id', $id)
            ->where('organization_id', auth()->user()->organization_id)
            ->firstOrFail();

        if ($claim->status !== 'pending') {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Only pending claims can be rejected in person.'], 422);
            }
            return redirect()->back()->with('error', 'Only pending claims can be rejected in person.');
        }

        $claim->status = 'rejected';
        $claim->resolved_at = now();
        $claim->resolved_by = auth()->id();
        $claim->rejection_reason = $claim->rejection_reason ?: 'Rejected after in-person verification';
        $claim->save();

        // Notify claimant of rejection
        try {
            $this->notificationService->notifyClaimStatusUpdate($claim, 'rejected');
        } catch (\Exception $e) {
            Log::error('Failed to send claim rejection notification: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Claim has been marked as rejected after in-person verification.');
    }

}
