<?php

use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Http\Response;

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

if (!function_exists('tmdbImageAllowedSize')) {
    function tmdbImageAllowedSize(string $size): bool {
        return in_array($size, ['w92', 'w154', 'w185', 'w342', 'w500', 'w780', 'original'], true);
    }
}

if (!function_exists('tmdbImageSize')) {
    function tmdbImageSize($size = null): string {
        $size = $size ?: kirby()->site()->tmdb_image_size()->value() ?: option('site.tmdb.image_size', 'w500');
        $size = (string)$size;

        return tmdbImageAllowedSize($size) ? $size : 'w500';
    }
}

if (!function_exists('tmdbImagePath')) {
    function tmdbImagePath($path): string|null {
        $path = ltrim((string)$path, '/');

        if (
            $path === '' ||
            str_contains($path, '..') ||
            preg_match('![^A-Za-z0-9/_\.\-]!', $path) === 1 ||
            preg_match('!\.(jpe?g|png|webp|avif)$!i', $path) !== 1
        ) {
            return null;
        }

        return $path;
    }
}

if (!function_exists('tmdbImageCacheEnabled')) {
    function tmdbImageCacheEnabled(): bool {
        return kirby()->site()->tmdb_image_cache_enabled()->toBool(option('site.tmdb.image_cache', true));
    }
}

if (!function_exists('tmdbImageCacheFile')) {
    function tmdbImageCacheFile(string $path, string $size): string {
        return kirby()->root('cache') . '/tmdb-images/' . $size . '/' . $path;
    }
}

if (!function_exists('tmdbCachedImageUrl')) {
    function tmdbCachedImageUrl($path, $size = null): string|null {
        $path = tmdbImagePath($path);
        if ($path === null) return null;

        $size = tmdbImageSize($size);

        return url('tmdb-image/' . rawurlencode($size) . '/' . $path);
    }
}

if (!function_exists('tmdbFetchImage')) {
    function tmdbFetchImage(string $url): string|null {
        if (extension_loaded('curl')) {
            try {
                $response = Remote::get($url, ['timeout' => 8]);

                return $response->code() === 200 ? $response->content() : null;
            } catch (Throwable $e) {
                return null;
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 8,
                'ignore_errors' => true,
            ]
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) return null;

        $status = $http_response_header[0] ?? '';

        return str_contains($status, '200') ? $body : null;
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

        if (tmdbImageCacheEnabled() && $cached = tmdbCachedImageUrl($path, $size)) {
            return $cached;
        }

        $size = tmdbImageSize($size);
        $path = tmdbImagePath($path);
        if ($path === null) return null;
        $path = '/' . $path;

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
        'image_cache' => true,
        'expires' => 10080, // minutes (1 week)
        'language' => 'tr-TR',
        'image_size' => 'w500'
    ],
    'siteMethods' => [
        'tmdbIntegrationVersion' => fn (): string => FA_TMDB_INTEGRATION_VERSION,
    ],
    'routes' => [
        [
            'pattern' => 'tmdb-image/(:any)/(:all)',
            'action' => function (string $size, string $path) {
                $size = rawurldecode($size);
                $path = tmdbImagePath($path);

                if (tmdbImageAllowedSize($size) === false || $path === null) {
                    return new Response('Not found', 'text/plain', 404);
                }

                $cacheFile = tmdbImageCacheFile($path, $size);
                if (is_file($cacheFile) === false) {
                    $content = tmdbFetchImage("https://image.tmdb.org/t/p/{$size}/{$path}");

                    if ($content === null) {
                        return new Response('Image unavailable', 'text/plain', 502);
                    }

                    Dir::make(dirname($cacheFile), true);
                    F::write($cacheFile, $content);
                }

                return Response::file($cacheFile, [
                    'headers' => [
                        'Cache-Control' => 'public, max-age=604800'
                    ]
                ]);
            }
        ]
    ],
    'pageMethods' => [
        'tmdb' => function () {
            $tmdbId = $this->tmdb_id()->value();
            if (empty($tmdbId)) return null;
            return tmdbMovie($tmdbId);
        }
    ]
]);
