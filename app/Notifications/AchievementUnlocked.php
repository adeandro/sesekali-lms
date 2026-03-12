<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AchievementUnlocked extends Notification
{
    use Queueable;

    public $achievement;
    public $xpReward;

    /**
     * Create a new notification instance.
     */
    public function __construct($achievement, $xpReward)
    {
        $this->achievement = $achievement;
        $this->xpReward = $xpReward;
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
            'type' => 'achievement_unlocked',
            'achievement_id' => $this->achievement->id,
            'title' => 'Achievement Unlocked!',
            'subtitle' => $this->achievement->title,
            'icon' => $this->achievement->icon ?? 'fas fa-medal text-amber-500',
            'reward' => '+' . $this->xpReward . ' EXP',
        ];
    }
}
