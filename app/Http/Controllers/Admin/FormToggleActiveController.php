<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\RedirectResponse;

final class FormToggleActiveController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Form $form): RedirectResponse
    {
        $form->active = $form->active ? 0 : 1;
        $form->save();

        return redirect(route('admin.form.index').'#form-'.$form->id)->with('status', 'Η φόρμα '.($form->active ? 'ενεργοποιήθηκε' : 'απενεργοποιήθηκε'));
    }
}
