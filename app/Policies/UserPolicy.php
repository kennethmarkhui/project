<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Collection;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionType::USER_READ->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can(PermissionType::USER_UPDATE->value, $model);
    }

    /**
     * Determine whether the user can update the model in bulk.
     */
    public function updateAny(User $authUser, Collection $users): bool
    {
        return $users->every(fn($user) => $authUser->can(PermissionType::USER_UPDATE->value, $user));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) return false;

        return $user->can(PermissionType::USER_DELETE->value, $model);
    }

    /**
     * Determine whether the user can delete the model in bulk.
     */
    // public function deleteAny(User $authUser, array $ids): bool
    public function deleteAny(User $authUser, Collection $users): bool
    {
        if ($users->contains->is($authUser)) return false;

        return $users->every(fn($user) => $authUser->can(PermissionType::USER_DELETE->value, $user));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can(PermissionType::USER_RESTORE->value, $model);
    }

    /**
     * Determine whether the user can restore the model in bulk.
     */
    public function restoreAny(User $authUser, Collection $users): bool
    {
        return $users->every(fn($user) => $authUser->can(PermissionType::USER_RESTORE->value, $user));
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->can(PermissionType::USER_FORCE_DELETE->value, $model);
    }

    /**
     * Determine whether the user can permanently delete the model in bulk.
     */
    public function forceDeleteAny(User $authUser, Collection $users): bool
    {
        return $users->every(fn($user) => $authUser->can(PermissionType::USER_FORCE_DELETE->value, $user));
    }
}
