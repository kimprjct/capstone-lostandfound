<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'user_id',
        'organization_id',
        'title',
        'message',
        'data',
        'read_at',
        'is_read',
        'user_read',
        'user_read_at',
        'user_hidden',
        'priority',
        'category',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
        'user_read' => 'boolean',
        'user_read_at' => 'datetime',
        'user_hidden' => 'array',
    ];

    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that created the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that the notification belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        Log::info("Notification model markAsRead called - ID: {$this->id}, Current is_read: {$this->is_read}");
        
        $result = $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        
        Log::info("Notification model markAsRead result - ID: {$this->id}, Update result: " . ($result ? 'true' : 'false'));
        
        // Refresh to get updated values
        $this->refresh();
        
        Log::info("Notification model markAsRead after refresh - ID: {$this->id}, is_read: {$this->is_read}, read_at: {$this->read_at}");
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Mark the notification as read by user.
     */
    public function markAsReadByUser(): void
    {
        $this->update([
            'user_read' => true,
            'user_read_at' => now(),
        ]);
    }

    /**
     * Mark the notification as unread by user.
     */
    public function markAsUnreadByUser(): void
    {
        $this->update([
            'user_read' => false,
            'user_read_at' => null,
        ]);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope for unread notifications by user.
     */
    public function scopeUnreadByUser($query)
    {
        return $query->where('user_read', false);
    }

    /**
     * Scope for read notifications by user.
     */
    public function scopeReadByUser($query)
    {
        return $query->where('user_read', true);
    }

    /**
     * Scope for notifications by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for notifications by category.
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for notifications by priority.
     */
    public function scopeOfPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Hide notification for a specific user.
     */
    public function hideForUser($userId): void
    {
        $hiddenUsers = $this->user_hidden ?? [];
        if (!in_array($userId, $hiddenUsers)) {
            $hiddenUsers[] = $userId;
            $this->update(['user_hidden' => $hiddenUsers]);
        }
    }

    /**
     * Show notification for a specific user (unhide).
     */
    public function showForUser($userId): void
    {
        $hiddenUsers = $this->user_hidden ?? [];
        $hiddenUsers = array_filter($hiddenUsers, function($id) use ($userId) {
            return $id != $userId;
        });
        $this->update(['user_hidden' => array_values($hiddenUsers)]);
    }

    /**
     * Check if notification is hidden for a specific user.
     */
    public function isHiddenForUser($userId): bool
    {
        $hiddenUsers = $this->user_hidden ?? [];
        return in_array($userId, $hiddenUsers);
    }

    /**
     * Scope for notifications visible to a specific user.
     */
    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->whereNull('user_hidden')
              ->orWhereJsonDoesntContain('user_hidden', $userId);
        });
    }
}
