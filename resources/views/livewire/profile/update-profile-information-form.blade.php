<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header class="flex items-center space-x-3 pb-4 border-b border-border-light">
        <div class="w-10 h-10 rounded-full bg-brand-neutral/5 flex items-center justify-center text-brand-neutral">
            <i class="ph-duotone ph-user-circle text-2xl"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-text-main">
                {{ __('Profile Information') }}
            </h2>
            <p class="text-xs text-text-muted mt-0.5">
                {{ __("Update your account's profile information and email address.") }}
            </p>
        </div>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-5">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-user text-lg"></i>
                </div>
                <x-text-input wire:model="name" id="name" name="name" type="text" class="block w-full pl-10" required autofocus autocomplete="name" />
            </div>
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                    <i class="ph ph-envelope text-lg"></i>
                </div>
                <x-text-input wire:model="email" id="email" name="email" type="email" class="block w-full pl-10" required autocomplete="username" />
            </div>
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-3 bg-amber-50/50 border border-amber-200/50 rounded-xl p-3">
                    <p class="text-xs text-amber-800 flex items-center gap-1.5">
                        <i class="ph ph-warning"></i>
                        <span>{{ __('Your email address is unverified.') }}</span>
                    </p>
                    <button wire:click.prevent="sendVerification" class="mt-1 text-xs text-brand-neutral font-bold hover:underline focus:outline-none">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1.5 font-bold text-xs text-emerald-800 flex items-center gap-1">
                            <i class="ph ph-check-circle"></i>
                            <span>{{ __('A new verification link has been sent to your email address.') }}</span>
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="px-6 py-2.5 text-xs font-bold uppercase tracking-wider">
                <span class="flex items-center gap-1">
                    <i class="ph ph-floppy-disk text-sm"></i>
                    <span>{{ __('Save') }}</span>
                </span>
            </x-primary-button>

            <x-action-message class="text-xs font-semibold text-emerald-800 flex items-center gap-1" on="profile-updated">
                <i class="ph ph-check-circle"></i>
                <span>{{ __('Saved.') }}</span>
            </x-action-message>
        </div>
    </form>
</section>
