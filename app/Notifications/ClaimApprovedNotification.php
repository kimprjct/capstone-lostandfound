<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Claim;

class ClaimApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $claim;
    protected $claimCode;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Claim $claim, string $claimCode)
    {
        $this->claim = $claim;
        $this->claimCode = $claimCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $itemName = $this->claim->foundItem ? $this->claim->foundItem->title : 
                   ($this->claim->lostItem ? $this->claim->lostItem->title : 'Unknown Item');
        
        $organization = $this->claim->organization;
        $claimLocation = $organization->claim_location ?? 'Lost and Found Office';
        $officeHours = $organization->office_hours ?? 'Monday to Friday, 8:00 AM - 5:00 PM';
        
        return (new MailMessage)
                    ->subject("Your Claim For {$itemName} Has Been Approved – Please Visit the Office")
                    ->greeting("Hello {$notifiable->first_name},")
                    ->line("Your claim for the item below has been approved. Please visit the Lost and Found office to verify and retrieve the item.")
                    ->line('')
                    ->line('🔍 **Claim Summary:**')
                    ->line("- **Claim Reference Number:** Claim #{$this->claim->id}")
                    ->line("- **Item Name:** {$itemName}")
                    ->line("- **Description:** {$this->claim->claim_reason}")
                    ->line("- **Submitted On:** {$this->claim->created_at->format('M d, Y H:i')}")
                    ->line('')
                    ->line("📍 **Claim Location:**")
                    ->line($claimLocation)
                    ->line('')
                    ->line("🕒 **Office Hours:**")
                    ->line($officeHours)
                    ->line('')
                    ->line('🪪 **What to Bring:**')
                    ->line('- A valid ID')
                    ->line('- Any proof of ownership')
                    ->line("- Your Claim Code: **{$this->claimCode}**")
                    ->line('')
                    ->line('⏳ **Deadline:**')
                    ->line('Please claim your item within 7 days of this approval.')
                    ->line('')
                    ->line("If you have any questions, contact {$organization->name}.")
                    ->action('View Claim Details', url('/claims/' . $this->claim->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $itemName = $this->claim->foundItem ? $this->claim->foundItem->title : 
                   ($this->claim->lostItem ? $this->claim->lostItem->title : 'Unknown Item');
        
        return [
            'type' => 'claim_approved',
            'claim_id' => $this->claim->id,
            'item_name' => $itemName,
            'claim_code' => $this->claimCode,
            'message' => "Your claim for {$itemName} has been approved. Please visit the office to retrieve your item.",
            'action_url' => url('/claims/' . $this->claim->id),
        ];
    }
}
