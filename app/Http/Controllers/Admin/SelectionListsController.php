<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSelectionListRequest;
use App\Http\Requests\UpdateSelectionListRequest;
use App\Models\SelectionList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SelectionListsController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(SelectionList::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter');
        if ($filter) {
            $lists = SelectionList::orderBy('name')
                ->where('name', 'like', '%'.$filter.'%')
                ->with('created_by')
                ->with('updated_by')
                ->paginate(15);
        }
        else {
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
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.list.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSelectionListRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSelectionListRequest $request)
    {
        $validatedData = $request->validated();
        $selectionListData = [];
        for ($i = 0; $i < count($validatedData['id']); $i++) {
            $selectionListData[] = [
                'id' => $validatedData['id'][$i],
                'value' => $validatedData['value'][$i]
            ];
        }

        $selectionList = new SelectionList();
        $selectionList->name = $validatedData['name'];
        $selectionList->data = json_encode($selectionListData);
        $selectionList->active = true;
        $selectionList->created_by = $request->user()->id;
        $selectionList->save();

        return redirect(route('admin.list.index'))->with('status', 'Η λίστα αποθηκεύτηκε!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Http\Response
     */
    public function show(SelectionList $selectionList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Http\Response
     */
    public function edit(SelectionList $selectionList)
    {
        return view('admin.list.edit', compact('selectionList'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSelectionListRequest  $request
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSelectionListRequest $request, SelectionList $selectionList)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('selection_lists')->ignore($selectionList->id)],
            'id' => ['array'],
            'id.*' => ['string', 'max:255'],
            'value' => ['array'],
            'value.*' => ['string', 'max:255']
        ]);

        $selectionListData = [];
        for ($i = 0; $i < count($validatedData['id']); $i++) {
            $selectionListData[] = [
                'id' => $validatedData['id'][$i],
                'value' => $validatedData['value'][$i]
            ];
        }

        $selectionList->update([
            'name' => $validatedData['name'],
            'data' => json_encode($selectionListData),
            'updated_by' => $request->user()->id,
        ]);

        return redirect(route('admin.list.index'))->with('status', 'Η λίστα ενημερώθηκε επιτυχώς!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SelectionList  $selectionList
     * @return \Illuminate\Http\Response
     */
    public function destroy(SelectionList $selectionList)
    {
        //
    }
}
