<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeviceLoginNotification extends Notification
{
    use Queueable;

    public $activity;

    public function __construct($activity)
    {
        $this->activity = $activity;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Login Detected')
            ->line('A new login was detected on your account from a new device.')
            ->line('IP Address: ' . $this->activity->ip_address)
            ->action('Secure Your Account', url('/security-settings'))
            ->line('If this was not you, please secure your account immediately.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ip_address' => $this->activity->ip_address,
            'device' => $this->activity->device,
            'logged_at' => $this->activity->created_at,
        ];
    }
}