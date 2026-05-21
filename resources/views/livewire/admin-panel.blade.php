<?php

use function Livewire\Volt\{state, computed};
use App\Models\User;
use App\Models\Trip;
use App\Models\Expense;

state([
    'activeAdminTab' => 'users',
    'searchUser' => '',
    'searchTrip' => '',
]);

$usersCount = computed(function () {
    return User::count();
});

$tripsCount = computed(function () {
    return Trip::count();
});

$totalExpenses = computed(function () {
    return Expense::sum('amount');
});

$users = computed(function () {
    $query = User::query();

    if ($this->searchUser) {
        $query->where(function ($q) {
            $q->where('name', 'like', '%' . $this->searchUser . '%')
                ->orWhere('email', 'like', '%' . $this->searchUser . '%');
        });
    }

    return $query->orderBy('created_at', 'desc')->get();
});

$trips = computed(function () {
    $query = Trip::with('creator', 'users');

    if ($this->searchTrip) {
        $query->where(function ($q) {
            $q->where('name', 'like', '%' . $this->searchTrip . '%')
                ->orWhere('destination', 'like', '%' . $this->searchTrip . '%');
        });
    }

    return $query->orderBy('created_at', 'desc')->get();
});

$toggleAdmin = function ($userId) {
    if ($userId === auth()->id()) {
        session()->flash('error', 'You cannot change your own admin status.');
        return;
    }

    $user = User::findOrFail($userId);
    $user->update(['is_admin' => !$user->is_admin]);
    session()->flash('success', "Admin status updated for {$user->name}.");
};

$deleteUser = function ($userId) {
    if ($userId === auth()->id()) {
        session()->flash('error', 'You cannot delete yourself.');
        return;
    }

    $user = User::findOrFail($userId);
    $user->delete();
    session()->flash('success', "User {$user->name} has been deleted.");
};

$deleteTrip = function ($tripId) {
    $trip = Trip::findOrFail($tripId);
    $name = $trip->name;
    $trip->delete();
    session()->flash('success', "Trip '{$name}' has been deleted.");
};

?>

