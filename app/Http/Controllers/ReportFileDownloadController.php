<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\UsesCasAccessFiltering;
use App\Models\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportFileDownloadController extends Controller
{
    use UsesCasAccessFiltering;

    public function __invoke(Form $report, string $fieldId, string $record): StreamedResponse|RedirectResponse|View
    {
        // Κάνε έναν απλό έλεγχο για ασφάλεια
        if (! is_numeric($record) ||
            ! is_numeric($fieldId)) {
            abort(404);
        }

        if ($report->active) {
            $access = $this->school_or_teacher_has_access($report);
            if (is_bool($access)) {
                if ($access) {
                    $school = session()->get('school');
                    $teacher = session()->get('teacher');
                    $other_teacher = session()->get('other_teacher');

                    if ($school !== null) {
                        $subfolder = "school/{$school->id}";
                        $record_data = $report->data()
                            ->where('school_id', $school->id)
                            ->where('record', $record)
                            ->where('form_field_id', $fieldId)
                            ->first();
                    } elseif ($teacher !== null) {
                        $subfolder = "teacher/{$teacher->id}";
                        $record_data = $report->data()
                            ->where('teacher_id', $teacher->id)
                            ->where('record', $record)
                            ->where('form_field_id', $fieldId)
                            ->first();
                    } else {
                        $subfolder = "other_teacher/{$other_teacher->id}";
                        $record_data = $report->data()
                            ->where('other_teacher_id', $other_teacher->id)
                            ->where('record', $record)
                            ->where('form_field_id', $fieldId)
                            ->first();
                    }

                    $path = "report/{$report->id}/$subfolder/$record/$fieldId";
                    if (Storage::exists($path)) {

                        return Storage::download($path, $record_data->data);
                    }

                    return to_route('report.index')->with('error', 'Το αρχείο δεν βρέθηκε');
                }

                return to_route('report.index')->with('error', 'Δεν έχετε δικαίωμα πρόσβασης στη φόρμα');
            }

            return $access;
        }

        return to_route('report.index')->with('error', 'Η φόρμα έχει κλείσει και δεν δέχεται άλλες απαντήσεις.');
    }
}
