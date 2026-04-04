<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'JobRadar — Unified Job Search')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Vite handles Tailwind compilation --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>

<body class="min-h-screen antialiased relative">

    {{-- ── Top loading bar ── --}}
    <div id="loading-bar"></div>

    <script>
        (function () {
            var bar = document.getElementById('loading-bar');
            document.addEventListener('livewire:navigating', function () {
                bar.className = 'running';
            });
            document.addEventListener('livewire:navigate', function () {
                bar.className = 'done';
                setTimeout(function () { bar.className = ''; }, 450);
            });
            window.addEventListener('livewire:request', function () {
                bar.className = 'running';
            });
            window.addEventListener('livewire:response', function () {
                bar.className = 'done';
                setTimeout(function () { bar.className = ''; }, 450);
            });
        })();
    </script>

    {{-- ── Navigation ── --}}
    <nav class="sticky top-0 z-50 border-b border-border bg-bg/85 backdrop-blur-lg">
        <div class="max-w-6xl mx-auto px-6 flex items-center justify-between h-15">

            <a href="{{ route('home') }}"
                class="flex items-center gap-2 font-display font-black text-xl tracking-tight text-text no-underline">
                <span
                    class="w-2 h-2 rounded-full bg-brand shadow-[0_0_10px_var(--color-brand)] animate-pulse-glow"></span>
                JobRadar
            </a>

            <span class="text-xs font-medium tracking-widest uppercase text-muted
                         px-3 py-1 border border-border rounded-full">
                4 Sources · Live
            </span>

        </div>
    </nav>

    {{-- ── Flash messages ── --}}
    @if(session('success'))
        <div class="max-w-6xl mx-auto px-6 mt-4">
            <div class="px-4 py-3 rounded-lg border border-success text-success text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- ── Main content ── --}}
    <main class="relative z-10">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @stack('scripts')
    @livewireScripts
</body>

</html>