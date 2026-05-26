<x-marketing-layout>
    <x-slot name="title">About Us - Raahi.com</x-slot>

    <!-- Hero Section -->
    <section class="relative min-h-[50vh] flex flex-col justify-center pt-32 pb-20 overflow-hidden bg-brand-neutral border-b border-brand-hover text-white">
        <!-- Background Image with green brand overlay & vintage vignette -->
        <div class="absolute inset-0 z-0">
            <img src="/images/about.jpg"
                 alt="Green Hills Landscape"
                 class="w-full h-full object-cover filter contrast-[1.1] brightness-[0.85]" />
            <!-- Brand linear overlay -->
            <div class="absolute inset-0 bg-linear-to-b from-brand-neutral/20 via-brand-neutral/40 to-brand-neutral/80"></div>
            <!-- Vintage vignette shadow corners -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(0,0,0,0)_40%,rgba(10,71,52,0.45)_80%,rgba(6,46,34,0.85)_100%)]"></div>
        </div>
        
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="hero-title text-4xl sm:text-6xl font-extralight tracking-tight leading-[1.1] max-w-4xl mx-auto text-shadow-md">
                We design tools <br><span class="font-normal italic text-teal-200">for collective adventures</span>
            </h1>
            <p class="hero-desc mt-6 text-sm sm:text-base text-white/80 max-w-2xl mx-auto leading-relaxed text-shadow-md">
                Raahi is built by a team of travelers, designers, and engineers who got tired of bad layouts and planning friction. We make traveling with your crew effortless.
            </p>
        </div>
    </section>

    <!-- Our Narrative Section -->
    <section class="py-24 bg-bg-primary text-text-main text-left">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 items-start">
                
                <!-- Title column -->
                <div class="lg:col-span-5">
                    <span class="text-[9px] uppercase tracking-widest text-brand-neutral font-extrabold">The Raahi Origin</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mt-2 leading-tight">
                        Born from the friction of group planning
                    </h2>
                    <p class="text-xs text-text-muted mt-6 leading-relaxed">
                        Every great trip starts with an idea. But quickly, that excitement gets buried under spreadsheets, coordinates scattered across multiple chat rooms, and endless payment discussions.
                    </p>
                    <p class="text-xs text-text-muted mt-4 leading-relaxed">
                        We built Raahi to act as your group's collective memory. A premium space where ideas flow organically, decisions are made transparently, and logistics feel like second nature.
                    </p>
                </div>

                <!-- Narrative column -->
                <div class="lg:col-span-7 space-y-8">
                    <div class="p-8 rounded-3xl bg-bg-secondary border border-border-light text-left relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-24 h-24 bg-brand-neutral/5 rounded-full blur-xl pointer-events-none"></div>
                        <h4 class="font-extrabold text-base text-brand-neutral">Why "Raahi"?</h4>
                        <p class="text-xs text-text-muted mt-3 leading-relaxed">
                            In Hindi, <strong>Raahi</strong> translates to a traveler or guide. It is someone who walks the path of adventure, exploring the unfamiliar while sharing the company of peers. That is exactly what our platform embodies.
                        </p>
                    </div>

                    <div class="p-8 rounded-3xl bg-bg-secondary border border-border-light text-left relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-24 h-24 bg-brand-neutral/5 rounded-full blur-xl pointer-events-none"></div>
                        <h4 class="font-extrabold text-base text-brand-neutral">Our Philosophy</h4>
                        <p class="text-xs text-text-muted mt-3 leading-relaxed">
                            We don't believe in rigid, automated travel templates that treat journeys like assembly lines. Travel is about vibes, unexpected local cafes, and flexibility. Raahi gives you structure when you need it, and gets out of your way when you don't.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Core Values Bento Grid -->
    <section class="py-24 bg-bg-secondary border-t border-b border-border-light text-text-main">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-[9px] uppercase tracking-widest text-brand-neutral font-extrabold">How We Build</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mt-2">Our Core Values</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                <!-- Card 1 -->
                <div class="bg-white border border-border-card rounded-3xl p-8 hover:border-brand-neutral/20 transition duration-300 flex flex-col justify-between min-h-[260px]">
                    <div class="w-10 h-10 rounded-xl bg-brand-neutral/5 flex items-center justify-center text-brand-neutral">
                        <i class="ph ph-compass text-lg font-bold"></i>
                    </div>
                    <div class="mt-6">
                        <h4 class="font-extrabold text-base">Absolute Precision</h4>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            We labor over spacing, typography, and response times. Every interaction is fast, fluid, and delightful.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white border border-border-card rounded-3xl p-8 hover:border-brand-neutral/20 transition duration-300 flex flex-col justify-between min-h-[260px]">
                    <div class="w-10 h-10 rounded-xl bg-brand-neutral/5 flex items-center justify-center text-brand-neutral">
                        <i class="ph ph-hands-clapping text-lg font-bold"></i>
                    </div>
                    <div class="mt-6">
                        <h4 class="font-extrabold text-base">Vibes Over Rigidity</h4>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            Structure is good, but flexible setups are better. We design for the human elements of group exploration.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white border border-border-card rounded-3xl p-8 hover:border-brand-neutral/20 transition duration-300 flex flex-col justify-between min-h-[260px]">
                    <div class="w-10 h-10 rounded-xl bg-brand-neutral/5 flex items-center justify-center text-brand-neutral">
                        <i class="ph ph-shield-check text-lg font-bold"></i>
                    </div>
                    <div class="mt-6">
                        <h4 class="font-extrabold text-base">Radical Trust</h4>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            No secret fees, no hidden models, and completely open ledgers. We respect your shared resources.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-24 bg-bg-primary">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Embark on your journey today</h2>
            <p class="text-xs text-text-muted mt-4 max-w-sm mx-auto leading-relaxed">Create a trip and invite your friends. Raahi is free to get started and perfect for group travels.</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="px-8 py-3.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold uppercase tracking-wider rounded-xl transition duration-150 inline-block shadow-xs hover:scale-[1.02]">
                    Get Started Free
                </a>
            </div>
        </div>
    </section>
</x-marketing-layout>
