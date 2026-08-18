<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SchoolImportController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function edit(): View
    {
        return view('admin.school.import');
    }

    /**
     * Display the specified resource.
     */
    public function update(Request $request, #[CurrentUser] User $user): RedirectResponse
    {
        DB::beginTransaction();

        $request->validate([
            'csvfile' => 'required|file|mimes:csv,txt',
        ]);

        $uploadedFile = $request->file('csvfile');
        $data = [];
        $numFields = 6; // name, username, code, email, telephone, category
        $missingField = false;
        if (($handle = fopen($uploadedFile->getPathname(), 'r')) !== false) {
            while (($row_data = fgetcsv($handle, 1000, ',', escape: '\\')) !== false) {
                if (count($row_data) !== $numFields) {
                    $missingField = true;
                    break;
                }

                $data[] = [
                    'name' => $row_data[0],
                    'username' => $row_data[1],
                    'code' => $row_data[2],
                    'email' => $row_data[3],
                    'telephone' => $row_data[4],
                    'category' => $row_data[5],
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
                        'name' => $row_data[0],
                        'username' => $row_data[1],
                        'code' => $row_data[2],
                        'email' => $row_data[3],
                        'telephone' => $row_data[4],
                        'category' => $row_data[5],
                    ];
                }
                fclose($handle);
            }
        }

        if ($missingField || $data === []) {
            return to_route('admin.school.index')->with('error', 'Λανθασμένη μορφή αρχείου');
        }

        foreach ($data as $row) {
            $school = School::where('code', $row['code'])->first();

            if ($school) {
                $school->name = $row['name'];
                $school->username = $row['username'];
                $school->email = $row['email'];
                $school->telephone = $row['telephone'];
                $school->updated_by = $user->id;
                $school->save();
            } else {
                $school = new School;
                $school->name = $row['name'];
                $school->username = $row['username'];
                $school->code = $row['code'];
                $school->email = $row['email'];
                $school->telephone = $row['telephone'];
                $school->active = true;
                $school->updated_by = $user->id;
                $school->save();

                $category_name = $row['category'];
                $category = SchoolCategory::where('name', $category_name)->first();

                if (! $category) { // Η κατηγορία δεν υπάρχει ήδη
                    $category = new SchoolCategory;
                    $category->name = $row['category'];
                    $category->save();
                }

                $school->categories()->attach($category);
            }
        }

        DB::commit();

        return to_route('admin.school.index')->with('success', 'Έγινε εισαγωγή '.count($data).' σχολικών μονάδων');
    }
}
