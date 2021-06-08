@extends('layouts.app')

@section('content')
    @php
        // Απενεργοποίησε όλες τα πεδία της φόρμας για να μην μπορούν
        // να αλλάξουν τις τιμές
        $disabled = "disabled";
    @endphp
    <br/>
    <a href="{{route('report.index')}}" class="btn btn-secondary" role="button">Πίσω</a>
    <h1>{{$form->title}}</h1>
    <h3>{{$form->notes}}</h3>
    <hr/>
    <div class="card">
        <div class="card-header">
            Συμπληρωμένη φόρμα - {{ Auth::guard('school')->user()->name }}
        </div>
        <div class="card-body">
            @foreach ($form->form_fields as $field)
                @include('inc.formfields')
            @endforeach
        </div>
    </div>
    <hr/>
    <a href="{{ route('report.edit', $form->id) }}" class="btn btn-primary">Επεξεργασία</a>

@endsection
