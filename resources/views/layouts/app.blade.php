<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Discussion Forum</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        nav { background: #2d6a4f; padding: 15px 30px; color: white; }
        nav a { color: white; text-decoration: none; margin-right: 20px; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .btn { padding: 8px 16px; background: #2d6a4f; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-red { background: #c0392b; }
        .alert-success { background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; color: #155724; }
        .alert-error { background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; color: #721c24; }
        .card { background: white; padding: 20px; border-radius: 6px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ route('topics.index') }}">Home</a>
        <a href="{{ route('topics.create') }}">New Topic</a>
    </nav>
    <div class="container">
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>



