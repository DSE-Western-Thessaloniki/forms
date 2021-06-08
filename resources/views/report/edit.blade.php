@extends('layouts.app')

@section('content')
    @if (session('status'))
    <div class="alert alert-success" role="alert">
        {{ session('status') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    </div><br />
    @endif

    <form action={{ route('report.update', Auth::guard('school')->user()->id) }} method='post'>

    <h1>{{$form->title}}</h1>
    <h3>{{$form->notes}}</h3>
    <hr/>
    <div class="card">
        <div class="card-header">
            {{$form->title}}
        </div>
        <div class="card-body">
            @foreach ($form->form_fields as $field)
                @include('inc.formfields')
            @endforeach
        </div>
    </div>
    <hr/>
    <div class="form-group row">
        <div class="col-2">
            <a class="btn btn-danger" href="{{ route('report.index') }}">Ακύρωση</a>
        </div>
        <div class="col-10 d-flex justify-content-end">
            @method('PUT')
            <button class='btn btn-primary' type='submit'>Αποθήκευση</a>
        </div>
    </div>
    @csrf


@endsection