<div class="py-8 bg-bg-secondary min-h-screen font-sans text-text-main" wire:poll.10s>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight font-display">System Administration</h1>
            <p class="text-text-muted text-sm mt-1">Manage users, monitor trips, and view system-wide stats.</p>
        </div>

        @if (session()->has('success'))
        <div class="mb-6 p-4 bg-bg-primary border border-brand-neutral rounded-xl text-sm font-semibold flex items-center space-x-2">
            <svg class="h-5 w-5 text-text-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if (session()->has('error'))
        <div class="mb-6 p-4 bg-bg-primary border border-red-500 rounded-xl text-sm font-semibold text-red-600 flex items-center space-x-2">
            <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Users Stat Card -->
            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-text-muted uppercase block font-bold tracking-wider">Total Users</span>
                        <span class="text-3xl font-extrabold tracking-tight mt-1 block">{{ $this->usersCount }}</span>
                    </div>
                    <div class="p-3 bg-bg-secondary border border-border-light rounded-xl">
                        👤
                    </div>
                </div>
            </div>

            <!-- Trips Stat Card -->
            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-text-muted uppercase block font-bold tracking-wider">Total Trips</span>
                        <span class="text-3xl font-extrabold tracking-tight mt-1 block">{{ $this->tripsCount }}</span>
                    </div>
                    <div class="p-3 bg-bg-secondary border border-border-light rounded-xl">
                        ✈️
                    </div>
                </div>
            </div>

            <!-- Expenses Stat Card -->
            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-text-muted uppercase block font-bold tracking-wider">Total Expenses Logged</span>
                        <span class="text-3xl font-extrabold tracking-tight mt-1 block">₹{{ number_format($this->totalExpenses, 2) }}</span>
                    </div>
                    <div class="p-3 bg-bg-secondary border border-border-light rounded-xl">
                        💵
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-border-light mb-8">
            <nav class="flex space-x-6 overflow-x-auto pb-px" aria-label="Tabs">
                <button type="button"
                    wire:click="$set('activeAdminTab', 'users')"
                    class="py-4 border-b-2 font-medium text-sm whitespace-nowrap focus:outline-none transition duration-150 {{ $activeAdminTab === 'users' ? 'border-brand-neutral text-text-main font-semibold' : 'border-transparent text-text-muted hover:text-text-main hover:border-border-card' }}">
                    Users Management
                </button>
                <button type="button"
                    wire:click="$set('activeAdminTab', 'trips')"
                    class="py-4 border-b-2 font-medium text-sm whitespace-nowrap focus:outline-none transition duration-150 {{ $activeAdminTab === 'trips' ? 'border-brand-neutral text-text-main font-semibold' : 'border-transparent text-text-muted hover:text-text-main hover:border-border-card' }}">
                    Trips Moderation
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div>
            <!-- USERS MANAGEMENT TAB -->
            @if ($activeAdminTab === 'users')
            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                <!-- Search Bar -->
                <div class="mb-6 max-w-md relative">
                    <input type="text"
                        wire:model.live.debounce.300ms="searchUser"
                        placeholder="Search users by name or email..."
                        class="w-full pl-10 pr-4 py-2 text-sm border border-border-card rounded-xl bg-bg-secondary focus:outline-none focus:border-brand-neutral transition" />
                    <div class="absolute left-3 top-2.5 text-text-muted">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Users Table -->
                @if ($this->users->isEmpty())
                <p class="text-sm text-text-muted text-center py-8">No users found matching your search criteria.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border-light text-text-muted font-semibold">
                                <th class="pb-3 pr-4">User</th>
                                <th class="pb-3 px-4">Onboarded</th>
                                <th class="pb-3 px-4">System Role</th>
                                <th class="pb-3 pl-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-light">
                            @foreach ($this->users as $u)
                            <tr class="align-middle">
                                <td class="py-4 pr-4">
                                    <div class="flex items-center space-x-3">
                                        <x-avatar :user="$u" class="h-9 w-9 bg-bg-secondary text-text-main border border-border-light text-xs" />
                                        <div>
                                            <span class="font-bold block text-text-main">{{ $u->name }}</span>
                                            <span class="text-xs text-text-muted">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if ($u->onboarded)
                                    <span class="px-2.5 py-0.5 border border-border-card bg-bg-secondary rounded-full text-xs text-text-main">
                                        Completed
                                    </span>
                                    @else
                                    <span class="px-2.5 py-0.5 border border-red-200 bg-red-50 rounded-full text-xs text-red-700">
                                        Pending
                                    </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    @if ($u->is_admin)
                                    <span class="px-2.5 py-0.5 bg-brand-neutral text-bg-primary rounded-full text-xs font-semibold">
                                        Administrator
                                    </span>
                                    @else
                                    <span class="px-2.5 py-0.5 border border-border-light text-text-muted rounded-full text-xs">
                                        Standard User
                                    </span>
                                    @endif
                                </td>
                                <td class="py-4 pl-4 text-right">
                                    <div class="flex items-center justify-end space-x-3">
                                        @if ($u->id !== auth()->id())
                                        <button wire:click="toggleAdmin({{ $u->id }})"
                                            class="px-3 py-1 border border-border-card hover:bg-bg-secondary text-xs rounded-lg font-semibold transition">
                                            {{ $u->is_admin ? 'Demote Admin' : 'Make Admin' }}
                                        </button>
                                        <button wire:click="deleteUser({{ $u->id }})"
                                            wire:confirm="Are you sure you want to delete user '{{ $u->name }}'? All their trips, expenses, and comments will be removed."
                                            class="px-3 py-1 border border-red-200 hover:bg-red-50 text-red-600 text-xs rounded-lg font-semibold transition">
                                            Delete
                                        </button>
                                        @else
                                        <span class="text-xs text-text-muted italic">You (Current Admin)</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endif

            <!-- TRIPS MODERATION TAB -->
            @if ($activeAdminTab === 'trips')
            <div class="bg-bg-primary border border-border-light rounded-2xl p-6 shadow-sm">
                <!-- Search Bar -->
                <div class="mb-6 max-w-md relative">
                    <input type="text"
                        wire:model.live.debounce.300ms="searchTrip"
                        placeholder="Search trips by title or destination..."
                        class="w-full pl-10 pr-4 py-2 text-sm border border-border-card rounded-xl bg-bg-secondary focus:outline-none focus:border-brand-neutral transition" />
                    <div class="absolute left-3 top-2.5 text-text-muted">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Trips Table -->
                @if ($this->trips->isEmpty())
                <p class="text-sm text-text-muted text-center py-8">No trips found matching your search criteria.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border-light text-text-muted font-semibold">
                                <th class="pb-3 pr-4">Trip Details</th>
                                <th class="pb-3 px-4">Creator</th>
                                <th class="pb-3 px-4">Timeline</th>
                                <th class="pb-3 px-4">Members</th>
                                <th class="pb-3 px-4">Budget Limit</th>
                                <th class="pb-3 pl-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-light">
                            @foreach ($this->trips as $t)
                            <tr class="align-middle">
                                <td class="py-4 pr-4">
                                    <div>
                                        <span class="font-bold block text-text-main">{{ $t->name }}</span>
                                        <span class="text-xs text-text-muted">{{ $t->destination }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-xs font-semibold text-text-main">
                                        {{ $t->creator ? $t->creator->name : 'Unknown User' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-xs text-text-muted">
                                        {{ $t->start_date->format('M d, Y') }} - {{ $t->end_date->format('M d, Y') }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 border border-border-card bg-bg-secondary rounded text-xs font-bold text-text-main">
                                        {{ $t->users->count() }} members
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-xs font-bold text-text-main">
                                        ₹{{ number_format($t->budget_estimate, 2) }}
                                    </span>
                                </td>
                                <td class="py-4 pl-4 text-right">
                                    <div class="flex items-center justify-end space-x-3">
                                        <a href="{{ route('trips.show', $t->id) }}"
                                            class="px-3 py-1 border border-border-card hover:bg-bg-secondary text-xs rounded-lg font-semibold transition"
                                            wire:navigate>
                                            View Trip
                                        </a>
                                        <button wire:click="deleteTrip({{ $t->id }})"
                                            wire:confirm="Are you sure you want to delete trip '{{ $t->name }}'? All itinerary items, polls, expenses, and documents in this trip will be permanently removed."
                                            class="px-3 py-1 border border-red-200 hover:bg-red-50 text-red-600 text-xs rounded-lg font-semibold transition">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endif
        </div>

    </div>
</div>