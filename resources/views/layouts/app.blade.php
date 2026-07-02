<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Discussion Forum</title>
   <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #31f7; }
    nav { background: linear-gradient(135deg, #0077b6, #00b4d8, #00cfc8); padding: 15px 30px; color: white; }
    nav a { color: white; text-decoration: none; margin-right: 20px; font-weight: bold; }
    nav a:hover { color: #caf0f8; }
    .container { max-width: 100%; margin: 30px auto; padding: 0 10px; }
    .btn { padding: 8px 16px; background: linear-gradient(135deg, #0077b6, #00b4d8); color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
    .btn:hover { background: linear-gradient(135deg, #023e8a, #0077b6); }
    .btn-red { background: #c0392b; }
    .alert-success { background: #caf0f8; padding: 10px; border-radius: 4px; margin-bottom: 15px; color: #0077b6; border-left: 4px solid #00b4d8; }
    .alert-error { background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; color: #721c24; }
    .card { padding: 20px; background: #c8f5d0; border-radius: 6px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0, 119, 182, 0.1); border-left: 4px solid #00b4d8; }
    .card h3 a { color: #0077b6; }
    .card h3 a:hover { color: #023e8a; }
    input, textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #00b4d8; border-radius: 4px; outline: none; background: #f0fff4; }
    input:focus, textarea:focus { border-color: #0077b6; box-shadow: 0 0 4px rgba(0, 119, 182, 0.3); }
    label { font-weight: bold; display: block; margin-bottom: 5px; color: #0077b6; }
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



