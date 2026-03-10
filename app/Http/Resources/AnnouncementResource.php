<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'content'         => $this->content,
            'type'            => $this->type,
            'type_label'      => $this->type_label,
            'type_color'      => $this->type_color,
            'target_role'     => $this->target_role,
            'target_class_id' => $this->target_class_id,
            'is_active'       => $this->is_active,
            'expires_at'      => $this->expires_at?->toDateTimeString(),
            'created_at'      => $this->created_at->diffForHumans(),
            'sender'          => [
                'id'   => $this->sender->id,
                'name' => $this->sender->full_name,
                'role' => $this->sender->role,
            ],
        ];
    }
}
