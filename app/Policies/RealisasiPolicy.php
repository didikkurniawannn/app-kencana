<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Realisasi;
use Illuminate\Auth\Access\HandlesAuthorization;

class RealisasiPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_realisasi');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Realisasi $realisasi): bool
    {
        return $user->can('view_realisasi');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_realisasi');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Realisasi $realisasi): bool
    {
        return $user->can('update_realisasi');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Realisasi $realisasi): bool
    {
        return $user->can('delete_realisasi');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_realisasi');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Realisasi $realisasi): bool
    {
        return $user->can('force_delete_realisasi');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_realisasi');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Realisasi $realisasi): bool
    {
        return $user->can('restore_realisasi');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_realisasi');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Realisasi $realisasi): bool
    {
        return $user->can('replicate_realisasi');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_realisasi');
    }
}
