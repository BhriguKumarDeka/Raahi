<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif-display font-bold text-3xl text-text-main leading-tight animate-fade-in">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Profile Header Banner -->
            <div class="relative overflow-hidden bg-bg-primary border border-border-card rounded-3xl shadow-[0_10px_40px_rgba(26,59,43,0.02)]">
                <!-- Decorative accent strip -->
                <div class="h-1.5 w-full bg-gradient-to-r from-brand-neutral via-brand-neutral/60 to-transparent"></div>

                <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center gap-6">
                    <!-- Avatar -->
                    <div class="h-20 w-20 rounded-full overflow-hidden border-2 border-brand-neutral/20 shadow-sm flex-shrink-0">
                        @if(auth()->user()->profile_image)
                            <img src="{{ Storage::url(auth()->user()->profile_image) }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full bg-brand-neutral/10 flex items-center justify-center">
                                <span class="text-2xl font-bold text-brand-neutral uppercase">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- User Info -->
                    <div class="text-center sm:text-left flex-1">
                        <h2 class="text-2xl font-bold font-serif-display text-text-main">{{ auth()->user()->name }}</h2>
                        <p class="text-sm text-text-muted mt-1 flex items-center justify-center sm:justify-start gap-1.5">
                            <i class="ph ph-envelope-simple text-sm"></i>
                            {{ auth()->user()->email }}
                        </p>
                        @if(auth()->user()->hasVerifiedEmail())
                            <span class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-2.5 py-0.5 rounded-full">
                                <i class="ph ph-seal-check text-xs"></i>
                                Verified
                            </span>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="flex gap-6 text-center flex-shrink-0">
                        <div>
                            <p class="text-xl font-extrabold text-text-main">{{ auth()->user()->trips()->count() }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-text-muted mt-0.5">Trips</p>
                        </div>
                        <div>
                            <p class="text-xl font-extrabold text-text-main">{{ auth()->user()->createdTrips()->count() }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-text-muted mt-0.5">Created</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-bg-primary border border-border-card rounded-3xl shadow-[0_10px_40px_rgba(26,59,43,0.02)]">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-bg-primary border border-border-card rounded-3xl shadow-[0_10px_40px_rgba(26,59,43,0.02)]">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-bg-primary border border-border-card rounded-3xl shadow-[0_10px_40px_rgba(26,59,43,0.02)]">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
