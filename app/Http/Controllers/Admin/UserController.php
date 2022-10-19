<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(User::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        return view('admin.user.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'min:6', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = new User([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'username' => $request->get('username'),
            'password' => Hash::make($request->get('password')),
            'active' => 1,
            'updated_by' => Auth::user()->id,
        ]);

        $user->save();

        $userRole = Role::where('name', 'User')->first();
        $user->roles()->attach($userRole);

        return redirect(route('admin.user.show', [$user]))
            ->with('status', 'Ο χρήστης αποθηκεύτηκε!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('admin.user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('admin.user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'username' => ['required', 'string', 'min:6', 'max:255', Rule::unique('users')->ignore($user)],
        ]);

        $user->username = $request->get('username');
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        // Ενημέρωση ρόλων και κατάστασης λογαριασμού μόνο από τους διαχειριστές
        if (Auth::user()->isAdministrator()) {
            $user->active = $request->get('active') == 1 ? 1 : 0;

            $roles = DB::table('roles')->get();
            $new_roles = array();
            foreach ($roles as $role) {
                $check = $request->get($role->name);
                if ($check == 1) {
                    array_push($new_roles, $role->id);
                }
            }
            $user->roles()->sync($new_roles);
        }

        $user->save();

        return redirect(route('admin.user.index'))->with('status', 'Τα στοιχεία του χρήστη ενημερώθηκαν!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect(route('admin.user.index'))->with('status', 'Ο χρήστης διαγράφηκε!');
    }

    public function password(User $user)
    {
        if (Auth::user()->isAdministrator() || Auth::user()->id == $user->id) {
            return view('admin.user.password')->with('user', $user);
        } else {
            abort(403);
        }
    }

    public function changePassword(Request $request, User $user)
    {
        if (Auth::user()->isAdministrator() || Auth::user()->id == $user->id) {
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user->password = Hash::make($request->get('password'));
            $user->save();

            if (Auth::user()->isAdministrator()) {
                return redirect()->route('admin.user.index')->with('status', 'Ο κωδικός άλλαξε!');
            } else {
                return redirect()->route('admin.index')->with('status', 'Ο κωδικός άλλαξε!');
            }
        } else {
            abort(403);
        }
    }

    public function confirmDelete(User $user)
    {
        return view('admin.user.confirm_delete')->with('user', $user);
    }
}
