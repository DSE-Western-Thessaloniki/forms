<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class UserPasswordController extends Controller
{
    public function edit(User $user): View
    {
        return view('admin.user.password')->with('user', $user);
    }

    public function update(Request $request, User $user, #[CurrentUser] User $currentUser): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($request->input('password'));
        $user->password_reset = 0;
        $user->save();

        if ($currentUser->isAdministrator()) {
            return to_route('admin.user.index')->with('status', 'Ο κωδικός άλλαξε!');
        }

        return to_route('admin.index')->with('status', 'Ο κωδικός άλλαξε!');
    }
}
