<?php

use function Livewire\Volt\{state, rules, computed, on};
use App\Models\Trip;
use App\Models\Invitation;

state([
    'showCreateModal' => fn () => request()->query('create') == 1,
    'name' => '',
    'destination' => '',
    'start_date' => '',
    'end_date' => '',
    'description' => '',
    'budget_estimate' => 0,
    'searchQuery' => '',
    'filterStatus' => 'All',
    'viewMode' => 'grid',
]);

rules([
    'name' => 'required|string|max:255',
    'destination' => 'required|string|max:255',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after_or_equal:start_date',
    'description' => 'nullable|string',
    'budget_estimate' => 'required|numeric|min:0',
]);

on(['invitation-updated' => function () {}]);

$pendingInvitations = computed(function () {
    $user = auth()->user();
    if (!$user) return collect();
    return Invitation::where('email', $user->email)
        ->where('status', 'pending')
        ->with('trip', 'inviter')
        ->get();
});

$acceptInvitation = function ($invitationId) {
    $invitation = Invitation::findOrFail($invitationId);
    $user = auth()->user();

    if (!$invitation->trip->users()->where('user_id', $user->id)->exists()) {
        $invitation->trip->users()->attach($user->id, ['role' => $invitation->role]);
    }

    $invitation->update(['status' => 'accepted']);

    $this->dispatch('invitation-updated');
    session()->flash('status', 'You have successfully joined the trip: ' . $invitation->trip->name);
    $this->filterStatus = 'All';
};

$rejectInvitation = function ($invitationId) {
    $invitation = Invitation::findOrFail($invitationId);
    $invitation->update(['status' => 'rejected']);

    $this->dispatch('invitation-updated');
    session()->flash('status', 'Invitation declined.');
};

$trips = computed(function () {
    $user = auth()->user();
    if (!$user) return collect();

    if ($this->filterStatus === 'Invites') {
        return collect();
    }

    $query = $user->trips();

    $today = now()->startOfDay()->toDateString();
    if ($this->filterStatus === 'Upcoming') {
        $query->where('end_date', '>=', $today);
    } elseif ($this->filterStatus === 'Planning') {
        $query->where('start_date', '>', $today);
    } elseif ($this->filterStatus === 'Completed') {
        $query->where('end_date', '<', $today);
    } elseif ($this->filterStatus === 'Groups') {
        $query->where('creator_id', '!=', $user->id);
    }

    if (!empty($this->searchQuery)) {
        $search = '%' . $this->searchQuery . '%';
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', $search)
              ->orWhere('destination', 'like', $search)
              ->orWhereHas('users', function ($uq) use ($search) {
                  $uq->where('name', 'like', $search);
              });
        });
    }

    return $query->orderBy('start_date', 'asc')->get();
});

$stats = computed(function () {
    $user = auth()->user();
    if (!$user) {
        return [
            'upcoming' => 0,
            'invites' => 0,
            'completed' => 0,
            'groups' => 0,
        ];
    }

    $allTrips = $user->trips()->get();
    $today = now()->startOfDay();

    $upcoming = $allTrips->filter(fn($t) => $t->end_date->gte($today))->count();
    $completed = $allTrips->filter(fn($t) => $t->end_date->lt($today))->count();
    $groups = $allTrips->filter(fn($t) => $t->creator_id !== $user->id)->count();

    $invites = Invitation::where('email', $user->email)
        ->where('status', 'pending')
        ->count();

    return [
        'upcoming' => $upcoming,
        'invites' => $invites,
        'completed' => $completed,
        'groups' => $groups,
    ];
});

$createTrip = function () {
    $this->validate();

    $trip = Trip::create([
        'name' => $this->name,
        'destination' => $this->destination,
        'start_date' => $this->start_date,
        'end_date' => $this->end_date,
        'description' => $this->description,
        'budget_estimate' => $this->budget_estimate,
        'creator_id' => auth()->id(),
    ]);

    // Attach creator as Organizer
    $trip->users()->attach(auth()->id(), ['role' => 'organizer']);

    $this->reset(['name', 'destination', 'start_date', 'end_date', 'description', 'budget_estimate', 'showCreateModal']);

    return redirect()->route('trips.show', $trip->id);
};

