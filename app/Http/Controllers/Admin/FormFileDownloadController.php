<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormFieldData;
use App\Models\OtherTeacher;
use App\Models\School;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Κάνει λήψη ενός μεμονωμένου αρχείου από μια φόρμα που περιέχει πεδία αρχείων
 */
final class FormFileDownloadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Form $form, string $category, string $categoryId, string $record, string $fieldId): StreamedResponse|RedirectResponse
    {
        // Κάνε έναν απλό έλεγχο για ασφάλεια
        if (! in_array($category, ['school', 'teacher', 'other_teacher']) ||
            ! is_numeric($categoryId) ||
            ! is_numeric($record) ||
            ! is_numeric($fieldId)) {
            abort(404);
        }

        $object = $this->getObject($category, $categoryId);

        $record_data = $this->getRecordData($form, $object, $record, $fieldId);

        // Αν περαστεί λάθος record
        if (! $record_data instanceof FormFieldData) {
            abort(404);
        }

        $filename = $record_data->data;
        if (Storage::exists("report/$form->id/$category/$categoryId/$record/$fieldId")) {
            return Storage::download("report/$form->id/$category/$categoryId/$record/$fieldId", $filename);
        }

        return redirect(route('admin.form.index'))->with('error', 'Το αρχείο δεν βρέθηκε');
    }

    private function getRecordData(Form $form, Model $object, string $record, string $fieldId): ?FormFieldData
    {
        $foreignKeyMap = [
            School::class => 'school_id',
            Teacher::class => 'teacher_id',
            OtherTeacher::class => 'other_teacher_id',
        ];

        $record_data = $form->data()
            ->where($foreignKeyMap[get_class($object)], $object->id)
            ->where('record', $record)
            ->where('form_field_id', $fieldId)
            ->first();

        return $record_data;
    }

    private function getObject(string $category, string $categoryId): School|Teacher|OtherTeacher
    {
        $object = null;

        $object = match ($category) {
            'school' => School::findOrFail($categoryId),
            'teacher' => Teacher::findOrFail($categoryId),
            'other_teacher' => OtherTeacher::findOrFail($categoryId),
            default => abort(404)
        };

        return $object;
    }
}
