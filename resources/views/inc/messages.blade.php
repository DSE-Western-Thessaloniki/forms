@if(count($errors) > 0)
    @foreach($errors->all() as $error)
        <div class="alert alert-danger">
            {{$error}}
        </div>
    @endforeach
@endif

@if(session('success'))
    <div class="alert alert-success h4">
        @icon('fas fa-check') {{session('success')}}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        @icon('fas fa-xmark') {{session('error')}}
    </div>
@endif
