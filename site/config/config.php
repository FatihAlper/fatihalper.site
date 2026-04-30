<?php

/**
 * Kirby Configuration
 * 
 * API keys are loaded from environment variables.
 * For hosts without Composer/console access, upload a real .env file to the
 * Kirby project root. bnomei/kirby3-dotenv is installed manually in
 * site/plugins/kirby3-dotenv and loaded here before Kirby parses options.
 */

$dotenv = dirname(__DIR__) . '/plugins/kirby3-dotenv/global.php';
if (is_file($dotenv)) {
    require_once $dotenv;
    loadenv(['dir' => dirname(__DIR__, 2)]);
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$appEnv = strtolower((string)env('APP_ENV', ''));
$isLocal = $host === ''
    || $host === 'kirby.test'
    || $host === 'localhost'
    || str_starts_with($host, '127.0.0.1')
    || str_ends_with($host, '.localhost');
$isProduction = $appEnv === 'production' || ($appEnv !== 'local' && $isLocal === false);

return [
    'debug' => $appEnv === 'local' || $host === 'kirby.test',

    'site.production' => $isProduction,

    'routes' => [
        [
            'pattern' => 'etiketler',
            'action'  => function () {
                $page = page('tags');

                if (!$page) {
                    return site()->errorPage();
                }

                return $page->render();
            }
        ],
        [
            'pattern' => 'etiketler/(:any)',
            'action'  => function (string $tagSlug) {
                $page = page('tags');

                if (!$page) {
                    return site()->errorPage();
                }

                return $page->render(['tagSlug' => $tagSlug]);
            }
        ],
        [
            'pattern' => 'tags/(:any)',
            'action'  => function (string $tagSlug) {
                $page = page('tags');

                if (!$page) {
                    return site()->errorPage();
                }

                return $page->render(['tagSlug' => $tagSlug]);
            }
        ]
    ],

    // TMDB Integration - keys from .env/server environment only
    'tmdb' => [
        'api_key'      => env('TMDB_API_KEY', ''),
        'access_token' => env('TMDB_ACCESS_TOKEN', '')
    ],

    // Spotify Integration - keys from .env/server environment only
    'spotify' => [
        'client_id'     => env('SPOTIFY_CLIENT_ID', ''),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET', ''),
        'refresh_token' => env('SPOTIFY_REFRESH_TOKEN', '')
    ],

    // Cache
    'cache' => [
        'site.tmdb'    => true,
        'site.spotify' => true,
        'pages' => [
            'active' => $isProduction,
            'ignore' => function ($page) {
                return $page->intendedTemplate()->name() === 'error';
            }
        ]
    ],

    'panel' => [
        'install' => true,
        'slug'    => 'panel',
        'language' => env('PANEL_LANGUAGE', 'tr')
    ],

    // Image Handling
    'timnarr.imagex' => [
        'cache' => true,
        'compareFormatsWeights' => 'mobile',
        'customLazyloading' => false,
        'formats' => ['webp'],
        'addOriginalFormatAsSource' => false,
        'noSrcsetInImg' => false,
        'relativeUrls' => false,
    ],

    'thumbs' => [
        'driver'  => 'gd',
        'quality' => 80,
        'presets' => [
            'avif' => ['format' => 'avif', 'quality' => 75],
            'webp' => ['format' => 'webp', 'quality' => 80]
        ],
        'srcsets' => [
            'default' => [
                '360w'  => ['width' => 360, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '720w'  => ['width' => 720, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '1080w' => ['width' => 1080, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '1440w' => ['width' => 1440, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
            ],
            'default-webp' => [
                '360w'  => ['width' => 360, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '720w'  => ['width' => 720, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '1080w' => ['width' => 1080, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '1440w' => ['width' => 1440, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
            ],
            'card' => [
                '320w' => ['width' => 320, 'crop' => true, 'quality' => 80, 'sharpen' => 10],
                '600w' => ['width' => 600, 'crop' => true, 'quality' => 80, 'sharpen' => 10],
                '900w' => ['width' => 900, 'crop' => true, 'quality' => 80, 'sharpen' => 10],
            ],
            'card-webp' => [
                '320w' => ['width' => 320, 'crop' => true, 'quality' => 74, 'sharpen' => 10, 'format' => 'webp'],
                '600w' => ['width' => 600, 'crop' => true, 'quality' => 74, 'sharpen' => 10, 'format' => 'webp'],
                '900w' => ['width' => 900, 'crop' => true, 'quality' => 74, 'sharpen' => 10, 'format' => 'webp'],
            ],
            'wide' => [
                '640w'  => ['width' => 640, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '960w'  => ['width' => 960, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '1280w' => ['width' => 1280, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '1920w' => ['width' => 1920, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
            ],
            'wide-webp' => [
                '640w'  => ['width' => 640, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '960w'  => ['width' => 960, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '1280w' => ['width' => 1280, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '1920w' => ['width' => 1920, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
            ],
            'hero' => [
                '960w'  => ['width' => 960, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '1440w' => ['width' => 1440, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '1920w' => ['width' => 1920, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
                '2400w' => ['width' => 2400, 'crop' => true, 'quality' => 82, 'sharpen' => 10],
            ],
            'hero-webp' => [
                '960w'  => ['width' => 960, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '1440w' => ['width' => 1440, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '1920w' => ['width' => 1920, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
                '2400w' => ['width' => 2400, 'crop' => true, 'quality' => 76, 'sharpen' => 10, 'format' => 'webp'],
            ],
        ]
    ],
    'site.tmdb' => [
        'expires'  => 10080, // minutes (1 week)
        'language' => 'tr-TR'
    ],
    'site.spotify' => [
        'expires' => 360, // minutes
        'market'  => 'TR'
    ]
];
