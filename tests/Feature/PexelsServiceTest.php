<?php

use App\Models\Trip;
use App\Models\User;
use App\Services\PexelsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();
    // Configure a dummy API key in environment for testing
    putenv('PEXELS_API=test-api-key');
});

afterEach(function () {
    putenv('PEXELS_API=');
});

test('PexelsService caches successful responses forever', function () {
    Http::fake([
        'https://api.pexels.com/v1/search*' => Http::response([
            'photos' => [
                [
                    'url' => 'https://www.pexels.com/photo/paris-123/',
                    'photographer' => 'Jane Doe',
                    'photographer_url' => 'https://www.pexels.com/@jane-doe',
                    'src' => [
                        'landscape' => 'https://images.pexels.com/photos/123/landscape.jpg',
                        'large2x' => 'https://images.pexels.com/photos/123/large.jpg'
                    ]
                ]
            ]
        ], 200)
    ]);

    // First call: should trigger API request and cache forever
    $data = PexelsService::getTripImageData('Paris');

    expect($data)->toBeArray()
        ->and($data['url'])->toBe('https://images.pexels.com/photos/123/landscape.jpg')
        ->and($data['photographer'])->toBe('Jane Doe')
        ->and($data['is_pexels'])->toBeTrue();

    Http::assertSentCount(1);

    // Second call: should serve from cache, not trigger another API request
    $cachedData = PexelsService::getTripImageData('Paris');

    expect($cachedData)->toBe($data);
    Http::assertSentCount(1);
});

test('PexelsService caches failed responses temporarily for 10 minutes', function () {
    Http::fake([
        'https://api.pexels.com/v1/search*' => Http::response('Rate limit exceeded', 429)
    ]);

    // First call (failure): should use fallback and cache temporarily for 10 minutes
    $data = PexelsService::getTripImageData('Paris');

    // Default fallback for Paris
    expect($data['url'])->toBe('https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1200&q=80')
        ->and($data['is_pexels'])->toBeFalse();

    Http::assertSentCount(1);

    // Second call: should read from temporary cache and not request API again
    $cachedData = PexelsService::getTripImageData('Paris');
    expect($cachedData)->toBe($data);

    Http::assertSentCount(1);
});

test('Trip model saves cover image automatically on create and update', function () {
    Http::fake([
        'https://api.pexels.com/v1/search?query=Kyoto*' => Http::response([
            'photos' => [
                [
                    'url' => 'https://www.pexels.com/photo/kyoto-456/',
                    'photographer' => 'John Kyoto',
                    'photographer_url' => 'https://www.pexels.com/@john-kyoto',
                    'src' => [
                        'landscape' => 'https://images.pexels.com/photos/456/landscape.jpg',
                        'large2x' => 'https://images.pexels.com/photos/456/large.jpg'
                    ]
                ]
            ]
        ], 200),
        'https://api.pexels.com/v1/search?query=Bali*' => Http::response([
            'photos' => [
                [
                    'url' => 'https://www.pexels.com/photo/bali-789/',
                    'photographer' => 'Budi Bali',
                    'photographer_url' => 'https://www.pexels.com/@budi-bali',
                    'src' => [
                        'landscape' => 'https://images.pexels.com/photos/789/landscape.jpg',
                        'large2x' => 'https://images.pexels.com/photos/789/large.jpg'
                    ]
                ]
            ]
        ], 200)
    ]);

    $user = User::factory()->create();

    // Creating a trip should trigger the event listener and set cover image details
    $trip = Trip::create([
        'name' => 'Autumn Kyoto Tour',
        'destination' => 'Kyoto',
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(15),
        'budget_estimate' => 5000,
        'creator_id' => $user->id,
    ]);

    expect($trip->cover_image_url)->toBe('https://images.pexels.com/photos/456/landscape.jpg')
        ->and($trip->photographer_name)->toBe('John Kyoto')
        ->and($trip->photographer_url)->toBe('https://www.pexels.com/@john-kyoto')
        ->and($trip->photo_url)->toBe('https://www.pexels.com/photo/kyoto-456/');

    Http::assertSentCount(1);

    // Updating a field other than destination should NOT trigger Pexels API
    $trip->update(['name' => 'Stunning Kyoto Autumn']);
    Http::assertSentCount(1);

    // Updating the destination to Bali should trigger Pexels API
    $trip->update(['destination' => 'Bali']);
    $trip->refresh();

    expect($trip->cover_image_url)->toBe('https://images.pexels.com/photos/789/landscape.jpg')
        ->and($trip->photographer_name)->toBe('Budi Bali');

    Http::assertSentCount(2); // Kyoto + Bali
});
