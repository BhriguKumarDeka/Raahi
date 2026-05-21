<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-8 text-center">
        <h2 class="font-serif-display font-bold text-2xl text-text-main">Start Your Journey</h2>
        <p class="text-xs text-text-muted mt-1.5 font-medium">Create a free account to begin planning together</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-user text-lg"></i>
                </div>
                <x-text-input wire:model="name" id="name" class="block w-full pl-10" type="text" name="name" required autofocus autocomplete="name" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-envelope text-lg"></i>
                </div>
                <x-text-input wire:model="email" id="email" class="block w-full pl-10" type="email" name="email" required autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-lock text-lg"></i>
                </div>
                <x-text-input wire:model="password" id="password" class="block w-full pl-10"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-lock-key text-lg"></i>
                </div>
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full pl-10"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-4">
            <x-primary-button class="w-full justify-center py-3 text-xs uppercase tracking-wider font-bold">
                {{ __('Create Account') }}
            </x-primary-button>
        </div>

        <div class="text-center pt-2">
            <p class="text-xs text-text-muted">
                Already have an account? 
                <a href="{{ route('login') }}" wire:navigate class="font-bold text-brand-neutral hover:underline">
                    Sign in instead
                </a>
            </p>
        </div>
    </form>
</div>
