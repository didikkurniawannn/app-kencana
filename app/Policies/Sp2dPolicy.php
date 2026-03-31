<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sp2d;
use Illuminate\Auth\Access\HandlesAuthorization;

class Sp2dPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_sp2d');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sp2d $sp2d): bool
    {
        return $user->can('view_sp2d');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_sp2d');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sp2d $sp2d): bool
    {
        return $user->can('update_sp2d');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sp2d $sp2d): bool
    {
        return $user->can('delete_sp2d');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_sp2d');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Sp2d $sp2d): bool
    {
        return $user->can('force_delete_sp2d');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_sp2d');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Sp2d $sp2d): bool
    {
        return $user->can('restore_sp2d');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_sp2d');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Sp2d $sp2d): bool
    {
        return $user->can('replicate_sp2d');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_sp2d');
    }
}
