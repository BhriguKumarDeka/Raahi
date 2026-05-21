<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header class="flex items-center space-x-3 pb-4 border-b border-border-light">
        <div class="w-10 h-10 rounded-full bg-red-500/5 flex items-center justify-center text-red-600">
            <i class="ph-duotone ph-warning-octagon text-2xl"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-text-main">
                {{ __('Delete Account') }}
            </h2>
            <p class="text-xs text-text-muted mt-0.5">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
            </p>
        </div>
    </header>

    <div class="bg-red-50/50 border border-red-200/40 rounded-2xl p-4">
        <p class="text-xs text-red-800 leading-relaxed">
            {{ __('Deleting your account is permanent and cannot be undone. Please download any data or information that you wish to retain before proceeding.') }}
        </p>
        <div class="mt-4">
            <x-danger-button
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl"
            >
                <span class="flex items-center gap-1.5">
                    <i class="ph ph-trash text-sm"></i>
                    <span>{{ __('Delete Account') }}</span>
                </span>
            </x-danger-button>
        </div>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6 sm:p-8 space-y-4">
            <div class="flex items-center space-x-3 text-red-600">
                <i class="ph ph-warning-circle text-2xl"></i>
                <h2 class="text-lg font-bold text-text-main">
                    {{ __('Are you sure you want to delete your account?') }}
                </h2>
            </div>

            <p class="text-xs text-text-muted leading-relaxed">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div>
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <div class="relative mt-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-text-muted/60">
                        <i class="ph ph-lock text-lg"></i>
                    </div>
                    <x-text-input
                        wire:model="password"
                        id="password"
                        name="password"
                        type="password"
                        class="block w-full pl-10"
                        placeholder="{{ __('Confirm Password') }}"
                    />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-border-light">
                <x-secondary-button x-on:click="$dispatch('close')" class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl">
                    {{ __('Permanently Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
