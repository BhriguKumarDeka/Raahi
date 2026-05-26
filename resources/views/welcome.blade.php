<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Raahi - Group Travel Planning & Itinerary Collaboration</title>

    <!-- Fonts (Google Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

<body class="font-sans antialiased bg-bg-primary text-text-main selection:bg-brand-neutral selection:text-bg-primary">
    <div class="min-h-screen flex flex-col justify-between overflow-x-hidden">

        <div id="landing-nav-shell" class="fixed top-6 left-0 right-0 z-50 px-4 sm:px-6 lg:px-8 transition-all duration-300">
            <header id="landing-nav" class="max-w-7xl mx-auto rounded-full border border-transparent bg-transparent transition-all duration-300">
                <div class="px-6 h-16 flex items-center justify-between">
                    <a href="/" data-nav-brand class="font-sans-display font-extrabold text-2xl tracking-tight text-white flex items-center space-x-2 text-shadow-lg transition-all duration-300 hover:scale-[1.05]">
                        <span>Raahi.com</span>
                    </a>

                    <!-- Nav links -->
                    <ul class="flex items-center space-x-6">
                        <li>
                            <a href="#discover" data-nav-link class="text-xs font-bold uppercase tracking-wider text-white/85 hover:text-white transition-colors duration-200 px-3 py-2">
                                Destinations
                            </a>
                        </li>
                        <li>
                            <a href="#values" data-nav-link class="text-xs font-bold uppercase tracking-wider text-white/85 hover:text-white transition-colors duration-200 px-3 py-2">
                                About
                            </a>
                        </li>
                        <li>
                            <a href="#features" data-nav-link class="text-xs font-bold uppercase tracking-wider text-white/85 hover:text-white transition-colors duration-200 px-3 py-2">
                                Features
                            </a>
                        </li>
                    </ul>

                    <!-- Auth Actions -->
                    <div class="flex items-center space-x-3">
                        @auth
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold rounded-full transition-all duration-200 shadow-sm hover:scale-[1.02]">
                            Dashboard
                        </a>
                        @else
                        <a href="{{ route('login') }}" data-nav-link class="text-xs font-bold uppercase tracking-wider text-white/85 hover:text-white transition-colors duration-200 px-3 py-2">
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

        <!-- Hero Section-->
        <section class="relative min-h-[90vh] flex flex-col justify-center pt-24 pb-16 overflow-hidden bg-bg-secondary border-b border-border-light">
            <div class="absolute inset-0 z-0">
                <img src="https://images.pexels.com/photos/34244310/pexels-photo-34244310.jpeg/"
                    alt="Lush green forested mountain valley"
                    class="w-full h-full object-fit filter contrast-1.1 brightness-90" />
                <div class="absolute inset-0 bg-linear-to-t from-bg-secondary/50 via-transparent to-white/50"></div>
            </div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white mt-4">
                <h1 class="hero-title text-4xl sm:text-6xl md:text-7xl font-extralight tracking-tight leading-[1.1] max-w-4xl mx-auto text-shadow-md">
                    Build Your Perfect<br><span>Travel Itinerary</span>
                </h1>
                <p class="hero-desc mt-6 text-sm sm:text-base text-white/80 max-w-xl mx-auto leading-relaxed text-shadow-md font-normal">
                    Collaboratively build day-by-day itineraries, budget tracking, polls and group chat. All in one beautifully unified workspace.
                </p>

            <div class="search-widget mt-12 max-w-2xl mx-auto relative group">
                <!-- Focus glow background -->
                <div
                    class="absolute -inset-0.5 bg-linear-to-r from-emerald-500 to-teal-500 rounded-full blur opacity-0 group-focus-within:opacity-40 transition duration-500">
                </div>

                <div
                    class="relative flex items-center justify-between bg-white rounded-full p-1.5 shadow-[0_20px_50px_rgba(26,59,43,0.12)] border border-border-card text-text-main">
                    <div class="pl-5 pt-1 text-text-muted">
                        <i class="ph ph-magnifying-glass text-lg"></i>
                    </div>
                    <input type="text" id="typing-search" placeholder="Search destinations..."
                        class="grow bg-transparent border-0 py-2.5 pl-3 pr-4 text-sm font-semibold text-text-main placeholder-text-muted/50 focus:ring-0 focus:outline-none" />
                    <div>
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="px-6 h-10 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition-all duration-200 shadow-sm font-bold text-xs uppercase tracking-wider gap-2">
                                <i class="ph ph-airplane-tilt text-lg"></i>
                                <span>Add Trip</span>
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                                class="px-6 h-10 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition-all duration-200 shadow-sm font-bold text-xs uppercase tracking-wider gap-2">
                                <i class="ph ph-plus text-xs"></i>
                                <span>Add Trip</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Brand Logo Bar  -->
    <section class="py-12 bg-bg-primary">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 opacity-40 grayscale contrast-125">
                <x-simpleicon-airbnb
                    class="h-7 w-auto text-text-muted fill-current hover:text-brand-neutral transition-colors duration-300" />
                <x-simpleicon-expedia
                    class="h-7 w-auto text-text-muted fill-current hover:text-brand-neutral transition-colors duration-300" />
                <x-simpleicon-uber
                    class="h-7 w-auto text-text-muted fill-current hover:text-brand-neutral transition-colors duration-300" />
                <x-simpleicon-bookingdotcom
                    class="h-7 w-auto text-text-muted fill-current hover:text-brand-neutral transition-colors duration-300" />
                <x-simpleicon-tripadvisor
                    class="h-7 w-auto text-text-muted fill-current hover:text-brand-neutral transition-colors duration-300" />
            </div>
        </div>
    </section>

    <!-- Custom Location Separator (inspired by Snapshot 1) -->
    <div class="flex items-center justify-center my-6">
        <div class="h-px flex-1 bg-linear-to-r from-transparent to-border-card"></div>
        <div class="flex items-center space-x-2 px-4 text-text-muted">
            <div class="h-1.5 w-1.5 rounded-full bg-brand-neutral/40"></div>
            <div class="h-1.5 w-1.5 rounded-full bg-brand-neutral/70"></div>
            <svg class="h-6 w-6 stroke-1.5 text-brand-neutral animate-pulse" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
            </svg>
            <div class="h-1.5 w-1.5 rounded-full bg-brand-neutral/70"></div>
            <div class="h-1.5 w-1.5 rounded-full bg-brand-neutral/40"></div>
        </div>
        <div class="h-px flex-1 bg-linear-to-l from-transparent to-border-card"></div>
    </div>

    <!-- "Not Your Boring Travel Agent" Carousel Grid-->
    <section id="values" class="py-20 bg-bg-primary overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-start gap-12">
                <!-- Left text details -->
                <div class="w-full lg:w-2/5 flex flex-col justify-between">
                    <div>
                        <h2
                            class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight text-text-main text-left">
                            Not Your Boring <br>Travel Agent
                        </h2>
                        <p class="text-sm text-text-muted mt-4 leading-relaxed max-w-sm text-left">
                            We plan chill, curated trips with good vibes and better people. No rigid itineraries. Just
                            flexible setups, local guides, and shared memories.
                        </p>
                    </div>
                    <div class="mt-8 text-left">
                        <a href="{{ route('register') }}"
                            class="px-6 py-3 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold uppercase tracking-wider rounded-xl transition duration-150 inline-block shadow-sm">
                            Book a Seat
                        </a>
                    </div>
                </div>

                <!-- Right carousel -->
                <div class="w-full lg:w-3/5 relative">
                    <!-- Prev/Next Controls floating right -->
                    <div class="absolute -top-12 right-2 flex space-x-2 z-10">
                        <button id="carousel-prev"
                            class="w-8 h-8 rounded-full border border-border-card bg-bg-primary flex items-center justify-center text-text-main hover:bg-brand-neutral hover:text-bg-primary hover:border-brand-neutral transition">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button id="carousel-next"
                            class="w-8 h-8 rounded-full border border-border-card bg-bg-primary flex items-center justify-center text-text-main hover:bg-brand-neutral hover:text-bg-primary hover:border-brand-neutral transition">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                    <!-- Carousel Card Track -->
                    <div id="carousel-track"
                        class="flex gap-6 overflow-x-auto snap-x scroll-smooth pb-4 pr-6 scrollbar-none"
                        style="scrollbar-width: none;">
                        <!-- Card 1 -->
                        <div class="w-70 shrink-0 snap-start group cursor-pointer">
                            <div class="relative h-85 rounded-3xl overflow-hidden shadow-sm border border-border-card">
                                <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80"
                                    alt="Bangli, East Bali"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/25 to-transparent">
                                </div>
                                <div class="absolute bottom-6 left-6 right-6 text-white">
                                    <p class="text-[9px] uppercase tracking-widest text-emerald-400 font-bold">Cultural
                                        Walk</p>
                                    <h4 class="font-extrabold text-base mt-1">Bangli, East Bali</h4>
                                    <p class="text-[10px] text-white/70 mt-1">Cultural walk with local guides</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="w-70 shrink-0 snap-start group cursor-pointer">
                            <div class="relative h-85 rounded-3xl overflow-hidden shadow-sm border border-border-card">
                                <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=600&q=80"
                                    alt="Uluwatu, Bali"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/25 to-transparent">
                                </div>
                                <div class="absolute bottom-6 left-6 right-6 text-white">
                                    <p class="text-[9px] uppercase tracking-widest text-amber-400 font-bold">Surf & Yoga
                                    </p>
                                    <h4 class="font-extrabold text-base mt-1">Uluwatu, Bali</h4>
                                    <p class="text-[10px] text-white/70 mt-1">Beach-front sunrise yoga</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="w-70 shrink-0 snap-start group cursor-pointer">
                            <div class="relative h-85 rounded-3xl overflow-hidden shadow-sm border border-border-card">
                                <img src="https://images.unsplash.com/photo-1527004013197-933c4bb611b3?auto=format&fit=crop&w=600&q=80"
                                    alt="Patagonia, Chile"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/25 to-transparent">
                                </div>
                                <div class="absolute bottom-6 left-6 right-6 text-white">
                                    <p class="text-[9px] uppercase tracking-widest text-sky-400 font-bold">Nature Trek
                                    </p>
                                    <h4 class="font-extrabold text-base mt-1">Patagonia, Chile</h4>
                                    <p class="text-[10px] text-white/70 mt-1">Mountain peaks & cozy yurt camps</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="w-70 shrink-0 snap-start group cursor-pointer">
                            <div class="relative h-85 rounded-3xl overflow-hidden shadow-sm border border-border-card">
                                <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=600&q=80"
                                    alt="Paris, France"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/25 to-transparent">
                                </div>
                                <div class="absolute bottom-6 left-6 right-6 text-white">
                                    <p class="text-[9px] uppercase tracking-widest text-pink-400 font-bold">City Break
                                    </p>
                                    <h4 class="font-extrabold text-base mt-1">Paris, France</h4>
                                    <p class="text-[10px] text-white/70 mt-1">Boutique cafes and art tours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <!-- "Pick the Place" Filtration Grid (inspired by Snapshot 1) -->
    <section id="discover" class="py-24 bg-bg-secondary border-t border-b border-border-light"
        x-data="{ currentTab: 'india' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Heading -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-10 text-center md:text-left">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text-main">Pick the Place</h2>
                </div>

                <!-- Modern Tab Switcher Pill -->
                <div class="bg-bg-primary border border-border-card p-1 rounded-full flex gap-1 shadow-sm">
                    <button @click="currentTab = 'india'"
                        :class="currentTab === 'india' ? 'bg-brand-neutral text-bg-primary shadow-xs' : 'text-text-muted hover:text-text-main'"
                        class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer">
                        India
                    </button>
                    <button @click="currentTab = 'abroad'"
                        :class="currentTab === 'abroad' ? 'bg-brand-neutral text-bg-primary shadow-xs' : 'text-text-muted hover:text-text-main'"
                        class="px-6 py-2 rounded-full font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer">
                        Abroad
                    </button>
                </div>
            </div>

            <!-- India Section -->
            <div x-show="currentTab === 'india'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-destination-card title="Leh Ladakh Adventure" location="Ladakh, Himalayas"
                    image="https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=600&q=80"
                    duration="6 Days" type="Group Trip" dates="10-16 Aug" accommodation="Homestays & High Camps"
                    transport="Private 4x4 Off-Roaders" meals="All Mountain Meals Included" price="₹34,500"
                    ctaUrl="/register" />
                <x-destination-card title="Kerala Tea Estate Escape" location="Munnar, Kerala"
                    image="https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=600&q=80"
                    duration="5 Days" type="Relaxed Retreat" dates="04-09 Sep" accommodation="Boutique Luxury Resort"
                    transport="Private AC Sedan" meals="Daily Breakfast & Dinner" price="₹27,800" ctaUrl="/register" />
                <x-destination-card title="Varkala Surf & Yoga" location="Varkala, Kerala"
                    image="https://images.unsplash.com/photo-1568849676085-51415703900f?auto=format&fit=crop&w=600&q=80"
                    duration="6 Days" type="Surf & Mindfulness" dates="12-18 Oct" accommodation="Oceanfront Cottages"
                    transport="Rental Scooters & Airport Cab" meals="Organic Breakfast & Beverages" price="₹23,500"
                    ctaUrl="/register" />
            </div>

            <!-- Abroad Section -->
            <div x-show="currentTab === 'abroad'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="grid grid-cols-1 md:grid-cols-3 gap-6" style="display: none;">
                <x-destination-card title="Uluwatu Beach House" location="Badung Regency, Bali"
                    image="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=600&q=80"
                    duration="6 Days" type="Open Trip" dates="12-18 Aug" accommodation="2N Seaside Villa, 3N Resort"
                    transport="Private Van & Local Driver" meals="Daily Breakfast & Dinner" price="$1,150"
                    ctaUrl="/register" />
                <x-destination-card title="Kyoto Temple Lodge" location="Kyoto, Japan"
                    image="https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=600&q=80"
                    duration="7 Days" type="Group Trip" dates="05-12 Sep" accommodation="3N Ryokan, 3N Boutique"
                    transport="Shinkansen Transit Passes" meals="Breakfast & Kaiseki Dinners" price="$2,400"
                    ctaUrl="/register" />
                <x-destination-card title="Patagonia Alpine Yurt" location="West Patagonia, Chile"
                    image="https://images.unsplash.com/photo-1527004013197-933c4bb611b3?auto=format&fit=crop&w=600&q=80"
                    duration="8 Days" type="Expedition" dates="20-28 Dec" accommodation="Glamping Yurts & Chalets"
                    transport="4x4 Offroad Transfers" meals="All Camp Meals & Cook" price="$3,100" ctaUrl="/register" />
            </div>
        </div>
    </section>

        <!-- Numeric Platform Statistics Row (inspired by Snapshot 4) -->
        <section class="py-16 bg-bg-primary border-b border-border-light">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div class="stat-item">
                        <p class="text-4xl font-extrabold text-brand-neutral"><span class="stat-num" data-target="50" data-suffix="+">0</span></p>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Verified Cozy Destinations</p>
                    </div>
                    <div class="stat-item">
                        <p class="text-4xl font-extrabold text-brand-neutral"><span class="stat-num" data-target="200" data-suffix="+">0</span></p>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Group Trips Planned</p>
                    </div>
                    <div class="stat-item">
                        <p class="text-4xl font-extrabold text-brand-neutral"><span class="stat-num" data-target="120000" data-suffix="+">0</span></p>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Active Co-planners</p>
                    </div>
                    <div class="stat-item">
                        <p class="text-4xl font-extrabold text-brand-neutral"><span class="stat-num" data-target="15" data-prefix="$" data-suffix=" Million">0</span></p>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-2">Expenses Nettted & Split</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- Bento Grid Section -->
        <section id="features" class="py-24 bg-bg-secondary border-t border-b border-border-light">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-text-main text-center mb-16">
                    Everything you need for <br class="hidden sm:inline">perfect group journeys
                </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <!-- Cell 1: Make a Trip (Spans 2 cols on md) -->
                <div
                    class="md:col-span-2 bg-bg-primary border border-border-card rounded-lg p-6 relative overflow-hidden group flex flex-col justify-between min-h-90 transition-all duration-300">
                    <div class="max-w-md z-10 text-left">
                        <h3 class="text-xl font-bold text-text-main mb-2">Make a Trip</h3>
                        <p class="text-xs text-text-muted leading-relaxed">
                            Design day-by-day itineraries with a beautiful interactive scrolling timeline. Connect
                            flights, hotels, and custom spots into one unified view.
                        </p>
                    </div>

                    <!-- Custom UI Illustration for Make a Trip (Timeline) -->
                    <div
                        class="relative mt-6 h-40 rounded-2xl border border-border-light bg-bg-secondary overflow-hidden p-4 select-none">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-2.5 py-1 bg-brand-neutral text-bg-primary text-[9px] font-bold rounded-full">Day
                                    1</span>
                                <div class="h-px grow bg-border-card"></div>
                            </div>
                            <div class="pl-4 border-l border-brand-neutral/20 ml-5 space-y-3">
                                <div
                                    class="bg-white border border-border-card rounded-xl p-3 shadow-xs max-w-sm flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <i class="ph ph-airplane text-text-muted"></i>
                                        <div class="text-left">
                                            <h5 class="text-[11px] font-bold text-text-main">Flight to Haneda</h5>
                                            <p class="text-[9px] text-text-muted">NH 848 • Departs 08:30 AM</p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[9px] px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-md font-bold">Confirmed</span>
                                </div>
                                <div
                                    class="bg-white border border-border-card rounded-xl p-3 shadow-xs max-w-sm flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <i class="ph ph-bed text-text-muted"></i>
                                        <div class="text-left">
                                            <h5 class="text-[11px] font-bold text-text-main">Kyoto Heritage Ryokan</h5>
                                            <p class="text-[9px] text-text-muted">Traditional Suite • Check-in 03:00 PM
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[9px] px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-md font-bold">Confirmed</span>
                                </div>
                            </div>
                        </div>
                        <div
                            class="absolute -right-10 -bottom-10 w-40 h-40 bg-brand-neutral/5 rounded-full blur-2xl pointer-events-none">
                        </div>
                    </div>
                </div>

                <!-- Cell 2: Group Collab (Spans 1 col) -->
                <div
                    class="bg-bg-primary border border-border-card rounded-lg p-6 relative overflow-hidden group flex flex-col justify-between min-h-90 transition-all duration-300">
                    <div class="z-10 text-left">
                        <h3 class="text-xl font-bold text-text-main mb-2">Group Collab</h3>
                        <p class="text-xs text-text-muted leading-relaxed">
                            Work live with your friends. Add members with read/write access and plan together with
                            instantaneous sync.
                        </p>
                    </div>

                    <!-- Custom UI Illustration for Group Collab (Active planners & Chat bubbles) -->
                    <div
                        class="relative mt-6 h-40 rounded-2xl border border-border-light bg-bg-secondary overflow-hidden p-4 select-none flex flex-col justify-end gap-3">
                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                <span
                                    class="w-7 h-7 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] font-bold border-2 border-bg-secondary">RD</span>
                                <span
                                    class="w-7 h-7 rounded-full bg-amber-600 text-white flex items-center justify-center text-[10px] font-bold border-2 border-bg-secondary">AK</span>
                                <span
                                    class="w-7 h-7 rounded-full bg-sky-600 text-white flex items-center justify-center text-[10px] font-bold border-2 border-bg-secondary">MS</span>
                            </div>
                            <span class="text-[9px] font-semibold text-text-muted">3 online now</span>
                        </div>

                        <div class="space-y-2">
                            <div
                                class="bg-white border border-border-card rounded-2xl rounded-bl-none p-2.5 shadow-xs max-w-[85%] self-start text-left">
                                <p class="text-[9px] text-text-muted font-bold">Rohan</p>
                                <p class="text-[10px] text-text-main mt-0.5">Let's book the tea ceremony tour!</p>
                            </div>
                            <div
                                class="bg-brand-neutral text-bg-primary rounded-2xl rounded-br-none p-2.5 shadow-xs max-w-[85%] self-end ml-auto text-left">
                                <p class="text-[9px] text-white/70 font-bold">You</p>
                                <p class="text-[10px] mt-0.5">Added to Day 2 afternoon slot!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cell 3: Poll Request (Spans 1 col) -->
                <div
                    class="bg-bg-primary border border-border-card rounded-lg p-6 relative overflow-hidden group flex flex-col justify-between min-h-90 transition-all duration-300">
                    <div class="z-10 text-left">
                        <h3 class="text-xl font-bold text-text-main mb-2">Poll Request</h3>
                        <p class="text-xs text-text-muted leading-relaxed">
                            Can't agree on lodging or hikes? Create elegant choice polls and let the majority vote
                            decide the plan.
                        </p>
                    </div>

                    <!-- Custom UI Illustration for Poll Request -->
                    <div
                        class="relative mt-6 h-40 rounded-2xl border border-border-light bg-bg-secondary overflow-hidden p-4 select-none flex flex-col justify-center gap-3 text-left">
                        <p class="text-[10px] font-bold text-text-main mb-1">Choose Villa accommodation:</p>
                        <div class="space-y-2.5">
                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-[9px] font-bold">
                                    <span class="text-text-main">A. Cliffside Infinity Villa</span>
                                    <span class="text-brand-neutral">75%</span>
                                </div>
                                <div class="w-full bg-border-card h-2 rounded-full overflow-hidden">
                                    <div class="bg-brand-neutral h-full rounded-full" style="width: 75%"></div>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-[9px] font-bold text-text-muted">
                                    <span>B. Jungle Treehouse Eco-lodge</span>
                                    <span>25%</span>
                                </div>
                                <div class="w-full bg-border-card h-2 rounded-full overflow-hidden">
                                    <div class="bg-brand-neutral/30 h-full rounded-full" style="width: 25%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cell 4: Budget Tracking (Spans 2 cols on md) -->
                <div
                    class="md:col-span-2 bg-bg-primary border border-border-card rounded-lg p-6 relative overflow-hidden group flex flex-col justify-between min-h-90 transition-all duration-300">
                    <div class="max-w-md z-10 text-left">
                        <h3 class="text-xl font-bold text-text-main mb-2">Budget & Split Tracking</h3>
                        <p class="text-xs text-text-muted leading-relaxed">
                            Log expenses in any currency and split them with customizable ratios. View exact balances
                            showing who owes who.
                        </p>
                    </div>

                    <!-- Custom UI Illustration for Budget Tracking -->
                    <div
                        class="relative mt-6 h-40 rounded-2xl border border-border-light bg-bg-secondary overflow-hidden p-4 select-none flex flex-col justify-between text-left">
                        <div class="flex items-center justify-between border-b border-border-light pb-2">
                            <span class="text-[10px] font-bold text-text-main">Trip Ledger</span>
                            <span
                                class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Total:
                                ₹84,500</span>
                        </div>
                        <div class="space-y-2 mt-2 grow overflow-hidden">
                            <div
                                class="flex justify-between items-center text-[9px] bg-white p-2 border border-border-card rounded-lg">
                                <div class="flex items-center gap-2">
                                    <i class="ph ph-bowl-food text-text-muted"></i>
                                    <span class="text-text-main">Sushi Dinner (Kyoto)</span>
                                </div>
                                <span class="font-bold text-text-main">₹12,400 <span
                                        class="text-[8px] text-text-muted font-normal">by Rohan</span></span>
                            </div>
                            <div
                                class="flex justify-between items-center text-[9px] bg-white p-2 border border-border-card rounded-lg">
                                <div class="flex items-center gap-2">
                                    <i class="ph ph-ticket text-text-muted"></i>
                                    <span class="text-text-main">Bullet Train Tickets</span>
                                </div>
                                <span class="font-bold text-text-main">₹36,000 <span
                                        class="text-[8px] text-text-muted font-normal">by You</span></span>
                            </div>
                        </div>
                        <div
                            class="bg-brand-neutral/5 border border-brand-neutral/10 rounded-xl p-2.5 mt-2 flex justify-between items-center">
                            <span class="text-[9px] font-bold text-brand-neutral flex items-center gap-1">
                                <i class="ph ph-hand-coins"></i>
                                <span>Rohan owes you ₹5,800</span>
                            </span>
                            <button
                                class="text-[8px] font-bold uppercase bg-brand-neutral text-bg-primary px-2.5 py-1 rounded-md cursor-pointer hover:bg-brand-hover">Settle
                                Up</button>
                        </div>
                    </div>
                </div>

                <!-- Cell 5: Clone Popular Itinerary -->
                <div
                    class="md:col-span-3 bg-bg-primary border border-border-card rounded-lg p-6 relative overflow-hidden group flex flex-col md:flex-row items-center justify-between transition-all duration-300">
                    <div class="max-w-md z-10 text-left">
                        <h3 class="text-xl font-bold text-text-main mb-2">Clone Itinerary</h3>
                        <p class="text-xs text-text-muted leading-relaxed">
                            Don't start from scratch. Browse world-class itineraries created by seasoned backpackers and
                            explorers, and clone them into your group workspace with a single click.
                        </p>
                    </div>

                        <!-- Custom UI Illustration for Clone Itinerary -->
                        <div class="relative mt-6 md:mt-0 w-full md:w-90 h-40 rounded-2xl border border-border-light bg-bg-secondary overflow-hidden p-4 select-none flex flex-col justify-between text-left">
                            <div>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-extrabold text-[12px] text-text-main">Spiti Valley Highlands Explorer</h4>
                                        <p class="text-[9px] text-text-muted">Kaza • Tabo • Dhankar Lake</p>
                                    </div>
                                    <span class="text-[8px] bg-brand-neutral text-bg-primary font-bold px-2 py-0.5 rounded-full">★ 4.9</span>
                                </div>
                                <div class="flex gap-2 mt-3">
                                    <span class="text-[8px] bg-white border border-border-card px-2 py-1 rounded-md text-text-muted flex items-center gap-1"><i class="ph ph-clock"></i> 9 Days</span>
                                    <span class="text-[8px] bg-white border border-border-card px-2 py-1 rounded-md text-text-muted flex items-center gap-1"><i class="ph ph-shield-check"></i> Expert Guided</span>
                                </div>
                            </div>
                            <button class="w-full bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-[10px] uppercase py-2.5 rounded-xl shadow-xs transition duration-200 flex items-center justify-center gap-2 cursor-pointer hover:scale-[1.01]">
                                <i class="ph ph-copy-simple text-sm"></i>
                                <span>Clone Itinerary Template</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24 bg-bg-primary border-b border-border-light">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <h2 class="text-3xl font-extrabold tracking-tight text-text-main text-center mb-12">
                Frequently Asked Questions
            </h2>

                <div class="space-y-4 text-left" x-data="{ active: null }">
                    <!-- FAQ 1 -->
                    <div class="border border-border-card rounded-2xl bg-bg-primary overflow-hidden transition duration-200">
                        <button @click="active = (active === 1 ? null : 1)" class="w-full flex justify-between items-center px-6 py-4.5 text-left font-bold text-sm text-text-main focus:outline-none cursor-pointer">
                            <span>What is Raahi?</span>
                            <i class="ph ph-caret-down text-text-muted transition-transform duration-300" :class="active === 1 ? 'rotate-180 text-brand-neutral' : ''"></i>
                        </button>
                        <div x-show="active === 1" x-transition class="px-6 pb-5 text-xs text-text-muted leading-relaxed">
                            Raahi is a collaborative group travel planner designed to bring itineraries, day-by-day maps, budgets, poll options, and group chat into a single, beautiful workspace. No more messy spreadsheets or endless WhatsApp threads.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="border border-border-card rounded-2xl bg-bg-primary overflow-hidden transition duration-200">
                        <button @click="active = (active === 2 ? null : 2)" class="w-full flex justify-between items-center px-6 py-4.5 text-left font-bold text-sm text-text-main focus:outline-none cursor-pointer">
                            <span>How does group collaboration work?</span>
                            <i class="ph ph-caret-down text-text-muted transition-transform duration-300" :class="active === 2 ? 'rotate-180 text-brand-neutral' : ''"></i>
                        </button>
                        <div x-show="active === 2" x-transition class="px-6 pb-5 text-xs text-text-muted leading-relaxed">
                            Once you create a trip, you can invite your friends with a custom link. You can assign different permissions (Admin, Editor, Viewer). Everyone can collaborate in real time to build the timeline, vote on accommodations, and log expenses.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="border border-border-card rounded-2xl bg-bg-primary overflow-hidden transition duration-200">
                        <button @click="active = (active === 3 ? null : 3)" class="w-full flex justify-between items-center px-6 py-4.5 text-left font-bold text-sm text-text-main focus:outline-none cursor-pointer">
                            <span>Can we track budgets in different currencies?</span>
                            <i class="ph ph-caret-down text-text-muted transition-transform duration-300" :class="active === 3 ? 'rotate-180 text-brand-neutral' : ''"></i>
                        </button>
                        <div x-show="active === 3" x-transition class="px-6 pb-5 text-xs text-text-muted leading-relaxed">
                            Yes! You can log expenses in any currency (USD, INR, EUR, etc.). Raahi automatically fetches current conversion rates and computes final balances in your home currency, showing exactly who owes how much to whom.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="border border-border-card rounded-2xl bg-bg-primary overflow-hidden transition duration-200">
                        <button @click="active = (active === 4 ? null : 4)" class="w-full flex justify-between items-center px-6 py-4.5 text-left font-bold text-sm text-text-main focus:outline-none cursor-pointer">
                            <span>Can I copy or clone an existing itinerary?</span>
                            <i class="ph ph-caret-down text-text-muted transition-transform duration-300" :class="active === 4 ? 'rotate-180 text-brand-neutral' : ''"></i>
                        </button>
                        <div x-show="active === 4" x-transition class="px-6 pb-5 text-xs text-text-muted leading-relaxed">
                            Absolutely! Our "Clone Template" feature lets you browse public itineraries uploaded by experienced travelers, guides, and creators. With one click, you can copy the full day-by-day plan into your private group dashboard and customize it.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Premium Structured Footer -->
        <footer class="bg-brand-neutral text-bg-primary/95 border-t border-brand-hover pt-20 pb-12 relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 bottom-0">
                <img src="/build/assets/footer-illustration.svg" alt="footer-illustration" class="w-full max-w-6xl mx-auto opacity-20" aria-hidden="true" />
            </div>
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 relative z-10">
                <!-- Layer 1: Newsletter -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-8 pb-12 border-b border-white/10">
                    <div class="max-w-md text-left">
                        <h4 class="text-xl font-bold text-white mb-2">Get travel inspiration & tips</h4>
                        <p class="text-xs text-bg-primary/60 leading-relaxed">Subscribe to our newsletter for hand-curated itineraries, local explorer guides, and early platform updates.</p>
                    </div>
                    <div class="w-full md:w-auto relative group">
                        <div class="absolute -inset-0.5 bg-linear-to-r from-emerald-400 to-teal-400 rounded-full blur opacity-0 group-focus-within:opacity-30 transition duration-500"></div>
                        <form action="#" class="relative flex items-center bg-white/5 border border-white/10 rounded-full p-1 w-full md:w-90">
                            <input type="email" placeholder="Your email address" class="grow bg-transparent border-0 py-2 pl-4 pr-3 text-xs font-semibold text-white placeholder-white/30 focus:ring-0 focus:outline-none" required />
                            <button type="submit" class="px-5 py-2 rounded-full bg-bg-primary hover:bg-bg-secondary text-brand-neutral font-bold text-xs uppercase tracking-wider transition-all duration-150 cursor-pointer">
                                Subscribe
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Layer 2: Main Footer Links -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-8 pt-4 text-left">
                    <!-- Column 1: Brand Info -->
                    <div class="col-span-2 space-y-4">
                        <a href="/" class="font-sans-display font-extrabold italic text-2xl tracking-tight text-white flex items-center space-x-2">
                            <span>Raahi.in</span>
                        </a>
                        <p class="text-xs text-bg-primary/60 leading-relaxed max-w-xs">
                            Empowering groups to plan, coordinate, and experience travel together. Beautiful, unified workspaces designed with absolute precision.
                        </p>
                        <!-- Social Icons -->
                        <div class="flex items-center space-x-3 pt-2">
                            <a href="#" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-white flex items-center justify-center transition border border-white/5">
                                <i class="ph ph-instagram-logo text-base"></i>
                            </a>
                            <a href="#" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-white flex items-center justify-center transition border border-white/5">
                                <i class="ph ph-twitter-logo text-base"></i>
                            </a>
                            <a href="#" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-white flex items-center justify-center transition border border-white/5">
                                <i class="ph ph-youtube-logo text-base"></i>
                            </a>
                            <a href="#" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-white flex items-center justify-center transition border border-white/5">
                                <i class="ph ph-linkedin-logo text-base"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Column 2: Product -->
                    <div class="space-y-3">
                        <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-white/50">Product</h5>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#" class="hover:text-white transition">Dynamic Timeline</a></li>
                            <li><a href="#" class="hover:text-white transition">Budget Ledger</a></li>
                            <li><a href="#" class="hover:text-white transition">Itinerary Cloner</a></li>
                            <li><a href="#" class="hover:text-white transition">Collaborative Polls</a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Company -->
                    <div class="space-y-3">
                        <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-white/50">Company</h5>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#" class="hover:text-white transition">About Us</a></li>
                            <li><a href="#" class="hover:text-white transition">Our Story</a></li>
                            <li><a href="#" class="hover:text-white transition">Careers</a></li>
                            <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
                        </ul>
                    </div>

                    <!-- Column 4: Resources -->
                    <div class="space-y-3">
                        <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-white/50">Resources</h5>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#" class="hover:text-white transition">Backpacker Blogs</a></li>
                            <li><a href="#" class="hover:text-white transition">Help Center</a></li>
                            <li><a href="#" class="hover:text-white transition">Planning Guides</a></li>
                            <li><a href="#" class="hover:text-white transition">API Access</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Layer 3: Copyright and Legal -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-8 border-t border-white/10 text-[11px] text-bg-primary/50 text-left">
                    <p>&copy; {{ date('Y') }} Raahi.in. Built with absolute precision. All rights reserved.</p>
                    <div class="flex space-x-6">
                        <a href="#" class="hover:text-white transition">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition">Terms of Service</a>
                        <a href="#" class="hover:text-white transition">Cookie Preferences</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Custom JS code for Carousel and Typewriter -->
    @push('head')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const initAnimations = () => {
                    if (!window.Motion) {
                        setTimeout(initAnimations, 50);
                        return;
                    }
                    const {
                        animate,
                        scroll,
                        inView
                    } = window.Motion;

                    // 1. Motion.dev Reveals & Glide Transitions (UX Interaction Design)
                    // Hero elements reveal
                    animate('.hero-title', {
                        opacity: [0, 1],
                        y: [30, 0]
                    }, {
                        duration: 0.8,
                        easing: 'ease-out'
                    });
                    animate('.hero-desc', {
                        opacity: [0, 1],
                        y: [20, 0]
                    }, {
                        duration: 0.8,
                        delay: 0.2,
                        easing: 'ease-out'
                    });
                    animate('.search-widget', {
                        opacity: [0, 1],
                        y: [25, 0]
                    }, {
                        duration: 0.8,
                        delay: 0.35,
                        easing: 'ease-out'
                    });

                // In-View animations for cards & grids
                inView('#values', () => {
                    animate('#values', {
                        opacity: [0, 1],
                        y: [30, 0]
                    }, {
                        duration: 0.6,
                        easing: 'ease-out'
                    });
                });

                    inView('#discover', () => {
                        animate('.grid > div', {
                            opacity: [0, 1],
                            y: [40, 0]
                        }, {
                            delay: (info) => info * 0.1,
                            duration: 0.5,
                            easing: 'ease-out'
                        });
                    });

                    inView('.stat-item', () => {
                        animate('.stat-item', {
                            opacity: [0, 1],
                            scale: [0.95, 1]
                        }, {
                            delay: (info) => info * 0.08,
                            duration: 0.5
                        });
                    });
                inView('.stat-item', () => {
                    animate('.stat-item', {
                        opacity: [0, 1],
                        scale: [0.95, 1]
                    }, {
                        delay: (info) => info * 0.08,
                        duration: 0.5
                    });

                    document.querySelectorAll('.stat-num').forEach(el => {
                        if (el.dataset.animated) return;
                        el.dataset.animated = "true";

                        const target = parseInt(el.dataset.target, 10);
                        const prefix = el.dataset.prefix || '';
                        const suffix = el.dataset.suffix || '';
                        const duration = 2000;
                        const startTime = performance.now();

                        const update = (time) => {
                            const elapsed = time - startTime;
                            const progress = Math.min(elapsed / duration, 1);

                            const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                            const current = Math.floor(easeProgress * target);

                            el.innerHTML = prefix + current.toLocaleString() + suffix;

                            if (progress < 1) {
                                requestAnimationFrame(update);
                            }
                        };
                        requestAnimationFrame(update);
                    });
                });

                    // Navbar shifts from transparent hero state to a soft pill after the partner band.
                    const navShell = document.getElementById('landing-nav-shell');
                    const nav = document.getElementById('landing-nav');
                    const brand = document.querySelector('[data-nav-brand]');
                    const navLinks = document.querySelectorAll('[data-nav-link]');
                    const partners = document.querySelector('section.py-12.bg-bg-primary');

                    const updateNav = () => {
                        const threshold = partners ?
                            partners.offsetTop + partners.offsetHeight - 80 :
                            window.innerHeight * 0.75;
                        const isSolid = window.scrollY > threshold;

                        navShell.classList.toggle('top-3', isSolid);
                        navShell.classList.toggle('top-6', !isSolid);
                        nav.classList.toggle('bg-white/70', isSolid);
                        nav.classList.toggle('backdrop-blur-md', isSolid);
                        nav.classList.toggle('border-white/60', isSolid);
                        nav.classList.toggle('shadow-[0_12px_40px_rgba(26,59,43,0.10)]', isSolid);
                        nav.classList.toggle('bg-transparent', !isSolid);
                        nav.classList.toggle('border-transparent', !isSolid);
                        brand.classList.toggle('text-brand-neutral', isSolid);
                        brand.classList.toggle('text-white', !isSolid);
                        brand.classList.toggle('text-shadow-lg', !isSolid);

                        navLinks.forEach((link) => {
                            link.classList.toggle('text-brand-neutral', isSolid);
                            link.classList.toggle('hover:text-brand-hover', isSolid);
                            link.classList.toggle('text-white/85', !isSolid);
                            link.classList.toggle('hover:text-white', !isSolid);
                        });
                    };

                    updateNav();
                    window.addEventListener('scroll', updateNav, {
                        passive: true
                    });
                    window.addEventListener('resize', updateNav);

                    // 2. Interactive Value Carousel Controls (Snapshot 1)
                    const track = document.getElementById('carousel-track');
                    const nextBtn = document.getElementById('carousel-next');
                    const prevBtn = document.getElementById('carousel-prev');

                    if (track && nextBtn && prevBtn) {
                        nextBtn.addEventListener('click', () => {
                            track.scrollBy({
                                left: 300,
                                behavior: 'smooth'
                            });
                        });
                        prevBtn.addEventListener('click', () => {
                            track.scrollBy({
                                left: -300,
                                behavior: 'smooth'
                            });
                        });
                    }

                    // 3. Typewriter effect for search bar
                    const searchInput = document.getElementById('typing-search');
                    if (searchInput) {
                        const destinations = ['Ladakh', 'Munnar', 'Goa', 'Jaipur', 'Varkala', 'Hampi', 'Manali', 'Alleppey', 'Udaipur', 'Dharamshala'];
                        let destIndex = 0;
                        let charIndex = 0;
                        let isDeleting = false;
                        let typingSpeed = 120;

                        function type() {
                            const currentDest = destinations[destIndex];

                            if (isDeleting) {
                                searchInput.placeholder = "Explore " + currentDest.substring(0, charIndex - 1);
                                charIndex--;
                                typingSpeed = 60;
                            } else {
                                searchInput.placeholder = "Explore " + currentDest.substring(0, charIndex + 1);
                                charIndex++;
                                typingSpeed = 120;
                            }

                            if (!isDeleting && charIndex === currentDest.length) {
                                isDeleting = true;
                                typingSpeed = 2000; // Pause at full word
                            } else if (isDeleting && charIndex === 0) {
                                isDeleting = false;
                                destIndex = (destIndex + 1) % destinations.length;
                                typingSpeed = 500; // Pause before typing next word
                            }

                            setTimeout(type, typingSpeed);
                        }

                        setTimeout(type, 1000);
                    }
                };
                initAnimations();
            });
        </script>
    @endpush
</x-marketing-layout>
