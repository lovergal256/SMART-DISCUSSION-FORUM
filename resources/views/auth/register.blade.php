
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Discussion Forum</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center py-10">

    <!-- Rules Modal -->
    <div id="rulesModal" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 hidden">
        <div class="bg-gray-900 border border-cyan-700 rounded-2xl p-6 w-full max-w-lg mx-4 shadow-2xl">
            <h2 class="text-xl font-bold text-cyan-400 mb-1">📋 Forum Rules & Guidelines</h2>
            <p class="text-gray-400 text-sm mb-4">Please read before accepting.</p>

            <div class="bg-gray-800 rounded-xl p-4 h-64 overflow-y-scroll text-sm text-gray-300 space-y-3 border border-gray-700">
                <p class="text-cyan-400 font-bold">Welcome to Smart Discussion Forum!</p>

                <p class="text-yellow-400 font-semibold">1. Respectful Communication</p>
                <p>Treat all members with respect. Harassment, bullying, hate speech or offensive language will not be tolerated and may result in immediate ban.</p>

                <p class="text-yellow-400 font-semibold">2. Academic Integrity</p>
                <p>Do not share exam answers or engage in any form of academic dishonesty. Plagiarism is strictly prohibited.</p>

                <p class="text-yellow-400 font-semibold">3. Relevant Content Only</p>
                <p>Post only academic and relevant content in appropriate groups. Off-topic posts, spam or advertisements will be removed immediately.</p>

                <p class="text-yellow-400 font-semibold">4. Privacy & Personal Information</p>
                <p>Do not share personal information of other students or staff without their consent.</p>

                <p class="text-yellow-400 font-semibold">5. No Harmful Content</p>
                <p>Posting violent, sexual, illegal or harmful content is strictly forbidden and will result in permanent ban.</p>

                <p class="text-yellow-400 font-semibold">6. Account Responsibility</p>
                <p>You are responsible for all activity on your account. Do not share your login credentials with anyone.</p>

                <p class="text-yellow-400 font-semibold">7. Consequences of Violations</p>
                <p>Violations may result in warnings, temporary suspension or permanent ban depending on severity.</p>

                <p class="text-green-400 font-semibold">By registering, you agree to follow all the rules above at all times.</p>
            </div>

            <button onclick="closeRules()"
                class="w-full mt-4 bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 rounded-lg transition">
                ✓ Close
            </button>
        </div>
    </div>

    <!-- Register Form -->
    <div class="bg-gray-900 p-8 rounded-2xl shadow-xl w-full max-w-md border border-cyan-800">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-cyan-400">Smart Discussion Forum</h1>
            <p class="text-gray-400 mt-2">Create your student account</p>
        </div>

        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500 text-red-400 p-3 rounded-lg mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <div class="mb-4">
                <label class="text-gray-300 block mb-2 text-sm">Full Name</label>
                <input type="text" name="FullName" value="{{ old('FullName') }}"
                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 border border-gray-700 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                    placeholder="Enter your full name" required>
            </div>

            <div class="mb-4">
                <label class="text-gray-300 block mb-2 text-sm">Email Address</label>
                <input type="email" name="Email" value="{{ old('Email') }}"
                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 border border-gray-700 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                    placeholder="Enter your email" required>
            </div>

            <div class="mb-4">
                <label class="text-gray-300 block mb-2 text-sm">Password</label>
                <input type="password" name="Password"
                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 border border-gray-700 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                    placeholder="Minimum 6 characters" required>
            </div>

            <div class="mb-4">
                <label class="text-gray-300 block mb-2 text-sm">Confirm Password</label>
                <input type="password" name="Password_confirmation"
                    class="w-full bg-gray-800 text-white rounded-lg px-4 py-3 border border-gray-700 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500"
                    placeholder="Repeat your password" required>
            </div>

            <!-- Terms Checkbox -->
            <div class="mb-6 bg-gray-800 border border-gray-700 rounded-xl p-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="terms" id="terms"
                        class="mt-1 w-4 h-4 accent-cyan-500"
                        {{ old('terms') ? 'checked' : '' }}>
                    <span class="text-gray-300 text-sm">
                        I have read and agree to the
                        <button type="button" onclick="openRules()"
                            class="text-cyan-400 hover:text-cyan-300 underline font-semibold">
                            Forum Rules & Guidelines
                        </button>
                    </span>
                </label>
                @error('terms')
                    <p class="text-red-400 text-xs mt-2 ml-7">⚠ You must accept the rules to register.</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded-lg transition duration-200">
                Create Account
            </button>
        </form>

        <div class="flex items-center my-6">
            <div class="flex-1 border-t border-gray-700"></div>
            <span class="px-4 text-gray-500 text-sm">already have an account?</span>
            <div class="flex-1 border-t border-gray-700"></div>
        </div>

        <a href="{{ route('login') }}"
            class="w-full block text-center border border-cyan-500 text-cyan-400 hover:bg-cyan-500 hover:text-white font-bold py-3 rounded-lg transition duration-200">
            Sign In Instead
        </a>
    </div>

    <script>
        function openRules() {
            document.getElementById('rulesModal').classList.remove('hidden');
        }
        function closeRules() {
            document.getElementById('rulesModal').classList.add('hidden');
            document.getElementById('terms').checked = true;
        }
    </script>

</body>
</html>