<?php

namespace App\Policies;

use App\Models\Deliveryman;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DeliverymanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('просмотр всех: курьер');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Deliveryman $deliveryman): bool
    {
        return $user->hasPermissionTo('просмотр: курьер');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('создание: курьер');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deliveryman $deliveryman): bool
    {
        return $user->hasPermissionTo('изменение: курьер');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deliveryman $deliveryman): bool
    {
        return $user->hasPermissionTo('удаление: курьер');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Deliveryman $deliveryman): bool
    {
        return $user->hasPermissionTo('восстановление: курьер');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Deliveryman $deliveryman): bool
    {
        return $user->hasPermissionTo('безвозвратное удаление: курьер');
    }
}
