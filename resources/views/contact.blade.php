<x-marketing-layout>
    <x-slot name="title">Contact Us - Raahi.com</x-slot>

    <!-- Hero Section -->
    <section class="relative min-h-[50vh] flex flex-col justify-center pt-32 pb-20 overflow-hidden bg-brand-neutral border-b border-brand-hover text-white">
        <!-- Background Ambient Mesh -->
        <div class="absolute inset-0 z-0 opacity-20">
            <div class="absolute -top-48 -left-48 w-96 h-96 rounded-full bg-teal-400 blur-3xl"></div>
            <div class="absolute -bottom-48 -right-48 w-96 h-96 rounded-full bg-emerald-300 blur-3xl"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="hero-title text-4xl sm:text-6xl font-extralight tracking-tight leading-[1.1] max-w-4xl mx-auto text-shadow-md">
                We'd love to hear <br><span class="font-normal italic text-teal-200">from your crew</span>
            </h1>
            <p class="hero-desc mt-6 text-sm sm:text-base text-white/80 max-w-2xl mx-auto leading-relaxed text-shadow-md">
                Have questions about our planner, feedback on features, or a stunning group trip template you want us to highlight? Reach out to the Raahi team.
            </p>
        </div>
    </section>

    <!-- Split Contact UI Section -->
    <section class="py-24 bg-bg-primary text-text-main text-left" x-data="{ submitted: false, name: '', email: '', subject: '', message: '' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 items-start">

                <!-- Contact Form Column -->
                <div class="lg:col-span-7 bg-bg-secondary border border-border-light rounded-3xl p-8 sm:p-10 relative overflow-hidden shadow-xs">

                    <!-- Success State -->
                    <div x-show="submitted" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="text-center py-12" style="display: none;">
                        <div class="w-16 h-16 rounded-full bg-emerald-50 text-brand-neutral border border-emerald-100 flex items-center justify-center mx-auto mb-6">
                            <i class="ph ph-paper-plane-tilt text-2xl font-bold"></i>
                        </div>
                        <h3 class="text-xl font-extrabold">Message Sent!</h3>
                        <p class="text-xs text-text-muted mt-3 max-w-xs mx-auto leading-relaxed">
                            Thank you, <span class="font-bold text-text-main" x-text="name"></span>. We have received your query regarding <span class="font-bold text-text-main" x-text="subject"></span> and will get back to you shortly.
                        </p>
                        <button @click="submitted = false; name = ''; email = ''; subject = ''; message = ''" class="mt-8 px-6 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold rounded-xl transition duration-150 shadow-xs cursor-pointer">
                            Send another message
                        </button>
                    </div>

                    <!-- Form State -->
                    <form x-show="!submitted" @submit.prevent="submitted = true" class="space-y-6">
                        <div class="text-left mb-2">
                            <h3 class="text-xl font-extrabold">Send a Message</h3>
                            <p class="text-xs text-text-muted mt-1 leading-relaxed">Our support and design teams typically respond within 12–24 hours.</p>
                        </div>

                        <!-- Name field -->
                        <div class="space-y-1.5 text-left">
                            <label for="form-name" class="text-[10px] font-extrabold uppercase tracking-widest text-text-muted">Full Name</label>
                            <input type="text" id="form-name" x-model="name" required class="w-full bg-white border border-border-card rounded-xl px-4 py-3 text-xs font-semibold text-text-main placeholder-text-muted/40 focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none transition" placeholder="Alex Carter" />
                        </div>

                        <!-- Email field -->
                        <div class="space-y-1.5 text-left">
                            <label for="form-email" class="text-[10px] font-extrabold uppercase tracking-widest text-text-muted">Email Address</label>
                            <input type="email" id="form-email" x-model="email" required class="w-full bg-white border border-border-card rounded-xl px-4 py-3 text-xs font-semibold text-text-main placeholder-text-muted/40 focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none transition" placeholder="alex@gmail.com" />
                        </div>

                        <!-- Subject field -->
                        <div class="space-y-1.5 text-left">
                            <label for="form-subject" class="text-[10px] font-extrabold uppercase tracking-widest text-text-muted">Subject</label>
                            <input type="text" id="form-subject" x-model="subject" required class="w-full bg-white border border-border-card rounded-xl px-4 py-3 text-xs font-semibold text-text-main placeholder-text-muted/40 focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none transition" placeholder="Feature suggestion / feedback" />
                        </div>

                        <!-- Message field -->
                        <div class="space-y-1.5 text-left">
                            <label for="form-message" class="text-[10px] font-extrabold uppercase tracking-widest text-text-muted">Message</label>
                            <textarea id="form-message" x-model="message" required rows="5" class="w-full bg-white border border-border-card rounded-xl px-4 py-3 text-xs font-semibold text-text-main placeholder-text-muted/40 focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none transition" placeholder="Tell us what you have in mind..."></textarea>
                        </div>

                        <button type="submit" class="w-full py-3 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold uppercase tracking-wider rounded-xl transition duration-150 inline-block shadow-xs cursor-pointer hover:scale-[1.01]">
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Info Cards Column -->
                <div class="lg:col-span-5 space-y-8">
                    <!-- Support Card -->
                    <div class="p-6 rounded-3xl bg-bg-secondary border border-border-light text-left group hover:border-brand-neutral/20 transition-all duration-300">
                        <div class="w-10 h-10 rounded-2xl bg-brand-neutral/5 text-brand-neutral flex items-center justify-center group-hover:bg-brand-neutral group-hover:text-bg-primary transition duration-300">
                            <i class="ph ph-envelope-simple text-base font-bold"></i>
                        </div>
                        <h4 class="font-extrabold text-base mt-4 text-text-main">Email Support</h4>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            For technical questions, general queries, and community integrations, send us a line anytime.
                        </p>
                        <a href="mailto:hello@raahi.in" class="text-xs font-bold text-brand-neutral mt-4 block hover:underline hover:text-brand-hover transition">
                            hello@raahi.in
                        </a>
                    </div>

                    <!-- HQ Card -->
                    <div class="p-6 rounded-3xl bg-bg-secondary border border-border-light text-left group hover:border-brand-neutral/20 transition-all duration-300">
                        <div class="w-10 h-10 rounded-2xl bg-brand-neutral/5 text-brand-neutral flex items-center justify-center group-hover:bg-brand-neutral group-hover:text-bg-primary transition duration-300">
                            <i class="ph ph-map-pin text-base font-bold"></i>
                        </div>
                        <h4 class="font-extrabold text-base mt-4 text-text-main">Our Headquarters</h4>
                        <p class="text-xs text-text-muted mt-2 leading-relaxed">
                            Raahi Inc.<br>
                            Lovely Professional University,<br>
                            Phagwara, Punjab, India
                        </p>
                    </div>

                    <!-- FAQ Redirect Card -->
                    <div class="p-6 rounded-3xl bg-brand-neutral/5 border border-brand-neutral/10 text-left relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-24 h-24 bg-brand-neutral/5 rounded-full blur-xl pointer-events-none"></div>
                        <h4 class="font-extrabold text-sm text-brand-neutral">Looking for instant answers?</h4>
                        <p class="text-[11px] text-text-muted mt-2 leading-relaxed">
                            Check out our frequently asked questions on the home page for quick guides on group invites, ledger splits, and collaborative voting timelines.
                        </p>
                        <a href="/#faq" class="text-[11px] font-bold text-brand-neutral hover:text-brand-hover hover:underline transition mt-3 block">
                            View FAQs &rarr;
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
</x-marketing-layout>
