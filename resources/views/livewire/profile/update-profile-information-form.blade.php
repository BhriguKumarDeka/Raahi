<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $profile_image_upload = null;

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

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ];

        if ($this->profile_image_upload) {
            $rules['profile_image_upload'] = ['image', 'max:2048'];
        }

        $validated = $this->validate($rules);

        // Handle avatar upload
        if ($this->profile_image_upload) {
            // Delete old avatar if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $path = $this->profile_image_upload->store('avatars', 'public');
            $user->profile_image = $path;
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->profile_image_upload = null;

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
    <header class="pb-4 border-b border-border-light">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-brand-neutral/10 flex items-center justify-center text-brand-neutral ring-2 ring-brand-neutral/20">
                <i class="ph-duotone ph-user-circle text-2xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-text-main flex items-center gap-2">
                    {{ __('Profile Information') }}
                    <span class="inline-block w-2 h-2 rounded-full bg-brand-neutral"></span>
                </h2>
                <p class="text-xs text-text-muted mt-0.5">
                    {{ __("Update your account's profile information and email address.") }}
                </p>
            </div>
        </div>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">

        <!-- Avatar Upload Section -->
        <div class="flex flex-col items-center sm:flex-row sm:items-start gap-5 p-5 bg-bg-secondary/50 border border-border-light rounded-2xl">
            <!-- Avatar Preview -->
            <div class="relative group">
                <div class="h-24 w-24 rounded-full overflow-hidden border-2 border-border-card shadow-sm transition-all group-hover:border-brand-neutral/40">
                    @if($profile_image_upload)
                        <img src="{{ $profile_image_upload->temporaryUrl() }}" alt="Preview" class="h-full w-full object-cover">
                    @elseif(auth()->user()->profile_image)
                        <img src="{{ Storage::url(auth()->user()->profile_image) }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full bg-brand-neutral/10 flex items-center justify-center">
                            <span class="text-3xl font-bold text-brand-neutral uppercase">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upload Controls -->
            <div class="flex-1 flex flex-col justify-center gap-2 text-center sm:text-left">
                <h3 class="text-sm font-bold text-text-main">Profile Photo</h3>
                <p class="text-[11px] text-text-muted">JPG, PNG or WebP. Max 2MB.</p>
                <label class="inline-flex items-center gap-1.5 px-4 py-2 bg-bg-primary border border-border-card rounded-xl text-xs font-semibold text-text-main hover:border-brand-neutral hover:text-brand-neutral transition cursor-pointer w-fit">
                    <i class="ph ph-upload-simple text-sm"></i>
                    <span>Upload Photo</span>
                    <input type="file" wire:model="profile_image_upload" accept="image/*" class="hidden">
                </label>
                @error('profile_image_upload')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                @if($profile_image_upload)
                    <p class="text-[11px] text-brand-neutral font-semibold flex items-center gap-1">
                        <i class="ph ph-check-circle text-xs"></i>
                        New photo selected -- save to apply.
                    </p>
                @endif
            </div>
        </div>

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
