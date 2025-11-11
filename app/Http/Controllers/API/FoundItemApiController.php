<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\NotificationService;

class FoundItemApiController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Get all found items for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $foundItems = FoundItem::with('photos')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Convert image paths to full URLs
        $foundItems->transform(function ($item) {
            if ($item->image) {
                $item->image_url = asset('storage/' . $item->image);
            }
            if ($item->photos) {
                $item->photos->transform(function ($photo) {
                    $photo->image_url = asset('storage/' . $photo->path);
                    return $photo;
                });
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $foundItems
        ]);
    }

    /**
     * Get all found items for a specific organization
     * This endpoint is publicly accessible without authentication
     *
     * @param  int  $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByOrganization($organizationId): JsonResponse
    {
        // Verify the organization exists
        $organization = Organization::find($organizationId);
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }
        
        // Get all found items for this organization
        $foundItems = FoundItem::with(['user', 'photos'])
            ->where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->reporter_name = optional($item->user)->first_name . ' ' . optional($item->user)->last_name;
                
                // Convert image paths to full URLs
                if ($item->image) {
                    $item->image_url = asset('storage/' . $item->image);
                }
                if ($item->photos) {
                    $item->photos->transform(function ($photo) {
                        $photo->image_url = asset('storage/' . $photo->path);
                        return $photo;
                    });
                }
                
                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => $foundItems
        ]);
    }

    /**
     * Store a newly created found item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $maxKb = (int) config('upload.max_photo_mb', 5) * 1024;
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'date_found' => 'required|date',
            'time_found' => 'required|date_format:H:i:s',
            'image' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:'.$maxKb],
            'images.*' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:'.$maxKb],
            'category' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::info('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Use database transaction to ensure data consistency
        $foundItem = null;
        $result = DB::transaction(function () use ($request, $user, &$foundItem) {
        
        $foundItem = new FoundItem();
        $foundItem->user_id = $user->id;
        $foundItem->organization_id = $request->organization_id;
        $foundItem->title = $request->title;
        $foundItem->description = $request->description;
        $foundItem->category = $request->category;
        $foundItem->location = $request->location;
        $foundItem->date_found = $request->date_found;
        $foundItem->time_found = $request->time_found;
        $foundItem->status = FoundItem::STATUS_UNCLAIMED;

        // Handle single image upload (for backward compatibility)
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            
            // Store the file in the public storage
            $path = $image->storeAs('found_items', $imageName, 'public');
            $foundItem->image = $path;
        }

        $foundItem->save();

        // Handle multiple images upload
        $imageFiles = [];
        $allFiles = $request->allFiles();
        
        // Debug: Log all files received
        Log::info('All files received:', array_keys($allFiles));
        
        foreach ($allFiles as $key => $file) {
            Log::info("Processing file key: $key");
            if (strpos($key, 'images[') === 0) {
                $imageFiles[] = $file;
                Log::info("Added image file: $key");
            }
        }
        
        Log::info('Total image files found: ' . count($imageFiles));
        
        if (!empty($imageFiles)) {
            foreach ($imageFiles as $index => $image) {
                $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('found_items', $imageName, 'public');
                
                Log::info("Storing image: $imageName at path: $path");
                
                $photo = \App\Models\FoundItemPhoto::create([
                    'found_item_id' => $foundItem->id,
                    'path' => $path,
                ]);
                
                Log::info("Created photo record with ID: " . $photo->id);
            }
        }
        
        // Also set the first image as the main image if no single image was uploaded
        if (!$foundItem->image && !empty($imageFiles)) {
            $firstImage = $imageFiles[0];
            $imageName = time() . '_main_' . $firstImage->getClientOriginalName();
            $path = $firstImage->storeAs('found_items', $imageName, 'public');
            $foundItem->image = $path;
            $foundItem->save();
            Log::info("Set main image: $path");
        }

        // Load photos for response
        $foundItem->load('photos');
        
        // Convert image paths to full URLs
        if ($foundItem->image) {
            $foundItem->image_url = asset('storage/' . $foundItem->image);
        }
        
        // Convert photo paths to full URLs
        if ($foundItem->photos) {
            $foundItem->photos->transform(function ($photo) {
                $photo->image_url = asset('storage/' . $photo->path);
                return $photo;
            });
        }
        
        // Send notification after successful item creation
        try {
            $this->notificationService->notifyNewItem($foundItem);
            Log::info('Notification sent successfully for found item ID: ' . $foundItem->id);
        } catch (\Exception $e) {
            Log::error('Failed to send notification for found item: ' . $e->getMessage());
        }
        
            // Return successful response
            return response()->json([
                'success' => true,
                'message' => 'Found item reported successfully',
                'data' => $foundItem
            ], 201);
        });
        
        return $result;
    }

    /**
     * Display the specified found item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        // Allow viewing a found item by ID regardless of the reporter
        $foundItem = FoundItem::with(['user', 'photos'])->find($id);

        if (!$foundItem) {
            return response()->json([
                'success' => false,
                'message' => 'Found item not found'
            ], 404);
        }

        // Attach reporter_name for mobile display
        $foundItem->reporter_name = optional($foundItem->user)->first_name . ' ' . optional($foundItem->user)->last_name;

        return response()->json([
            'success' => true,
            'data' => $foundItem
        ]);
    }

    /**
     * Update the specified found item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $maxKb = (int) config('upload.max_photo_mb', 5) * 1024;
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'date_found' => 'sometimes|required|date',
            'image' => ['nullable','image','mimes:jpeg,png,jpg,gif','max:'.$maxKb],
            'category' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $foundItem = FoundItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$foundItem) {
            return response()->json([
                'success' => false,
                'message' => 'Found item not found or not authorized'
            ], 404);
        }

        if ($request->has('title')) {
            $foundItem->title = $request->title;
        }

        if ($request->has('description')) {
            $foundItem->description = $request->description;
        }

        if ($request->has('location')) {
            $foundItem->location = $request->location;
        }

        if ($request->has('date_found')) {
            $foundItem->date_found = $request->date_found;
        }

        if ($request->has('category')) {
            $foundItem->category = $request->category;
        }

        // Handle file upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($foundItem->image) {
                Storage::disk('public')->delete($foundItem->image);
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            
            // Store the file
            $path = $image->storeAs('found_items', $imageName, 'public');
            $foundItem->image = $path;
        }

        $foundItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Found item updated successfully',
            'data' => $foundItem
        ]);
    }

    /**
     * Cancel a found item report.
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $foundItem = FoundItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$foundItem) {
            return response()->json([
                'success' => false,
                'message' => 'Found item not found or not authorized'
            ], 404);
        }

        if (!$foundItem->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This found item cannot be cancelled in its current status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $foundItem->cancel($request->cancellation_reason);

        return response()->json([
            'success' => true,
            'message' => 'Found item report cancelled successfully',
            'data' => $foundItem
        ]);
    }

    /**
     * Remove the specified found item from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $foundItem = FoundItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$foundItem) {
            return response()->json([
                'success' => false,
                'message' => 'Found item not found or not authorized'
            ], 404);
        }

        // Delete image if exists
        if ($foundItem->image) {
            Storage::disk('public')->delete($foundItem->image);
        }

        $foundItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Found item deleted successfully'
        ]);
    }
    
    /**
     * Get organizations for dropdown
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizations(): JsonResponse
    {
        $organizations = Organization::select('id', 'name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $organizations
        ]);
    }
}
