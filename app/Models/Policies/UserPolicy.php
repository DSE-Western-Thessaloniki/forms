<?php

namespace App\Models\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $current_user, $ability)
    {
        return $current_user->isAdministrator() ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $current_user
     * @return mixed
     */
    public function viewAny(User $current_user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $current_user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function view(User $current_user, User $user)
    {
        return $current_user->is($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $current_user
     * @return mixed
     */
    public function create(User $current_user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $current_user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function update(User $current_user, User $user)
    {
        return $current_user->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $current_user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function delete(User $current_user, User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $current_user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function restore(User $current_user, User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $current_user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function forceDelete(User $current_user, User $user)
    {
        return false;
    }
}
