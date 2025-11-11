<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\API\OrganizationApiController;
use App\Http\Controllers\API\AppointmentApiController;
use App\Http\Controllers\API\FoundItemApiController;
use App\Http\Controllers\API\LostItemApiController;
use App\Http\Controllers\API\ClaimApiController;
use App\Http\Controllers\API\NotificationApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ProfileController::class, 'show']);
    Route::put('/me', [ProfileController::class, 'update']);
});

Route::get('/clinics/{clinicId}/appointments', [AppointmentApiController::class, 'index']);
Route::get('/clinics/{clinicId}/appointments/{id}', [AppointmentApiController::class, 'show']);
Route::put('/clinics/{clinicId}/appointments/{id}/status', [AppointmentApiController::class, 'updateStatus']);

//extra

// Route::middleware('auth:sanctum')->get('/me', MeController::class);

Route::get('/user', [AuthController::class, 'user']);

Route::middleware('auth:sanctum')->put('/user', [UserController::class, 'update']);

Route::middleware('auth:sanctum')->put('/update-profile', [AuthController::class, 'updateProfile']);


//org
Route::get('/organizations', [OrganizationApiController::class, 'index']);
Route::get('/organizations/{id}', [OrganizationApiController::class, 'show']);

// Test endpoint to check if API is working
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => now()
    ]);
});

// Test endpoint to show all notifications
Route::get('/test/show-notifications', function () {
    $notifications = \App\Models\Notification::orderBy('created_at', 'desc')->get();
    
    return response()->json([
        'success' => true,
        'total' => $notifications->count(),
        'notifications' => $notifications
    ]);
});

// Test endpoint to populate user_id for existing notifications
Route::get('/test/populate-user-ids', function () {
    $notifications = \App\Models\Notification::whereNull('user_id')->get();
    $updated = 0;
    
    foreach ($notifications as $notification) {
        // Try to get user_id from the data field or set a default
        $userId = null;
        
        if (isset($notification->data['reporter_id'])) {
            $userId = $notification->data['reporter_id'];
        } elseif (isset($notification->data['user_id'])) {
            $userId = $notification->data['user_id'];
        } else {
            // Set a default user ID (you can change this to any existing user ID)
            $userId = 1; // Default to user ID 1
        }
        
        $notification->update(['user_id' => $userId]);
        $updated++;
    }
    
    return response()->json([
        'success' => true,
        'message' => "Updated {$updated} notifications with user_id",
        'updated_count' => $updated
    ]);
});

// Test endpoint to create sample notifications - REMOVED
// This endpoint was creating fake notifications and has been removed
// to prevent dummy data from appearing in the mobile app

// Debug endpoint removed - issue has been resolved

