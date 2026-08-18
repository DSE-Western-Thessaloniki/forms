<?php

declare(strict_types=1);

namespace App\Http\Traits;

use App\Models\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

trait UsesCasAccessFiltering
{
    private function school_or_teacher_has_access(Form $form): RedirectResponse|bool
    {
        $login_category = cas()->getAttribute('businesscategory');
        if ($login_category === 'ΕΚΠΑΙΔΕΥΤΙΚΟΣ' || $login_category === 'ΠΡΟΣΩΠΙΚΟ') { // Εκπαιδευτικός

            if (! $form->for_teachers) { // Η φόρμα δεν είναι για συμπλήρωση από εκπαιδευτικούς
                Log::warning('Δεν επιτρέπεται η φόρμα σε εκπαιδευτικούς. Ο χρήστης με uid:'.cas()->getAttribute('uid').' και email:'.cas()->getAttribute('mail').' προσπάθησε να αποκτήσει πρόσβαση.');

                return redirect(route('report.index'))->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός.');
            }

            $teacher = session()->get('teacher', null);

            if (! $teacher /* && $allow_all_teachers->value === '1' */) { // Για εκπαιδευτικούς από παντού
                if (! $form->for_all_teachers) {
                    Log::warning('Δεν επιτρέπεται η φόρμα σε εκπαιδευτικούς εκτός της Διεύθυνσης. Ο χρήστης με uid:'.cas()->getAttribute('uid').' και email:'.cas()->getAttribute('mail').' προσπάθησε να αποκτήσει πρόσβαση.');

                    return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα ως εκπαιδευτικός που δεν ανήκει στη Διεύθυνση.');
                }

                $other_teacher = session()->get('other_teacher', null);

                return true;
            }

            return true;
        }

        $school = session()->get('school', null);

        $categories = $school->categories;
        $form_categories = $form->school_categories;
        $in_category = false;
        foreach ($categories as $category) {
            if ($form_categories->contains($category)) {
                $in_category = true;
            }
        }

        return $form->schools()->where('school_id', $school->id)->count() > 0 || $in_category;
    }
}
