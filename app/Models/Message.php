<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'body',
        'attachment_path',
        'is_read',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Replies to this message (children in the thread).
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_id')->orderBy('created_at');
    }

    /**
     * The parent message of this reply.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    /**
     * Only root-level messages (thread starters).
     */
    public function scopeThreads(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Root threads involving the given user (as sender or receiver).
     * Eager-loads sender, receiver, replies, latest reply.
     */
    public function scopeInboxFor(Builder $query, User $user): Builder
    {
        return $query->threads()
                     ->where(function ($q) use ($user) {
                         $q->where('sender_id', $user->id)
                           ->orWhere('receiver_id', $user->id);
                     })
                     ->with(['sender', 'receiver', 'replies.sender'])
                     ->orderByDesc('updated_at');
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Count unread replies for a given user in this thread.
     */
    public function unreadCountFor(int $userId): int
    {
        // Count unread root message if receiver
        $count = ($this->receiver_id === $userId && !$this->is_read) ? 1 : 0;
        // Count unread replies
        $count += $this->replies->filter(fn ($r) => $r->receiver_id === $userId && !$r->is_read)->count();
        return $count;
    }

    /**
     * Get the other party in the conversation relative to the given user.
     */
    public function otherParty(int $userId): User
    {
        return $this->sender_id === $userId ? $this->receiver : $this->sender;
    }
}
