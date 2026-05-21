<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header class="flex items-center space-x-3 pb-4 border-b border-border-light">
        <div class="w-10 h-10 rounded-full bg-brand-neutral/5 flex items-center justify-center text-brand-neutral">
            <i class="ph-duotone ph-key text-2xl"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-text-main">
                {{ __('Update Password') }}
            </h2>
            <p class="text-xs text-text-muted mt-0.5">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </div>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-5">
        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-lock text-lg"></i>
                </div>
                <x-text-input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="block w-full pl-10" autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-lock-key text-lg"></i>
                </div>
                <x-text-input wire:model="password" id="update_password_password" name="password" type="password" class="block w-full pl-10" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-lock-key text-lg"></i>
                </div>
                <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="block w-full pl-10" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="px-6 py-2.5 text-xs font-bold uppercase tracking-wider">
                <span class="flex items-center gap-1">
                    <i class="ph ph-floppy-disk text-sm"></i>
                    <span>{{ __('Save') }}</span>
                </span>
            </x-primary-button>

            <x-action-message class="text-xs font-semibold text-emerald-800 flex items-center gap-1" on="password-updated">
                <i class="ph ph-check-circle"></i>
                <span>{{ __('Saved.') }}</span>
            </x-action-message>
        </div>
    </form>
</section>
