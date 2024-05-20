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