// Mobile notifications endpoint (no authentication required)
Route::get('/mobile/notifications', function (Illuminate\Http\Request $request) {
    $userId = $request->get('user_id');
    $filter = $request->get('filter', 'all'); // all, unread, read
    
    if (!$userId) {
        return response()->json([
            'success' => false,
            'message' => 'user_id parameter is required'
        ], 400);
    }
    
    // Get the user to access their information
    $user = \App\Models\User::find($userId);
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
    
    // Use the same logic as the NotificationApiController for mobile notifications
    $query = \App\Models\Notification::where(function($q) {
        // Include all organization notifications
        $q->where('notifiable_type', 'App\Models\Organization')
          // Include all user notifications (but we'll filter out user's own reports)
          ->orWhere('notifiable_type', 'App\Models\User');
    })->visibleToUser($user->id)->orderBy('created_at', 'desc');
    
    // Get all notifications first
    $allNotifications = $query->get();
    
    // Filter out notifications related to user's own reports
    $filteredNotifications = $allNotifications->filter(function ($notification) use ($user) {
        // If it's a user notification, check if it's not related to user's own reports
        if ($notification->notifiable_type === 'App\Models\User') {
            // Skip if it's the user's own notification
            if ($notification->notifiable_id === $user->id) {
                return false;
            }
            
            // Check if the notification is about the user's own reports
            if (isset($notification->data['reporter_name'])) {
                $reporterName = $notification->data['reporter_name'];
                $userName = $user->first_name . ' ' . $user->last_name;
                if ($reporterName === $userName) {
                    return false;
                }
            }
        }
        
        // Check if the notification is about an item that the user reported themselves
        if (isset($notification->data['item_id'])) {
            $itemId = $notification->data['item_id'];
            $itemType = $notification->data['item_type'] ?? 'found';
            
            // Get the item to check who reported it
            if ($itemType === 'found') {
                $item = \App\Models\FoundItem::find($itemId);
            } else {
                $item = \App\Models\LostItem::find($itemId);
            }
            
            // If the item exists and the user is the one who reported it, filter it out
            if ($item && $item->user_id === $user->id) {
                return false;
            }
        }
        
        // Include all other notifications
        return true;
    });
    
    // Apply user_read filter
    if ($filter === 'unread') {
        $filteredNotifications = $filteredNotifications->filter(function ($notification) {
            return !$notification->user_read;
        });
    } elseif ($filter === 'read') {
        $filteredNotifications = $filteredNotifications->filter(function ($notification) {
            return $notification->user_read;
        });
    }
    
    // Convert back to array and enhance with item details
    $notifications = $filteredNotifications->values()->map(function ($notification) {
        $notificationArray = $notification->toArray();
        
        // Get item details if available
        if (isset($notification->data['item_id'])) {
            $itemId = $notification->data['item_id'];
            $itemType = $notification->data['item_type'] ?? 'found';

            if ($itemType === 'found') {
                $item = \App\Models\FoundItem::with(['photos', 'organization', 'user'])->find($itemId);
            } else {
                $item = \App\Models\LostItem::with(['photos', 'organization', 'user'])->find($itemId);
            }
            
            if ($item) {
                $notificationArray['item_details'] = [
                    'id' => $item->id,
                    'name' => $item->title, // Use title instead of name
                    'description' => $item->description,
                    'location' => $item->location,
                    'found_date' => $item->date_found ?? $item->date_lost, // Use correct column names
                    'found_time' => $item->time_found ?? $item->time_lost, // Use correct column names
                    'category' => $item->category,
                    'status' => $item->status,
                    'image' => $item->image, // Include the main item image
                    'image_url' => $item->image_url, // Include the full image URL
                    'photos' => $item->photos->map(function($photo) {
                        return [
                            'id' => $photo->id,
                            'image_path' => $photo->image_path,
                            'is_primary' => $photo->is_primary
                        ];
                    }),
                    'organization' => $item->organization ? [
                        'id' => $item->organization->id,
                        'name' => $item->organization->name,
                        'logo' => $item->organization->logo
                    ] : null,
                    'reporter' => $item->user ? [
                        'id' => $item->user->id,
                        'name' => trim($item->user->first_name . ' ' . $item->user->middle_name . ' ' . $item->user->last_name),
                        'email' => $item->user->email
                    ] : null
                ];
            }
        }
        
        // Get claim details if available
        if (isset($notification->data['claim_id'])) {
            $claim = \App\Models\Claim::with(['user', 'foundItem', 'lostItem'])->find($notification->data['claim_id']);
            if ($claim) {
                // Determine which item this claim is for
                $item = null;
                $itemType = null;
                
                if ($claim->foundItem) {
                    $item = $claim->foundItem;
                    $itemType = 'FoundItem';
                } elseif ($claim->lostItem) {
                    $item = $claim->lostItem;
                    $itemType = 'LostItem';
                }
                
                $notificationArray['claim_details'] = [
                    'id' => $claim->id,
                    'status' => $claim->status,
                    'description' => $claim->claim_reason,
                    'claimer' => $claim->user ? [
                        'id' => $claim->user->id,
                        'name' => trim($claim->user->first_name . ' ' . $claim->user->middle_name . ' ' . $claim->user->last_name),
                        'email' => $claim->user->email
                    ] : null,
                    'item' => $item ? [
                        'id' => $item->id,
                        'name' => $item->title, // Use title instead of name
                        'type' => $itemType
                    ] : null
                ];
            }
        }
        
        return $notificationArray;
    })->toArray();
    
    return response()->json([
        'success' => true,
        'data' => [
            'data' => $notifications,
            'total' => count($notifications)
        ]
    ]);
});

