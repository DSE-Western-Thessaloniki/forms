@extends('layouts.app')

@section('content')
    <h3>Forms</h3>
    @if(count($forms) > 1)
        @foreach($forms as $form)
            <div class="well">
                <h3>{{$form->title}}</h3>
            </div>
        @endforeach
    @else
        <p>No forms found</p>
    @endif
@endsection
