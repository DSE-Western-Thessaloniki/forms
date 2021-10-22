<?php

namespace App\Models\Policies;

use App\Models\Form;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        return $user->isAdministrator() ? true : null;
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
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Form $form)
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
        if ($user->roles()->where('name', 'Author')->exists()) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Form $form)
    {
        if ($user->roles()->where('name', 'Author')->exists() && $form->user->id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Form $form)
    {
        if ($user->roles()->where('name', 'Author')->exists() && $form->user->id === $user->id) {
            return true;
        }

        return false;
    }
}