// Mobile unread count endpoint (no authentication required)
Route::get('/mobile/notifications/unread-count', function (Illuminate\Http\Request $request) {
    $userId = $request->get('user_id');
    
    if (!$userId) {
        return response()->json([
            'success' => false,
            'message' => 'user_id parameter is required'
        ], 400);
    }
    
    // Get the user to access their information
    $user = \App\Models\User::find($userId);
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
    
    // Use the same logic as the NotificationApiController for mobile notifications
    $query = \App\Models\Notification::where(function($q) {
        // Include all organization notifications
        $q->where('notifiable_type', 'App\Models\Organization')
          // Include all user notifications (but we'll filter out user's own reports)
          ->orWhere('notifiable_type', 'App\Models\User');
    })->visibleToUser($user->id);
    
    // Get all notifications first
    $allNotifications = $query->get();
    
    // Filter out notifications related to user's own reports
    $filteredNotifications = $allNotifications->filter(function ($notification) use ($user) {
        // If it's a user notification, check if it's not related to user's own reports
        if ($notification->notifiable_type === 'App\Models\User') {
            // Skip if it's the user's own notification
            if ($notification->notifiable_id === $user->id) {
                return false;
            }
            
            // Check if the notification is about the user's own reports
            if (isset($notification->data['reporter_name'])) {
                $reporterName = $notification->data['reporter_name'];
                $userName = $user->first_name . ' ' . $user->last_name;
                if ($reporterName === $userName) {
                    return false;
                }
            }
        }
        
        // Check if the notification is about an item that the user reported themselves
        if (isset($notification->data['item_id'])) {
            $itemId = $notification->data['item_id'];
            $itemType = $notification->data['item_type'] ?? 'found';
            
            // Get the item to check who reported it
            if ($itemType === 'found') {
                $item = \App\Models\FoundItem::find($itemId);
            } else {
                $item = \App\Models\LostItem::find($itemId);
            }
            
            // If the item exists and the user is the one who reported it, filter it out
            if ($item && $item->user_id === $user->id) {
                return false;
            }
        }
        
        // Include all other notifications
        return true;
    });
    
    // Count only unread notifications
    $unreadCount = $filteredNotifications->filter(function ($notification) {
        return !$notification->user_read;
    })->count();
    
    return response()->json([
        'success' => true,
        'count' => $unreadCount
    ]);
});

// Mobile mark notification as read endpoint (no authentication required)
Route::put('/mobile/notifications/{id}/read', function (Illuminate\Http\Request $request, $id) {
    $userId = $request->get('user_id');
    
    if (!$userId) {
        return response()->json([
            'success' => false,
            'message' => 'user_id parameter is required'
        ], 400);
    }
    
    // Get the user to access their information
    $user = \App\Models\User::find($userId);
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
    
    $notification = \App\Models\Notification::find($id);
    
    if (!$notification) {
        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }
    
    // Check if this notification should be visible to the user (using same logic as fetch)
    $shouldShow = true;
    
    // If it's a user notification, check if it's not related to user's own reports
    if ($notification->notifiable_type === 'App\Models\User') {
        // Skip if it's the user's own notification
        if ($notification->notifiable_id === $user->id) {
            $shouldShow = false;
        }
        
        // Check if the notification is about the user's own reports
        if (isset($notification->data['reporter_name'])) {
            $reporterName = $notification->data['reporter_name'];
            $userName = $user->first_name . ' ' . $user->last_name;
            if ($reporterName === $userName) {
                $shouldShow = false;
            }
        }
    }
    
    if (!$shouldShow) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot mark this notification as read'
        ], 403);
    }
    
    $notification->update([
        'user_read' => true,
        'user_read_at' => now()
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Notification marked as read'
    ]);
});

// Mobile endpoint to hide notifications for a user
Route::put('/mobile/notifications/hide', function (Illuminate\Http\Request $request) {
    $userId = $request->get('user_id');
    $notificationIds = $request->get('notification_ids', []);
    
    if (!$userId) {
        return response()->json([
            'success' => false,
            'message' => 'User ID is required'
        ], 400);
    }
    
    if (empty($notificationIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Notification IDs are required'
        ], 400);
    }
    
    $user = \App\Models\User::find($userId);
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
    
    $hiddenCount = 0;
    foreach ($notificationIds as $notificationId) {
        $notification = \App\Models\Notification::find($notificationId);
        if ($notification) {
            $notification->hideForUser($userId);
            $hiddenCount++;
        }
    }
    
    return response()->json([
        'success' => true,
        'message' => "Successfully hidden {$hiddenCount} notification(s)"
    ]);
});

