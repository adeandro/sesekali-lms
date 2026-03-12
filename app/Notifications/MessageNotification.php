<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MessageNotification extends Notification
{
    use Queueable;

    public $message;
    public $sender;

    public function __construct($message, $sender)
    {
        $this->message = $message;
        $this->sender  = $sender;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // Use parent_id as thread anchor (root message = parent_id is null, so use own id)
        $threadId = $this->message->parent_id ?? $this->message->id;

        return [
            'type'         => 'message',
            'message_id'   => $this->message->id,
            'thread_id'    => $threadId,
            'sender_id'    => $this->sender->id,
            'sender_name'  => $this->sender->name,
            'sender_avatar'=> $this->sender->avatar_url ?? null,
            'title'        => 'Pesan dari ' . $this->sender->name,
            'body'         => Str::limit(strip_tags($this->message->body), 60),
            'icon'         => 'fas fa-envelope',
            'icon_color'   => 'text-blue-500',
            'icon_bg'      => 'bg-blue-50',
            'action_url'   => '/communication/messages/' . $threadId,
        ];
    }
}
