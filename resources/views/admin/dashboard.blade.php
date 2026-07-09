<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Discussion Forum</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-gray-900 border-b border-cyan-800 px-6 py-4 flex justify-between items-center">
        <h1 class="text-cyan-400 font-bold text-xl">Smart Discussion Forum</h1>
        <div class="flex items-center gap-4">
            <span class="text-gray-300">👋 {{ Auth::user()->FullName }}</span>
            <span class="bg-cyan-600 text-white text-xs px-2 py-1 rounded-full">Admin</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="bg-red-600 hover:bg-red-500 text-white text-sm px-4 py-2 rounded-lg transition">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-6xl mx-auto p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Admin Dashboard</h2>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-900 border border-cyan-800 rounded-2xl p-6">
                <p class="text-gray-400 text-sm">Total Users</p>
                <p class="text-3xl font-bold text-cyan-400 mt-2">0</p>
            </div>
            <div class="bg-gray-900 border border-green-800 rounded-2xl p-6">
                <p class="text-gray-400 text-sm">Total Topics</p>
                <p class="text-3xl font-bold text-green-400 mt-2">0</p>
            </div>
            <div class="bg-gray-900 border border-cyan-800 rounded-2xl p-6">
                <p class="text-gray-400 text-sm">Total Groups</p>
                <p class="text-3xl font-bold text-cyan-400 mt-2">0</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6">
            <h3 class="text-white font-bold text-lg mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button class="bg-cyan-600 hover:bg-cyan-500 text-white py-3 px-4 rounded-xl transition text-sm">
                    Add Lecturer
                </button>
                <button class="bg-green-600 hover:bg-green-500 text-white py-3 px-4 rounded-xl transition text-sm">
                    Manage Users
                </button>
                <button class="bg-gray-700 hover:bg-gray-600 text-white py-3 px-4 rounded-xl transition text-sm">
                    View Groups
                </button>
                <button class="bg-gray-700 hover:bg-gray-600 text-white py-3 px-4 rounded-xl transition text-sm">
                    View Reports
                </button>
            </div>
        </div>
    </div>

</body>
</html>