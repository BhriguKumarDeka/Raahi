<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Raahi') }}</title>

        <!-- Fonts (Plus Jakarta Sans & Playfair Display for premium typography) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..700;1,400..700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans-display antialiased bg-bg-secondary text-text-main selection:bg-brand-neutral selection:text-bg-primary">
        <div class="min-h-screen flex flex-col">
            <!-- Navigation -->
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-bg-primary border-b border-border-light py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-grow">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-bg-primary border-t border-border-light py-8 text-center text-xs text-text-muted">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    &copy; {{ date('Y') }} Raahi. Crafted for collaborative group travels.
                </div>
            </footer>
        </div>
    </body>
</html>
