<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-8 text-center">
        <h2 class="font-serif-display font-bold text-2xl text-text-main">Welcome Back</h2>
        <p class="text-xs text-text-muted mt-1.5">Sign in to co-plan and share travel memories</p>
    </div>

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-envelope text-lg"></i>
                </div>
                <x-text-input wire:model="form.email" id="email" class="block w-full pl-10" type="email" name="email" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-text-muted hover:text-brand-neutral transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-lock text-lg"></i>
                </div>
                <x-text-input wire:model="form.password" id="password" class="block w-full pl-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember" class="inline-flex items-center cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-border-card text-brand-neutral bg-bg-primary focus:ring-brand-neutral focus:ring-offset-0 shadow-none cursor-pointer w-4 h-4 transition duration-150" name="remember">
                <span class="ms-2 text-xs text-text-muted select-none">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 text-xs uppercase tracking-wider font-bold">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="text-center pt-2">
            <p class="text-xs text-text-muted">
                New to Raahi? 
                <a href="{{ route('register') }}" wire:navigate class="font-bold text-brand-neutral hover:underline">
                    Create an account
                </a>
            </p>
        </div>
    </form>
</div>
