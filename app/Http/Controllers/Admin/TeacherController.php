<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TeacherController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Teacher::class, 'teacher');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        if ($request->exists('teacher_filter')) {
            $filter = $request->input('teacher_filter');
        } else {
            $filter = $request->session()->get('teacher_filter', '');
        }
        $request->session()->put('teacher_filter', $filter);

        if ($filter) {
            $teachers = Teacher::query()
                ->where('surname', 'like', '%'.$filter.'%')
                ->orWhere('name', 'like', '%'.$filter.'%')
                ->orWhere('am', 'like', '%'.$filter.'%')
                ->orWhere('afm', 'like', '%'.$filter.'%')
                ->orderBy('surname', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(15);
        } else {
            $teachers = Teacher::orderBy('surname', 'asc')
                ->orderBy('name', 'asc')
                ->paginate(15);
        }

        return view('admin.teacher.index')
            ->with('teachers', $teachers)
            ->with('filter', $filter);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.teacher.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        Teacher::create([
            ...$request->validated(),
            'active' => 1,
        ]);

        return to_route('admin.teacher.index')
            ->with('status', 'Ο εκπαιδευτικός αποθηκεύτηκε!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher): View
    {
        return view('admin.teacher.edit')->with('teacher', $teacher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $teacher->update(
            [
                'active' => (bool) $request->validated('active', false),
                ...$request->validated(),
            ]
        );

        return to_route('admin.teacher.index')
            ->with('status', 'Ο εκπαιδευτικός ενημερώθηκε!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->delete();

        return to_route('admin.teacher.index')
            ->with('status', 'Ο εκπαιδευτικός διαγράφηκε!');
    }

    public function confirmDelete(Teacher $teacher): View
    {
        return view('admin.teacher.confirm_delete')
            ->with('teacher', $teacher);
    }

    public function showImport(): View
    {
        return view('admin.teacher.import');
    }

    public function import(Request $request): RedirectResponse
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
