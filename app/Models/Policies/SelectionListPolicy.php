<?php

namespace App\Models\Policies;

use App\Models\SelectionList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SelectionListPolicy
{
    use HandlesAuthorization;

    public function before(User $current_user): ?true
    {
        return $current_user->isAdministrator() ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->roles()->where("name", "Author")->exists();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user)
    {
        return $user->roles()->where("name", "Author")->exists();
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->roles()->where("name", "Author")->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SelectionList $selectionList): bool
    {
        if (!$user->roles()->where("name", "Author")->exists())
            return false;
        return $selectionList->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SelectionList $selectionList): bool
    {
        if (!$user->roles()->where("name", "Author")->exists())
            return false;
        return $selectionList->created_by == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(): bool
    {
        return false;
    }
}
