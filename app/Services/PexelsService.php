<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PexelsService
{
    /**
     * Get landscape/large image data (url, photo_url, photographer, photographer_url) for a given destination.
     * Caches forever to prevent hitting the rate limit.
     *
     * @param string $destination
     * @param array|null $fallback
     * @return array
     */
    public static function getTripImageData(string $destination, ?array $fallback = null): array
    {
        $destination = trim($destination);
        if (empty($destination)) {
            return $fallback ?: self::getDefaultFallbackData($destination);
        }
        // Cache key based on md5 hash of destination name
        $cacheKey = 'pexels_image_data_v2_' . md5(strtolower($destination));

        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            return $cachedData;
        }

        $apiKey = trim(env('PEXELS_API'));
        if (!$apiKey) {
            return $fallback ?: self::getDefaultFallbackData($destination);
        }

        try {
            // Query Pexels search endpoint
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->timeout(5)->get('https://api.pexels.com/v1/search', [
                'query' => $destination,
                'per_page' => 1,
                'orientation' => 'landscape',
            ]);

            if ($response->successful()) {
                $photos = $response->json('photos');
                if (!empty($photos) && isset($photos[0])) {
                    $photo = $photos[0];
                    $data = [
                        'url' => $photo['src']['landscape'] ?? $photo['src']['large2x'],
                        'photo_url' => $photo['url'],
                        'photographer' => $photo['photographer'],
                        'photographer_url' => $photo['photographer_url'],
                        'is_pexels' => true,
                    ];
                    Cache::forever($cacheKey, $data);
                    return $data;
                }
            } else {
                Log::warning('Pexels API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception during Pexels API request: ' . $e->getMessage());
        }

        // Cache fallback data temporarily (e.g. 10 minutes) if the API request failed or was rate-limited
        $fallbackData = $fallback ?: self::getDefaultFallbackData($destination);
        Cache::put($cacheKey, $fallbackData, now()->addMinutes(10));
        return $fallbackData;
    }

    /**
     * Get a landscape/large image URL for a given destination from Pexels API.
     *
     * @param string $destination
     * @param string|null $fallback
     * @return string
     */
    public static function getTripImage(string $destination, ?string $fallback = null): string
    {
        $fallbackArray = $fallback ? [
            'url' => $fallback,
            'photo_url' => 'https://unsplash.com',
            'photographer' => 'Unsplash',
            'photographer_url' => 'https://unsplash.com',
            'is_pexels' => false,
        ] : null;

        $data = self::getTripImageData($destination, $fallbackArray);
        return $data['url'];
    }

    /**
     * Get fallback array data.
     *
     * @param string $destination
     * @return array
     */
    private static function getDefaultFallbackData(string $destination): array
    {
        return [
            'url' => self::getDefaultFallback($destination),
            'photo_url' => 'https://unsplash.com',
            'photographer' => 'Unsplash',
            'photographer_url' => 'https://unsplash.com',
            'is_pexels' => false,
        ];
    }

    /**
     * Fallback Unsplash images based on matching keyword or a general travel fallback.
     *
     * @param string $destination
     * @return string
     */
    private static function getDefaultFallback(string $destination): string
    {
        $dest = strtolower($destination);
        if (str_contains($dest, 'bali')) {
            return 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80';
        } elseif (str_contains($dest, 'kyoto')) {
            return 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1200&q=80';
        } elseif (str_contains($dest, 'patagonia')) {
            return 'https://images.unsplash.com/photo-1517411032315-54ef2cb783bb?auto=format&fit=crop&w=1200&q=80';
        } elseif (str_contains($dest, 'paris')) {
            return 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1200&q=80';
        } elseif (str_contains($dest, 'manali')) {
            return 'https://images.unsplash.com/photo-1596701062351-8c2c14d1fdd0?auto=format&fit=crop&w=1200&q=80';
        } elseif (str_contains($dest, 'assam')) {
            return 'https://images.unsplash.com/photo-1582298538104-fc2c0c567793?auto=format&fit=crop&w=1200&q=80';
        } elseif (str_contains($dest, 'shillong')) {
            return 'https://images.unsplash.com/photo-1588880331149-6ee5b291d3b7?auto=format&fit=crop&w=1200&q=80';
        }
        return 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1200&q=80';
    }
}
