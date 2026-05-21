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
    <body class="font-sans-display text-text-main antialiased bg-bg-secondary">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-bg-secondary">
            <div>
                <a href="/" wire:navigate class="flex items-center space-x-2 text-brand-neutral font-extrabold text-2xl tracking-tight">
                    <x-application-logo class="w-8 h-8 text-brand-neutral" />
                    <span>Raahi</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-bg-primary border border-border-card sm:rounded-3xl shadow-[0_10px_40px_rgba(26,59,43,0.03)]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
