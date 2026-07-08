<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Smart Discussion Forum')</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @stack('styles')
</head>
<body>

    @include('partials.sidebar')

    <main>
        @include('partials.topbar')

        <div class="content">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>