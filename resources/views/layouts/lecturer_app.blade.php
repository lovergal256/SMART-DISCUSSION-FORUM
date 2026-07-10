<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Smart Discussion Forum')</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        body { font-family: 'Times New Roman', Times, serif; margin: 0; padding: 0; background: #f0f4f8; font-weight: 400; }
        h1, h2, h3, nav a, .btn { font-family: 'Times New Roman', Times, serif; font-weight: 700; letter-spacing: 0.5px; }
        nav { background: linear-gradient(135deg, #0077b6, #023e8a); padding: 15px 30px; color: white; }
        nav a { color: white; text-decoration: none; margin-right: 20px; font-weight: bold; }
        nav a:hover { color: #caf0f8; }
        .container { max-width: 750px; margin: 30px auto; padding: 0 10px; }
        .btn { padding: 8px 16px; background: linear-gradient(135deg, #0077b6, #023e8a); color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn:hover { background: linear-gradient(135deg, #023e8a, #03045e); }
        .btn-red { background: #c0392b; }
        .alert-success { background: #d0e8f5; padding: 10px; border-radius: 4px; margin-bottom: 15px; color: #0077b6; border-left: 4px solid #0077b6; }
        .alert-error { background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; color: #721c24; }
        .card { padding: 20px; background: white; border-radius: 6px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #0077b6; }
        .card h3 a { color: #0077b6; }
        .card h3 a:hover { color: #023e8a; }
        input, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #b0c4d8; border-radius: 4px; outline: none; background: #f8fafc; }
        input:focus, textarea:focus { border-color: #0077b6; box-shadow: 0 0 4px rgba(0,119,182,0.3); }
        label { font-weight: bold; display: block; margin-bottom: 5px; color: #0077b6; }
    </style>
    @stack('styles')
</head>
<body>

    @include('partials.lecturer_sidebar')

    <main>
        @include('partials.topbar')

        <div class="content">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
