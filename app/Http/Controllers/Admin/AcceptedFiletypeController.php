<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcceptedFiletypeRequest;
use App\Http\Requests\UpdateAcceptedFiletypeRequest;
use App\Models\AcceptedFiletype;

class AcceptedFiletypeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(AcceptedFiletype::class, 'accepted_filetype');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accepted_filetypes = AcceptedFiletype::all();

        return view('admin.accepted_filetype.index')
            ->with('accepted_filetypes', $accepted_filetypes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.accepted_filetype.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAcceptedFiletypeRequest $request)
    {
        // Αφαίρεσε το * από την επέκταση αν έχει κατά λάθος δοθεί
        $extension = $request->validated('extension');
        $extension = str_replace('*.', '.', $extension);

        AcceptedFiletype::create(
            array_merge(
                $request->validated(),
                [
                    'extension' => $extension,
                ]
            )
        );

        return redirect(route('admin.accepted_filetype.index'))
            ->with('success', 'Ο τύπος αρχείων δημιουργήθηκε επιτυχώς');
    }

    /**
     * Display the specified resource.
     */
    public function show(AcceptedFiletype $acceptedFiletype)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcceptedFiletype $acceptedFiletype)
    {
        return view('admin.accepted_filetype.edit')
            ->with('accepted_filetype', $acceptedFiletype);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAcceptedFiletypeRequest $request, AcceptedFiletype $acceptedFiletype)
    {
        // Αφαίρεσε το * από την επέκταση αν έχει κατά λάθος δοθεί
        $extension = $request->validated('extension');
        $extension = str_replace('*.', '.', $extension);

        $acceptedFiletype->update(
            array_merge(
                $request->validated(),
                [
                    'extension' => $extension,
                ]
            )
        );

        return redirect(route('admin.accepted_filetype.index'))
            ->with('success', 'Ο τύπος αρχείων ενημερώθηκε επιτυχώς');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcceptedFiletype $acceptedFiletype)
    {
        $acceptedFiletype->delete();

        return redirect(route('admin.accepted_filetype.index'))
            ->with('success', 'Επιτυχής διαγραφή τύπου αρχείων');
    }
}
