<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TeacherImportController extends Controller
{
    public function edit(): View
    {
        return view('admin.teacher.import');
    }

    public function update(Request $request): RedirectResponse
    {
        DB::beginTransaction();
        $request->validate([
            'csvfile' => 'required|file|mimes:csv,txt',
        ]);

        $uploadedFile = $request->file('csvfile');
        $data = [];
        $numFields = 4; // surname, name, am, afm
        $missingField = false;
        if (($handle = fopen($uploadedFile->getPathname(), 'r')) !== false) {
            while (($row_data = fgetcsv($handle, 1000, ',', escape: '\\')) !== false) {
                if (count($row_data) !== $numFields) {
                    $missingField = true;
                    break;
                }
                $data[] = [
                    'surname' => $row_data[0],
                    'name' => $row_data[1],
                    'am' => $row_data[2],
                    'afm' => $row_data[3],
                    'active' => 1,
                ];
            }
            fclose($handle);
        }

        if ($missingField || $data === []) { // Δοκίμασε το ';' ως διαχωριστικό
            $missingField = false;
            $data = [];
            if (($handle = fopen($uploadedFile->getPathname(), 'r')) !== false) {
                while (($row_data = fgetcsv($handle, 1000, ';', escape: '\\')) !== false) {
                    if (count($row_data) !== $numFields) {
                        $missingField = true;
                        break;
                    }
                    $data[] = [
                        'surname' => $row_data[0],
                        'name' => $row_data[1],
                        'am' => $row_data[2],
                        'afm' => $row_data[3],
                        'active' => 1,
                    ];
                }
                fclose($handle);
            }
        }

        if ($missingField || $data === []) {
            return to_route('admin.teacher.index')->with('error', 'Λανθασμένη μορφή αρχείου');
        }

        DB::table('teachers')->
            update(['active' => 0]);

        $alreadyExist = [];
        foreach ($data as $key => $row) {
            $check = Teacher::where('am', $row['am'])
                ->orWhere('afm', $row['afm'])
                ->first();

            if ($check) {
                if (($check->am !== $row['am'] || $check->afm !== $row['afm']) && ($check->am !== $check->afm && intval($check->am) !== intval($check->afm))) {
                    return to_route('admin.teacher.index')->with('error', "Ασυμφωνία ΑΜ/ΑΦΜ με τη βάση για τον εκπαιδευτικό του πίνακα {$row['surname']} {$row['name']} ΑΜ: {$row['am']} ΑΦΜ: {$row['afm']}");
                }
                $check->surname = $row['surname'];
                $check->name = $row['name'];
                $check->active = true;
                $check->save();

                $alreadyExist[] = $key;
            }
        }

        // Αφαίρεσε τις εγγραφές που ήδη υπάρχουν στη βάση
        foreach ($alreadyExist as $item) {
            unset($data[$item]);
        }

        Teacher::insert($data);

        DB::commit();

        return to_route('admin.teacher.index')->with('success', 'Έγινε εισαγωγή '.count($data).' εκπαιδευτικών');

    }
}