$getTripImage = function ($destination) {
    $dest = strtolower($destination);
    if (str_contains($dest, 'bali')) {
        return 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80';
    } elseif (str_contains($dest, 'kyoto')) {
        return 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=600&q=80';
    } elseif (str_contains($dest, 'patagonia')) {
        return 'https://images.unsplash.com/photo-1517411032315-54ef2cb783bb?auto=format&fit=crop&w=600&q=80';
    } elseif (str_contains($dest, 'paris')) {
        return 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=600&q=80';
    } elseif (str_contains($dest, 'manali')) {
        return 'https://images.unsplash.com/photo-1596701062351-8c2c14d1fdd0?auto=format&fit=crop&w=600&q=80';
    } elseif (str_contains($dest, 'assam')) {
        return 'https://images.unsplash.com/photo-1582298538104-fc2c0c567793?auto=format&fit=crop&w=600&q=80';
    } elseif (str_contains($dest, 'shillong')) {
        return 'https://images.unsplash.com/photo-1588880331149-6ee5b291d3b7?auto=format&fit=crop&w=600&q=80';
    }
    return 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=600&q=80';
};

?>

<div class="py-12 bg-bg-secondary min-h-screen text-text-main font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">
        
        <!-- Minimal small Header & Search block -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 pb-2">
            <div class="max-w-xl">
                <p class="font-serif-display italic text-brand-neutral text-lg">Plan. Connect. Explore.</p>
                <h1 class="text-4xl font-extrabold tracking-tight font-sans-display text-text-main mt-1">Your journeys, beautifully organized.</h1>
                <p class="text-text-muted text-sm mt-2 leading-relaxed">Plan itineraries, collaborate with your group, and make every trip unforgettable.</p>
            </div>
            
            <!-- Search bar -->
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="searchQuery" 
                       placeholder="Search trips, destinations..." 
                       class="block w-full pl-10 pr-4 py-2.5 bg-bg-primary border border-border-card rounded-full text-xs placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral shadow-none transition">
            </div>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="p-4 bg-bg-primary border border-border-card rounded-xl text-sm text-text-main font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- "At a glance" Stats Banner -->
        <div class="bg-bg-primary border border-border-light rounded-2xl p-6 md:p-8 flex flex-col lg:flex-row justify-between items-stretch gap-6 shadow-none">
            <!-- Left Section: Header label -->
            <div class="lg:w-1/5 flex items-center border-b lg:border-b-0 lg:border-r border-border-light pb-4 lg:pb-0 lg:pr-6">
                <span class="font-serif-display italic text-2xl text-brand-neutral">At a glance</span>
            </div>

            <!-- Middle Section: Stats Grid -->
            <div class="flex-grow grid grid-cols-2 sm:grid-cols-4 gap-4 items-center px-2">
                <button wire:click="$set('filterStatus', 'Upcoming')" 
                        class="flex items-start space-x-3 text-left focus:outline-none group/stat cursor-pointer w-full p-2 rounded-xl transition duration-150 {{ $filterStatus === 'Upcoming' ? 'bg-bg-secondary border border-border-card' : 'border border-transparent hover:bg-bg-secondary/50' }}">
                    <!-- Icon -->
                    <div class="p-2.5 bg-brand-neutral/5 rounded-xl text-brand-neutral group-hover/stat:bg-brand-neutral group-hover/stat:text-bg-primary transition duration-150 shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-2xl font-extrabold text-brand-neutral font-sans-display leading-none block">{{ $this->stats['upcoming'] }}</span>
                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mt-1 group-hover/stat:text-text-main transition duration-150">Upcoming</span>
                    </div>
                </button>

                <button wire:click="$set('filterStatus', 'Invites')" 
                        class="flex items-start space-x-3 text-left focus:outline-none group/stat cursor-pointer w-full p-2 rounded-xl transition duration-150 {{ $filterStatus === 'Invites' ? 'bg-bg-secondary border border-border-card' : 'border border-transparent hover:bg-bg-secondary/50' }}">
                    <!-- Icon -->
                    <div class="p-2.5 bg-brand-neutral/5 rounded-xl text-brand-neutral group-hover/stat:bg-brand-neutral group-hover/stat:text-bg-primary transition duration-150 shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-2xl font-extrabold text-brand-neutral font-sans-display leading-none block">{{ $this->stats['invites'] }}</span>
                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mt-1 group-hover/stat:text-text-main transition duration-150">Invites</span>
                    </div>
                </button>

                <button wire:click="$set('filterStatus', 'Completed')" 
                        class="flex items-start space-x-3 text-left focus:outline-none group/stat cursor-pointer w-full p-2 rounded-xl transition duration-150 {{ $filterStatus === 'Completed' ? 'bg-bg-secondary border border-border-card' : 'border border-transparent hover:bg-bg-secondary/50' }}">
                    <!-- Icon -->
                    <div class="p-2.5 bg-brand-neutral/5 rounded-xl text-brand-neutral group-hover/stat:bg-brand-neutral group-hover/stat:text-bg-primary transition duration-150 shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-2xl font-extrabold text-brand-neutral font-sans-display leading-none block">{{ $this->stats['completed'] }}</span>
                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mt-1 group-hover/stat:text-text-main transition duration-150">Completed</span>
                    </div>
                </button>

                <button wire:click="$set('filterStatus', 'Groups')" 
                        class="flex items-start space-x-3 text-left focus:outline-none group/stat cursor-pointer w-full p-2 rounded-xl transition duration-150 {{ $filterStatus === 'Groups' ? 'bg-bg-secondary border border-border-card' : 'border border-transparent hover:bg-bg-secondary/50' }}">
                    <!-- Icon -->
                    <div class="p-2.5 bg-brand-neutral/5 rounded-xl text-brand-neutral group-hover/stat:bg-brand-neutral group-hover/stat:text-bg-primary transition duration-150 shrink-0">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-2xl font-extrabold text-brand-neutral font-sans-display leading-none block">{{ $this->stats['groups'] }}</span>
                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mt-1 group-hover/stat:text-text-main transition duration-150">Groups</span>
                    </div>
                </button>
            </div>

            <!-- Right Section: Quote -->
            <div class="lg:w-1/3 flex items-center border-t lg:border-t-0 lg:border-l border-border-light pt-4 lg:pt-0 lg:pl-6 text-text-muted">
                <blockquote class="italic text-xs font-serif-display leading-relaxed border-l-2 border-brand-neutral pl-3 py-1">
                    "Travel is the only thing you buy that makes you richer."
                </blockquote>
            </div>
        </div>

        <!-- Filter Row: Title, Filters & Grid/List toggles -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-border-light pb-4">
            <div class="flex items-center space-x-2">
                <h2 class="text-xl font-bold font-sans-display text-text-main">Your Trips</h2>
                <svg class="h-5 w-5 text-text-muted stroke-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                <!-- Status tabs -->
                <div class="flex flex-wrap bg-bg-primary p-1 rounded-xl border border-border-light text-xs font-semibold gap-1">
                    @foreach(['All', 'Upcoming', 'Planning', 'Completed', 'Groups', 'Invites'] as $statusTab)
                        <button wire:click="$set('filterStatus', '{{ $statusTab }}')"
                                class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 {{ $filterStatus === $statusTab ? 'bg-brand-neutral text-bg-primary shadow-none' : 'text-text-muted hover:text-text-main' }}">
                            <span>{{ $statusTab }}</span>
                            @if ($statusTab === 'Invites' && $this->stats['invites'] > 0)
                                <span class="px-1.5 py-0.5 text-[9px] font-bold rounded-full {{ $filterStatus === 'Invites' ? 'bg-bg-primary text-brand-neutral' : 'bg-brand-neutral text-bg-primary' }}">
                                    {{ $this->stats['invites'] }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <!-- Grid/List View Toggles -->
                <div class="flex bg-bg-primary p-1 rounded-xl border border-border-light text-xs font-semibold">
                    <button wire:click="$set('viewMode', 'grid')"
                            class="p-1.5 rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-brand-neutral text-bg-primary shadow-none' : 'text-text-muted hover:text-text-main' }}"
                            title="Grid View">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button wire:click="$set('viewMode', 'list')"
                            class="p-1.5 rounded-lg transition-all {{ $viewMode === 'list' ? 'bg-brand-neutral text-bg-primary shadow-none' : 'text-text-muted hover:text-text-main' }}"
                            title="List View">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Trips Display Area -->
        @if ($filterStatus === 'Invites')
            @if ($this->pendingInvitations->isEmpty())
                <div class="bg-bg-primary border border-border-light rounded-2xl p-16 text-center shadow-none w-full">
                    <svg class="mx-auto h-12 w-12 text-text-muted stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="mt-4 text-lg font-bold">No pending invitations</h3>
                    <p class="mt-1 text-sm text-text-muted">You don't have any pending invites to join other trips.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($this->pendingInvitations as $invite)
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start">
                                    <span class="text-[10px] text-brand-neutral font-bold tracking-wide uppercase">{{ $invite->trip->destination }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-bg-secondary border border-border-light rounded-full text-text-main capitalize">
                                        As {{ str_replace('_', ' ', $invite->role) }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-xl mt-2 text-text-main">{{ $invite->trip->name }}</h3>
                                <p class="text-xs text-text-muted mt-2">
                                    Invited by <strong class="text-text-main font-semibold">{{ $invite->inviter->name }}</strong> ({{ $invite->inviter->email }})
                                </p>
                                <p class="text-xs text-text-muted mt-1">
                                    Dates: {{ $invite->trip->start_date->format('M d, Y') }} - {{ $invite->trip->end_date->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex space-x-3 mt-6 pt-4 border-t border-border-light">
                                <button wire:click="acceptInvitation({{ $invite->id }})" 
                                        class="flex-1 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold py-2.5 px-4 rounded-xl transition text-center shadow-none cursor-pointer">
                                    Accept Invite
                                </button>
                                <button wire:click="rejectInvitation({{ $invite->id }})" 
                                        class="flex-1 bg-bg-secondary hover:bg-border-light text-text-main border border-border-card text-xs font-bold py-2.5 px-4 rounded-xl transition text-center shadow-none cursor-pointer">
                                    Ignore
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            @if ($this->trips->isEmpty())
                <div class="bg-bg-primary border border-border-light rounded-2xl p-16 text-center shadow-none w-full">
                    <svg class="mx-auto h-12 w-12 text-text-muted stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <h3 class="mt-4 text-lg font-bold">No trips found</h3>
                    <p class="mt-1 text-sm text-text-muted">Create a new trip or modify your filters/search to get started.</p>
                    <button type="button" 
                            wire:click="$set('showCreateModal', true)"
                            class="mt-6 px-6 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition shadow-none cursor-pointer">
                        Create a Trip
                    </button>
                </div>
            @else
            @if ($viewMode === 'grid')
                <!-- Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($this->trips as $trip)
                        @php
                            $role = $trip->pivot->role;
                            $status = 'Planning';
                            $today = now()->startOfDay();
                            if ($today->between($trip->start_date, $trip->end_date)) {
                                $status = 'Ongoing';
                            } elseif ($today->gt($trip->end_date)) {
                                $status = 'Completed';
                            }
                        @endphp
                        <a href="{{ route('trips.show', $trip->id) }}" 
                           class="group block bg-bg-primary border border-border-light rounded-2xl overflow-hidden hover:shadow-md transition duration-200"
                           wire:navigate>
                            <!-- Cover Image -->
                            <div class="bg-bg-secondary h-44 w-full flex items-center justify-center border-b border-border-light relative overflow-hidden">
                                <div class="absolute inset-0 bg-cover bg-center filter brightness-95 transition group-hover:scale-105 duration-300" style="background-image: url('{{ $this->getTripImage($trip->destination) }}')"></div>
                                <div class="absolute top-3 left-3 bg-bg-primary/95 backdrop-blur-sm border border-border-light px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm text-text-main">
                                    {{ $status }}
                                </div>
                            </div>
                            <div class="p-6">
                                <p class="text-[10px] text-brand-neutral font-bold tracking-wide uppercase">{{ $trip->destination }}</p>
                                <h3 class="font-bold text-xl mt-1 text-text-main group-hover:text-brand-neutral transition">{{ $trip->name }}</h3>
                                
                                <div class="flex items-center text-xs text-text-muted mt-3 space-x-4">
                                    <div class="flex items-center space-x-1">
                                        <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $trip->start_date->format('M d, Y') }} - {{ $trip->end_date->format('M d, Y') }}</span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center mt-6 pt-4 border-t border-border-light">
                                    <span class="text-xs font-medium text-text-muted uppercase">Role: <strong class="text-text-main font-semibold capitalize">{{ $role }}</strong></span>
                                    <div class="flex -space-x-2">
                                        @foreach($trip->users->take(4) as $u)
                                            <div class="h-6 w-6 rounded-full bg-bg-secondary border-2 border-bg-primary flex items-center justify-center font-bold text-[10px] uppercase text-text-main" title="{{ $u->name }}">
                                                {{ $u->name[0] }}
                                            </div>
                                        @endforeach
                                        @if($trip->users->count() > 4)
                                            <div class="h-6 w-6 rounded-full bg-bg-primary border-2 border-bg-primary flex items-center justify-center font-bold text-[10px] text-text-muted">
                                                +{{ $trip->users->count() - 4 }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <!-- List Layout -->
                <div class="space-y-4">
                    @foreach ($this->trips as $trip)
                        @php
                            $role = $trip->pivot->role;
                            $status = 'Planning';
                            $today = now()->startOfDay();
                            if ($today->between($trip->start_date, $trip->end_date)) {
                                $status = 'Ongoing';
                            } elseif ($today->gt($trip->end_date)) {
                                $status = 'Completed';
                            }
                        @endphp
                        <div class="bg-bg-primary border border-border-light rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:shadow-sm transition">
                            <div class="flex items-center space-x-4">
                                <div class="h-16 w-16 rounded-xl bg-cover bg-center shrink-0 border border-border-light" style="background-image: url('{{ $this->getTripImage($trip->destination) }}')"></div>
                                <div>
                                    <span class="text-[10px] text-brand-neutral font-bold uppercase tracking-wider">{{ $trip->destination }}</span>
                                    <h3 class="font-bold text-lg text-text-main leading-tight mt-0.5">{{ $trip->name }}</h3>
                                    <p class="text-xs text-text-muted flex items-center mt-1">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $trip->start_date->format('M d, Y') }} - {{ $trip->end_date->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 sm:gap-6 w-full sm:w-auto sm:justify-end">
                                <div>
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border border-border-light bg-bg-secondary text-text-main">
                                        {{ $status }}
                                    </span>
                                </div>

                                <div class="text-xs">
                                    <span class="text-text-muted">Role: </span>
                                    <span class="font-bold capitalize text-text-main">{{ $role }}</span>
                                </div>

                                <div class="flex -space-x-2">
                                    @foreach($trip->users->take(4) as $u)
                                        <div class="h-6 w-6 rounded-full bg-bg-secondary border-2 border-bg-primary flex items-center justify-center font-bold text-[10px] uppercase text-text-main" title="{{ $u->name }}">
                                            {{ $u->name[0] }}
                                        </div>
                                    @endforeach
                                    @if($trip->users->count() > 4)
                                        <div class="h-6 w-6 rounded-full bg-bg-primary border-2 border-bg-primary flex items-center justify-center font-bold text-[10px] text-text-muted">
                                            +{{ $trip->users->count() - 4 }}
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <a href="{{ route('trips.show', $trip->id) }}" 
                                       class="px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-none"
                                       wire:navigate>
                                        <span>Workspace</span>
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    @endif
</div>

    <!-- Create Trip Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
            <div class="bg-bg-primary border border-border-light w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-8 animate-fade-in font-sans">
                <div class="flex justify-between items-center pb-4 border-b border-border-light mb-6">
                    <h2 class="text-xl font-bold text-text-main">Create a New Trip</h2>
                    <button type="button" 
                            wire:click="$set('showCreateModal', false)"
                            class="text-text-muted hover:text-text-main transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="createTrip" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-text-main">Trip Name</label>
                        <input type="text" id="name" wire:model="name" placeholder="e.g. Summer Vacation"
                               class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none">
                        @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="destination" class="block text-sm font-semibold text-text-main">Destination</label>
                        <input type="text" id="destination" wire:model="destination" placeholder="e.g. Barcelona, Spain"
                               class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none">
                        @error('destination') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-semibold text-text-main">Start Date</label>
                            <input type="date" id="start_date" wire:model="start_date"
                                   class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none">
                            @error('start_date') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-semibold text-text-main">End Date</label>
                            <input type="date" id="end_date" wire:model="end_date"
                                   class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none">
                            @error('end_date') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="budget_estimate" class="block text-sm font-semibold text-text-main">Estimated Budget ($)</label>
                        <input type="number" id="budget_estimate" wire:model="budget_estimate" placeholder="e.g. 1500"
                               class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none">
                        @error('budget_estimate') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-text-main">Description (optional)</label>
                        <textarea id="description" wire:model="description" rows="3" placeholder="Briefly describe the trip theme..."
                                  class="mt-1 block w-full px-4 py-2.5 border border-border-card rounded-xl text-sm bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral focus:outline-none"></textarea>
                        @error('description') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-6 border-t border-border-light mt-6">
                        <button type="button" 
                                wire:click="$set('showCreateModal', false)"
                                class="px-5 py-2.5 border border-border-card rounded-xl text-sm font-semibold text-text-main hover:bg-bg-secondary transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition shadow-none">
                            Create Trip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
