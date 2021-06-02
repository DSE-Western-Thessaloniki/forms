@extends('layouts.admin.app')

@section('content')
    <h1 class="text-center">Δημιουργίας φόρμας</h1>
    <form action="{{ route('admin.form.store') }}" method="post">
        <vform-component
            :schools="{{ json_encode($schools) }}"
            :categories="{{ json_encode($categories) }}"
        >
        </vform-component>

        <div class="form-group row mt-5">
            <div class="col-2">
                <a class="btn btn-danger" href="{{ route('admin.form.index') }}">Ακύρωση</a>
            </div>
            <div class="col-md-10 d-flex justify-content-end">
                <button class="btn btn-primary" type="submit">Αποθήκευση</button>
            </div>
        </div>
        @csrf
    </form>

@endsection
