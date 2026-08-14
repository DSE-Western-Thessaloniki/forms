<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSelectionListRequest;
use App\Http\Requests\UpdateSelectionListRequest;
use App\Models\SelectionList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SelectionListsController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(SelectionList::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filter = $request->input('filter');
        if ($filter) {
            $lists = SelectionList::orderBy('name')
                ->where('name', 'like', '%'.$filter.'%')
                ->with('created_by')
                ->with('updated_by')
                ->paginate(15);
        } else {
            $lists = SelectionList::orderBy('name')
                ->with('created_by')
                ->with('updated_by')
                ->paginate(15);
        }

        return view('admin.list.index')
            ->with('lists', $lists)
            ->with('filter', $filter);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.list.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSelectionListRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        $selectionListData = [];
        $counter = count($validatedData['id']);
        for ($i = 0; $i < $counter; $i++) {
            $selectionListData[] = [
                'id' => $i,
                'value' => $validatedData['value'][$i],
            ];
        }

        $selectionList = new SelectionList;
        $selectionList->name = $validatedData['name'];
        $selectionList->data = json_encode($selectionListData);
        $selectionList->active = true;
        $selectionList->created_by = $request->user()->id;
        $selectionList->save();

        return to_route('admin.list.index')->with('status', 'Η λίστα αποθηκεύτηκε!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SelectionList $selectionList): View
    {
        return view('admin.list.edit', ['selectionList' => $selectionList]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSelectionListRequest $request, SelectionList $selectionList): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('selection_lists')->ignore($selectionList->id)],
            'id' => ['array'],
            'id.*' => ['string', 'max:255'],
            'value' => ['array'],
            'value.*' => ['string', 'max:255'],
            'active' => [Rule::in('0', '1', true, false), 'nullable'],
        ]);

        $selectionListData = [];
        $counter = count($validatedData['id']);
        for ($i = 0; $i < $counter; $i++) {
            $selectionListData[] = [
                'id' => $i,
                'value' => $validatedData['value'][$i],
            ];
        }

        $selectionList->update([
            'name' => $validatedData['name'],
            'data' => json_encode($selectionListData),
            'active' => $validatedData['active'] ?? false,
            'updated_by' => $request->user()->id,
        ]);

        return to_route('admin.list.index')->with('status', 'Η λίστα ενημερώθηκε επιτυχώς!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SelectionList $selectionList): RedirectResponse
    {
        $selectionList->delete();

        return to_route('admin.list.index')->with('status', 'Η λίστα διαγράφηκε!');
    }

    public function showImport(): View
    {
        return view('admin.list.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csvfile' => 'required|file|mimes:csv,txt',
        ]);

        $uploadedFile = $request->file('csvfile');
        $data = [];
        if (($handle = fopen($uploadedFile->getPathname(), 'r')) !== false) {
            while (($row_data = fgetcsv($handle, 1000, ';', escape: '\\')) !== false) {
                $data[] = $row_data;
            }
            fclose($handle);
        }

        if ($data === []) {
            return to_route('admin.list.index')->with('error', 'Λανθασμένη μορφή αρχείου');
        }

        $name = '';
        $listData = [];

        foreach ($data as $index => $row) {
            if ($index === 0) {
                $name = $row[0];
            } else {
                $listData[] = [
                    'id' => $index - 1,
                    'value' => $row[0],
                ];
            }
        }

        $selectionList = new SelectionList;
        $selectionList->name = $name;
        $selectionList->active = true;
        $selectionList->data = json_encode($listData);
        $selectionList->created_by = $request->user()->id;
        $selectionList->save();

        return to_route('admin.list.index')->with('status', 'Έγινε εισαγωγή νέας λίστας');
    }

    public function confirmDelete(SelectionList $selectionList): View
    {
        return view('admin.list.confirm_delete')
            ->with('list', $selectionList);
    }

    public function copyList(SelectionList $selectionList): RedirectResponse
    {
        // Δημιουργία αντιγράφου
        $selectionListClone = $selectionList->replicate();
        $selectionListClone->name = $selectionList->name.' (Αντίγραφο)';
        $selectionListClone->created_by = Auth::user()->id;
        $selectionListClone->save();

        return to_route('admin.list.index')->with('status', 'Το αντίγραφο της λίστας δημιουργήθηκε');
    }
}
