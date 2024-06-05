<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/ts/app.ts'])
    @routes(['report'])

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet" type="text/css">
</head>

<body class="d-flex flex-column h-100">
    @include('inc.navbar')
    <div class="container-fluid flex-shrink-0" id="app">

        <main class="py-4">
            @include('inc.messages')
            @yield('content')
        </main>
    </div>
    @include('inc.footer')
</body>

</html>
