<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'body'        => $this->body,
            'is_read'     => $this->is_read,
            'created_at'  => $this->created_at->diffForHumans(),
            'sender'      => [
                'id'         => $this->sender->id,
                'name'       => $this->sender->formatted_name,
                'role'       => $this->sender->role,
                'avatar_url' => $this->sender->photo_url,
            ],
            'receiver'    => [
                'id'   => $this->receiver->id,
                'name' => $this->receiver->formatted_name,
                'role' => $this->receiver->role,
            ],
            'replies'     => MessageResource::collection($this->whenLoaded('replies')),
        ];
    }
}
