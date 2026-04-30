<?php

use Kirby\Cms\App as Kirby;
use Kirby\Http\Remote;

const FA_TMDB_INTEGRATION_VERSION = '0.1b';

if (!function_exists('tmdbFetchJson')) {
    function tmdbFetchJson(string $url, array $headers = []): array|null {
        $headers = array_values(array_filter($headers));

        if (extension_loaded('curl')) {
            try {
                $response = Remote::get($url, [
                    'headers' => $headers,
                    'timeout' => 5
                ]);

                if ($response->code() === 200) {
                    return $response->json();
                }
            } catch (Throwable $e) {
                return null;
            }

            return null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 5,
                'ignore_errors' => true,
            ]
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            return null;
        }

        $status = $http_response_header[0] ?? '';
        if (!str_contains($status, '200')) {
            return null;
        }

        $data = json_decode($body, true);

        return is_array($data) ? $data : null;
    }
}

// Global helper functions for TMDB
if (!function_exists('tmdbMovie')) {
    function tmdbMovie(string $id) {
        $kirby = kirby();
        if (empty($id)) return null;
        
        $enabled = $kirby->site()->tmdb_enabled()->toBool(true);
        if (!$enabled) return null;

        $cacheEnabled = $kirby->site()->tmdb_cache_enabled()->toBool(option('site.tmdb.cache', true));
        $cache = $kirby->cache('site.tmdb');
        $cacheKey = 'movie-' . $id;
        
        if ($cacheEnabled && $data = $cache->get($cacheKey)) {
            return $data;
        }

        $token = option('tmdb.access_token');
        $apiKey = option('tmdb.api_key');
        $token = preg_replace('/^Bearer\s+/i', '', trim((string)$token, " \t\n\r\0\x0B\"'"));
        $apiKey = trim((string)$apiKey, " \t\n\r\0\x0B\"'");

        if (empty($apiKey) && empty($token)) return null;

        $lang = $kirby->site()->tmdb_language()->value() ?: option('site.tmdb.language', 'tr-TR');
        
        // Try the bearer token first, then fall back to the v3 API key.
        $url = "https://api.themoviedb.org/3/movie/{$id}?language={$lang}&append_to_response=credits";
        $baseHeaders = [
            'accept: application/json',
            'user-agent: Kirby-Archive-Panel'
        ];
        $requests = [];

        if (!empty($token)) {
            $requests[] = [
                'url' => $url,
                'headers' => array_merge($baseHeaders, ['Authorization: Bearer ' . trim($token)])
            ];
        }

        if (!empty($apiKey)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $requests[] = [
                'url' => $url . $separator . 'api_key=' . rawurlencode(trim($apiKey)),
                'headers' => $baseHeaders
            ];
        }

        foreach ($requests as $request) {
            $data = tmdbFetchJson($request['url'], $request['headers']);
            if (is_array($data)) {
                if ($cacheEnabled) {
                    $ttl = (int)$kirby->site()->tmdb_cache_ttl()->value() ?: option('site.tmdb.expires', 10080);
                    $cache->set($cacheKey, $data, $ttl);
                }

                return $data;
            }
        }

        return null;
    }
}

if (!function_exists('tmdbImageUrl')) {
    function tmdbImageUrl($path, $size = null) {
        if (empty($path)) return null;
        $size = $size ?: kirby()->site()->tmdb_image_size()->value() ?: option('site.tmdb.image_size', 'w500');
        return "https://image.tmdb.org/t/p/{$size}{$path}";
    }
}

if (!function_exists('tmdbDirector')) {
    function tmdbDirector($movie) {
        if (empty($movie['credits']['crew'])) return null;
        $directors = array_filter($movie['credits']['crew'], function ($person) {
            return $person['job'] === 'Director';
        });
        $director = array_shift($directors);
        return $director['name'] ?? null;
    }
}

if (!function_exists('tmdbGenres')) {
    function tmdbGenres($movie) {
        if (empty($movie['genres'])) return [];
        return array_column($movie['genres'], 'name');
    }
}

Kirby::plugin('fatihalper/tmdb-integration', [
    'options' => [
        'version' => FA_TMDB_INTEGRATION_VERSION,
        'cache' => true,
        'expires' => 10080, // minutes (1 week)
        'language' => 'tr-TR',
        'image_size' => 'w500'
    ],
    'siteMethods' => [
        'tmdbIntegrationVersion' => fn (): string => FA_TMDB_INTEGRATION_VERSION,
    ],
    'pageMethods' => [
        'tmdb' => function () {
            $tmdbId = $this->tmdb_id()->value();
            if (empty($tmdbId)) return null;
            return tmdbMovie($tmdbId);
        }
    ]
]);
