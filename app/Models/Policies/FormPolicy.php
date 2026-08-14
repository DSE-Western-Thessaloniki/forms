<?php

namespace App\Models\Policies;

use App\Models\Form;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?true
    {
        return $user->isAdministrator() ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->roles()->where('name', 'Author')->exists()) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Form $form): bool
    {
        if ($user->roles()->where('name', 'Author')->exists() && $form->user->id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Form $form): bool
    {
        if ($user->roles()->where('name', 'Author')->exists() && $form->user->id === $user->id) {
            return true;
        }

        return false;
    }
}
