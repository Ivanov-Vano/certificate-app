<?php

namespace App\Policies;

use App\Models\Chamber;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChamberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('просмотр всех: палата');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chamber $chamber): bool
    {
        return $user->hasPermissionTo('просмотр: палата');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('создание: палата');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chamber $chamber): bool
    {
        return $user->hasPermissionTo('изменение: палата');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chamber $chamber): bool
    {
        return $user->hasPermissionTo('удаление: палата');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Chamber $chamber): bool
    {
        return $user->hasPermissionTo('восстановление: палата');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Chamber $chamber): bool
    {
        return $user->hasPermissionTo('безвозвратное удаление: палата');
    }
}
