<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-6 text-center">
        <h2 class="font-serif-display font-bold text-2xl text-text-main">Reset Password</h2>
        <p class="text-xs text-text-muted mt-1.5 font-medium leading-relaxed">
            Forgot your password? No problem. Just let us know your email address and we will mail you a link to choose a new one.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-envelope text-lg"></i>
                </div>
                <x-text-input wire:model="email" id="email" class="block w-full pl-10" type="email" name="email" required autofocus />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 text-xs uppercase tracking-wider font-bold">
                {{ __('Email Reset Link') }}
            </x-primary-button>
        </div>

        <div class="text-center pt-2">
            <p class="text-xs text-text-muted">
                Remember your password? 
                <a href="{{ route('login') }}" wire:navigate class="font-bold text-brand-neutral hover:underline">
                    Sign in
                </a>
            </p>
        </div>
    </form>
</div>
