<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Universal gamification notification.
 *
 * @param string $category  One of: achievement | level_up | theme | item
 * @param string $title     Main headline (e.g. "Achievement Unlocked!")
 * @param string $subtitle  Supporting text (e.g. achievement name / level)
 * @param string $icon      FontAwesome class (e.g. "fas fa-trophy")
 * @param string|null $reward  Optional reward string (e.g. "+200 EXP")
 */
class GamificationNotification extends Notification
{
    use Queueable;

    public string $category;
    public string $title;
    public string $subtitle;
    public string $icon;
    public ?string $reward;

    public function __construct(
        string $category,
        string $title,
        string $subtitle,
        string $icon = 'fas fa-star',
        ?string $reward = null
    ) {
        $this->category = $category;
        $this->title    = $title;
        $this->subtitle = $subtitle;
        $this->icon     = $icon;
        $this->reward   = $reward;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        [$iconColor, $iconBg] = match ($this->category) {
            'level_up'    => ['text-purple-500',  'bg-purple-50'],
            'achievement' => ['text-amber-500',   'bg-amber-50'],
            'theme'       => ['text-emerald-500', 'bg-emerald-50'],
            'item'        => ['text-indigo-500',  'bg-indigo-50'],
            default       => ['text-gray-500',    'bg-gray-50'],
        };

        return [
            'type'       => 'gamification',
            'category'   => $this->category,
            'title'      => $this->title,
            'body'       => $this->subtitle,  // alias 'body' for uniform dropdown rendering
            'subtitle'   => $this->subtitle,
            'icon'       => $this->icon,
            'icon_color' => $iconColor,
            'icon_bg'    => $iconBg,
            'reward'     => $this->reward,
        ];
    }
}
