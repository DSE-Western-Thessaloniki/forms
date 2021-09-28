<?php

namespace App\Models\Policies;

use App\Models\Form;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class FormPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
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
        if (Auth::guard('school')->check()) { // The user is a school
            $school = School::find($user->id);
            $categories = $school->categories;
            $form_categories = $form->school_categories;
            $in_category = false;
            foreach ($categories as $category) {
                if ($form_categories->contains($category))
                    $in_category = true;
            }
            if ($form->schools()->where('school_id', Auth::guard('school')->user()->id)->count() > 0 || $in_category)
                return true;
            return false;
        }
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
        if ($user->roles()->where('name', 'Author')) {
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
        if (Auth::guard('school')->check()) { // The user is a school
            $school = School::find($user->id);
            $categories = $school->categories;
            $form_categories = $form->school_categories;
            $in_category = false;
            foreach ($categories as $category) {
                if ($form_categories->contains($category))
                    $in_category = true;
            }
            if ($form->schools()->where('school_id', Auth::guard('school')->user()->id)->count() > 0 || $in_category)
                return true;
            return false;
        }

        if ($user->roles()->where('name', 'Author') && $form->user()->id === $user->id) {
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
        if ($user->roles()->where('name', 'Author') && $form->user()->id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Form $form)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Form $form)
    {
        //
    }
}
