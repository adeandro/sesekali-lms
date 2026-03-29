<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnnouncementPolicy
{
    use HandlesAuthorization;

    /**
     * Any authenticated user can view the announcement list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only superadmin, admin (superadmin role acts as admin), or teacher can create.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin', 'teacher']);
    }

    /**
     * Superadmin can update anything; teacher can only update their own.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        return $user->role === 'teacher' && $announcement->user_id === $user->id;
    }

    /**
     * Superadmin can deactivate anything; teacher can only deactivate their own.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        if ($user->role === 'superadmin') {
            return true;
        }

        return $user->role === 'teacher' && $announcement->user_id === $user->id;
    }

    /**
     * Only Superadmin can permanently delete from the database.
     */
    public function permanentDelete(User $user, Announcement $announcement): bool
    {
        return $user->role === 'superadmin';
    }

    /**
     * Superadmin can toggle active status on any announcement.
     */
    public function toggleActive(User $user): bool
    {
        return $user->role === 'superadmin';
    }
}
