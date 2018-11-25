<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="{{asset('css/app.css')}}" type="text/css" charset="utf-8">
        <title>{{config('app.name')}}</title>

    </head>
    <body>
        @include('inc.navbar')
	<div class="container">
            @include('inc.messages')
	    @yield('content')
	</div>
        <script src="{{ asset('js/app.js') }}"></script>
        <script src="{{ asset('vendor/unisharp/laravel-ckeditor/ckeditor.js') }}"></script>
        <script>
            CKEDITOR.replace( 'article-ckeditor' );
        </script>
    </body>
</html>
