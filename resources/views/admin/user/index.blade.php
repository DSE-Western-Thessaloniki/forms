@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
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

                    <div class="row justify-content-md-center">
                        @forelse($users as $user)
                        <div class="col-xl-4">
                            <div class="card my-2 shadow">
                                @if($user->active)
                                <div class="card-header">
                                @else
                                <div class="card-header bg-danger text-white">
                                @endif
                                    <a href="{{ route('admin.user.show', $user->id) }}">{{$user->username}}</a>
                                    @if(!$user->active)
                                    <em>(Ανενεργός)</em>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-2">
                                                    @if($user->roles->where('name', 'Administrator')->count() > 0)
                                                        <span class="text-dark h1">@icon('fas fa-user-ninja')</span>
                                                    @elseif ($user->roles->where('name', 'Author')->count() > 0)
                                                        <span class="text-danger h1">@icon('fas fa-user-edit')</span>
                                                    @else
                                                        <span class="text-success h1">@icon('fas fa-user')</span>
                                                    @endif
                                                </div>
                                                <div class="col-10">
                                                    <div>
                                                        Όνομα: {{$user->name}}
                                                    </div>
                                                    <div>
                                                        E-mail: {{$user->email}}
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row justify-content-md-center">
                                            <a href="{{ route('admin.user.edit', $user)}}" class="btn btn-primary m-1">@icon('fas fa-edit') Επεξεργασία</a><br/>
                                            <a href="{{ route('admin.user.password', $user)}}" class="btn btn-success m-1">@icon('fas fa-key') Αλλαγή κωδικού</a>
                                            <form action="{{ route('admin.user.destroy', $user->id)}}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger m-1" type="submit">@icon('fas fa-trash-alt') Διαγραφή</button>
                                            </form>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @empty
                            Δεν υπάρχουν διαθέσιμοι χρήστες
                        @endforelse
                        {{ $users->links() }}
                    </div>
                </div>
                @else
                    Δεν επιτρέπεται η πρόσβαση
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
