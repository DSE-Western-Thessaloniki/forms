@extends('layouts.admin.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Επιλογές</div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="container">
                            @if (Auth::user()->roles->where('name', 'Administrator')->count())
                                <form action="{{ route('admin.options.store') }}" method="post">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="allow_teacher_login" id="allow_teacher_login" value="1"
                                            {{ App\Models\Option::where('name', 'allow_teacher_login')->first()->value === "1" ? "checked" : "" }}
                                        />
                                        <label for="allow_teacher_login">Ενεργοποίηση σύνδεσης εκπαιδευτικών</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="allow_all_teachers" id="allow_all_teachers" value="1"
                                            {{ App\Models\Option::where('name', 'allow_all_teachers')->first()->value === "1" ? "checked" : "" }}
                                        />
                                        <label for="allow_all_teachers">Ενεργοποίηση σύνδεσης εκπαιδευτικών από όλες τις Διευθύνσεις</label>
                                    </div>
                                    <button class="btn btn-primary mt-3" type="submit">Αποθήκευση</button>
                                    @csrf
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
