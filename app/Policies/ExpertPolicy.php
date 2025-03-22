<?php

namespace App\Policies;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpertPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('просмотр всех: эксперт');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Expert $expert): bool
    {
        return $user->hasPermissionTo('просмотр: эксперт');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('создание: эксперт');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Expert $expert): bool
    {
        return $user->hasPermissionTo('изменение: эксперт');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Expert $expert): bool
    {
        return $user->hasPermissionTo('удаление: эксперт');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Expert $expert): bool
    {
        return $user->hasPermissionTo('восстановление: эксперт');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Expert $expert): bool
    {
        return $user->hasPermissionTo('безвозвратное удаление: эксперт');
    }
}