// Mobile endpoint to get notification details with item information
Route::get('/mobile/notifications/{id}/details', function (Illuminate\Http\Request $request, $id) {
    $userId = $request->get('user_id');
    
    if (!$userId) {
        return response()->json([
            'success' => false,
            'message' => 'user_id parameter is required'
        ], 400);
    }
    
    // Get the user to access their information
    $user = \App\Models\User::find($userId);
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }
    
    $notification = \App\Models\Notification::find($id);
    
    if (!$notification) {
        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }
    
    // Check if this notification should be visible to the user (using same logic as fetch)
    $shouldShow = true;
    
    // If it's a user notification, check if it's not related to user's own reports
    if ($notification->notifiable_type === 'App\Models\User') {
        // Skip if it's the user's own notification
        if ($notification->notifiable_id === $user->id) {
            $shouldShow = false;
        }
        
        // Check if the notification is about the user's own reports
        if (isset($notification->data['reporter_name'])) {
            $reporterName = $notification->data['reporter_name'];
            $userName = $user->first_name . ' ' . $user->last_name;
            if ($reporterName === $userName) {
                $shouldShow = false;
            }
        }
    }
    
    if (!$shouldShow) {
        return response()->json([
            'success' => false,
            'message' => 'Notification not accessible'
        ], 403);
    }
    
    $details = [
        'id' => $notification->id,
        'type' => $notification->type,
        'title' => $notification->title,
        'message' => $notification->message,
        'data' => $notification->data,
        'created_at' => $notification->created_at,
        'user_read' => $notification->user_read,
        'user_read_at' => $notification->user_read_at,
    ];
    
    // Get item details if available
    if (isset($notification->data['item_id'])) {
        $itemId = $notification->data['item_id'];
        $itemType = $notification->data['item_type'] ?? 'found';
        
        if ($itemType === 'found') {
            $item = \App\Models\FoundItem::with(['photos', 'organization', 'user'])->find($itemId);
        } else {
            $item = \App\Models\LostItem::with(['photos', 'organization', 'user'])->find($itemId);
        }
        
        if ($item) {
            $details['item'] = [
                'id' => $item->id,
                'name' => $item->title, // Use title instead of name
                'description' => $item->description,
                'location' => $item->location,
                'found_date' => $item->date_found ?? $item->date_lost, // Use correct column names
                'found_time' => $item->time_found ?? $item->time_lost, // Use correct column names
                'category' => $item->category,
                'status' => $item->status,
                'image' => $item->image, // Include the main item image
                'image_url' => $item->image_url, // Include the full image URL
                'photos' => $item->photos->map(function($photo) {
                    return [
                        'id' => $photo->id,
                        'image_path' => $photo->image_path,
                        'is_primary' => $photo->is_primary
                    ];
                }),
                'organization' => $item->organization ? [
                    'id' => $item->organization->id,
                    'name' => $item->organization->name,
                    'logo' => $item->organization->logo
                ] : null,
                'reporter' => $item->user ? [
                    'id' => $item->user->id,
                    'name' => trim($item->user->first_name . ' ' . $item->user->middle_name . ' ' . $item->user->last_name),
                    'email' => $item->user->email
                ] : null
            ];
        }
    }
    
    // Get claim details if available
    if (isset($notification->data['claim_id'])) {
        $claim = \App\Models\Claim::with(['user', 'foundItem', 'lostItem'])->find($notification->data['claim_id']);
        if ($claim) {
            // Determine which item this claim is for
            $item = null;
            $itemType = null;
            
            if ($claim->foundItem) {
                $item = $claim->foundItem;
                $itemType = 'FoundItem';
            } elseif ($claim->lostItem) {
                $item = $claim->lostItem;
                $itemType = 'LostItem';
            }
            
            $details['claim'] = [
                'id' => $claim->id,
                'status' => $claim->status,
                'description' => $claim->claim_reason,
                'claimer' => $claim->user ? [
                    'id' => $claim->user->id,
                    'name' => trim($claim->user->first_name . ' ' . $claim->user->middle_name . ' ' . $claim->user->last_name),
                    'email' => $claim->user->email
                ] : null,
                'item' => $item ? [
                    'id' => $item->id,
                    'name' => $item->title, // Use title instead of name
                    'type' => $itemType
                ] : null
            ];
        }
    }
    
    return response()->json([
        'success' => true,
        'data' => $details
    ]);
});

