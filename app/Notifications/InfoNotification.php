<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class InfoNotification extends Notification
{
    use Queueable;

    public $announcement;

    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'info',
            'announcement_id' => $this->announcement->id,
            'title'           => $this->announcement->title,
            'body'            => Str::limit(strip_tags($this->announcement->content), 60),
            'icon'            => 'fas fa-bullhorn',
            'icon_color'      => 'text-emerald-500',
            'icon_bg'         => 'bg-emerald-50',
            'action_url'      => '/communication/announcements',
        ];
    }
}
