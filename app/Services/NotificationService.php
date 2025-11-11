<?php

namespace App\Services;

use App\Models\Notification as CustomNotification;
use App\Models\User;
use App\Models\Organization;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Claim;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function createUserNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        string $category = 'general',
        int $organizationId = null,
        int $createdByUserId = null
    ): CustomNotification {
        Log::info("Creating user notification for user: {$user->email}, type: {$type}, title: {$title}");
        
        $notification = CustomNotification::create([
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $createdByUserId, // Track who created this notification
            'organization_id' => $organizationId,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'category' => $category,
        ]);
        
        Log::info("User notification created with ID: {$notification->id}");
        return $notification;
    }

    /**
     * Create a notification for an organization (sends to all admin users in the organization)
     */
    public function createOrganizationNotification(
        Organization $organization,
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        string $category = 'general',
        int $createdByUserId = null
    ): void {
        Log::info("Creating organization notification for organization: {$organization->name}, type: {$type}, title: {$title}");
        
        // Create only ONE organization-level notification
        $notification = CustomNotification::create([
            'type' => $type,
            'notifiable_type' => Organization::class,
            'notifiable_id' => $organization->id,
            'user_id' => $createdByUserId, // Track who created this notification
            'organization_id' => $organization->id,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'category' => $category,
        ]);
        
        Log::info("Organization notification created with ID: {$notification->id}");
    }

    /**
     * Notify user about claim status update
     */
    public function notifyClaimStatusUpdate(Claim $claim, string $status): void
    {
        $user = $claim->user;
        $item = $claim->foundItem ?? $claim->lostItem;
        $organization = $claim->organization ?? ($item ? $item->organization : null);

        $itemName = $item ? $item->title : 'Item';
        $location = $organization->claim_location ?? ($item->location ?? 'Lost and Found Office');
        $officeHours = $organization->office_hours ?? 'Office hours';
        $requirements = 'Valid ID and proof of ownership';
        $refCode = $claim->claim_code ?: '';

        $title = 'FoundU - Claim Status Update';
        $message = 'FoundU - ';
        if ($status === 'approved') {
            $message .= "Your claim for {$itemName} has been approved!\n";
            $message .= "Location: {$location}\n";
            $message .= "Office Hours: {$officeHours}\n";
            $message .= "Within 1 month to claim the item\n";
            $message .= "Bring: {$requirements}";
            if ($refCode) {
                $message .= "\nReference Code: {$refCode}";
            }
        } elseif ($status === 'rejected') {
            $reason = $claim->rejection_reason ?: 'No reason provided';
            $message .= "Your claim for {$itemName} has been rejected.\nReason: {$reason}";
        } else {
            $message .= 'Your claim status has been updated.';
        }

        $this->createUserNotification(
            $user,
            'claim_status_update',
            $title,
            $message,
            [
                'claim_id' => $claim->id,
                'item_id' => $item?->id,
                'item_type' => $claim->found_item_id ? 'found' : 'lost',
                'status' => $status,
                'location' => $location,
                'office_hours' => $officeHours,
                'requirements' => $requirements,
                'reference_code' => $refCode,
                'rejection_reason' => $claim->rejection_reason,
            ],
            $status === 'approved' ? 'high' : 'normal',
            'claim'
        );

        // If approved and completed, update item status
        if ($status === 'completed') {
            $this->updateItemStatusAfterClaim($claim);
        }
    }

    /**
     * Notify lost item reporter about potential match
     */
    public function notifyLostItemMatch(LostItem $lostItem, FoundItem $foundItem): void
    {
        $user = $lostItem->user;
        
        $this->createUserNotification(
            $user,
            'item_match',
            'Potential Match Found!',
            "A found item '{$foundItem->title}' might match your lost item '{$lostItem->title}'",
            [
                'lost_item_id' => $lostItem->id,
                'found_item_id' => $foundItem->id,
                'match_confidence' => 'high', // Could be calculated based on similarity
            ],
            'high',
            'item'
        );
    }

    /**
     * Notify found item reporter about new claim
     */
    public function notifyFoundItemClaim(FoundItem $foundItem, Claim $claim): void
    {
        $user = $foundItem->user;
        
        $this->createUserNotification(
            $user,
            'new_claim',
            'New Claim Submitted',
            "Someone has claimed your found item '{$foundItem->title}'",
            [
                'found_item_id' => $foundItem->id,
                'claim_id' => $claim->id,
                'claimer_name' => $claim->user->first_name . ' ' . $claim->user->last_name,
            ],
            'normal',
            'claim'
        );
    }

    /**
     * Notify organization admin about new item
     */
    public function notifyNewItem(LostItem|FoundItem $item): void
    {
        $organization = $item->organization;
        $itemType = $item instanceof LostItem ? 'lost' : 'found';
        
        Log::info("Creating notification for {$itemType} item ID: {$item->id}, Organization: {$organization->name}");
        
        // Create only ONE notification for the organization
        $this->createOrganizationNotification(
            $organization,
            'new_item',
            'New Item Reported',
            $itemType === 'lost' ? 'A user reported a lost item!' : 'A user reported a found item!',
            [
                'item_id' => $item->id,
                'item_type' => $itemType,
                'reporter_name' => $item->user->first_name . ' ' . $item->user->last_name,
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
            ],
            'normal',
            'item',
            $item->user->id // Pass the user who created the item
        );
        
        Log::info("Created single notification for organization: {$organization->name}");
    }

    /**
     * Notify organization admin about new claim
     */
    public function notifyNewClaim(Claim $claim): void
    {
        $item = $claim->foundItem ?? $claim->lostItem;
        $organization = $item->organization;
        $itemType = $claim->found_item_id ? 'found' : 'lost';
        
        Log::info("Creating notification for {$itemType} claim ID: {$claim->id}, Organization: {$organization->name}");
        
        // Create only ONE notification for the organization (similar to notifyNewItem)
        $this->createOrganizationNotification(
            $organization,
            'new_claim',
            'New Claim Attempt',
            'A user wants to claim an item!',
            [
                'claim_id' => $claim->id,
                'item_id' => $item->id,
                'item_type' => $itemType,
                'claimer_name' => $claim->user->first_name . ' ' . $claim->user->last_name,
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
            ],
            'normal',
            'claim'
        );
        
        Log::info("Created single notification for organization: {$organization->name}");
    }

    /**
     * Notify about item escalation (unclaimed for too long)
     */
    public function notifyItemEscalation(LostItem|FoundItem $item, int $daysUnclaimed): void
    {
        $organization = $item->organization;
        $itemType = $item instanceof LostItem ? 'lost' : 'found';
        
        $this->createOrganizationNotification(
            $organization,
            'item_escalation',
            'Item Escalation Required',
            "The {$itemType} item '{$item->title}' has been unclaimed for {$daysUnclaimed} days. Please review.",
            [
                'item_id' => $item->id,
                'item_type' => $itemType,
                'days_unclaimed' => $daysUnclaimed,
            ],
            'high',
            'system'
        );
    }

    /**
     * Notify superadmin about system alerts
     */
    public function notifySystemAlert(string $alertType, array $data = []): void
    {
        // Get all superadmins
        $superadmins = User::where('role', 'admin')->get();
        
        foreach ($superadmins as $admin) {
            $this->createUserNotification(
                $admin,
                'system_alert',
                'System Alert: ' . ucfirst(str_replace('_', ' ', $alertType)),
                $this->getSystemAlertMessage($alertType, $data),
                array_merge($data, ['alert_type' => $alertType]),
                'urgent',
                'system'
            );
        }
    }

    /**
     * Send organization-wide announcement
     */
    public function sendOrganizationAnnouncement(Organization $organization, string $title, string $message): void
    {
        $users = $organization->users;
        
        foreach ($users as $user) {
            $this->createUserNotification(
                $user,
                'announcement',
                $title,
                $message,
                ['organization_id' => $organization->id],
                'normal',
                'announcement'
            );
        }
    }

    /**
     * Update item status after successful claim
     */
    private function updateItemStatusAfterClaim(Claim $claim): void
    {
        if ($claim->found_item_id) {
            $foundItem = $claim->foundItem;
            $foundItem->update(['status' => 'claimed']);
        } else {
            $lostItem = $claim->lostItem;
            $lostItem->update(['status' => 'returned']);
        }
    }

    /**
     * Get system alert message based on type
     */
    private function getSystemAlertMessage(string $alertType, array $data): string
    {
        $messages = [
            'high_activity' => "High activity detected in organization {$data['organization_name']}",
            'storage_limit' => "Storage is nearing capacity: {$data['usage_percentage']}% used",
            'admin_action' => "Admin action taken: {$data['action']} by {$data['admin_name']}",
            'escalation' => "Escalation case requires review: {$data['case_description']}",
        ];

        return $messages[$alertType] ?? 'System alert: ' . $alertType;
    }


    /**
     * Get unread notification count for an organization
     */
    public function getUnreadCountForOrganization(Organization $organization): int
    {
        return CustomNotification::where('notifiable_type', Organization::class)
            ->where('notifiable_id', $organization->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get unread notification count for a user (including organization notifications)
     */
    public function getUnreadCount(User $user): int
    {
        $userNotifications = CustomNotification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('is_read', false)
            ->count();

        // If user is part of an organization, also count organization notifications
        if ($user->organization_id) {
            $orgNotifications = CustomNotification::where('notifiable_type', Organization::class)
                ->where('notifiable_id', $user->organization_id)
                ->where('is_read', false)
                ->count();
            
            return $userNotifications + $orgNotifications;
        }

        return $userNotifications;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(CustomNotification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(CustomNotification $notification): void
    {
        $notification->markAsUnread();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        // Mark user notifications as read
        CustomNotification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // If user is part of an organization, also mark organization notifications as read
        if ($user->organization_id) {
            CustomNotification::where('notifiable_type', Organization::class)
                ->where('notifiable_id', $user->organization_id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
    }

    /**
     * Get notifications for a user with pagination and filters
     */
    public function getUserNotifications(User $user, array $filters = [])
    {
        $query = CustomNotification::where(function($q) use ($user) {
            // User notifications
            $q->where(function($subQ) use ($user) {
                $subQ->where('notifiable_type', User::class)
                     ->where('notifiable_id', $user->id);
            });

            // Organization notifications if user is part of an organization
            if ($user->organization_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('notifiable_type', Organization::class)
                         ->where('notifiable_id', $user->organization_id);
                });
            }
        })->orderBy('created_at', 'desc');

        // Apply filters
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        if (isset($filters['per_page'])) {
            return $query->paginate($filters['per_page']);
        }

        return $query->get();
    }

    /**
     * Get recent notifications for a user (last 24 hours)
     */
    public function getRecentNotifications(User $user, int $limit = 10)
    {
        return CustomNotification::where(function($q) use ($user) {
            // User notifications
            $q->where(function($subQ) use ($user) {
                $subQ->where('notifiable_type', User::class)
                     ->where('notifiable_id', $user->id);
            });

            // Organization notifications if user is part of an organization
            if ($user->organization_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('notifiable_type', Organization::class)
                         ->where('notifiable_id', $user->organization_id);
                });
            }
        })
        ->where('created_at', '>=', now()->subDay())
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * Notify user when their item has been claimed/returned
     */
    public function notifyItemClaimed(LostItem|FoundItem $item, Claim $claim): void
    {
        $user = $item->user;
        $itemType = $item instanceof LostItem ? 'lost' : 'found';
        $action = $item instanceof LostItem ? 'returned' : 'claimed';
        
        $this->createUserNotification(
            $user,
            'item_claimed',
            'FoundU - ' . ucfirst($itemType) . ' Item ' . ucfirst($action),
            "FoundU - Your {$itemType} item '{$item->title}' has been {$action} successfully!",
            [
                'item_id' => $item->id,
                'item_type' => $itemType,
                'claim_id' => $claim->id,
                'claimer_name' => $claim->user->first_name . ' ' . $claim->user->last_name,
            ],
            'high',
            'item'
        );
    }

    /**
     * Notify organization when an item has been successfully claimed/returned
     */
    public function notifyItemCompleted(LostItem|FoundItem $item, Claim $claim): void
    {
        $organization = $item->organization;
        $itemType = $item instanceof LostItem ? 'lost' : 'found';
        $action = $item instanceof LostItem ? 'returned' : 'claimed';
        
        $this->createOrganizationNotification(
            $organization,
            'item_completed',
            'FoundU - ' . ucfirst($itemType) . ' Item ' . ucfirst($action),
            "A {$itemType} item '{$item->title}' has been successfully {$action}",
            [
                'item_id' => $item->id,
                'item_type' => $itemType,
                'claim_id' => $claim->id,
                'claimer_name' => $claim->user->first_name . ' ' . $claim->user->last_name,
                'organization_id' => $organization->id,
            ],
            'normal',
            'item'
        );
    }
}