// Mobile endpoint to get item image by ID
Route::get('/mobile/item-image/{itemId}', function ($itemId) {
    // Try to find the item in lost_items or found_items
    $lostItem = \App\Models\LostItem::find($itemId);
    if ($lostItem && $lostItem->image_url) {
        return response()->json([
            'success' => true,
            'image_url' => $lostItem->image_url
        ]);
    }
    
    $foundItem = \App\Models\FoundItem::find($itemId);
    if ($foundItem && $foundItem->image_url) {
        return response()->json([
            'success' => true,
            'image_url' => $foundItem->image_url
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Item not found or no image available'
    ], 404);
});

//found items
Route::get('/found-items/organizations', [FoundItemApiController::class, 'getOrganizations']);

// New route to get found items by organization (publicly accessible)
Route::get('/found-items/organization/{organizationId}', [FoundItemApiController::class, 'getByOrganization']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/found-items', [FoundItemApiController::class, 'index']);
    Route::post('/found-items', [FoundItemApiController::class, 'store']);
    Route::get('/found-items/{id}', [FoundItemApiController::class, 'show']);
    Route::put('/found-items/{id}', [FoundItemApiController::class, 'update']);
    Route::post('/found-items/{id}/cancel', [FoundItemApiController::class, 'cancel']);
    Route::delete('/found-items/{id}', [FoundItemApiController::class, 'destroy']);
});

// lost items - public routes first
Route::get('/lost-items/organizations', [LostItemApiController::class, 'getOrganizations']);
Route::get('/lost-items/organization/{organizationId}', [LostItemApiController::class, 'getByOrganization']);
Route::get('/lost-items/all', [LostItemApiController::class, 'getAllLostItems']);

// lost items - authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/lost-items', [LostItemApiController::class, 'index']);
    Route::post('/lost-items', [LostItemApiController::class, 'store']);
    Route::get('/lost-items/{id}', [LostItemApiController::class, 'show']);
    Route::put('/lost-items/{id}', [LostItemApiController::class, 'update']);
    Route::post('/lost-items/{id}/cancel', [LostItemApiController::class, 'cancel']);
    Route::delete('/lost-items/{id}', [LostItemApiController::class, 'destroy']);
    // claims
    Route::get('/claims', [ClaimApiController::class, 'index']);
    Route::post('/claims', [ClaimApiController::class, 'store']);
    Route::get('/claims/check', [ClaimApiController::class, 'check']);
    
    // Test authenticated endpoint
    Route::get('/test-auth', function () {
        $user = auth()->user();
        return response()->json([
            'success' => true,
            'message' => 'Authentication working',
            'user' => $user ? [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ] : null,
            'timestamp' => now()
        ]);
    });
    
    // notifications
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationApiController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [NotificationApiController::class, 'markAllAsRead']);
    Route::put('/notifications/{id}/unread', [NotificationApiController::class, 'markAsUnread']);
    Route::delete('/notifications/{id}', [NotificationApiController::class, 'destroy']);
    Route::get('/notifications/category/{category}', [NotificationApiController::class, 'getByCategory']);
    Route::get('/notifications/priority/{priority}', [NotificationApiController::class, 'getByPriority']);
    Route::get('/notifications/recent', [NotificationApiController::class, 'getRecent']);
    Route::get('/notifications/organization', [NotificationApiController::class, 'getOrganizationNotifications']);
    Route::get('/notifications/admin', [NotificationApiController::class, 'getAdminNotifications']);
    Route::get('/notifications/admin/stats', [NotificationApiController::class, 'getAdminStats']);
    
});

// Web-based notification routes (for web interface with session auth)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationApiController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [NotificationApiController::class, 'markAllAsRead']);
    Route::put('/notifications/{id}/unread', [NotificationApiController::class, 'markAsUnread']);
    Route::delete('/notifications/{id}', [NotificationApiController::class, 'destroy']);
    Route::get('/notifications/category/{category}', [NotificationApiController::class, 'getByCategory']);
    Route::get('/notifications/priority/{priority}', [NotificationApiController::class, 'getByPriority']);
    Route::get('/notifications/recent', [NotificationApiController::class, 'getRecent']);
    Route::get('/notifications/organization', [NotificationApiController::class, 'getOrganizationNotifications']);
    Route::get('/notifications/admin', [NotificationApiController::class, 'getAdminNotifications']);
    Route::get('/notifications/admin/stats', [NotificationApiController::class, 'getAdminStats']);
    
});
