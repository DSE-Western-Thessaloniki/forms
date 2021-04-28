@extends('layouts.app')

@section('content')
    <h1 class="text-center">{{__('Create Form')}}</h1>
    <form action="{{ route('forms.store')}}" method="post">
        <vform-component></vform-component>

        <br/>
        <div class="col-md-10 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">{{ __('Save') }}</button>
        </div>
    </form>

@endsection
