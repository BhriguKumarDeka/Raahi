<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Raahi') }} - Join the Adventure</title>

        <!-- Fonts (Google Sans) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-text-main antialiased bg-bg-secondary selection:bg-brand-neutral selection:text-bg-primary">
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-12 bg-bg-secondary relative overflow-hidden">
            <!-- Ambient top glow -->
            <div class="absolute -top-48 -left-48 w-[400px] h-[400px] rounded-full bg-brand-neutral/5 blur-[120px] pointer-events-none"></div>
            <div class="absolute -bottom-48 -right-48 w-[400px] h-[400px] rounded-full bg-brand-neutral/5 blur-[120px] pointer-events-none"></div>
            
            <div x-data x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], y: [15, 0] }, { duration: 0.6, easing: 'ease-out' }) }" class="w-full sm:max-w-md flex flex-col items-center z-10 px-4">
                <div class="mb-8">
                    <a href="/" wire:navigate class="flex items-center space-x-2.5 text-brand-neutral font-extrabold text-3xl tracking-tight hover:scale-[1.02] transition-transform duration-200">
                        <x-application-logo class="w-8 h-8 text-brand-neutral" />
                        <span>Raahi</span>
                    </a>
                </div>

                <div class="w-full bg-bg-primary border border-border-card rounded-3xl shadow-[0_15px_50px_rgba(26,59,43,0.04)] p-8 sm:p-10 relative overflow-hidden">
                    {{ $slot }}
                </div>
                
                <div class="mt-8 text-center text-xs text-text-muted">
                    <a href="/" wire:navigate class="hover:text-brand-neutral transition-colors flex items-center justify-center gap-1">
                        <i class="ph ph-arrow-left"></i>
                        <span>Return to home</span>
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
