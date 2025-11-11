<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class LostItemApiController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Get all lost items for the authenticated user
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $lostItems = LostItem::with('photos')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Convert image paths to full URLs
        $lostItems->transform(function ($item) {
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
            'data' => $lostItems
        ]);
    }

    /**
     * Get lost items by organization (publicly accessible)
     */
    public function getByOrganization($organizationId): JsonResponse
    {
        $lostItems = LostItem::with(['user', 'photos'])
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
            'data' => $lostItems
        ]);
    }

    

    /**
     * Get organizations that have lost items
     */
    public function getOrganizations(): JsonResponse
    {
        $organizations = \App\Models\Organization::whereHas('lostItems')
            ->select('id', 'name', 'logo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $organizations
        ]);
    }

    /**
     * Get all lost items from all organizations
     */
    public function getAllLostItems(): JsonResponse
    {
        $lostItems = LostItem::with(['user', 'photos'])
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
            'data' => $lostItems
        ]);
    }

    /**
     * Store a newly created lost item in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Lost item store method called');
            Log::info('Request data:', $request->all());
            
            $maxKb = (int) config('upload.max_photo_mb', 5) * 1024;
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'date_lost' => 'required|date',
                'time_lost' => 'required|date_format:H:i:s',
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
            Log::info('Creating lost item for user:', ['user_id' => $user->id]);
            
            // Use database transaction to ensure data consistency
            $lostItem = null;
            $result = DB::transaction(function () use ($request, $user, &$lostItem) {

            // Create lost item with minimal data first
            $lostItem = LostItem::create([
                'user_id' => $user->id,
                'organization_id' => $request->organization_id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'location' => $request->location,
                'date_lost' => $request->date_lost,
                'time_lost' => $request->time_lost,
                'status' => LostItem::STATUS_UNRESOLVED,
            ]);
            
            Log::info('Lost item created successfully with ID:', ['id' => $lostItem->id]);

            // Handle single image upload (for backward compatibility)
            Log::info("Checking for single image file...");
            Log::info("hasFile('image'): " . ($request->hasFile('image') ? 'true' : 'false'));
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                
                // Store the file in the public storage
                $path = $image->storeAs('lost_items', $imageName, 'public');
                $lostItem->image = $path;
                $lostItem->save();
                
                Log::info("Stored main image: $imageName at path: $path");
            } else {
                Log::info("No single image file found");
            }

            // Handle multiple images upload
            $imageFiles = [];
            $allFiles = $request->allFiles();
            
            // Debug: Log all files received
            Log::info('All files received:', array_keys($allFiles));
            Log::info('All files details:', $allFiles);
            
            foreach ($allFiles as $key => $file) {
                Log::info("Processing file key: $key");
                if (strpos($key, 'images[') === 0) {
                    $imageFiles[] = $file;
                    Log::info("Added image file: $key");
                } elseif ($key === 'image') {
                    // Handle single image case
                    try {
                        $imageName = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('lost_items', $imageName, 'public');
                        
                        Log::info("Stored single image: $imageName at path: $path");
                        
                        // Set as main image
                        $lostItem->image = $path;
                        $lostItem->save();
                        
                        Log::info("Set main image: $path");
                    } catch (\Exception $e) {
                        Log::error("Error storing single image: " . $e->getMessage());
                    }
                }
            }
            
            Log::info('Total image files found: ' . count($imageFiles));
            
            if (!empty($imageFiles)) {
                foreach ($imageFiles as $index => $image) {
                    $imageName = time() . '_' . $index . '_' . $image->getClientOriginalName();
                    $path = $image->storeAs('lost_items', $imageName, 'public');
                    
                    Log::info("Storing image: $imageName at path: $path");
                    
                    $photo = \App\Models\LostItemPhoto::create([
                        'lost_item_id' => $lostItem->id,
                        'path' => $path,
                    ]);
                    
                    Log::info("Created photo record with ID: " . $photo->id);
                }
            }
            
            // Also set the first image as the main image if no single image was uploaded
            if (!$lostItem->image && !empty($imageFiles)) {
                $firstImage = $imageFiles[0];
                $imageName = time() . '_main_' . $firstImage->getClientOriginalName();
                $path = $firstImage->storeAs('lost_items', $imageName, 'public');
                $lostItem->image = $path;
                $lostItem->save();
                Log::info("Set main image: $path");
            }

            // Load photos and convert URLs
            $lostItem->load('photos');
            if ($lostItem->image) {
                $lostItem->image_url = asset('storage/' . $lostItem->image);
            }
            if ($lostItem->photos) {
                $lostItem->photos->transform(function ($photo) {
                    $photo->image_url = asset('storage/' . $photo->path);
                    return $photo;
                });
            }
            
            // Send notification after successful item creation
            try {
                $this->notificationService->notifyNewItem($lostItem);
                Log::info('Notification sent successfully for lost item ID: ' . $lostItem->id);
            } catch (\Exception $e) {
                Log::error('Failed to send notification for lost item: ' . $e->getMessage());
            }
            
                // Return successful response
                return response()->json([
                    'success' => true,
                    'message' => 'Lost item reported successfully',
                    'data' => $lostItem
                ], 201);
            });
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Error creating lost item: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lost item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified lost item.
     */
    public function show($id): JsonResponse
    {
        // Allow viewing a lost item by ID regardless of the reporter, include user
        $lostItem = LostItem::with('user')->find($id);

        if (!$lostItem) {
            return response()->json([
                'success' => false,
                'message' => 'Lost item not found'
            ], 404);
        }

        // Include reporter_name for mobile display
        $lostItem->reporter_name = optional($lostItem->user)->first_name . ' ' . optional($lostItem->user)->last_name;

        return response()->json([
            'success' => true,
            'data' => $lostItem
        ]);
    }

    /**
     * Update the specified lost item.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $maxKb = (int) config('upload.max_photo_mb', 5) * 1024;
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'date_lost' => 'sometimes|required|date',
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
        $lostItem = LostItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$lostItem) {
            return response()->json([
                'success' => false,
                'message' => 'Lost item not found or not authorized'
            ], 404);
        }

        if ($request->has('title')) $lostItem->title = $request->title;
        if ($request->has('description')) $lostItem->description = $request->description;
        if ($request->has('location')) $lostItem->location = $request->location;
        if ($request->has('date_lost')) $lostItem->date_lost = $request->date_lost;
        if ($request->has('category')) $lostItem->category = $request->category;

        if ($request->hasFile('image')) {
            if ($lostItem->image) {
                Storage::disk('public')->delete($lostItem->image);
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('lost_items', $imageName, 'public');
            $lostItem->image = $path;
        }

        $lostItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Lost item updated successfully',
            'data' => $lostItem
        ]);
    }

    /**
     * Cancel a lost item report.
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $lostItem = LostItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$lostItem) {
            return response()->json([
                'success' => false,
                'message' => 'Lost item not found or not authorized'
            ], 404);
        }

        if (!$lostItem->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This lost item cannot be cancelled in its current status'
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

        $lostItem->cancel($request->cancellation_reason);

        return response()->json([
            'success' => true,
            'message' => 'Lost item report cancelled successfully',
            'data' => $lostItem
        ]);
    }

    /**
     * Remove the specified lost item.
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $lostItem = LostItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$lostItem) {
            return response()->json([
                'success' => false,
                'message' => 'Lost item not found or not authorized'
            ], 404);
        }

        if ($lostItem->image) {
            Storage::disk('public')->delete($lostItem->image);
        }

        $lostItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lost item deleted successfully'
        ]);
    }

    
    
}
