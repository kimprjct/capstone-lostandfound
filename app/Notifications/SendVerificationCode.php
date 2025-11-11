<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SendVerificationCode extends Notification
{
    use Queueable;

    protected $verificationCode;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Log verification code for debugging (only in non-production)
        if (config('app.debug') || config('app.env') !== 'production') {
            Log::info('Verification code generated', [
                'email' => $notifiable->email,
                'verification_code' => $this->verificationCode
            ]);
        }
        
        return (new MailMessage)
            ->subject('Email Verification Code - FoundU')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Thank you for registering with FoundU. Please use the verification code below to verify your email address.')
            ->line('Your verification code is:')
            ->line('**' . $this->verificationCode . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Best regards, The FoundU Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
