<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSelectionListRequest;
use App\Http\Requests\UpdateSelectionListRequest;
use App\Models\SelectionList;
use Illuminate\Http\Request;

class SelectionListController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(SelectionList::class, 'selection_list');
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
        $request_data = $request->validated();
        $selection_list_data = [];
        for ($i = 0; $i < count($request_data['id']); $i++) {
            $selection_list_data[] = [
                'id' => $request_data['id'][$i],
                'value' => $request_data['value'][$i]
            ];
        }

        $selection_list = new SelectionList();
        $selection_list->name = $request_data['name'];
        $selection_list->data = json_encode($selection_list_data);
        $selection_list->active = true;
        $selection_list->created_by = $request->user()->id;
        $selection_list->save();

        return redirect(route('admin.list.index'));
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
        //
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
        //
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
