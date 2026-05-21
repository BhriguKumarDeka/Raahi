<?php

use App\Models\User;
use App\Models\Trip;
use App\Models\Invitation;
use Livewire\Livewire;

test('non-onboarded user is redirected to onboarding page', function () {
    $user = User::factory()->create(['onboarded' => false]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('onboarding'));
});

test('non-onboarded user can access onboarding page', function () {
    $user = User::factory()->create(['onboarded' => false]);

    $response = $this->actingAs($user)->get('/onboarding');

    $response->assertStatus(200);
});

test('onboarded user is redirected from onboarding page to dashboard', function () {
    $user = User::factory()->create(['onboarded' => true]);

    $response = $this->actingAs($user)->get('/onboarding');

    $response->assertRedirect(route('dashboard'));
});

test('onboarded user can access dashboard page', function () {
    $user = User::factory()->create(['onboarded' => true]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('standard user cannot access admin dashboard', function () {
    $user = User::factory()->create(['is_admin' => false, 'onboarded' => true]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertStatus(403);
});

test('admin user can access admin dashboard', function () {
    $user = User::factory()->create(['is_admin' => true, 'onboarded' => true]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertStatus(200);
});

test('user can view a trip they are member of', function () {
    $user = User::factory()->create(['onboarded' => true]);
    $trip = Trip::create([
        'name' => 'Test Trip',
        'destination' => 'Test City',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
        'creator_id' => $user->id,
    ]);
    $trip->users()->attach($user->id, ['role' => 'organizer']);

    $response = $this->actingAs($user)->get("/trips/{$trip->id}");

    $response->assertStatus(200);
});

test('user cannot view a trip they are not member of', function () {
    $user = User::factory()->create(['onboarded' => true]);
    $otherUser = User::factory()->create(['onboarded' => true]);
    $trip = Trip::create([
        'name' => 'Test Trip',
        'destination' => 'Test City',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
        'creator_id' => $otherUser->id,
    ]);
    $trip->users()->attach($otherUser->id, ['role' => 'organizer']);

    $response = $this->actingAs($user)->get("/trips/{$trip->id}");

    $response->assertStatus(403);
});

test('onboarded user can access explore page', function () {
    $user = User::factory()->create(['onboarded' => true]);

    $response = $this->actingAs($user)->get('/explore');

    $response->assertStatus(200);
});

test('user can clone explore itinerary as a new trip', function () {
    $user = User::factory()->create(['onboarded' => true]);

    Livewire::actingAs($user)
        ->test('explore')
        ->call('cloneTrip', 'bali')
        ->assertRedirect();

    $trip = Trip::where('creator_id', $user->id)->first();
    expect($trip)->not->toBeNull();
    expect($trip->name)->toBe('Summer in Bali');
    expect($trip->destination)->toBe('Bali, Indonesia');

    expect($trip->itineraryItems()->count())->toBe(7);
});

test('user can open preview modal on explore page', function () {
    $user = User::factory()->create(['onboarded' => true]);

    Livewire::actingAs($user)
        ->test('explore')
        ->set('previewKey', 'bali')
        ->set('showPreviewModal', true)
        ->assertSee('Summer in Bali Itinerary');
});

test('dashboard clicking stats banners sets the correct filter', function () {
    $user = User::factory()->create(['onboarded' => true]);

    Livewire::actingAs($user)
        ->test('dashboard')
        ->assertSet('filterStatus', 'All')
        ->call('$set', 'filterStatus', 'Upcoming')
        ->assertSet('filterStatus', 'Upcoming')
        ->call('$set', 'filterStatus', 'Invites')
        ->assertSet('filterStatus', 'Invites')
        ->call('$set', 'filterStatus', 'Completed')
        ->assertSet('filterStatus', 'Completed')
        ->call('$set', 'filterStatus', 'Groups')
        ->assertSet('filterStatus', 'Groups');
});

test('dashboard groups filter returns only group-invited trips where user is participant but not creator', function () {
    $user = User::factory()->create(['onboarded' => true]);
    $otherUser = User::factory()->create(['onboarded' => true]);

    // Trip 1: created by user
    $trip1 = Trip::create([
        'name' => 'My Creator Trip',
        'destination' => 'Destination A',
        'start_date' => now()->addDays(20),
        'end_date' => now()->addDays(25),
        'creator_id' => $user->id,
    ]);
    $trip1->users()->attach($user->id, ['role' => 'organizer']);

    // Trip 2: created by other user, user is participant (invited/member)
    $trip2 = Trip::create([
        'name' => 'Group Invited Trip',
        'destination' => 'Destination B',
        'start_date' => now()->addDays(2),
        'end_date' => now()->addDays(5),
        'creator_id' => $otherUser->id,
    ]);
    $trip2->users()->attach($otherUser->id, ['role' => 'organizer']);
    $trip2->users()->attach($user->id, ['role' => 'member']);

    Livewire::actingAs($user)
        ->test('dashboard')
        ->set('filterStatus', 'Groups')
        ->assertSee('Group Invited Trip')
        ->assertDontSee('My Creator Trip');
});

test('dashboard accepting/rejecting invitations updates DB states correctly', function () {
    $user = User::factory()->create(['onboarded' => true]);
    $inviter = User::factory()->create(['onboarded' => true]);

    $trip = Trip::create([
        'name' => 'Invited Trip',
        'destination' => 'Destination C',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
        'creator_id' => $inviter->id,
    ]);
    $trip->users()->attach($inviter->id, ['role' => 'organizer']);

    $invitation = Invitation::create([
        'trip_id' => $trip->id,
        'email' => $user->email,
        'token' => 'test-token-123',
        'role' => 'member',
        'invited_by' => $inviter->id,
        'status' => 'pending',
    ]);

    // Accept invitation
    Livewire::actingAs($user)
        ->test('dashboard')
        ->call('acceptInvitation', $invitation->id);

    $invitation->refresh();
    expect($invitation->status)->toBe('accepted');
    expect($trip->users()->where('user_id', $user->id)->exists())->toBeTrue();

    // Create another invitation to reject
    $trip2 = Trip::create([
        'name' => 'Another Invited Trip',
        'destination' => 'Destination D',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
        'creator_id' => $inviter->id,
    ]);
    $trip2->users()->attach($inviter->id, ['role' => 'organizer']);

    $invitation2 = Invitation::create([
        'trip_id' => $trip2->id,
        'email' => $user->email,
        'token' => 'test-token-456',
        'role' => 'member',
        'invited_by' => $inviter->id,
        'status' => 'pending',
    ]);

    // Reject invitation
    Livewire::actingAs($user)
        ->test('dashboard')
        ->call('rejectInvitation', $invitation2->id);

    $invitation2->refresh();
    expect($invitation2->status)->toBe('rejected');
    expect($trip2->users()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('explore page search bar correctly filters destinations', function () {
    $user = User::factory()->create(['onboarded' => true]);

    Livewire::actingAs($user)
        ->test('explore')
        ->set('searchQuery', 'Bali')
        ->assertSeeHtml('>Summer in Bali</h3>')
        ->assertDontSeeHtml('>Kyoto Cultural Discovery</h3>');

    Livewire::actingAs($user)
        ->test('explore')
        ->set('searchQuery', 'Kyoto')
        ->assertSeeHtml('>Kyoto Cultural Discovery</h3>')
        ->assertDontSeeHtml('>Summer in Bali</h3>');

    Livewire::actingAs($user)
        ->test('explore')
        ->set('searchQuery', 'Mountain')
        ->assertSeeHtml('>Patagonia Adventure &amp; Hiking</h3>')
        ->assertDontSeeHtml('>Paris Culture &amp; Fine Dining</h3>');
});
