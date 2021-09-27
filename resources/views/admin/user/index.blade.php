@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Χρήστες</div>

                @if(Auth::user()->isAdministrator())
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="btn-toolbar pb-2" role="toolbar">
                        <div class="btn-group mr-2">
                            <a class="btn btn-primary" href="{{ route('admin.user.create')}}">
                            @icon('plus-circle') Νέος χρήστης
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Όνομα χρήστη</th>
                                    <th>Όνομα</th>
                                    <th>E-mail</th>
                                    <th>Ενεργός</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration + $users->firstItem() - 1 }}.</td>
                                    <td>
                                        @if($user->isAdministrator())
                                            <span class="text-dark h2">@icon('fas fa-user-ninja')</span>
                                        @elseif ($user->roles()->where('name', 'Author')->count() > 0)
                                            <span class="text-danger h2">@icon('fas fa-user-edit')</span>
                                        @else
                                            <span class="text-success h2">@icon('fas fa-user')</span>
                                        @endif
                                        <a href="{{ route('admin.user.show', $user->id) }}">{{$user->username}}</a></td>
                                    <td>{{$user->name}}</td>
                                    <td><pre class="text-center">{{$user->email}}</pre></td>
                                    @if($user->active)
                                        <td class="text-center text-success">
                                            @icon('check')
                                        </td>
                                    @else
                                        <td class="text-center text-danger">
                                            @icon('times')
                                        </td>
                                    @endif
                                    <td>
                                        <a href="{{ route('admin.user.edit', $user)}}" class="btn btn-primary m-1">@icon('fas fa-edit') Επεξεργασία</a><br/>
                                        <a href="{{ route('admin.user.password', $user)}}" class="btn btn-success m-1">@icon('fas fa-key') Αλλαγή κωδικού</a>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.user.destroy', $user->id)}}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">@icon('fas fa-trash-alt') Διαγραφή</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6">Δεν υπάρχουν διαθέσιμοι χρήστες</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $users->links() }}
                </div>
                @else
                    Δεν επιτρέπεται η πρόσβαση
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
