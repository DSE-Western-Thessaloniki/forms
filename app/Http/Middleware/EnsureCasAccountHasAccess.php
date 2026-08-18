<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Option;
use App\Models\OtherTeacher;
use App\Models\School;
use App\Models\Teacher;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCasAccountHasAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $school_model = null;
        $teacher_model = null;
        $other_teacher_model = null;
        $cas_model_category = '';

        session()->forget(['school', 'teacher', 'other_teacher', 'cas_model_category']);

        $teacher_uid = cas()->getAttribute('employeenumber');
        $login_category = cas()->getAttribute('businesscategory');
        if ($login_category === 'ΕΚΠΑΙΔΕΥΤΙΚΟΣ' || $login_category === 'ΠΡΟΣΩΠΙΚΟ') { // Εκπαιδευτικός
            $allow_teachers = Option::where('name', 'allow_teacher_login')->first();
            $allow_all_teachers = Option::where('name', 'allow_all_teachers')->first();

            if ($allow_teachers->value !== '1') { // Δεν επιτρέπεται η είσοδος εκπαιδευτικών
                Log::warning('Δεν επιτρέπεται η είσοδος σε εκπαιδευτικούς. Ο χρήστης με uid:'.cas()->getAttribute('uid').' και email:'.cas()->getAttribute('mail').' προσπάθησε να αποκτήσει πρόσβαση.');

                return response()->view('pages.deny_access');
            }

            $teacher = Teacher::where('am', $teacher_uid)
                ->orWhere('afm', $teacher_uid)
                ->first();

            if (! $teacher && $allow_all_teachers->value !== '1') { // Αν δεν βρέθηκε ο εκπαιδευτικός και δεν επιτρέπεται η είσοδος σε εκπαιδευτικούς από όλη την Ελλάδα
                Log::warning('Δεν επιτρέπεται η είσοδος σε εκπαιδευτικούς από όλη τη χώρα. Ο χρήστης με uid:'.cas()->getAttribute('uid').' και email:'.cas()->getAttribute('mail').' προσπάθησε να αποκτήσει πρόσβαση.');

                return response()->view('pages.deny_access');
            }

            if (! $teacher /* && $allow_all_teachers->value === '1' */) { // Για εκπαιδευτικούς από παντού
                // Βρες τον εκπαιδευτικό από τον πίνακα other_teachers και ενημέρωσε τα στοιχεία του
                $other_teacher = OtherTeacher::firstOrNew([
                    'employeenumber' => cas()->getAttribute('employeenumber'),
                ]);

                if ($other_teacher->name !== cas()->getAttribute('cn') ||
                    $other_teacher->email !== cas()->getAttribute('mail')) {

                    $other_teacher->name = cas()->getAttribute('cn');
                    $other_teacher->email = cas()->getAttribute('mail');
                    $other_teacher->save();
                }

                $cas_model_category = 'other_teacher';

                $other_teacher_model = $other_teacher;
            } else {
                $cas_model_category = 'teacher';

                $teacher_model = $teacher;
            }
        } else {
            $school = School::where('username', cas()->getAttribute('uid'))
                ->orWhere('email', cas()
                    ->getAttribute('mail'))
                ->first();
            if (! $school) { // Αν ο λογαριασμός δεν αντιστοιχεί σε σχολική μονάδα
                Log::warning('Το uid:'.cas()->getAttribute('uid').' και το email:'.cas()->getAttribute('mail').' δεν αντιστοιχούν σε λογαριασμό.');

                return response()->view('pages.deny_access');
            }

            $cas_model_category = 'school';

            $school_model = $school;
        }

        session([
            'school' => $school_model,
            'teacher' => $teacher_model,
            'other_teacher' => $other_teacher_model,
            'cas_model_category' => $cas_model_category,
        ]);

        return $next($request);
    }
}
