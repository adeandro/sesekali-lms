<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CommunicationService
{
    // ── Announcements ────────────────────────────────────────

    /**
     * Create a new announcement.
     * Authorization must be checked at the controller level via Policy.
     */
    public function createAnnouncement(User $author, array $data): Announcement
    {
        $announcement = Announcement::create([
            'user_id'         => $author->id,
            'title'           => $data['title'],
            'content'         => $data['content'],
            'type'            => $data['type'],
            'target_role'     => $data['target_role'],
            'target_class_id' => $data['target_class_id'] ?? null,
            'expires_at'      => $data['expires_at'] ?? null,
            'is_active'       => true,
            'show_on_login'   => $data['show_on_login'] ?? false,
        ]);

        // Dispatch notification
        $query = User::where('status', 'Aktif');

        if ($data['target_role'] !== 'all') {
            $query->where('role', $data['target_role']);
        }

        if (!empty($data['target_class_id'])) {
            $query->where('class_group', $data['target_class_id']);
        }

        $query->chunk(100, function ($users) use ($announcement) {
            foreach ($users as $user) {
                // Avoid notifying the author themselves
                if ($user->id !== $announcement->user_id) {
                    $user->notify(new \App\Notifications\InfoNotification($announcement));
                }
            }
        });

        return $announcement;
    }

    /**
     * Get all active announcements visible to a specific user.
     * Uses Announcement scopes: active() + forUser().
     */
    public function getActiveAnnouncementsForUser(User $user): Collection
    {
        return Announcement::active()
                           ->forUser($user)
                           ->with('sender:id,name,role')
                           ->latest()
                           ->get();
    }

    /**
     * Get paginated announcements for admin/teacher management view.
     */
    public function getAnnouncementsForManagement(User $manager, int $perPage = 20)
    {
        $query = Announcement::with('sender:id,name,role')->latest();

        // Teachers only see their own announcements
        if ($manager->role === 'teacher') {
            $query->where('user_id', $manager->id);
        }

        return $query->paginate($perPage);
    }

    // ── Messages ─────────────────────────────────────────────

    /**
     * Send a direct message, creating or continuing a thread.
     *
     * @param  int|null  $parentId  If set, this is a reply in an existing thread.
     */
    public function sendMessage(
        User $sender,
        int $receiverId,
        string $body,
        ?string $attachmentPath = null,
        ?int $parentId = null
    ): Message {
        $message = Message::create([
            'sender_id'       => $sender->id,
            'receiver_id'     => $receiverId,
            'body'            => $body,
            'attachment_path' => $attachmentPath,
            'is_read'         => false,
            'parent_id'       => $parentId,
        ]);

        $receiver = User::find($receiverId);
        if ($receiver) {
            $receiver->notify(new \App\Notifications\MessageNotification($message, $sender));
        }

        return $message;
    }

    /**
     * Get all root threads (conversations) for the inbox of a user.
     * Eager-loads sender, receiver, replies with their senders.
     * No N+1 query: all loaded in 3 queries.
     */
    public function getInboxForUser(User $user): Collection
    {
        return Message::inboxFor($user)->get();
    }

    /**
     * Get a single thread with all its replies.
     */
    public function getThread(int $rootMessageId): ?Message
    {
        return Message::with(['sender', 'receiver', 'replies.sender', 'replies.receiver'])
                      ->find($rootMessageId);
    }

    /**
     * Count total unread messages for a user (used for navbar badge).
     */
    public function getUnreadMessageCount(User $user): int
    {
        return $user->unreadMessagesCount();
    }

    /**
     * Mark all messages in a thread as read for the given user.
     */
    public function markThreadRead(User $user, int $rootMessageId): void
    {
        // Mark root message
        Message::where('id', $rootMessageId)
               ->where('receiver_id', $user->id)
               ->update(['is_read' => true]);

        // Mark all replies
        Message::where('parent_id', $rootMessageId)
               ->where('receiver_id', $user->id)
               ->update(['is_read' => true]);
    }

    /**
     * Get all users that a given user can message.
     *  - teachers/superadmins: can message any student.
     *  - students: can only reply (returns empty — initiation restricted).
     */
    public function getMessageableUsers(User $sender): Collection
    {
        if ($sender->role === 'student') {
            return new Collection(); // Students cannot initiate
        }

        return User::where('role', 'student')
                   ->where('status', 'Aktif')
                   ->orderBy('name')
                   ->get(['id', 'name', 'class_group', 'grade']);
    }
}
