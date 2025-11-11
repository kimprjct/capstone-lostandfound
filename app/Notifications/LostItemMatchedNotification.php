<?php

namespace App\Notifications;

use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LostItemMatchedNotification extends Notification
{
    use Queueable;

    public $foundItem;
    public $lostItem;

    public function __construct(FoundItem $foundItem, LostItem $lostItem)
    {
        $this->foundItem = $foundItem;
        $this->lostItem = $lostItem;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // email + in-app notifications
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Potential Match Found for Your Lost Item')
            ->line("A found item titled '{$this->foundItem->title}' may match your lost item '{$this->lostItem->title}'.")
            ->action('View Found Item', url(route('tenant.found-items.show', $this->foundItem->id)))
            ->line('Please check and claim if it is yours.');
    }

    public function toArray($notifiable)
    {
        return [
            'found_item_id' => $this->foundItem->id,
            'lost_item_id' => $this->lostItem->id,
            'message' => "A potential match has been found for your lost item '{$this->lostItem->title}'.",
        ];
    }
}
