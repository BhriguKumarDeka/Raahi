<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif-display font-bold text-3xl text-text-main leading-tight animate-fade-in">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
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
