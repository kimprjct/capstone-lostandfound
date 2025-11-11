<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification as CustomNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationApiController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        Log::info("=== GET NOTIFICATIONS REQUEST ===");
        Log::info("User: " . ($user ? $user->email : 'null'));
        Log::info("Role: " . ($user ? $user->role : 'null'));
        Log::info("Request headers: " . json_encode($request->headers->all()));
        Log::info("Request params: " . json_encode($request->all()));
        
        if (!$user) {
            Log::error("No authenticated user found");
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        /** @var User $user */
        $user = $user;

        // Check if this is a mobile request (exclude user's own reports)
        $excludeOwnReports = $request->get('exclude_own_reports', false);
        
        if ($excludeOwnReports) {
            // For mobile users - get notifications from all organizations but exclude their own reports
            $notifications = $this->getMobileNotifications($user, $request);
        } else {
            // Original behavior for web users
            $filters = [
                'per_page' => $request->get('per_page', 15),
                'category' => $request->get('category'),
                'priority' => $request->get('priority'),
                'is_read' => $request->get('is_read') !== null ? (bool) $request->get('is_read') : null,
            ];

            $notifications = $this->notificationService->getUserNotifications($user, $filters);
        }

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();
        Log::info("=== UNREAD COUNT REQUEST ===");
        Log::info("User: " . ($user ? $user->email : 'null'));
        Log::info("Role: " . ($user ? $user->role : 'null'));
        Log::info("Request headers: " . json_encode($request->headers->all()));
        Log::info("Request params: " . json_encode($request->all()));
        
        if (!$user) {
            Log::error("No authenticated user found for unread count");
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        /** @var User $user */
        $user = $user;
        
        // Check if this is a mobile request (exclude user's own reports)
        $excludeOwnReports = $request->get('exclude_own_reports', false);
        
        if ($excludeOwnReports) {
            // For mobile users - count notifications from all organizations but exclude their own reports
            $count = $this->getMobileUnreadCount($user);
        } else {
            // Original behavior for web users
            $count = 0;
            
            if ($user->role === 'tenant') {
                // For tenant admins, count organization notifications
                $count = CustomNotification::where(function($q) use ($user) {
                    $q->where(function($subQ) use ($user) {
                        $subQ->where('notifiable_type', 'App\Models\Organization')
                             ->where('notifiable_id', $user->organization_id);
                    })
                    ->orWhere(function($subQ) use ($user) {
                        $subQ->where('notifiable_type', 'App\Models\User')
                             ->where('notifiable_id', $user->id);
                    })
                    ->orWhere(function($subQ) use ($user) {
                        $subQ->where('organization_id', $user->organization_id);
                    });
                })->where('is_read', false)->count();
            } elseif ($user->role === 'admin') {
                // For admins, count all notifications
                $count = CustomNotification::where('is_read', false)->count();
            } else {
                // For regular users
                $count = CustomNotification::where('notifiable_type', 'App\Models\User')
                    ->where('notifiable_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            }
        }

        Log::info("Unread count for user {$user->id}: {$count}");

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id): JsonResponse
    {
        $user = Auth::user();
        Log::info("=== MARK AS READ REQUEST ===");
        Log::info("User: " . ($user ? $user->email : 'null'));
        Log::info("Role: " . ($user ? $user->role : 'null'));
        Log::info("Notification ID: {$id}");
        Log::info("Request method: " . request()->method());
        Log::info("Request headers: " . json_encode(request()->headers->all()));
        
        if (!$user) {
            Log::error("No authenticated user found");
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        // Start database transaction
        DB::beginTransaction();
        
        try {
            // Find the notification
            Log::info("Looking for notification with ID: {$id}");
            $notification = CustomNotification::find($id);
            
            if (!$notification) {
                Log::error("Notification not found with ID: {$id}");
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }
            
            Log::info("Found notification - ID: {$notification->id}");
            Log::info("Current is_read: " . ($notification->is_read ? 'true' : 'false'));
            Log::info("Current read_at: " . ($notification->read_at ? $notification->read_at : 'null'));
            Log::info("Notification type: {$notification->type}");
            Log::info("Notifiable type: {$notification->notifiable_type}");
            Log::info("Notifiable ID: {$notification->notifiable_id}");
            Log::info("Organization ID: {$notification->organization_id}");
            
            // Check if user has permission to mark this notification as read
            $hasPermission = false;
            
            if ($user->role === 'admin') {
                $hasPermission = true;
                Log::info("Admin user - has permission");
            } elseif ($user->role === 'tenant') {
                // Check if notification belongs to user's organization or is a user notification
                if ($notification->organization_id == $user->organization_id || 
                    ($notification->notifiable_type == 'App\Models\User' && $notification->notifiable_id == $user->id) ||
                    ($notification->notifiable_type == 'App\Models\Organization' && $notification->notifiable_id == $user->organization_id)) {
                    $hasPermission = true;
                    Log::info("Tenant user - has permission");
                } else {
                    Log::warning("Tenant user - no permission. User org: {$user->organization_id}, Notification org: {$notification->organization_id}");
                }
            } else {
                // Regular user - can only mark their own notifications
                if ($notification->notifiable_type == 'App\Models\User' && $notification->notifiable_id == $user->id) {
                    $hasPermission = true;
                    Log::info("Regular user - has permission");
                } else {
                    Log::warning("Regular user - no permission. User ID: {$user->id}, Notification notifiable: {$notification->notifiable_id}");
                }
            }
            
            if (!$hasPermission) {
                Log::error("User does not have permission to mark this notification as read");
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to mark this notification as read'
                ], 403);
            }

            // Mark as read using direct database update
            Log::info("Updating notification in database...");
            $updateResult = CustomNotification::where('id', $id)->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            
            Log::info("Database update result: " . ($updateResult ? 'success' : 'failed'));
            
            if (!$updateResult) {
                Log::error("Failed to update notification in database");
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update notification in database'
                ], 500);
            }
            
            // Commit the transaction
            DB::commit();
            
            // Refresh the notification to get updated values
            $notification->refresh();
            
            Log::info("After update - is_read: " . ($notification->is_read ? 'true' : 'false'));
            Log::info("After update - read_at: " . ($notification->read_at ? $notification->read_at : 'null'));

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'notification' => [
                    'id' => $notification->id,
                    'is_read' => $notification->is_read,
                    'read_at' => $notification->read_at
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Exception in markAsRead: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while marking notification as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        Log::info("=== MARK ALL AS READ REQUEST ===");
        Log::info("User: " . ($user ? $user->email : 'null'));
        Log::info("Role: " . ($user ? $user->role : 'null'));
        
        if (!$user) {
            Log::error("No authenticated user found for mark all as read");
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        DB::beginTransaction();
        
        try {
            $updatedCount = 0;
            
            if ($user->role === 'tenant') {
                // For tenant admins, mark organization notifications as read
                Log::info("Updating notifications for tenant admin");
                $updatedCount = CustomNotification::where(function($q) use ($user) {
                    $q->where(function($subQ) use ($user) {
                        $subQ->where('notifiable_type', 'App\Models\Organization')
                             ->where('notifiable_id', $user->organization_id);
                    })
                    ->orWhere(function($subQ) use ($user) {
                        $subQ->where('notifiable_type', 'App\Models\User')
                             ->where('notifiable_id', $user->id);
                    })
                    ->orWhere(function($subQ) use ($user) {
                        $subQ->where('organization_id', $user->organization_id);
                    });
                })->where('is_read', false)->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            } elseif ($user->role === 'admin') {
                // For admins, mark all notifications as read
                Log::info("Updating all notifications for admin");
                $updatedCount = CustomNotification::where('is_read', false)->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            } else {
                // For regular users
                Log::info("Updating user notifications");
                $updatedCount = CustomNotification::where('notifiable_type', 'App\Models\User')
                    ->where('notifiable_id', $user->id)
                    ->where('is_read', false)
                    ->update([
                        'is_read' => true,
                        'read_at' => now(),
                    ]);
            }
            
            DB::commit();
            Log::info("Updated {$updatedCount} notifications");

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Exception in markAllAsRead: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while marking all notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread($id): JsonResponse
    {
        $user = Auth::user();
        $notification = CustomNotification::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread'
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $notification = CustomNotification::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Get notifications by category
     */
    public function getByCategory($category): JsonResponse
    {
        $user = Auth::user();
        $notifications = CustomNotification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user->id)
            ->where('category', $category)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get notifications by priority
     */
    public function getByPriority($priority): JsonResponse
    {
        $user = Auth::user();
        $notifications = CustomNotification::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user->id)
            ->where('priority', $priority)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get recent notifications (last 24 hours)
     */
    public function getRecent(): JsonResponse
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getRecentNotifications($user);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get notifications for organization (tenant admin)
     */
    public function getOrganizationNotifications(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user is organization admin
        if ($user->role !== 'tenant' || !$user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $perPage = $request->get('per_page', 15);
        $category = $request->get('category');
        $priority = $request->get('priority');
        $isRead = $request->get('status') === 'unread' ? false : ($request->get('status') === 'read' ? true : null);
        $dateFilter = $request->get('date');

        $query = CustomNotification::where(function($q) use ($user) {
            // Organization-level notifications
            $q->where(function($subQ) use ($user) {
                $subQ->where('notifiable_type', 'App\Models\Organization')
                     ->where('notifiable_id', $user->organization_id);
            })
            // Individual user notifications for this tenant admin
            ->orWhere(function($subQ) use ($user) {
                $subQ->where('notifiable_type', 'App\Models\User')
                     ->where('notifiable_id', $user->id);
            })
            // Notifications for this organization
            ->orWhere(function($subQ) use ($user) {
                $subQ->where('organization_id', $user->organization_id);
            });
        })->orderBy('created_at', 'desc');

        // Apply filters
        if ($category) {
            $query->where('category', $category);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($isRead !== null) {
            $query->where('is_read', $isRead);
        }

        if ($dateFilter) {
            $this->applyDateFilter($query, $dateFilter);
        }

        // Organization admins should NOT see user-only claim status updates
        $notifications = $query->whereNotIn('type', ['claim_status_update','item_claimed'])->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get notifications for superadmin
     */
    public function getAdminNotifications(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Check if user is superadmin
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $perPage = $request->get('per_page', 15);
        $category = $request->get('category');
        $priority = $request->get('priority');
        $isRead = $request->get('status') === 'unread' ? false : ($request->get('status') === 'read' ? true : null);
        $dateFilter = $request->get('date');
        $organizationId = $request->get('organization_id');
        $type = $request->get('type');

        $query = CustomNotification::where(function($q) use ($user) {
            // Individual user notifications for this admin
            $q->where(function($subQ) use ($user) {
                $subQ->where('notifiable_type', 'App\Models\User')
                     ->where('notifiable_id', $user->id);
            })
            // All user notifications (admin can see all user notifications)
            ->orWhere('notifiable_type', 'App\Models\User')
            // All organization notifications (admin can see all organization notifications)
            ->orWhere('notifiable_type', 'App\Models\Organization');
        })->orderBy('created_at', 'desc');

        // Apply filters
        if ($category) {
            $query->where('category', $category);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($isRead !== null) {
            $query->where('is_read', $isRead);
        }

        if ($dateFilter) {
            $this->applyDateFilter($query, $dateFilter);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($organizationId) {
            $query->whereJsonContains('data->organization_id', $organizationId);
        }

        // Superadmins should NOT see user-only claim status updates
        $notifications = $query->whereNotIn('type', ['claim_status_update','item_claimed'])->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get admin notification statistics
     */
    public function getAdminStats(): JsonResponse
    {
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $stats = [
            'total' => CustomNotification::where('notifiable_type', 'App\Models\User')
                ->where('notifiable_id', $user->id)
                ->count(),
            'unread' => CustomNotification::where('notifiable_type', 'App\Models\User')
                ->where('notifiable_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'urgent' => CustomNotification::where('notifiable_type', 'App\Models\User')
                ->where('notifiable_id', $user->id)
                ->where('priority', 'urgent')
                ->count(),
            'system_alerts' => CustomNotification::where('notifiable_type', 'App\Models\User')
                ->where('notifiable_id', $user->id)
                ->where('type', 'system_alert')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get unread count for mobile users (from all organizations, excluding user's own reports)
     */
    private function getMobileUnreadCount(User $user): int
    {
        Log::info("Getting mobile unread count for user: {$user->email}");

        // Get all unread notifications from all organizations
        $allNotifications = CustomNotification::where(function($q) {
            // Include all organization notifications
            $q->where('notifiable_type', 'App\Models\Organization')
              // Include all user notifications (but we'll filter out user's own reports)
              ->orWhere('notifiable_type', 'App\Models\User');
        })->where('is_read', false)->get();

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
            
            // Include organization notifications (these are about other users' reports)
            return true;
        });

        return $filteredNotifications->count();
    }

    /**
     * Get notifications for mobile users (from all organizations, excluding user's own reports)
     */
    private function getMobileNotifications(User $user, Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $category = $request->get('category');
        $priority = $request->get('priority');
        $isRead = $request->get('is_read') !== null ? (bool) $request->get('is_read') : null;

        Log::info("Getting mobile notifications for user: {$user->email}");

        // Get all notifications from all organizations
        $query = CustomNotification::where(function($q) {
            // Include all organization notifications
            $q->where('notifiable_type', 'App\Models\Organization')
              // Include all user notifications (but we'll filter out user's own reports)
              ->orWhere('notifiable_type', 'App\Models\User');
        })->orderBy('created_at', 'desc');

        // Apply filters
        if ($category) {
            $query->where('category', $category);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($isRead !== null) {
            $query->where('is_read', $isRead);
        }

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
            
            // Include organization notifications (these are about other users' reports)
            return true;
        });

        // Convert back to paginated result
        $total = $filteredNotifications->count();
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $items = $filteredNotifications->slice($offset, $perPage)->values();

        // Create pagination metadata
        $lastPage = ceil($total / $perPage);
        
        return (object) [
            'data' => $items,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }

    /**
     * Apply date filter to query
     */
    private function applyDateFilter($query, string $dateFilter): void
    {
        $now = now();
        
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('created_at', $now->toDateString());
                break;
            case 'week':
                $query->where('created_at', '>=', $now->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', $now->subMonth());
                break;
        }
    }
}