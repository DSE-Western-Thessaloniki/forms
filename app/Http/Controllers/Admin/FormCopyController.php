<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Services\FormService;
use Illuminate\Http\RedirectResponse;

final class FormCopyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Form $form, FormService $formService): RedirectResponse
    {
        $formService->copyForm($form);

        return redirect(route('admin.form.index'))->with('status', 'Το αντίγραφο της φόρμας δημιουργήθηκε');
    }
}
