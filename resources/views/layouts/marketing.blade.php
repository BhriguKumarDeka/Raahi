<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Raahi - Group Travel Planning & Itinerary Collaboration' }}</title>

        <!-- Fonts (Google Sans) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&display=swap" rel="stylesheet">

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
        @stack('head')
    </head>
    <body class="font-sans antialiased bg-bg-primary text-text-main selection:bg-brand-neutral selection:text-bg-primary">
        <div class="min-h-screen flex flex-col justify-between overflow-x-hidden">
            
            <!-- Navbar Shell -->
            <div id="landing-nav-shell" class="fixed top-6 left-0 right-0 z-50 px-4 sm:px-6 lg:px-8 transition-all duration-300">
                <header id="landing-nav" class="max-w-5xl mx-auto rounded-full border border-transparent bg-transparent transition-all duration-300">
                    <div class="px-6 h-16 flex items-center justify-between">
                        <a href="/" data-nav-brand class="font-sans-display font-extrabold text-3xl tracking-tight text-white flex items-center space-x-2 text-shadow-lg transition-all duration-300 hover:scale-[1.05]">
                            <span>Raahi.com</span>
                        </a>

                        <!-- Nav links -->
                        <ul class="flex items-center space-x-6">
                            <li>
                                <a href="/how-it-works" data-nav-link class="text-xs font-bold uppercase tracking-wider text-white/85 hover:text-white transition-colors duration-200 px-3 py-2">
                                    How it works
                                </a>
                            </li>
                            <li>
                                <a href="/about" data-nav-link class="text-xs font-bold uppercase tracking-wider text-white/85 hover:text-white transition-colors duration-200 px-3 py-2">
                                    About
                                </a>
                            </li>
                            <li>
                                <a href="/contact" data-nav-link class="text-xs font-bold uppercase tracking-wider text-white/85 hover:text-white transition-colors duration-200 px-3 py-2">
                                    Contact
                                </a>
                            </li>
                        </ul>

                        <!-- Auth Actions -->
                        <div class="flex items-center space-x-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 {{ request()->routeIs('how-it-works') ? 'bg-[#124b73] hover:bg-[#0c3552]' : 'bg-brand-neutral hover:bg-brand-hover' }} text-bg-primary text-xs font-bold rounded-full transition-all duration-200 shadow-sm hover:scale-[1.02]">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" data-nav-link class="text-xs font-bold uppercase tracking-wider text-white/85 hover:text-white transition-colors duration-200 px-3 py-2">
                                    Sign In
                                </a>
                                <a href="{{ route('register') }}" class="px-5 py-2.5 {{ request()->routeIs('how-it-works') ? 'bg-[#124b73] hover:bg-[#0c3552]' : 'bg-brand-neutral hover:bg-brand-hover' }} text-bg-primary text-xs font-bold rounded-full transition-all duration-200 shadow-sm hover:scale-[1.02]">
                                    Get Started
                                </a>
                            @endauth
                        </div>
                    </div>
                </header>
            </div>

            <!-- Page Content -->
            <main class="flex-grow">
                {{ $slot }}
            </main>

            <!-- Premium Structured Footer -->
            <footer class="bg-brand-neutral text-bg-primary/95 border-t border-brand-hover pt-20 pb-12">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
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
                                <li><a href="/about" class="hover:text-white transition">About Us</a></li>
                                <li><a href="#" class="hover:text-white transition">Our Story</a></li>
                                <li><a href="#" class="hover:text-white transition">Careers</a></li>
                                <li><a href="/contact" class="hover:text-white transition">Contact Us</a></li>
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

        <!-- Custom JS code for Animations, Carousel and Typewriter -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const initAnimations = () => {
                    if (!window.Motion) {
                        setTimeout(initAnimations, 50);
                        return;
                    }
                    const { animate, scroll, inView } = window.Motion;

                    // 1. Motion.dev Reveals & Glide Transitions
                    // Hero elements reveal (only runs on pages containing these elements)
                    if (document.querySelector('.hero-title')) {
                        animate('.hero-title', { opacity: [0, 1], y: [30, 0] }, { duration: 0.8, easing: 'ease-out' });
                    }
                    if (document.querySelector('.hero-desc')) {
                        animate('.hero-desc', { opacity: [0, 1], y: [20, 0] }, { duration: 0.8, delay: 0.2, easing: 'ease-out' });
                    }
                    if (document.querySelector('.search-widget')) {
                        animate('.search-widget', { opacity: [0, 1], y: [25, 0] }, { duration: 0.8, delay: 0.35, easing: 'ease-out' });
                    }

                    // In-View animations for cards & grids (generic/reusable)
                    if (document.getElementById('values')) {
                        inView('#values', () => {
                            animate('#values', { opacity: [0, 1], y: [30, 0] }, { duration: 0.6, easing: 'ease-out' });
                        });
                    }

                    if (document.getElementById('discover')) {
                        inView('#discover', () => {
                            animate('#discover .grid > div', { opacity: [0, 1], y: [40, 0] }, { 
                                delay: (info) => info * 0.1, 
                                duration: 0.5, 
                                easing: 'ease-out' 
                            });
                        });
                    }

                    if (document.querySelector('.stat-item')) {
                        inView('.stat-item', () => {
                            animate('.stat-item', { opacity: [0, 1], scale: [0.95, 1] }, { 
                                delay: (info) => info * 0.08, 
                                duration: 0.5 
                            });
                        });
                    }

                    // General page animations for specific marketing sections
                    if (document.querySelector('.animate-fade-in-up')) {
                        animate('.animate-fade-in-up', { opacity: [0, 1], y: [20, 0] }, { 
                            delay: (info) => info * 0.15,
                            duration: 0.6, 
                            easing: 'ease-out' 
                        });
                    }

                    // Navbar shifts from transparent hero state to a soft pill on scroll.
                    const navShell = document.getElementById('landing-nav-shell');
                    const nav = document.getElementById('landing-nav');
                    const brand = document.querySelector('[data-nav-brand]');
                    const navLinks = document.querySelectorAll('[data-nav-link]');
                    
                    // On sub-pages without a specific dark image banner or with a shorter hero, we can set default solid state 
                    // or let scroll handle it. Let's make it shift dynamic on scroll relative to the viewport height.
                    const updateNav = () => {
                        const threshold = 100; // Solid after 100px scroll
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
                    window.addEventListener('scroll', updateNav, { passive: true });
                    window.addEventListener('resize', updateNav);

                    // 2. Value Carousel Controls
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
    </body>
</html>
