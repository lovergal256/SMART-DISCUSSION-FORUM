
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Discussion Forum</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center">
    <div class="bg-gray-900 p-8 rounded-2xl shadow-xl w-full max-w-md border border-cyan-800">

        <!-- Logo / Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-cyan-400">Smart Discussion Forum</h1>
            <p class="text-gray-400 mt-2">Sign in to your account</p>
        </div>

        <!-- Error Message -->
        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-lg mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-4">
                <label class="text-gray-300 block mb-2 text-sm">Email Address</label>
                <input type="email" name="Email" value="{{ old('Email') }}"
                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 border border-gray-700 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                    placeholder="Enter your email" required>
            </div>
            <div class="mb-6">
                <label class="text-gray-300 block mb-2 text-sm">Password</label>
                <input type="password" name="Password"
                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 border border-gray-700 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                    placeholder="Enter your password" required>
            </div>
            <button type="submit"
                class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 rounded-lg transition duration-200">
                Sign In
            </button>
        </form>

        <!-- Divider -->
        <div class="flex items-center my-6">
            <div class="flex-1 border-t border-gray-700"></div>
            <span class="px-4 text-gray-500 text-sm">Students only</span>
            <div class="flex-1 border-t border-gray-700"></div>
        </div>

        <!-- Register Link -->
        <a href="{{ route('register') }}"
            class="w-full block text-center border border-green-500 text-green-400 hover:bg-green-500 hover:text-white font-bold py-3 rounded-lg transition duration-200">
            Create Student Account
        </a>

        <p class="text-gray-600 text-center mt-6 text-xs">
            Admin/Lecturer? Contact your administrator for access.
        </p>
    </div>
</body>
</html>