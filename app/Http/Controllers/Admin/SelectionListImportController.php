<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelectionList;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SelectionListImportController extends Controller
{
    public function edit(): View
    {
        return view('admin.list.import');
    }

    public function update(Request $request, #[CurrentUser] User $user): RedirectResponse
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
        $selectionList->created_by = $user->id;
        $selectionList->save();

        return to_route('admin.list.index')->with('status', 'Έγινε εισαγωγή νέας λίστας');
    }
}
