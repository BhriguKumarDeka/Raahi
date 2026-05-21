<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use App\Models\Invitation;

new class extends Component
{
    protected $listeners = [
        'invitation-updated' => '$refresh',
    ];

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    /**
     * Get pending invitations.
     */
    public function getInvitations()
    {
        if (!auth()->check()) {
            return collect();
        }
        return Invitation::where('email', auth()->user()->email)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Accept invitation.
     */
    public function acceptInvitation($invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);
        $user = auth()->user();

        if (!$invitation->trip->users()->where('user_id', $user->id)->exists()) {
            $invitation->trip->users()->attach($user->id, ['role' => $invitation->role]);
        }

        $invitation->update(['status' => 'accepted']);

        $this->dispatch('invitation-updated');
        $this->redirect(route('trips.show', $invitation->trip_id), navigate: true);
    }

    /**
     * Reject invitation.
     */
    public function rejectInvitation($invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);
        $invitation->update(['status' => 'rejected']);

        $this->dispatch('invitation-updated');
    }
}; ?>

<nav x-data="{ open: false }" class="bg-bg-primary border-b border-border-light font-sans">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="font-display font-bold text-xl tracking-tight text-text-main flex items-center space-x-2" wire:navigate>
                        <i class="ph-duotone ph-map-pin text-brand-neutral text-2xl"></i>
                        <span>Raahi</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:-my-px sm:flex sm:space-x-6 h-full">
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out {{ request()->routeIs('dashboard') ? 'border-brand-neutral text-text-main font-semibold' : 'border-transparent text-text-muted hover:text-text-main hover:border-border-card' }}" 
                       wire:navigate>
                        Trips
                    </a>
                    
                    <a href="{{ route('explore') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out {{ request()->routeIs('explore') ? 'border-brand-neutral text-text-main font-semibold' : 'border-transparent text-text-muted hover:text-text-main hover:border-border-card' }}" 
                       wire:navigate>
                        Explore
                    </a>
                    
                    @if (auth()->user() && auth()->user()->isSystemAdmin())
                        <a href="{{ route('admin.dashboard') }}" 
                           class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out {{ request()->routeIs('admin.dashboard') ? 'border-brand-neutral text-text-main font-semibold' : 'border-transparent text-text-muted hover:text-text-main hover:border-border-card' }}" 
                           wire:navigate>
                            Admin Panel
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-3">
                <!-- New Trip Button -->
                <a href="{{ route('dashboard', ['create' => 1]) }}" class="inline-flex items-center px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold rounded-xl transition duration-150 ease-in-out shadow-none hover:scale-[1.02] mr-1" wire:navigate>
                    <i class="ph-bold ph-plus mr-1"></i>
                    <span>New Trip</span>
                </a>

                <!-- Notification Bell Dropdown -->
                <div class="relative">
                    <x-dropdown align="right" width="w-80" contentClasses="py-2 bg-bg-primary border border-border-light rounded-2xl shadow-xl">
                        <x-slot name="trigger">
                            <button class="relative p-2 text-text-muted hover:text-text-main hover:bg-bg-secondary rounded-full transition duration-150 ease-in-out focus:outline-none border border-border-light bg-bg-primary">
                                <i class="ph ph-bell text-lg block"></i>
                                @php
                                    $invitations = $this->getInvitations();
                                    $inviteCount = $invitations->count();
                                @endphp
                                @if ($inviteCount > 0)
                                    <span class="absolute top-1 right-1 flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-neutral opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-neutral"></span>
                                    </span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 border-b border-border-light flex justify-between items-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-text-muted">Pending Invites</span>
                                @if ($inviteCount > 0)
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-brand-neutral text-bg-primary rounded-full">
                                        {{ $inviteCount }} new
                                    </span>
                                @endif
                            </div>
                            
                            <div class="max-h-64 overflow-y-auto divide-y divide-border-light">
                                @if ($invitations->isEmpty())
                                    <div class="px-4 py-6 text-center text-xs text-text-muted">
                                        No pending invitations.
                                    </div>
                                @else
                                    @foreach ($invitations as $invite)
                                        <div class="p-4 space-y-2">
                                            <p class="text-xs text-text-main font-medium">
                                                <span class="font-bold text-text-main">{{ $invite->inviter->name }}</span> invited you to join:
                                            </p>
                                            <div>
                                                <h4 class="text-sm font-bold text-text-main">{{ $invite->trip->name }}</h4>
                                                <p class="text-[11px] text-text-muted flex items-center mt-0.5">
                                                    <i class="ph ph-map-pin mr-1"></i>
                                                    {{ $invite->trip->destination }}
                                                </p>
                                            </div>
                                            <div class="flex space-x-2 pt-1">
                                                <button wire:click="acceptInvitation({{ $invite->id }})" 
                                                        class="flex-1 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-[10px] font-semibold py-1.5 px-3 rounded-lg transition text-center">
                                                    Accept
                                                </button>
                                                <button wire:click="rejectInvitation({{ $invite->id }})" 
                                                        class="flex-1 bg-bg-secondary hover:bg-border-light text-text-main border border-border-card text-[10px] font-semibold py-1.5 px-3 rounded-lg transition text-center">
                                                    Ignore
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-border-card text-sm font-medium rounded-full text-text-main bg-bg-primary hover:shadow-sm focus:outline-none transition duration-150 ease-in-out">
                             <div class="flex items-center space-x-2">
                                <i class="ph ph-list text-base text-text-muted"></i>
                                <div class="h-6 w-6 rounded-full bg-bg-secondary flex items-center justify-center font-bold text-xs uppercase border border-border-light" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name[0]" x-on:profile-updated.window="name = $event.detail.name"></div>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs text-text-muted font-semibold border-b border-border-light">
                            Manage Account
                        </div>
                        
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-text-muted hover:text-text-main hover:bg-bg-secondary focus:outline-none transition duration-150 ease-in-out">
                    <i class="ph text-2xl block" :class="open ? 'ph-x' : 'ph-list'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-border-light bg-bg-primary">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" 
               class="block py-2 px-4 text-base font-semibold text-text-main {{ request()->routeIs('dashboard') ? 'bg-bg-secondary' : '' }}" 
               wire:navigate>
                Trips
            </a>
            <a href="{{ route('explore') }}" 
               class="block py-2 px-4 text-base font-semibold text-text-main {{ request()->routeIs('explore') ? 'bg-bg-secondary' : '' }}" 
               wire:navigate>
                Explore
            </a>
            @if (auth()->user() && auth()->user()->isSystemAdmin())
                <a href="{{ route('admin.dashboard') }}" 
                   class="block py-2 px-4 text-base font-semibold text-text-main {{ request()->routeIs('admin.dashboard') ? 'bg-bg-secondary' : '' }}" 
                   wire:navigate>
                    Admin Panel
                </a>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-4 border-t border-border-light">
            <div class="px-4 flex items-center space-x-3">
                <div class="h-8 w-8 rounded-full bg-bg-secondary flex items-center justify-center font-bold text-sm uppercase border border-border-light">
                    {{ auth()->user()->name[0] }}
                </div>
                <div>
                    <div class="font-semibold text-text-main">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-text-muted">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>

        @php
            $invitations = $this->getInvitations();
        @endphp
        @if ($invitations->isNotEmpty())
            <div class="pt-4 pb-4 border-t border-border-light bg-bg-secondary px-4">
                <p class="text-xs font-bold text-text-muted uppercase tracking-wider mb-2">Pending Invites</p>
                <div class="space-y-3">
                    @foreach ($invitations as $invite)
                        <div class="p-3 bg-bg-primary rounded-xl border border-border-light space-y-2">
                            <p class="text-xs text-text-main font-semibold">
                                {{ $invite->inviter->name }} invited you to:
                            </p>
                            <p class="text-sm font-bold text-text-main">{{ $invite->trip->name }}</p>
                            <p class="text-xs text-text-muted">{{ $invite->trip->destination }}</p>
                            <div class="flex space-x-2 pt-1">
                                <button wire:click="acceptInvitation({{ $invite->id }})" 
                                        class="flex-1 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-semibold py-1.5 px-3 rounded-lg transition">
                                    Accept
                                </button>
                                <button wire:click="rejectInvitation({{ $invite->id }})" 
                                        class="flex-1 bg-bg-primary hover:bg-bg-secondary text-text-main border border-border-card text-xs font-semibold py-1.5 px-3 rounded-lg transition">
                                    Ignore
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</nav>
