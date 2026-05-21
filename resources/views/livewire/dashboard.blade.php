<?php

use function Livewire\Volt\{state, rules, computed, on};
use App\Models\Trip;
use App\Models\Invitation;

state([
    'showCreateModal' => fn() => request()->query('create') == 1,
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

    // Enforce single-use: only pending invitations can be accepted
    if ($invitation->status !== 'pending') {
        session()->flash('status', 'This invitation has already been ' . $invitation->status . '.');
        return;
    }

    // Check expiration
    if ($invitation->expires_at && $invitation->expires_at->isPast()) {
        $invitation->update(['status' => 'expired']);
        session()->flash('status', 'This invitation has expired.');
        $this->dispatch('invitation-updated');
        return;
    }

    // Verify email matches
    if (strtolower($user->email) !== strtolower($invitation->email)) {
        session()->flash('status', 'This invitation was sent to a different email address.');
        return;
    }

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

    return $query->with('users')->orderBy('start_date', 'asc')->get();
});

$stats = computed(function () {
    $user = auth()->user();
    if (!$user) {
        return [
            'upcoming' => 0,
            'invites' => 0,
            'completed' => 0,
            'groups' => 0,
            'daysToNextTrip' => null,
            'nextTripName' => null,
            'pendingDecisions' => 0,
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

    $nextTrip = $allTrips->filter(fn($t) => $t->start_date->gte($today))->sortBy('start_date')->first();
    $daysToNextTrip = $nextTrip ? $today->diffInDays($nextTrip->start_date) : null;
    $nextTripName = $nextTrip ? $nextTrip->name : null;

    // Calculate pending decisions across active trips
    $pendingDecisions = 0;
    $activeTrips = $allTrips->filter(fn($t) => $t->end_date->gte($today));
    foreach ($activeTrips as $trip) {
        $tripPolls = $trip->polls()->where('is_locked', false)->get();
        foreach ($tripPolls as $poll) {
            $hasVoted = $poll->votes()->where('user_id', $user->id)->exists();
            if (!$hasVoted) {
                $pendingDecisions++;
            }
        }
    }

    return [
        'upcoming' => $upcoming,
        'invites' => $invites,
        'completed' => $completed,
        'groups' => $groups,
        'daysToNextTrip' => $daysToNextTrip,
        'nextTripName' => $nextTripName,
        'pendingDecisions' => $pendingDecisions,
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

$deleteTrip = function ($tripId) {
    $trip = Trip::findOrFail($tripId);

    if (!$trip->canDelete(auth()->user())) {
        abort(403, 'Only the trip creator can delete this trip.');
    }

    $name = $trip->name;
    $trip->delete();

    unset($this->trips, $this->stats);
    session()->flash('status', "Trip '{$name}' has been deleted.");
};

$getTripImage = function ($destination) {
    return \App\Services\PexelsService::getTripImage($destination);
};

?>

<div class="py-10 bg-bg-secondary min-h-screen text-text-main font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">

        @if (session('status'))
        <div class="p-4 bg-bg-primary border border-border-card rounded-2xl text-sm text-text-main font-medium">
            {{ session('status') }}
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════
             2. "AT A GLANCE" BANNER — narrative left + static metric
                counters right (big bold numbers, NOT buttons)
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="bg-bg-primary border border-border-light rounded-3xl p-6 md:p-8 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 shadow-[0_4px_24px_rgba(26,59,43,0.03)]">
            {{-- Left — narrative --}}
            <div class="w-full lg:w-1/2 space-y-2.5">
                <span class="font-serif-display italic text-brand-neutral text-xl block">Your Journey at Raahi</span>
                <h3 class="text-2xl font-bold tracking-tight font-sans-display text-text-main leading-snug">
                    @if ($this->stats['daysToNextTrip'] !== null)
                    Your next adventure, <span class="italic text-brand-neutral font-serif-display font-medium">{{ $this->stats['nextTripName'] }}</span>, starts in <span class="text-brand-neutral font-extrabold">{{ $this->stats['daysToNextTrip'] }} days</span>!
                    @else
                    Where will you and your friends explore next?
                    @endif
                </h3>
                <p class="text-text-muted text-xs leading-relaxed">
                    @if ($this->stats['pendingDecisions'] > 0)
                    There are <strong class="text-brand-neutral font-semibold">{{ $this->stats['pendingDecisions'] }} pending group decisions</strong> awaiting your response.
                    @else
                    All collaborative decisions are currently resolved.
                    @endif
                </p>
            </div>

            {{-- Right — static metric counters (2x2 grid) --}}
            <div class="w-full lg:w-1/2 grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-2 gap-3">
                {{-- Upcoming --}}
                <div class="bg-bg-secondary/60 border border-border-light rounded-2xl p-4 text-center">
                    <div class="flex items-center justify-center gap-2 mb-1">
                    </div>
                    <span class="text-3xl font-extrabold text-text-main leading-none block">{{ $this->stats['upcoming'] }}</span>
                    <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider mt-1.5 block">Upcoming {{ Str::plural('Trip', $this->stats['upcoming']) }}</span>
                </div>

                {{-- Invitations --}}
                <div class="bg-bg-secondary/60 border border-border-light rounded-2xl p-4 text-center">
                    <div class="flex items-center justify-center gap-2 mb-1">
                    </div>
                    <span class="text-3xl font-extrabold text-text-main leading-none block">{{ $this->stats['invites'] }}</span>
                    <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider mt-1.5 block">Pending {{ Str::plural('Invite', $this->stats['invites']) }}</span>
                </div>

                {{-- Completed --}}
                <div class="bg-bg-secondary/60 border border-border-light rounded-2xl p-4 text-center">
                    <div class="flex items-center justify-center gap-2 mb-1">
                    </div>
                    <span class="text-3xl font-extrabold text-text-main leading-none block">{{ $this->stats['completed'] }}</span>
                    <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider mt-1.5 block">Completed</span>
                </div>

                {{-- Groups --}}
                <div class="bg-bg-secondary/60 border border-border-light rounded-2xl p-4 text-center">
                    <div class="flex items-center justify-center gap-2 mb-1">
                    </div>
                    <span class="text-3xl font-extrabold text-text-main leading-none block">{{ $this->stats['groups'] }}</span>
                    <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider mt-1.5 block">Group {{ Str::plural('Trip', $this->stats['groups']) }}</span>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             3. FILTER ROW — "Your Trips" + tabs + INLINE search + view toggles
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-border-light pb-4">

            {{-- Left: Heading + Search Bar --}}
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                <div class="flex items-center space-x-2 shrink-0">
                    <h2 class="text-xl font-bold font-sans-display text-text-main">Your Trips</h2>
                    <i class="ph ph-airplane-takeoff text-lg text-text-muted"></i>
                </div>

                {{-- SEARCH BAR with Alpine suggestions dropdown --}}
                <div class="relative w-full sm:w-64"
                    x-data="{
                         query: @entangle('searchQuery'),
                         focused: false,
                         get suggestions() {
                             if (!this.query || this.query.length < 1) return [];
                             const q = this.query.toLowerCase();
                             const trips = @js($this->trips->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'destination' => $t->destination])->values()->toArray());
                             return trips.filter(t => t.name.toLowerCase().includes(q) || t.destination.toLowerCase().includes(q)).slice(0, 5);
                         },
                         get showDropdown() {
                             return this.focused && this.query && this.query.length >= 1 && this.suggestions.length > 0;
                         }
                     }"
                    @click.away="focused = false">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ph ph-magnifying-glass text-text-muted text-sm"></i>
                    </div>
                    <input type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        x-model="query"
                        @focus="focused = true"
                        @input="focused = true"
                        placeholder="Search trips..."
                        class="block w-full pl-9 pr-4 py-2 bg-bg-primary border border-border-card rounded-full text-xs placeholder-text-muted focus:outline-none focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral transition shadow-inner-sm">

                    {{-- Suggestions dropdown --}}
                    <div x-show="showDropdown"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        x-cloak
                        class="absolute z-40 mt-1.5 w-full bg-bg-primary border border-border-card rounded-2xl shadow-xl overflow-hidden">
                        <template x-for="item in suggestions" :key="item.id">
                            <a :href="'/trips/' + item.id"
                                class="flex items-center gap-3 px-4 py-3 hover:bg-bg-secondary transition text-xs group border-b border-border-light last:border-0"
                                wire:navigate>
                                <i class="ph ph-map-pin text-brand-neutral text-sm shrink-0"></i>
                                <div class="min-w-0">
                                    <span class="font-semibold text-text-main block truncate" x-text="item.name"></span>
                                    <span class="text-text-muted text-[10px] block truncate" x-text="item.destination"></span>
                                </div>
                                <i class="ph ph-arrow-right text-text-muted text-xs ml-auto opacity-0 group-hover:opacity-100 transition shrink-0"></i>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Right: tabs + view toggles --}}
            <div class="flex flex-wrap lg:flex-nowrap items-center gap-3 w-full lg:w-auto shrink-0 mt-3 lg:mt-0">
                {{-- Status tabs --}}
                <div class="flex flex-wrap bg-bg-primary p-1 rounded-2xl border border-border-light text-xs font-bold uppercase tracking-wider shrink-0 overflow-x-auto scrollbar-none">
                    @foreach(['All', 'Upcoming', 'Planning', 'Completed', 'Groups', 'Invites'] as $statusTab)
                    <button wire:click="$set('filterStatus', '{{ $statusTab }}')"
                        class="cursor-pointer whitespace-nowrap px-4 py-1.5 rounded-xl transition-all flex items-center gap-1.5 {{ $filterStatus === $statusTab ? 'bg-brand-neutral text-bg-primary shadow-sm' : 'text-text-muted hover:text-text-main' }}">
                        <span>{{ $statusTab }}</span>
                        @if ($statusTab === 'Invites' && $this->stats['invites'] > 0)
                        <span class="px-1.5 py-0.5 text-[9px] font-bold rounded-full {{ $filterStatus === 'Invites' ? 'bg-bg-primary text-brand-neutral' : 'bg-brand-neutral text-bg-primary' }}">
                            {{ $this->stats['invites'] }}
                        </span>
                        @endif
                    </button>
                    @endforeach
                </div>

                {{-- Grid / List view toggles --}}
                <div class="flex bg-bg-primary p-1 rounded-xl border border-border-light text-xs font-semibold shrink-0">
                    <button wire:click="$set('viewMode', 'grid')"
                        class="cursor-pointer p-1.5 rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-brand-neutral text-bg-primary shadow-none' : 'text-text-muted hover:text-text-main' }}"
                        title="Grid View">
                        <i class="ph-bold ph-squares-four text-sm block"></i>
                    </button>
                    <button wire:click="$set('viewMode', 'list')"
                        class="cursor-pointer p-1.5 rounded-lg transition-all {{ $viewMode === 'list' ? 'bg-brand-neutral text-bg-primary shadow-none' : 'text-text-muted hover:text-text-main' }}"
                        title="List View">
                        <i class="ph-bold ph-list-bullets text-sm block"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             TRIPS DISPLAY AREA
        ═══════════════════════════════════════════════════════════════ --}}
        @if ($filterStatus === 'Invites')
        @if ($this->pendingInvitations->isEmpty())
        <div class="bg-bg-primary border border-border-light rounded-3xl p-16 text-center w-full">
            <i class="ph ph-envelope-open text-5xl text-text-muted block mx-auto"></i>
            <h3 class="mt-4 text-lg font-bold">No pending invitations</h3>
            <p class="mt-1 text-sm text-text-muted">You don't have any pending invites to join other trips.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($this->pendingInvitations as $invite)
            <div class="bg-bg-primary border border-border-light rounded-3xl p-6 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] text-brand-neutral font-bold tracking-wide uppercase">{{ $invite->trip->destination }}</span>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-bg-secondary border border-border-light rounded-full text-text-main capitalize">
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
        <div class="bg-bg-primary border border-border-light rounded-3xl p-16 text-center w-full">
            <i class="ph ph-compass text-5xl text-text-muted block mx-auto"></i>
            <h3 class="mt-4 text-lg font-bold">No trips found</h3>
            <p class="mt-1 text-sm text-text-muted">Create a new trip or modify your filters/search to get started.</p>
            <button type="button"
                wire:click="$set('showCreateModal', true)"
                class="mt-6 px-6 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition shadow-none cursor-pointer">
                Create a Trip
            </button>
        </div>
        @else

        {{-- ═══════════════════════════════════════════════════════════
                 5. GRID VIEW — premium card style (standard)
            ═══════════════════════════════════════════════════════════ --}}
        @if ($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($this->trips as $trip)
            @php
            $role = $trip->pivot->role ?? 'member';
            $status = 'Planning';
            $today = now()->startOfDay();
            if ($today->between($trip->start_date, $trip->end_date)) {
            $status = 'Ongoing';
            } elseif ($today->gt($trip->end_date)) {
            $status = 'Completed';
            }
            $durationDays = $trip->start_date->diffInDays($trip->end_date);
            @endphp
            <div class="bg-bg-primary border border-border-light rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition duration-200">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <a href="{{ route('trips.show', $trip->id) }}" wire:navigate class="min-w-0">
                            <h3 class="text-xl font-extrabold text-text-main leading-tight hover:text-brand-neutral transition">{{ $trip->name }}</h3>
                            <p class="text-sm text-text-muted mt-1">{{ $trip->destination }}</p>
                        </a>
                        @if ($trip->canDelete(auth()->user()))
                        <button type="button"
                            wire:click="deleteTrip({{ $trip->id }})"
                            wire:confirm="Delete '{{ $trip->name }}'? This will permanently remove its itinerary, polls, expenses, documents, and chat."
                            class="h-9 w-9 rounded-full border border-border-card text-text-muted hover:text-red-700 hover:border-red-200 hover:bg-red-50 flex items-center justify-center transition cursor-pointer"
                            title="Delete trip">
                            <i class="ph ph-trash text-base"></i>
                        </button>
                        @endif
                    </div>

                    <a href="{{ route('trips.show', $trip->id) }}" wire:navigate class="block relative mt-6 rounded-3xl overflow-hidden group">
                        <img src="{{ $trip->cover_image_url ?: $this->getTripImage($trip->destination) }}"
                            alt="{{ $trip->destination }}"
                            class="h-56 w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute left-5 right-5 bottom-5 rounded-full bg-black/60 text-white px-4 py-3 flex items-center justify-between gap-3 text-xs font-bold shadow-lg">
                            <span class="flex items-center gap-1.5"><i class="ph ph-clock"></i>{{ $durationDays }} {{ Str::plural('Day', $durationDays) }}</span>
                            <span class="flex items-center gap-1.5"><i class="ph ph-globe-hemisphere-east"></i>{{ $status }}</span>
                            <span class="flex items-center gap-1.5"><i class="ph ph-calendar-blank"></i>{{ $trip->start_date->format('d M') }}-{{ $trip->end_date->format('d M') }}</span>
                        </div>
                    </a>

                    <div class="mt-5 flex items-center justify-between gap-4">
                        <div class="flex items-center min-w-0">
                            <div class="flex -space-x-2 shrink-0">
                                @foreach($trip->users->take(3) as $u)
                                <x-avatar :user="$u" class="h-8 w-8 border-2 border-bg-primary text-[10px]" title="{{ $u->name }}" />
                                @endforeach
                                @if($trip->users->count() > 3)
                                <div class="h-8 w-8 rounded-full bg-bg-secondary border-2 border-bg-primary flex items-center justify-center font-bold text-[10px] text-text-muted">
                                    +{{ $trip->users->count() - 3 }}
                                </div>
                                @endif
                            </div>
                            <div class="ml-3 min-w-0">
                                <p class="text-[10px] uppercase tracking-wider font-extrabold text-text-muted">Workspace</p>
                                <p class="text-xs font-bold text-text-main truncate capitalize">
                                    {{ str_replace('_', ' ', $role) }} / {{ $trip->users->count() }} {{ Str::plural('traveler', $trip->users->count()) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('trips.show', $trip->id) }}"
                                class="h-10 w-10 rounded-full bg-brand-neutral hover:bg-brand-hover text-bg-primary flex items-center justify-center transition shadow-md"
                                wire:navigate
                                title="Open workspace">
                                <i class="ph-bold ph-arrow-right text-base"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else

        {{-- ═══════════════════════════════════════════════════════════
                 6. LIST VIEW — polished for consistency
            ═══════════════════════════════════════════════════════════ --}}
        <div class="space-y-3">
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
            <div class="group bg-bg-primary border border-border-card rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:shadow-md hover:border-border-light transition-all duration-200">
                <div class="flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-2xl bg-cover bg-center shrink-0 border border-border-light shadow-sm ring-1 ring-black/5" style="background-image: url('{{ $trip->cover_image_url ?: $this->getTripImage($trip->destination) }}')"></div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-brand-neutral font-bold uppercase tracking-wider">{{ $trip->destination }}</span>
                        <h3 class="font-bold text-base text-text-main leading-tight mt-0.5 truncate group-hover:text-brand-neutral transition">{{ $trip->name }}</h3>
                        <p class="text-[11px] text-text-muted flex items-center mt-1 gap-1">
                            <i class="ph ph-calendar text-xs text-text-muted"></i>
                            {{ $trip->start_date->format('M d, Y') }} - {{ $trip->end_date->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 sm:gap-5 w-full sm:w-auto sm:justify-end">
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border border-border-light bg-bg-secondary text-text-main">
                        {{ $status }}
                    </span>

                    <div class="text-[11px]">
                        <span class="text-text-muted">Role:</span>
                        <span class="font-bold capitalize text-text-main ml-0.5">{{ $role }}</span>
                    </div>

                    <div class="flex -space-x-2">
                        @foreach($trip->users->take(4) as $u)
                        <x-avatar :user="$u" class="h-6 w-6 border-2 border-bg-primary text-[10px]" title="{{ $u->name }}" />
                        @endforeach
                        @if($trip->users->count() > 4)
                        <div class="h-6 w-6 rounded-full bg-bg-primary border-2 border-bg-primary flex items-center justify-center font-bold text-[10px] text-text-muted">
                            +{{ $trip->users->count() - 4 }}
                        </div>
                        @endif
                    </div>

                    <a href="{{ route('trips.show', $trip->id) }}"
                        class="px-4 py-2 bg-brand-neutral hover:bg-brand-hover text-bg-primary text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-none"
                        wire:navigate>
                        <span>Workspace</span>
                        <i class="ph ph-arrow-right text-xs"></i>
                    </a>

                    @if ($trip->canDelete(auth()->user()))
                    <button type="button"
                        wire:click="deleteTrip({{ $trip->id }})"
                        wire:confirm="Delete '{{ $trip->name }}'? This will permanently remove its itinerary, polls, expenses, documents, and chat."
                        class="h-9 w-9 rounded-xl border border-border-card text-text-muted hover:text-red-700 hover:border-red-200 hover:bg-red-50 flex items-center justify-center transition cursor-pointer"
                        title="Delete trip">
                        <i class="ph ph-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         CREATE TRIP MODAL (unchanged functionality)
    ═══════════════════════════════════════════════════════════════ --}}
    @if ($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity">
        <div class="bg-bg-primary border border-border-light w-full max-w-lg rounded-3xl shadow-xl overflow-hidden p-8 animate-fade-in font-sans">
            <div class="flex justify-between items-center pb-4 border-b border-border-light mb-6">
                <h2 class="text-xl font-bold text-text-main">Create a New Trip</h2>
                <button type="button"
                    wire:click="$set('showCreateModal', false)"
                    class="text-text-muted hover:text-text-main transition cursor-pointer">
                    <i class="ph ph-x text-lg"></i>
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
                    <label for="budget_estimate" class="block text-sm font-semibold text-text-main">Estimated Budget</label>
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
                        class="px-5 py-2.5 border border-border-card rounded-xl text-sm font-semibold text-text-main hover:bg-bg-secondary transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-semibold text-sm rounded-xl transition shadow-none cursor-pointer">
                        Create Trip
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

