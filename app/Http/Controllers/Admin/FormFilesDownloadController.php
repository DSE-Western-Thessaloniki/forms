<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\UsesFileFiltering;
use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use DateTime;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Κάνει λήψη όλων των αρχείων που έχουν ανέβει σε μια φόρμα. Δημιουργεί ένα
 * zip αρχείο με τα ονόματα των πεδίων ως γονικό φάκελο και εσωτερικά έχει ένα
 * φάκελο ανά σχολείο/καθηγητή με το αρχείο που ανέβασε.
 */
final class FormFilesDownloadController extends Controller
{
    use UsesFileFiltering;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Form $form, #[CurrentUser] User $user): RedirectResponse|StreamedResponse
    {
        $fields = $form->form_fields->where('type', FormField::TYPE_FILE);

        if ($fields->isEmpty()) {
            abort(404);
        }

        $zip_path = '/tmp/'.$user->id.'/';
        Storage::makeDirectory($zip_path);

        // Κάνε εκκαθάριση παλιών αρχείων
        foreach (Storage::files($zip_path) as $file) {
            Storage::delete($file);
        }

        $zip = new ZipArchive;
        $now = DateTime::createFromFormat('U.u', ''.microtime(true));
        $zip_name = $now->format('YmdHisu').'.zip';
        $zip->open(Storage::path($zip_path.$zip_name), ZipArchive::CREATE);

        $files_added = 0;
        foreach ($fields as $field) {
            $subfolder = mb_strimwidth($this->filterFilename($field->title, false), 0, 15, '...');
            foreach ($field->field_data as $data) {
                if ($data->school) {
                    $schoolName = $this->filterFilename($data->school->name, false);
                    if ($form->multiple) {
                        $subfolder2 = "$subfolder/{$schoolName}/{$data->record}";
                    } else {
                        $subfolder2 = "$subfolder/{$schoolName}";
                    }
                    $filepath = "/report/{$form->id}/school/{$data->school->id}/{$data->record}/{$field->id}";
                    $local_file = Storage::path($filepath);
                    if ($field->required || file_exists("$local_file")) {
                        $zip->addFile($local_file, "$subfolder2/{$data->data}");
                        $files_added++;
                    }
                } elseif ($data->teacher) {
                    $teacherName = $this->filterFilename("{$data->teacher->surname} {$data->teacher->name} {$data->teacher->am}", false);
                    if ($form->multiple) {
                        $subfolder2 = "$subfolder/{$teacherName}/{$data->record}";
                    } else {
                        $subfolder2 = "$subfolder/{$teacherName}";
                    }
                    $filepath = "/report/{$form->id}/teacher/{$data->teacher->id}/{$data->record}/{$field->id}";
                    $local_file = Storage::path($filepath);
                    if ($field->required || file_exists("$local_file")) {
                        $zip->addFile($local_file, "$subfolder2/{$data->data}");
                        $files_added++;
                    }
                } else {
                    $otherTeacherName = $this->filterFilename($data->other_teacher->name, false);
                    if ($form->multiple) {
                        $subfolder2 = "$subfolder/{$otherTeacherName}/{$data->record}";
                    } else {
                        $subfolder2 = "$subfolder/{$otherTeacherName}";
                    }
                    $filepath = "/report/{$form->id}/other_teacher/{$data->other_teacher->id}/{$data->record}/{$field->id}";
                    $local_file = Storage::path($filepath);
                    if ($field->required || file_exists("$local_file")) {
                        $zip->addFile($local_file, "$subfolder2/{$data->data}");
                        $files_added++;
                    }
                }
            }
        }

        $zip->close();

        if ($files_added === 0) {
            Storage::delete($zip_path.$zip_name);

            return redirect(route('admin.form.data', $form->id))->with('error', 'Δεν βρέθηκαν αρχεία για λήψη');
        }

        return Storage::download($zip_path.$zip_name);
    }
}
