<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use App\Models\UserDevice;
use App\Services\ExpoPushService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

class ClaimApiController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $organizationId = $request->query('organization_id');

        $query = Claim::with([
            'foundItem:id,title,image',
            'lostItem:id,title,image',
        ])
            ->where('user_id', $userId)
            ->orderByDesc('created_at');

        if (!empty($organizationId)) {
            $query->where('organization_id', (int) $organizationId);
        }

        $claims = $query->get();

        return response()->json([
            'success' => true,
            'data' => $claims,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'found_item_id' => 'nullable|exists:found_items,id',
            'lost_item_id' => 'nullable|exists:lost_items,id',
            'claim_reason' => 'required|string|min:5',
            'location' => 'nullable|string|max:255',
            'claim_datetime' => 'nullable|date',
            'time_lost' => 'nullable|date_format:H:i:s',
            'time_found' => 'nullable|date_format:H:i:s',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();
        $foundItemId = $request->filled('found_item_id') ? (int) $request->input('found_item_id') : null;
        $lostItemId = $request->filled('lost_item_id') ? (int) $request->input('lost_item_id') : null;

        if (!$foundItemId && !$lostItemId) {
            return response()->json([
                'success' => false,
                'message' => 'Either found_item_id or lost_item_id is required.'
            ], 422);
        }

        $duplicateQuery = Claim::where('user_id', $userId);
        if ($foundItemId) $duplicateQuery->where('found_item_id', $foundItemId);
        if ($lostItemId) $duplicateQuery->where('lost_item_id', $lostItemId);
        if ($duplicateQuery->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You already submitted a claim for this item.'
            ], 409);
        }

        $organizationId = null;
        if ($foundItemId) {
            $foundItem = FoundItem::findOrFail($foundItemId);
            $organizationId = $foundItem->organization_id;
        } elseif ($lostItemId) {
            $lostItem = LostItem::findOrFail($lostItemId);
            $organizationId = $lostItem->organization_id ?? null;
        }

        $claim = new Claim();
        $claim->user_id = $userId;
        if ($foundItemId) $claim->found_item_id = $foundItemId;
        if ($lostItemId) $claim->lost_item_id = $lostItemId;
        $claim->claim_reason = $request->input('claim_reason');
        if ($request->filled('location') && Schema::hasColumn('claims', 'location')) {
            $claim->location = $request->input('location');
        }
        if ($request->filled('claim_datetime')) {
            $claim->claim_datetime = $request->input('claim_datetime');
        }
        if ($request->filled('time_lost')) {
            $claim->time_lost = $request->input('time_lost');
        }
        if ($request->filled('time_found')) {
            $claim->time_found = $request->input('time_found');
        }
        if ($request->hasFile('photo') && Schema::hasColumn('claims', 'photo')) {
            $claim->photo = $request->file('photo')->store('claims', 'public');
        }
        if (Schema::hasColumn('claims', 'organization_id')) {
            $claim->organization_id = $organizationId;
        }
        $claim->status = 'pending';
        $claim->save();

        // Send notification to organization admin about new claim
        $this->notificationService->notifyNewClaim($claim);
        
        // Notify found item reporter if claiming a found item
        if ($foundItemId) {
            $this->notificationService->notifyFoundItemClaim($foundItem, $claim);
        }

        // Do NOT notify the claimant about their own submission (per requirements)

        // Notify org admins
        try {
            $expo = app(ExpoPushService::class);
            $adminTokens = UserDevice::whereHas('user', function($q) use ($organizationId) {
                $q->where('organization_id', $organizationId)->whereIn('role', ['admin','tenant']);
            })->pluck('expo_push_token')->all();
            $itemTitle = $foundItemId ? ($foundItem->title ?? 'Item') : ($lostItem->title ?? 'Item');
            $expo->send($adminTokens, 'New claim filed', "A user filed a claim for {$itemTitle}. Awaiting in-person verification.", [
                'type' => 'claim_filed',
                'claim_id' => $claim->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Expo notify admins failed: '.$e->getMessage());
        }

        if ($organizationId && Schema::hasColumn('users', 'organization_id')) {
            $user = \App\Models\User::find($userId);
            if ($user && empty($user->organization_id)) {
                $user->organization_id = $organizationId;
                $user->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Claim submitted successfully',
            'data' => $claim,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $claim = Claim::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $maxKb = (int) config('upload.max_photo_mb', 5) * 1024;
        $validator = Validator::make($request->all(), [
            'claim_reason' => 'required|string|min:5',
            'location' => 'nullable|string|max:255',
            'claim_datetime' => 'nullable|date',
            'photo' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:'.$maxKb],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $claim->claim_reason = $request->input('claim_reason');
        if ($request->filled('location')) $claim->location = $request->input('location');
        if ($request->filled('claim_datetime')) $claim->claim_datetime = $request->input('claim_datetime');

        if ($request->hasFile('photo')) {
            if ($claim->photo && Storage::disk('public')->exists($claim->photo)) {
                Storage::disk('public')->delete($claim->photo);
            }
            $claim->photo = $request->file('photo')->store('claims', 'public');
        }

        $claim->save();

        return response()->json([
            'success' => true,
            'message' => 'Claim updated successfully',
            'data' => $claim,
        ]);
    }

    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'found_item_id' => 'nullable|exists:found_items,id',
            'lost_item_id' => 'nullable|exists:lost_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();
        $query = Claim::where('user_id', $userId);
        if ($request->filled('found_item_id')) {
            $query->where('found_item_id', (int) $request->input('found_item_id'));
        }
        if ($request->filled('lost_item_id')) {
            $query->where('lost_item_id', (int) $request->input('lost_item_id'));
        }
        $exists = $query->exists();

        return response()->json([
            'success' => true,
            'alreadyClaimed' => $exists,
        ]);
    }
}
