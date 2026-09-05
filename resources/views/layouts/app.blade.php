<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasklane — @yield('title', 'My Board')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper font-body text-ink">
    <header class="border-b border-line px-6 py-5 sm:px-10">
        <p class="font-display text-xl font-semibold text-ink">Tasklane</p>
    </header>

    <main class="px-6 py-8 sm:px-10">
        @if (session('status'))
            <div class="mb-6 rounded-md border border-teal/30 bg-teal/5 px-4 py-2 text-sm text-teal">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
