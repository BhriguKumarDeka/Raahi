<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Volt::route('onboarding', 'onboarding')->name('onboarding');
    Volt::route('explore', 'explore')->name('explore');
    
    Route::get('trips/{trip}', function (\App\Models\Trip $trip) {
        if (!$trip->canView(auth()->user())) {
            abort(403, 'You do not have access to this trip.');
        }
        return view('trip.show', compact('trip'));
    })->name('trips.show');

    Route::get('admin', function () {
        return view('admin.dashboard');
    })->middleware('admin')->name('admin.dashboard');
});

Route::view('profile', 'profile')
    ->middleware(['auth', 'verified', 'onboarded'])
    ->name('profile');

Route::get('invitations/{token}/accept', function ($token) {
    $invitation = \App\Models\Invitation::where('token', $token)->firstOrFail();

    // Enforce single-use: only pending invitations can be accepted
    if ($invitation->status !== 'pending') {
        return redirect()->route('dashboard')
            ->with('status', 'This invitation has already been ' . $invitation->status . '.');
    }

    // Check expiration
    if ($invitation->expires_at && $invitation->expires_at->isPast()) {
        $invitation->update(['status' => 'expired']);
        return redirect()->route('dashboard')
            ->with('status', 'This invitation has expired.');
    }

    if (auth()->check()) {
        $user = auth()->user();

        // Verify email matches the invitation
        if (strtolower($user->email) !== strtolower($invitation->email)) {
            return redirect()->route('dashboard')
                ->with('status', 'This invitation was sent to a different email address.');
        }

        if (!$invitation->trip->users()->where('user_id', $user->id)->exists()) {
            $invitation->trip->users()->attach($user->id, ['role' => $invitation->role]);
        }
        $invitation->update(['status' => 'accepted']);
        return redirect()->route('trips.show', $invitation->trip_id)
            ->with('status', 'You have joined the trip: ' . $invitation->trip->name);
    }

    session(['pending_invitation_token' => $token]);
    return redirect()->route('register')
        ->with('status', 'Please register to join the trip.');
})->name('invitations.accept');

require __DIR__.'/auth.php';
