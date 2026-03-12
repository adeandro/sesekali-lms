<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LevelUp extends Notification
{
    use Queueable;

    public $newLevel;
    public $levelTitle;

    /**
     * Create a new notification instance.
     */
    public function __construct($newLevel, $levelTitle = null)
    {
        $this->newLevel = $newLevel;
        $this->levelTitle = $levelTitle;
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
            'type' => 'level_up',
            'new_level' => $this->newLevel,
            'title' => 'Level Up!',
            'subtitle' => 'Selamat, kamu berhasil mencapai Level ' . $this->newLevel,
            'icon' => 'fas fa-arrow-up text-indigo-500',
            'reward' => $this->levelTitle ? 'Peringkat: ' . $this->levelTitle : null,
        ];
    }
}
