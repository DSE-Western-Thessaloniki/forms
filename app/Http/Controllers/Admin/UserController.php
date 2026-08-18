<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

final class UserController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(User::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::with('roles')->paginate(15);

        return view('admin.user.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        $userData = $request->validated();
        $userData['password'] = Hash::make($userData['password']);
        $userData['password_reset'] = (bool) $request->validated('password_reset', false);
        $user = new User([
            'active' => true,
            'updated_by' => $user->id,
            ...$userData,
        ]);

        $user->saveOrFail();

        $userRole = Role::where('name', 'User')->first();
        $user->roles()->attach($userRole);

        return to_route('admin.user.show', ['user' => $user])
            ->with('status', 'Ο χρήστης αποθηκεύτηκε!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        return view('admin.user.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('admin.user.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user, #[CurrentUser] User $currentUser): RedirectResponse
    {
        $user->username = $request->validated('username');
        $user->name = $request->validated('name');
        $user->email = $request->validated('email');
        $user->password_reset = $request->validated('password_reset') ? 1 : 0;

        // Ενημέρωση ρόλων και κατάστασης λογαριασμού μόνο από τους διαχειριστές
        if ($currentUser->isAdministrator()) {
            $user->active = (bool) $request->validated('active');

            $roles = Role::all();
            $new_roles = [];
            foreach ($roles as $role) {
                $check = (bool) $request->validated($role->name, false);
                if ($check) {
                    $new_roles[] = $role->id;
                }
            }
            $user->roles()->sync($new_roles);
        }

        $user->save();

        return to_route('admin.user.index')->with('status', 'Τα στοιχεία του χρήστη ενημερώθηκαν!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return to_route('admin.user.index')->with('status', 'Ο χρήστης διαγράφηκε!');
    }
}
