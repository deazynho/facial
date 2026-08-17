<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Chat') - AI Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('chats.index') }}" class="text-xl font-bold text-blue-600">AI Chat</a>
            <div class="flex gap-4 items-center">
                @auth
                    <span class="text-gray-600">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700">Login</a>
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700">Register</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="py-8">
        @if ($errors->any())
            <div class="container mx-auto mb-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="container mx-auto mb-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-white border-t mt-12 py-6">
        <div class="container mx-auto text-center text-gray-600">
            <p>&copy; 2026 AI Chat. Powered by Ollama.</p>
        </div>
    </footer>
</body>
</html>

