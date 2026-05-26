<x-marketing-layout>
    <x-slot name="title">How it Works - Raahi.com</x-slot>

    <!-- Hero Section -->
    <section class="relative min-h-[50vh] flex flex-col justify-center pt-32 pb-20 overflow-hidden text-white border-b border-brand-hover bg-brand-neutral">
        <!-- Background Image with green brand overlay & vintage vignette -->
        <div class="absolute inset-0 z-0">
            <img src="/images/nepal.jpg"
                 alt="Nepal Mountain Landscape"
                 class="w-full h-full object-cover filter contrast-[1.1] brightness-[0.85]" />
            <!-- Brand linear overlay -->
            <div class="absolute inset-0 bg-linear-to-b from-brand-neutral/20 via-brand-neutral/40 to-brand-neutral/80"></div>
            <!-- Vintage vignette shadow corners -->
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(0,0,0,0)_40%,rgba(10,71,52,0.45)_80%,rgba(6,46,34,0.85)_100%)]"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="hero-title text-4xl sm:text-6xl font-extralight tracking-tight leading-[1.1] max-w-4xl mx-auto text-shadow-md">
                Plan your group trips <br><span class="font-normal italic text-teal-200">without the chaos</span>
            </h1>
            <p class="hero-desc mt-6 text-sm sm:text-base text-white/80 max-w-2xl mx-auto leading-relaxed text-shadow-md">
                No more messy spreadsheets, fragmented maps, or endless messaging threads. Settle decisions, build coordinates, and track ledger balances in one unified space.
            </p>
        </div>
    </section>

    <!-- Interactive Step Timeline Section -->
    <section class="py-24 bg-bg-primary text-text-main">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">The Raahi Flow</h2>
                <p class="text-xs text-text-muted mt-2 font-semibold">Four simple steps to design your dream collective travel experience.</p>
            </div>

            <!-- Steps Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 relative">

                <!-- Step 1 -->
                <div class="animate-fade-in-up flex gap-6 p-6 rounded-3xl bg-bg-secondary border border-border-light hover:border-brand-neutral/20 transition-all duration-300 hover:scale-[1.01] hover:shadow-xs group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-brand-neutral/5 flex items-center justify-center text-brand-neutral group-hover:bg-brand-neutral group-hover:text-bg-primary transition duration-300">
                        <i class="ph ph-plus text-xl font-bold"></i>
                    </div>
                    <div class="text-left">
                        <span class="text-[9px] uppercase tracking-widest text-brand-neutral font-extrabold">Step 01</span>
                        <h3 class="text-lg font-extrabold mt-1">Create & Invite</h3>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            Initialize your trip with custom dates, destinations, and a cover visual. Invite your friends instantly using a simple magic signup link. Set customized reader, editor, or admin roles.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="animate-fade-in-up flex gap-6 p-6 rounded-3xl bg-bg-secondary border border-border-light hover:border-brand-neutral/20 transition-all duration-300 hover:scale-[1.01] hover:shadow-xs group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-brand-neutral/5 flex items-center justify-center text-brand-neutral group-hover:bg-brand-neutral group-hover:text-bg-primary transition duration-300">
                        <i class="ph ph-users text-xl font-bold"></i>
                    </div>
                    <div class="text-left">
                        <span class="text-[9px] uppercase tracking-widest text-brand-neutral font-extrabold">Step 02</span>
                        <h3 class="text-lg font-extrabold mt-1">Collaborate Live</h3>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            Watch your timeline populate instantly as team members add flight coordinates, boutique lodgings, and curated spots. Live sync ensures nobody misses an update.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="animate-fade-in-up flex gap-6 p-6 rounded-3xl bg-bg-secondary border border-border-light hover:border-brand-neutral/20 transition-all duration-300 hover:scale-[1.01] hover:shadow-xs group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-brand-neutral/5 flex items-center justify-center text-brand-neutral group-hover:bg-brand-neutral group-hover:text-bg-primary transition duration-300">
                        <i class="ph ph-chart-bar-horizontal text-xl font-bold"></i>
                    </div>
                    <div class="text-left">
                        <span class="text-[9px] uppercase tracking-widest text-brand-neutral font-extrabold">Step 03</span>
                        <h3 class="text-lg font-extrabold mt-1">Vote & Decide</h3>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            Can't agree on which resort to book or what hour to head to the temple? Spawn elegant Choice Polls. Vote live and let group consensus drive the journey structure.
                        </p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="animate-fade-in-up flex gap-6 p-6 rounded-3xl bg-bg-secondary border border-border-light hover:border-brand-neutral/20 transition-all duration-300 hover:scale-[1.01] hover:shadow-xs group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-brand-neutral/5 flex items-center justify-center text-brand-neutral group-hover:bg-brand-neutral group-hover:text-bg-primary transition duration-300">
                        <i class="ph ph-hand-coins text-xl font-bold"></i>
                    </div>
                    <div class="text-left">
                        <span class="text-[9px] uppercase tracking-widest text-brand-neutral font-extrabold">Step 04</span>
                        <h3 class="text-lg font-extrabold mt-1">Split Expenses Seamlessly</h3>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            Log flight, hotel, and dining expenses in any local currency. Raahi converts currency and tracks the ledger automatically, showing who owes who what. Settle up with one click.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Side-by-Side Comparison Feature -->
    <section class="py-24 bg-bg-secondary border-t border-b border-border-light">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-center mb-16 text-text-main">
                Say goodbye to planning friction
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- WhatsApp Chaos -->
                <div class="bg-white border border-border-card rounded-3xl p-8 relative overflow-hidden flex flex-col justify-between min-h-[360px] text-left">
                    <div>
                        <div class="flex items-center gap-2 text-rose-600 mb-4">
                            <i class="ph ph-x-circle text-lg font-bold"></i>
                            <span class="text-xs font-bold uppercase tracking-wider">Before: The Messaging Maze</span>
                        </div>
                        <h3 class="text-xl font-bold text-text-main mb-2">Fragmented Group Chat Chaos</h3>
                        <p class="text-xs text-text-muted leading-relaxed">
                            Dozens of links buried under 500 unread messages, outdated spreadsheets nobody checks, screenshots of flights lost in the gallery, and awkward "who paid for dinner" discussions.
                        </p>
                    </div>

                    <div class="mt-6 space-y-2 select-none opacity-80">
                        <div class="bg-rose-50/50 border border-rose-100 rounded-2xl p-3 flex gap-2">
                            <span class="w-6 h-6 rounded-full bg-rose-600 text-white text-[9px] font-bold flex items-center justify-center flex-shrink-0">RD</span>
                            <div class="text-xs text-left">
                                <p class="font-bold text-text-main">Rohan</p>
                                <p class="text-text-muted mt-0.5">Wait, who ended up booking the Airbnb? Is it near the beach or in the center?</p>
                            </div>
                        </div>
                        <div class="bg-rose-50/50 border border-rose-100 rounded-2xl p-3 flex gap-2">
                            <span class="w-6 h-6 rounded-full bg-rose-700 text-white text-[9px] font-bold flex items-center justify-center flex-shrink-0">AK</span>
                            <div class="text-xs text-left">
                                <p class="font-bold text-text-main">Aman</p>
                                <p class="text-text-muted mt-0.5">Let me dig up the excel link. Wait, is it the V2 or V3 sheet?</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Raahi Solution -->
                <div class="bg-white border border-brand-neutral/20 rounded-3xl p-8 relative overflow-hidden flex flex-col justify-between min-h-[360px] text-left shadow-xs">
                    <div>
                        <div class="flex items-center gap-2 text-brand-neutral mb-4">
                            <i class="ph ph-check-circle text-lg font-bold"></i>
                            <span class="text-xs font-bold uppercase tracking-wider">After: Raahi Cohesion</span>
                        </div>
                        <h3 class="text-xl font-bold text-text-main mb-2">Beautifully Unified Workspace</h3>
                        <p class="text-xs text-text-muted leading-relaxed">
                            A single, real-time board with your dynamic timeline, active polls, direct maps, and budget ledgers. Designed to keep everyone aligned and excited for the journey.
                        </p>
                    </div>

                    <div class="mt-6 bg-brand-neutral/5 border border-brand-neutral/10 rounded-2xl p-4 flex justify-between items-center select-none text-left">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-hand-coins text-lg text-brand-neutral"></i>
                            <div class="text-left">
                                <h5 class="text-[11px] font-bold text-text-main">Ledger Synced</h5>
                                <p class="text-[9px] text-brand-neutral font-bold mt-0.5">Rohan owes you ₹5,800</p>
                            </div>
                        </div>
                        <span class="text-[9px] bg-brand-neutral text-bg-primary font-bold px-3 py-1.5 rounded-xl">Settle Up</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-24 bg-bg-primary">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Ready to co-plan your next escape?</h2>
            <p class="text-xs text-text-muted mt-4 max-w-sm mx-auto leading-relaxed">Join thousands of group planners, backpackers, and digital nomads building memories on Raahi.</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="px-8 py-3.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold uppercase tracking-wider rounded-xl transition duration-150 inline-block shadow-xs hover:scale-[1.02]">
                    Get Started Free
                </a>
            </div>
        </div>
    </section>
</x-marketing-layout>
