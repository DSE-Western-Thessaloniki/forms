<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SelectionList;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

final class SelectionListCopyController extends Controller
{
    public function __invoke(SelectionList $selectionList, #[CurrentUser] User $user): RedirectResponse
    {
        // Δημιουργία αντιγράφου
        $selectionListClone = $selectionList->replicate();
        $selectionListClone->name = $selectionList->name.' (Αντίγραφο)';
        $selectionListClone->created_by = $user->id;
        $selectionListClone->save();

        return to_route('admin.list.index')->with('status', 'Το αντίγραφο της λίστας δημιουργήθηκε');
    }
}
