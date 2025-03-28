<?php

namespace App\Policies;

use App\Models\Type;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('просмотр всех: тип сертификата');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Type $type): bool
    {
        return $user->hasPermissionTo('просмотр: тип сертификата');

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('создание: тип сертификата');

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Type $type): bool
    {
        return $user->hasPermissionTo('изменение: тип сертификата');

    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Type $type): bool
    {
        return $user->hasPermissionTo('удаление: тип сертификата');

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Type $type): bool
    {
        return $user->hasPermissionTo('восстановление: тип сертификата');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Type $type): bool
    {
        return $user->hasPermissionTo('безвозвратное удаление: тип сертификата');
    }
}
