<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Superadmin can see all threads (read-only audit).
     * Others can only see threads they are part of.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only teacher or superadmin can initiate a NEW thread.
     * Students are restricted from starting conversations.
     */
    public function initiate(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'teacher']);
    }

    /**
     * Any authenticated user can reply to a thread they are part of.
     * Superadmin can also reply (no longer read-only).
     */
    public function reply(User $user, Message $message): bool
    {
        if ($user->role === 'superadmin') {
            return true; // superadmin can reply to any thread
        }

        return $message->sender_id === $user->id
            || $message->receiver_id === $user->id;
    }

    /**
     * Superadmin can delete any message; sender can delete their own.
     */
    public function deleteMessage(User $user, Message $message): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        return $message->sender_id === $user->id;
    }

    /**
     * Only superadmin or the original sender (teacher/superadmin) can delete a thread.
     * Receivers (students) cannot delete threads — only the initiator can.
     */
    public function deleteThread(User $user, Message $message): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        // Only the sender (who initiated the thread) can delete it
        return $message->sender_id === $user->id;
    }
}
