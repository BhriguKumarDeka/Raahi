<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Raahi - Group Travel Planning & Itinerary Collaboration</title>

        <!-- Fonts (Plus Jakarta Sans & Playfair Display for premium typography) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..700;1,400..700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            ::-webkit-scrollbar-track {
                background: #f4f4f0;
            }
            ::-webkit-scrollbar-thumb {
                background: #e4e4df;
                border-radius: 3px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #5e605c;
            }
        </style>
    </head>
    <body class="font-sans-display antialiased bg-bg-primary text-text-main selection:bg-brand-neutral selection:text-bg-primary">
        <div class="min-h-screen flex flex-col justify-between overflow-x-hidden">
            
            <!-- Pill Floating Glassmorphism Navbar (inspired by Snapshots 2 & 4) -->
            <div class="fixed top-6 left-0 right-0 z-50 px-4 sm:px-6 lg:px-8">
                <header class="max-w-5xl mx-auto rounded-full bg-white/70 backdrop-blur-md border border-white/20 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300">
                    <div class="px-6 h-16 flex items-center justify-between">
                        <!-- Logo -->
                        <a href="/" class="font-sans-display font-extrabold text-xl tracking-tight text-brand-neutral flex items-center space-x-2 transition-transform hover:scale-[1.02]">
                            <svg class="h-6 w-6 stroke-2 text-brand-neutral" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Raahi</span>
                        </a>

                        <!-- Navigation Links -->
                        <nav class="hidden md:flex items-center space-x-8 text-xs font-semibold uppercase tracking-wider text-text-muted">
                            <a href="#features" class="hover:text-brand-neutral transition-colors duration-200">Features</a>
                            <a href="#values" class="hover:text-brand-neutral transition-colors duration-200">Our Value</a>
                            <a href="#discover" class="hover:text-brand-neutral transition-colors duration-200">Explore</a>
                        </nav>

                        <!-- Auth Actions -->
                        <div class="flex items-center space-x-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold rounded-full transition-all duration-200 shadow-sm hover:scale-[1.02]">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-xs font-bold uppercase tracking-wider text-text-muted hover:text-brand-neutral transition-colors duration-200 px-3 py-2">
                                    Sign In
                                </a>
                                <a href="{{ route('register') }}" class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold rounded-full transition-all duration-200 shadow-sm hover:scale-[1.02]">
                                    Get Started
                                </a>
                            @endauth
                        </div>
                    </div>
                </header>
            </div>

            <!-- Hero Section (inspired by Snapshots 1 & 2) -->
            <section class="relative min-h-[90vh] flex flex-col justify-center pt-24 pb-16 overflow-hidden bg-bg-secondary border-b border-border-light">
                <!-- Background Image with soft mountain overlay -->
                <div class="absolute inset-0 z-0">
                    <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1920&q=80" 
                         alt="Lush green forested mountain valley" 
                         class="w-full h-full object-cover filter brightness-[0.92] contrast-[1.02]" />
                    <div class="absolute inset-0 bg-gradient-to-t from-bg-secondary via-transparent to-black/10"></div>
                </div>

                <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white mt-12">
                    <!-- Subtle Tech Badge (Snapshot 2/4 style) -->
                    <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md border border-white/20 px-3 py-1.5 rounded-full mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-300">New Advanced AI Planning</span>
                    </div>

                    <!-- Immersive Typography: Sans-serif blended with Italic Serif Accent -->
                    <h1 class="hero-title text-4xl sm:text-6xl md:text-7xl font-extrabold tracking-tight leading-[1.1] max-w-4xl mx-auto text-shadow-md">
                        Find Your Next Escape, <br class="hidden sm:inline">
                        <span class="font-serif-display italic font-normal text-bg-primary">One Adventure</span> at a Time!
                    </h1>
                    <p class="hero-desc mt-6 text-sm sm:text-base text-white/80 max-w-xl mx-auto leading-relaxed text-shadow-sm font-medium">
                        Collaboratively build day-by-day itineraries, vote on accommodations, net budgets, and group chat. All in one beautifully unified workspace.
                    </p>

                    <!-- Floating Search Widget (inspired by Snapshot 1) -->
                    <div class="search-widget mt-12 max-w-4xl mx-auto bg-white rounded-2xl md:rounded-full p-2.5 shadow-[0_20px_50px_rgba(26,59,43,0.15)] border border-border-card flex flex-col md:flex-row items-center gap-1 md:gap-0 justify-between text-text-main">
                        <!-- Activity/Goal Field -->
                        <div class="flex-1 text-left px-5 py-2.5 w-full md:w-auto border-b md:border-b-0 md:border-r border-border-light hover:bg-bg-primary/50 rounded-xl transition duration-150 group">
                            <label class="block text-[9px] font-extrabold uppercase tracking-wider text-text-muted group-hover:text-brand-neutral transition-colors">Activity / Goal</label>
                            <input type="text" placeholder="Yoga / Trek / Surf" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-text-main placeholder-text-muted/60 focus:ring-0 focus:outline-none mt-1" />
                        </div>
                        <!-- Location Field -->
                        <div class="flex-1 text-left px-5 py-2.5 w-full md:w-auto border-b md:border-b-0 md:border-r border-border-light hover:bg-bg-primary/50 rounded-xl transition duration-150 group">
                            <label class="block text-[9px] font-extrabold uppercase tracking-wider text-text-muted group-hover:text-brand-neutral transition-colors">Location</label>
                            <input type="text" placeholder="Kyoto, Japan" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-text-main placeholder-text-muted/60 focus:ring-0 focus:outline-none mt-1" />
                        </div>
                        <!-- Dates Field -->
                        <div class="flex-1 text-left px-5 py-2.5 w-full md:w-auto border-b md:border-b-0 md:border-r border-border-light hover:bg-bg-primary/50 rounded-xl transition duration-150 group">
                            <label class="block text-[9px] font-extrabold uppercase tracking-wider text-text-muted group-hover:text-brand-neutral transition-colors">Date / Duration</label>
                            <input type="text" placeholder="Anytime / 3 Days" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-text-main placeholder-text-muted/60 focus:ring-0 focus:outline-none mt-1" />
                        </div>
                        <!-- Budget Field -->
                        <div class="flex-1 text-left px-5 py-2.5 w-full md:w-auto hover:bg-bg-primary/50 rounded-xl transition duration-150 group">
                            <label class="block text-[9px] font-extrabold uppercase tracking-wider text-text-muted group-hover:text-brand-neutral transition-colors">Budget</label>
                            <input type="text" placeholder="$1000 - $3000" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-text-main placeholder-text-muted/60 focus:ring-0 focus:outline-none mt-1" />
                        </div>
                        <!-- Explore Search CTA -->
                        <div class="w-full md:w-auto px-2 md:px-0 mt-3 md:mt-0">
                            @auth
                                <a href="{{ route('dashboard') }}" class="w-full md:px-8 h-12 rounded-xl md:rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition-all duration-200 shadow-sm font-bold text-xs uppercase tracking-wider gap-2">
                                    <span>Explore</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="w-full md:px-8 h-12 rounded-xl md:rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition-all duration-200 shadow-sm font-bold text-xs uppercase tracking-wider gap-2">
                                    <span>Explore</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </section>

            <!-- Partners / Brand Logo Bar (inspired by Snapshot 2) -->
            <section class="py-10 bg-bg-primary border-b border-border-light">
                <div class="max-w-5xl mx-auto px-4 text-center">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-text-muted/60 mb-6">Empowering group trips globally</p>
                    <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 opacity-40 grayscale contrast-125">
                        <span class="font-sans-display font-black text-lg tracking-tighter">logoipsum*</span>
                        <span class="font-serif-display italic font-bold text-xl tracking-wide">L I T U</span>
                        <span class="font-sans-display font-extrabold text-base tracking-widest">C O D O</span>
                        <span class="font-sans-display font-medium text-lg tracking-tight">logoipsum</span>
                        <span class="font-serif-display font-extrabold text-lg">LOCO</span>
                    </div>
                </div>
            </section>

            <!-- Custom Location Separator (inspired by Snapshot 1) -->
            <div class="flex items-center justify-center my-6">
                <div class="h-[1px] flex-1 bg-gradient-to-r from-transparent to-border-card"></div>
                <div class="flex items-center space-x-2 px-4 text-text-muted">
                    <div class="h-1.5 w-1.5 rounded-full bg-brand-neutral/40"></div>
                    <div class="h-1.5 w-1.5 rounded-full bg-brand-neutral/70"></div>
                    <svg class="h-6 w-6 stroke-1.5 text-brand-neutral animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
                    </svg>
                    <div class="h-1.5 w-1.5 rounded-full bg-brand-neutral/70"></div>
                    <div class="h-1.5 w-1.5 rounded-full bg-brand-neutral/40"></div>
                </div>
                <div class="h-[1px] flex-1 bg-gradient-to-l from-transparent to-border-card"></div>
            </div>

            <!-- "Not Your Boring Travel Agent" Carousel Grid (inspired by Snapshot 1) -->
            <section id="values" class="py-20 bg-bg-primary overflow-hidden">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col lg:flex-row items-start gap-12">
                        
                        <!-- Left text details -->
                        <div class="w-full lg:w-2/5 flex flex-col justify-between">
                            <div>
                                <span class="inline-block px-3 py-1 bg-bg-secondary text-brand-neutral border border-border-card text-[10px] font-extrabold uppercase tracking-widest rounded-full mb-6">
                                    • 01 Our Value
                                </span>
                                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight text-text-main">
                                    Not Your Boring <br>Travel Agent
                                </h2>
                                <p class="text-sm text-text-muted mt-4 leading-relaxed max-w-sm">
                                    We plan chill, curated trips with good vibes and better people. No rigid itineraries. Just flexible setups, local guides, and shared memories.
                                </p>
                            </div>
                            <div class="mt-8">
                                <a href="{{ route('register') }}" class="px-6 py-3 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold uppercase tracking-wider rounded-xl transition duration-150 inline-block shadow-sm">
                                    Book a Seat
                                </a>
                            </div>
                        </div>

                        <!-- Right carousel -->
                        <div class="w-full lg:w-3/5 relative">
                            <!-- Prev/Next Controls floating right -->
                            <div class="absolute -top-12 right-2 flex space-x-2 z-10">
                                <button id="carousel-prev" class="w-8 h-8 rounded-full border border-border-card bg-bg-primary flex items-center justify-center text-text-main hover:bg-brand-neutral hover:text-bg-primary hover:border-brand-neutral transition">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button id="carousel-next" class="w-8 h-8 rounded-full border border-border-card bg-bg-primary flex items-center justify-center text-text-main hover:bg-brand-neutral hover:text-bg-primary hover:border-brand-neutral transition">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Carousel Card Track -->
                            <div id="carousel-track" class="flex gap-6 overflow-x-auto snap-x scroll-smooth pb-4 pr-12 scrollbar-none" style="scrollbar-width: none;">
                                <!-- Card 1 -->
                                <div class="w-[280px] flex-shrink-0 snap-start group cursor-pointer">
                                    <div class="relative h-[340px] rounded-3xl overflow-hidden shadow-sm border border-border-card">
                                        <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80" 
                                             alt="Bangli, East Bali" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>
                                        <div class="absolute bottom-6 left-6 right-6 text-white">
                                            <p class="text-[9px] uppercase tracking-widest text-emerald-400 font-bold">Cultural Walk</p>
                                            <h4 class="font-extrabold text-base mt-1">Bangli, East Bali</h4>
                                            <p class="text-[10px] text-white/70 mt-1">Cultural walk with local guides</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 2 -->
                                <div class="w-[280px] flex-shrink-0 snap-start group cursor-pointer">
                                    <div class="relative h-[340px] rounded-3xl overflow-hidden shadow-sm border border-border-card">
                                        <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=600&q=80" 
                                             alt="Uluwatu, Bali" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>
                                        <div class="absolute bottom-6 left-6 right-6 text-white">
                                            <p class="text-[9px] uppercase tracking-widest text-amber-400 font-bold">Surf & Yoga</p>
                                            <h4 class="font-extrabold text-base mt-1">Uluwatu, Bali</h4>
                                            <p class="text-[10px] text-white/70 mt-1">Beach-front sunrise yoga</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 3 -->
                                <div class="w-[280px] flex-shrink-0 snap-start group cursor-pointer">
                                    <div class="relative h-[340px] rounded-3xl overflow-hidden shadow-sm border border-border-card">
                                        <img src="https://images.unsplash.com/photo-1527004013197-933c4bb611b3?auto=format&fit=crop&w=600&q=80" 
                                             alt="Patagonia, Chile" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>
                                        <div class="absolute bottom-6 left-6 right-6 text-white">
                                            <p class="text-[9px] uppercase tracking-widest text-sky-400 font-bold">Nature Trek</p>
                                            <h4 class="font-extrabold text-base mt-1">Patagonia, Chile</h4>
                                            <p class="text-[10px] text-white/70 mt-1">Mountain peaks & cozy yurt camps</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 4 -->
                                <div class="w-[280px] flex-shrink-0 snap-start group cursor-pointer">
                                    <div class="relative h-[340px] rounded-3xl overflow-hidden shadow-sm border border-border-card">
                                        <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=600&q=80" 
                                             alt="Paris, France" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>
                                        <div class="absolute bottom-6 left-6 right-6 text-white">
                                            <p class="text-[9px] uppercase tracking-widest text-pink-400 font-bold">City Break</p>
                                            <h4 class="font-extrabold text-base mt-1">Paris, France</h4>
                                            <p class="text-[10px] text-white/70 mt-1">Boutique cafes and art tours</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- "Pick the Place" Filtration Grid (inspired by Snapshot 1) -->
            <section id="discover" class="py-20 bg-bg-secondary border-t border-b border-border-light">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Heading -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-10">
                        <div>
                            <span class="text-[9px] text-text-muted uppercase font-extrabold tracking-widest">Popular Destinations</span>
                            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text-main mt-1">Pick the Place</h2>
                        </div>
                        <p class="text-xs text-text-muted max-w-xs leading-relaxed">
                            We have great options for everyone and cozy spots for your squad to enjoy together!
                        </p>
                    </div>

                    <!-- Snapshot 1 Grid Filters -->
                    <div class="bg-bg-primary rounded-2xl p-4 border border-border-card shadow-sm flex flex-wrap items-center justify-between gap-4 mb-8">
                        <div class="flex-1 min-w-[140px] text-left px-3 py-1">
                            <label class="block text-[8px] font-extrabold uppercase tracking-wider text-text-muted">Destination</label>
                            <select class="w-full bg-transparent border-0 p-0 text-xs font-bold text-text-main focus:ring-0 focus:outline-none mt-1">
                                <option>Kyoto, Japan</option>
                                <option>Uluwatu, Bali</option>
                                <option>Patagonia, Chile</option>
                                <option>Paris, France</option>
                            </select>
                        </div>
                        <div class="h-8 w-[1px] bg-border-light hidden md:block"></div>
                        <div class="flex-1 min-w-[140px] text-left px-3 py-1">
                            <label class="block text-[8px] font-extrabold uppercase tracking-wider text-text-muted">Category</label>
                            <select class="w-full bg-transparent border-0 p-0 text-xs font-bold text-text-main focus:ring-0 focus:outline-none mt-1">
                                <option>Adventure & Outdoor</option>
                                <option>Relaxed / Boutique Stays</option>
                                <option>Cultural Tour</option>
                                </select>
                        </div>
                        <div class="h-8 w-[1px] bg-border-light hidden md:block"></div>
                        <div class="flex-1 min-w-[140px] text-left px-3 py-1">
                            <label class="block text-[8px] font-extrabold uppercase tracking-wider text-text-muted">Price Range</label>
                            <select class="w-full bg-transparent border-0 p-0 text-xs font-bold text-text-main focus:ring-0 focus:outline-none mt-1">
                                <option>$500 - $1500 / person</option>
                                <option>$1500 - $3000 / person</option>
                                <option>$3000+ / person</option>
                            </select>
                        </div>
                        <div class="h-8 w-[1px] bg-border-light hidden md:block"></div>
                        <div class="flex-1 min-w-[140px] text-left px-3 py-1">
                            <label class="block text-[8px] font-extrabold uppercase tracking-wider text-text-muted">Travel Date</label>
                            <select class="w-full bg-transparent border-0 p-0 text-xs font-bold text-text-main focus:ring-0 focus:outline-none mt-1">
                                <option>August 12 - August 18</option>
                                <option>September 5 - September 12</option>
                                <option>December 20 - December 27</option>
                            </select>
                        </div>
                        
                        <button class="px-6 py-3 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-sm">
                            Discover
                        </button>
                    </div>

                    <!-- Destination Cards Grid (Snapshot 1 style) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Card 1 -->
                        <div class="bg-bg-primary rounded-3xl border border-border-card shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between overflow-hidden group">
                            <div class="p-4 flex-1">
                                <!-- Card Header with slot badge -->
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-extrabold text-sm text-text-main">Uluwatu Beach House</h4>
                                        <p class="text-[10px] text-text-muted">Badung Regency, Bali</p>
                                    </div>
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[9px] font-extrabold uppercase tracking-wider rounded-full">
                                        4 Slots left
                                    </span>
                                </div>

                                <!-- Image with hover scaling -->
                                <div class="relative h-[200px] rounded-2xl overflow-hidden bg-bg-secondary border border-border-light">
                                    <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=600&q=80" 
                                         alt="Uluwatu" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                    <!-- Overlay stats -->
                                    <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[9px] font-bold bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                                        <span class="flex items-center gap-1">⏱️ 6 Days</span>
                                        <span>🌐 Open Trip</span>
                                        <span>📅 12-18 Aug</span>
                                    </div>
                                </div>

                                <!-- Package Details (inspired by Snapshot 1 cards) -->
                                <div class="mt-4 pt-4 border-t border-border-light space-y-2 text-[10px] text-text-muted">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Accommodation</span>
                                        <span>2N at Seaside Villa</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Transport</span>
                                        <span>Private Van & Driver</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Meals</span>
                                        <span>Daily Breakfast & Dinner</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Price & Action Footer -->
                            <div class="px-6 py-4 bg-bg-secondary border-t border-border-light flex justify-between items-center">
                                <div>
                                    <p class="text-[8px] font-extrabold uppercase tracking-wider text-text-muted">Starting at</p>
                                    <p class="font-extrabold text-base text-text-main">$1,150 <span class="text-[9px] font-normal text-text-muted">/ person</span></p>
                                </div>
                                @auth
                                    <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" class="w-8 h-8 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @endauth
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="bg-bg-primary rounded-3xl border border-border-card shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between overflow-hidden group">
                            <div class="p-4 flex-1">
                                <!-- Card Header with slot badge -->
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-extrabold text-sm text-text-main">Kyoto Temple Lodge</h4>
                                        <p class="text-[10px] text-text-muted">Kyoto, Japan</p>
                                    </div>
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[9px] font-extrabold uppercase tracking-wider rounded-full">
                                        5 Slots left
                                    </span>
                                </div>

                                <!-- Image with hover scaling -->
                                <div class="relative h-[200px] rounded-2xl overflow-hidden bg-bg-secondary border border-border-light">
                                    <img src="https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=600&q=80" 
                                         alt="Kyoto" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                    <!-- Overlay stats -->
                                    <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[9px] font-bold bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                                        <span class="flex items-center gap-1">⏱️ 7 Days</span>
                                        <span>🌐 Group Trip</span>
                                        <span>📅 05-12 Sep</span>
                                    </div>
                                </div>

                                <!-- Package Details -->
                                <div class="mt-4 pt-4 border-t border-border-light space-y-2 text-[10px] text-text-muted">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Accommodation</span>
                                        <span>3N Ryokan, 3N Boutique Hotel</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Transport</span>
                                        <span>Shinkansen Transit Passes</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Meals</span>
                                        <span>Breakfast & Traditional Dinners</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Price & Action Footer -->
                            <div class="px-6 py-4 bg-bg-secondary border-t border-border-light flex justify-between items-center">
                                <div>
                                    <p class="text-[8px] font-extrabold uppercase tracking-wider text-text-muted">Starting at</p>
                                    <p class="font-extrabold text-base text-text-main">$2,400 <span class="text-[9px] font-normal text-text-muted">/ person</span></p>
                                </div>
                                @auth
                                    <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" class="w-8 h-8 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @endauth
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="bg-bg-primary rounded-3xl border border-border-card shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between overflow-hidden group">
                            <div class="p-4 flex-1">
                                <!-- Card Header with slot badge -->
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="font-extrabold text-sm text-text-main">Patagonia Alpine Yurt</h4>
                                        <p class="text-[10px] text-text-muted">West Patagonia, Chile</p>
                                    </div>
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[9px] font-extrabold uppercase tracking-wider rounded-full">
                                        2 Slots left
                                    </span>
                                </div>

                                <!-- Image with hover scaling -->
                                <div class="relative h-[200px] rounded-2xl overflow-hidden bg-bg-secondary border border-border-light">
                                    <img src="https://images.unsplash.com/photo-1527004013197-933c4bb611b3?auto=format&fit=crop&w=600&q=80" 
                                         alt="Patagonia" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                    <!-- Overlay stats -->
                                    <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[9px] font-bold bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                                        <span class="flex items-center gap-1">⏱️ 8 Days</span>
                                        <span>🌐 Expedition</span>
                                        <span>📅 20-28 Dec</span>
                                    </div>
                                </div>

                                <!-- Package Details -->
                                <div class="mt-4 pt-4 border-t border-border-light space-y-2 text-[10px] text-text-muted">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Accommodation</span>
                                        <span>Glamping Yurts & Mountain Chalets</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Transport</span>
                                        <span>4x4 Offroad Transfers</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold text-text-main">Meals</span>
                                        <span>All camp meals & cook service</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Price & Action Footer -->
                            <div class="px-6 py-4 bg-bg-secondary border-t border-border-light flex justify-between items-center">
                                <div>
                                    <p class="text-[8px] font-extrabold uppercase tracking-wider text-text-muted">Starting at</p>
                                    <p class="font-extrabold text-base text-text-main">$3,100 <span class="text-[9px] font-normal text-text-muted">/ person</span></p>
                                </div>
                                @auth
                                    <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" class="w-8 h-8 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Numeric Platform Statistics Row (inspired by Snapshot 4) -->
            <section class="py-16 bg-bg-primary border-b border-border-light">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                        <div class="stat-item">
                            <p class="text-4xl font-extrabold text-brand-neutral">50+</p>
                            <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Verified Cozy Destinations</p>
                        </div>
                        <div class="stat-item">
                            <p class="text-4xl font-extrabold text-brand-neutral">200+</p>
                            <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Group Trips Planned</p>
                        </div>
                        <div class="stat-item">
                            <p class="text-4xl font-extrabold text-brand-neutral">120,000+</p>
                            <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Active Co-planners</p>
                        </div>
                        <div class="stat-item">
                            <p class="text-4xl font-extrabold text-brand-neutral">$15 Million</p>
                            <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Expenses Nettted & Split</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- "Designed to Work at Scale" Step Section (inspired by Snapshot 3) -->
            <section id="features" class="py-20 bg-bg-primary">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <!-- Box Wrapper -->
                    <div class="bg-brand-neutral text-bg-primary rounded-3xl p-8 md:p-12 relative overflow-hidden shadow-xl">
                        <!-- Background glow effect -->
                        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-emerald-900/20 blur-3xl"></div>
                        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-teal-900/20 blur-3xl"></div>

                        <div class="relative z-10">
                            <!-- Section Header -->
                            <div class="flex flex-col md:flex-row justify-between items-start gap-4 pb-8 border-b border-white/10 mb-10">
                                <div>
                                    <span class="inline-block px-3 py-1 bg-white/10 text-emerald-300 border border-white/10 text-[9px] font-extrabold uppercase tracking-widest rounded-full mb-3">
                                        • How it Works
                                    </span>
                                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Designed to Work at Scale</h3>
                                </div>
                                <p class="text-xs text-white/70 max-w-xs leading-relaxed">
                                    A structured approach to managing complex itineraries—from group connection to long-term trip memories.
                                </p>
                            </div>

                            <!-- Carousel Body -->
                            <div class="flex flex-col md:flex-row gap-8 items-center justify-between">
                                <!-- Left side details -->
                                <div class="w-full md:w-1/3 flex flex-col justify-between self-stretch">
                                    <div>
                                        <p id="step-number" class="text-4xl font-light text-white/40 tracking-tight font-serif-display">01<span class="text-lg text-white/20 font-sans-display">/04</span></p>
                                        <h4 id="step-title" class="text-lg font-bold text-white mt-4">Invite & Coordinate</h4>
                                        <p id="step-desc" class="text-xs text-white/70 mt-2 leading-relaxed">
                                            Bring co-planners and group members into a shared workspace with tailored permissions (Organizer, Member, Viewer).
                                        </p>
                                    </div>
                                    <!-- Controls -->
                                    <div class="flex space-x-2 mt-8 md:mt-0">
                                        <button id="step-prev" class="w-8 h-8 rounded-full border border-white/15 flex items-center justify-center text-white hover:bg-white hover:text-brand-neutral transition">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button id="step-next" class="w-8 h-8 rounded-full border border-white/15 flex items-center justify-center text-white hover:bg-white hover:text-brand-neutral transition">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Right side cards (Snapshot 3 style) -->
                                <div class="w-full md:w-2/3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Card 1 -->
                                    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 relative overflow-hidden group">
                                        <span class="inline-block px-2.5 py-0.5 bg-white/10 text-white text-[9px] font-bold rounded mb-4">Connect</span>
                                        <h5 class="font-bold text-sm text-white mb-2">Bring Itineraries Into One System</h5>
                                        <p class="text-[11px] text-white/60 leading-relaxed">Connect flights, lodging bookings, local excursions, and maps in a neat scrolling timeline.</p>
                                    </div>
                                    
                                    <!-- Card 2 -->
                                    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 relative overflow-hidden group">
                                        <span class="inline-block px-2.5 py-0.5 bg-white/10 text-white text-[9px] font-bold rounded mb-4">Structure</span>
                                        <h5 class="font-bold text-sm text-white mb-2">Organize Budgets with Clarity</h5>
                                        <p class="text-[11px] text-white/60 leading-relaxed">Divide villa rents and dinner tabs fairly. Let the system run settlements instantly with zero awkwardness.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="bg-bg-secondary border-t border-border-light py-12 text-center text-xs text-text-muted">
                <div class="max-w-5xl mx-auto px-4 space-y-4">
                    <p class="font-extrabold text-brand-neutral font-sans-display text-sm flex items-center justify-center space-x-2">
                        <svg class="h-5 w-5 stroke-2 stroke-current" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Raahi</span>
                    </p>
                    <p>&copy; {{ date('Y') }} Raahi. Designed with absolute precision. All rights reserved.</p>
                </div>
            </footer>
        </div>

        <!-- Custom JS code for Carousel and Motion.dev animations -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const initAnimations = () => {
                    if (!window.Motion) {
                        setTimeout(initAnimations, 50);
                        return;
                    }
                    const { animate, scroll, inView } = window.Motion;

                    // 1. Motion.dev Reveals & Glide Transitions (UX Interaction Design)
                // Hero elements reveal
                animate('.hero-title', { opacity: [0, 1], y: [30, 0] }, { duration: 0.8, easing: 'ease-out' });
                animate('.hero-desc', { opacity: [0, 1], y: [20, 0] }, { duration: 0.8, delay: 0.2, easing: 'ease-out' });
                animate('.search-widget', { opacity: [0, 1], y: [25, 0] }, { duration: 0.8, delay: 0.35, easing: 'ease-out' });

                // In-View animations for cards & grids
                inView('#values', () => {
                    animate('#values', { opacity: [0, 1], y: [30, 0] }, { duration: 0.6, easing: 'ease-out' });
                });

                inView('#discover', () => {
                    animate('.grid > div', { opacity: [0, 1], y: [40, 0] }, { 
                        delay: (info) => info * 0.1, 
                        duration: 0.5, 
                        easing: 'ease-out' 
                    });
                });

                inView('.stat-item', () => {
                    animate('.stat-item', { opacity: [0, 1], scale: [0.95, 1] }, { 
                        delay: (info) => info * 0.08, 
                        duration: 0.5 
                    });
                });

                // Navbar scrolling shrink effect (Jakob's Law feedback)
                const header = document.querySelector('header');
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 50) {
                        header.classList.remove('top-6');
                        header.classList.add('top-2', 'py-1');
                    } else {
                        header.classList.remove('top-2', 'py-1');
                        header.classList.add('top-6');
                    }
                });

                // 2. Interactive Value Carousel Controls (Snapshot 1)
                const track = document.getElementById('carousel-track');
                const nextBtn = document.getElementById('carousel-next');
                const prevBtn = document.getElementById('carousel-prev');

                if (track && nextBtn && prevBtn) {
                    nextBtn.addEventListener('click', () => {
                        track.scrollBy({ left: 300, behavior: 'smooth' });
                    });
                    prevBtn.addEventListener('click', () => {
                        track.scrollBy({ left: -300, behavior: 'smooth' });
                    });
                }

                // 3. Interactive Features/Steps Slider (Snapshot 3)
                const steps = [
                    {
                        num: "01",
                        title: "Invite & Coordinate",
                        desc: "Bring co-planners and group members into a shared workspace with tailored permissions (Organizer, Member, Viewer)."
                    },
                    {
                        num: "02",
                        title: "Build Shared Itineraries",
                        desc: "Map hotels, flights, and meals chronologically. Allow anyone to drop notes or suggestions onto individual items."
                    },
                    {
                        num: "03",
                        title: "Interactive Choice Polls",
                        desc: "Create dynamic polls for hotel listings or weekend options. Lock choices when the team reaches a majority vote."
                    },
                    {
                        num: "04",
                        title: "Transparent Expense Netting",
                        desc: "Add expenses in any currency, divide fractions, and see automated net balances showing who pays who."
                    }
                ];

                let currentStepIdx = 0;
                const stepNumEl = document.getElementById('step-number');
                const stepTitleEl = document.getElementById('step-title');
                const stepDescEl = document.getElementById('step-desc');
                const stepPrevBtn = document.getElementById('step-prev');
                const stepNextBtn = document.getElementById('step-next');

                function updateStepUI() {
                    const step = steps[currentStepIdx];
                    
                    // Animate out text, update content, animate back in (Feedback loop)
                    animate([stepNumEl, stepTitleEl, stepDescEl], { opacity: 0, x: -10 }, { duration: 0.15 }).finished.then(() => {
                        stepNumEl.innerHTML = `${step.num}<span class="text-lg text-white/20 font-sans-display">/04</span>`;
                        stepTitleEl.innerText = step.title;
                        stepDescEl.innerText = step.desc;
                        
                        animate([stepNumEl, stepTitleEl, stepDescEl], { opacity: 1, x: 0 }, { duration: 0.25, easing: 'ease-out' });
                    });
                }

                if (stepPrevBtn && stepNextBtn) {
                    stepNextBtn.addEventListener('click', () => {
                        currentStepIdx = (currentStepIdx + 1) % steps.length;
                        updateStepUI();
                    });

                    stepPrevBtn.addEventListener('click', () => {
                        currentStepIdx = (currentStepIdx - 1 + steps.length) % steps.length;
                        updateStepUI();
                    });
                }
                };
                initAnimations();
            });
        </script>
    </body>
</html>
