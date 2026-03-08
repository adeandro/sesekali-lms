<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GamificationUnlocked extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
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
            'type' => $this->data['type'] ?? 'achievement', // achievement, level_up, item_unlock
            'title' => $this->data['title'] ?? 'Milestone Reached!',
            'subtitle' => $this->data['subtitle'] ?? '',
            'icon' => $this->data['icon'] ?? 'fas fa-star',
            'reward' => $this->data['reward'] ?? '',
        ];
    }
}
