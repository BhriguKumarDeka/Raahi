<?php

use App\Models\Trip;
use App\Models\Invitation;
use App\Models\User;
use function Livewire\Volt\{state, computed, on};

state([
    'trip',
    'invite_email' => '',
    'invite_role' => 'member',
]);

on(['trip-updated' => '$refresh']);

$users = computed(function () {
    return $this->trip->users;
});

$sendInvitation = function () {
    if (!$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    $this->validate([
        'invite_email' => 'required|email',
        'invite_role' => 'required|string',
    ]);

    // Generate unique token
    $token = bin2hex(random_bytes(32));

    $invitation = Invitation::create([
        'trip_id' => $this->trip->id,
        'email' => $this->invite_email,
        'token' => $token,
        'role' => $this->invite_role,
        'invited_by' => auth()->id(),
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    // Send the actual email
    \Illuminate\Support\Facades\Mail::to($this->invite_email)->send(new \App\Mail\TripInvitationMail($invitation));

    // To make it easy for testing if Mail isn't setup, we will also output a session status
    session()->flash('invitation_link', route('invitations.accept', $token));

    $this->reset(['invite_email', 'invite_role']);
    $this->dispatch('trip-updated');
};

$updateUserRole = function ($userId, $newRole) {
    if (!$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    if ($userId == auth()->id()) {
        // Can't demote yourself
        return;
    }

    $this->trip->users()->updateExistingPivot($userId, ['role' => $newRole]);
    $this->dispatch('trip-updated');
};

$removeUser = function ($userId) {
    if (!$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    if ($userId == auth()->id()) {
        return;
    }

    $this->trip->users()->detach($userId);
    $this->dispatch('trip-updated');
};

?>

<div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], y: [10, 0] }, { duration: 0.3 }) }">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Invite friends panel -->
        <div class="md:col-span-1">
            <div class="bg-bg-primary border border-border-light rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-sm text-text-main mb-4 flex items-center space-x-1.5">
                    <i class="ph ph-user-plus"></i>
                    <span>Invite Friends</span>
                </h3>

                @if (session('invitation_link'))
                <div class="mb-4 p-3.5 bg-bg-secondary border border-border-card rounded-xl text-xs space-y-2">
                    <p class="font-bold text-brand-neutral">Acceptance Link generated:</p>
                    <input type="text" readonly value="{{ session('invitation_link') }}"
                        class="w-full bg-bg-primary border border-border-card rounded p-2 font-mono text-[10px] text-text-main focus:outline-none">
                    <p class="text-[9px] text-text-muted">Copy this link and share with your friend to test joining.</p>
                </div>
                @endif

                @if ($trip->canManageMembers(auth()->user()))
                <form wire:submit.prevent="sendInvitation" class="space-y-4">
                    <div>
                        <label for="invite_email" class="block text-xs font-semibold text-text-main">Email Address</label>
                        <input type="email" id="invite_email" wire:model="invite_email" placeholder="friend@example.com"
                            class="mt-1 block w-full px-3 py-2 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                        @error('invite_email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="invite_role" class="block text-xs font-semibold text-text-main">Permission Level</label>
                        <select id="invite_role" wire:model="invite_role"
                            class="mt-1 block w-full px-3 py-2 border border-border-card rounded-xl text-xs bg-bg-primary focus:ring-1 focus:ring-brand-neutral focus:border-brand-neutral">
                            <option value="co_planner">Co-Planner (Can edit timeline)</option>
                            <option value="member">Member (Can post & vote)</option>
                            <option value="viewer">Viewer (Read-only)</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-xs rounded-xl transition mt-4 cursor-pointer">
                        Send Invite
                    </button>
                </form>
                @else
                <p class="text-[11px] text-text-muted leading-relaxed">Only the Trip Organizer is authorized to invite new members or change permission roles.</p>
                @endif
            </div>
        </div>

        <!-- Manage planning team -->
        <div class="md:col-span-2">
            <div class="bg-bg-primary border border-border-light rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-sm text-text-main border-b border-border-light pb-2 mb-4">Planning Team ({{ $this->users->count() }})</h3>
                <div class="divide-y divide-border-light">
                    @foreach ($this->users as $u)
                    @php
                    $uRole = $trip->getUserRole($u);
                    @endphp
                    <div class="flex items-center justify-between py-3.5">
                        <div class="flex items-center space-x-3">
                            <x-avatar :user="$u" class="h-9 w-9 text-xs" />
                            <div>
                                <p class="text-xs font-bold text-text-main">{{ $u->name }}</p>
                                <p class="text-[10px] text-text-muted">{{ $u->email }}</p>
                            </div>
                        </div>

                        <!-- Role label / dropdown -->
                        <div class="flex items-center space-x-2">
                            @if ($trip->canManageMembers(auth()->user()) && $u->id !== auth()->id() && !$u->isSystemAdmin())
                            <select wire:change="updateUserRole({{ $u->id }}, $event.target.value)"
                                class="text-[11px] border border-border-card rounded bg-bg-primary text-text-main px-2 py-1 focus:ring-brand-neutral">
                                <option value="organizer" {{ $uRole === 'organizer' ? 'selected' : '' }}>Organizer</option>
                                <option value="co_planner" {{ $uRole === 'co_planner' ? 'selected' : '' }}>Co-Planner</option>
                                <option value="member" {{ $uRole === 'member' ? 'selected' : '' }}>Member</option>
                                <option value="viewer" {{ $uRole === 'viewer' ? 'selected' : '' }}>Viewer</option>
                            </select>

                            <button wire:click="removeUser({{ $u->id }})"
                                wire:confirm="Remove this user from the planning team?"
                                class="text-text-muted hover:text-red-600 transition p-1.5 bg-bg-secondary hover:bg-red-50 rounded-lg cursor-pointer"
                                title="Remove User">
                                <i class="ph ph-trash text-sm block"></i>
                            </button>
                            @else
                            <span class="text-[10px] font-bold uppercase tracking-wider text-text-muted bg-bg-secondary px-2.5 py-1 rounded-full border border-border-light capitalize">
                                {{ $uRole }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>