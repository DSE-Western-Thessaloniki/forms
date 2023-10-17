<?php

namespace App\Models\Policies;

use App\Models\SelectionList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SelectionListPolicy
{
    use HandlesAuthorization;

    public function before(User $current_user, $ability)
    {
        return (
            $current_user->isAdministrator() ||
            $current_user->roles()->where("name", "Author")->exists()
        ) ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, SelectionList $selectionList)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, SelectionList $selectionList)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, SelectionList $selectionList)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, SelectionList $selectionList)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, SelectionList $selectionList)
    {
        return false;
    }
}
