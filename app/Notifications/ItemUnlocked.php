<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemUnlocked extends Notification
{
    use Queueable;

    public $title;
    public $subtitle;
    public $icon;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $subtitle, $icon)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->icon = $icon;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'item_unlock',
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'icon' => $this->icon,
        ];
    }
}
