<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSelectionListRequest;
use App\Http\Requests\UpdateSelectionListRequest;
use App\Models\SelectionList;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class SelectionListController extends Controller
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
    public function store(StoreSelectionListRequest $request, #[CurrentUser] User $user): RedirectResponse
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
        $selectionList->created_by = $user->id;
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
    public function update(UpdateSelectionListRequest $request, SelectionList $selectionList, #[CurrentUser] User $user): RedirectResponse
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
            'updated_by' => $user->id,
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
}
