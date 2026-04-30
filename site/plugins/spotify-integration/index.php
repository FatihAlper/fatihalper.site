<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;

const FA_SPOTIFY_API_BASE = 'https://api.spotify.com/v1';
const FA_SPOTIFY_TOKEN_URL = 'https://accounts.spotify.com/api/token';

function faSpotifyEnv(string $key, mixed $default = null): mixed
{
    if (function_exists('env')) {
        return env($key, $default);
    }

    $value = getenv($key);

    return $value === false ? $default : $value;
}

function faSpotifyCacheKey(string $key): string
{
    return 'spotify-' . md5($key);
}

function faSpotifyCache()
{
    return kirby()->cache('site.spotify');
}

function faSpotifyRequest(string $method, string $url, array $options = []): array
{
    $headers = $options['headers'] ?? [];
    $body = $options['body'] ?? null;

    if (extension_loaded('curl')) {
        $responseHeaders = [];
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => function ($curl, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return $length;
            },
        ]);

        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($raw === false) {
            return [
                'ok' => false,
                'status' => 0,
                'headers' => $responseHeaders,
                'data' => null,
                'error' => 'Spotify isteği başarısız: ' . ($error ?: 'bilinmeyen ağ hatası'),
            ];
        }

        $data = json_decode((string)$raw, true);

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'headers' => $responseHeaders,
            'data' => is_array($data) ? $data : null,
            'error' => $status >= 200 && $status < 300 ? null : faSpotifyErrorMessage($status, is_array($data) ? $data : null, $responseHeaders),
        ];
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body ?? '',
            'timeout' => 12,
            'ignore_errors' => true,
        ]
    ]);
    $raw = @file_get_contents($url, false, $context);
    $responseHeaders = [];
    foreach ($http_response_header ?? [] as $line) {
        $parts = explode(':', $line, 2);
        if (count($parts) === 2) {
            $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
        }
    }
    $statusLine = $http_response_header[0] ?? '';
    preg_match('/\s(\d{3})\s/', $statusLine, $match);
    $status = isset($match[1]) ? (int)$match[1] : 0;
    $data = is_string($raw) ? json_decode($raw, true) : null;

    return [
        'ok' => $status >= 200 && $status < 300,
        'status' => $status,
        'headers' => $responseHeaders,
        'data' => is_array($data) ? $data : null,
        'error' => $status >= 200 && $status < 300 ? null : faSpotifyErrorMessage($status, is_array($data) ? $data : null, $responseHeaders),
    ];
}

function faSpotifyErrorMessage(int $status, ?array $data = null, array $headers = []): string
{
    $apiMessage = $data['error']['message'] ?? $data['error_description'] ?? null;
    $retryAfter = $headers['retry-after'] ?? null;

    return match ($status) {
        401 => 'Spotify kimlik doğrulaması başarısız. Client ID/Secret değerlerini kontrol edin.',
        403 => 'Spotify bu playlist verisine izin vermedi. Public playlist için client credentials yeterlidir; private veya collaborative içerik için Authorization Code/refresh token gerekir.',
        404 => 'Spotify playlist bulunamadı. URL/URI/ID değerini kontrol edin.',
        429 => 'Spotify rate limit uyguladı.' . ($retryAfter ? ' Retry-After: ' . $retryAfter . ' sn.' : ''),
        0 => $apiMessage ?: 'Spotify ağına ulaşılamadı.',
        default => 'Spotify API hatası' . ($status > 0 ? ' (' . $status . ')' : '') . ($apiMessage ? ': ' . $apiMessage : ''),
    };
}

function faSpotifyAccessToken(bool $force = false, string $flow = 'client'): array
{
    $clientId = trim((string)option('spotify.client_id', faSpotifyEnv('SPOTIFY_CLIENT_ID', '')));
    $clientSecret = trim((string)option('spotify.client_secret', faSpotifyEnv('SPOTIFY_CLIENT_SECRET', '')));
    $refreshToken = trim((string)option('spotify.refresh_token', faSpotifyEnv('SPOTIFY_REFRESH_TOKEN', '')));
    $flow = $flow === 'user' ? 'user' : 'client';

    if ($clientId === '' || $clientSecret === '') {
        return ['ok' => false, 'error' => 'SPOTIFY_CLIENT_ID ve SPOTIFY_CLIENT_SECRET .env içinde tanımlı değil.'];
    }

    if ($flow === 'user' && $refreshToken === '') {
        return ['ok' => false, 'error' => 'Track listesi için SPOTIFY_REFRESH_TOKEN gerekli. Client credentials playlist metadata için yeterli, fakat Spotify playlist item endpoint kullanıcı tokenı istiyor.'];
    }

    $cache = faSpotifyCache();
    $cacheKey = faSpotifyCacheKey('token-' . $flow);
    if ($force === false && ($cached = $cache->get($cacheKey))) {
        return ['ok' => true, 'token' => $cached];
    }

    $body = $flow === 'user'
        ? ['grant_type' => 'refresh_token', 'refresh_token' => $refreshToken]
        : ['grant_type' => 'client_credentials'];

    $response = faSpotifyRequest('POST', FA_SPOTIFY_TOKEN_URL, [
        'headers' => [
            'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
            'Content-Type: application/x-www-form-urlencoded',
        ],
        'body' => http_build_query($body),
    ]);

    if ($response['ok'] !== true || empty($response['data']['access_token'])) {
        $tokenType = $flow === 'user' ? 'user access token' : 'access token';
        return ['ok' => false, 'error' => $response['error'] ?? ('Spotify ' . $tokenType . ' alınamadı.')];
    }

    $ttl = max(60, ((int)($response['data']['expires_in'] ?? 3600)) - 60);
    $cache->set($cacheKey, $response['data']['access_token'], (int)ceil($ttl / 60));

    return ['ok' => true, 'token' => $response['data']['access_token']];
}

function faSpotifyPlaylistId(string $input): string
{
    $input = trim($input);
    if ($input === '') {
        return '';
    }

    if (preg_match('/spotify:playlist:([A-Za-z0-9]+)/', $input, $match)) {
        return $match[1];
    }

    if (preg_match('~/playlist/([A-Za-z0-9]+)~', $input, $match)) {
        return $match[1];
    }

    if (preg_match('/^[A-Za-z0-9]{15,}$/', $input)) {
        return $input;
    }

    return '';
}

function faSpotifyGetJson(string $path, string $token, array $query = []): array
{
    $url = str_starts_with($path, 'https://')
        ? $path
        : FA_SPOTIFY_API_BASE . $path . (empty($query) ? '' : '?' . http_build_query($query));

    return faSpotifyRequest('GET', $url, [
        'headers' => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ],
    ]);
}

function faSpotifyFetchPlaylist(string $playlistId, bool $force = false): array
{
    $ttl = max(5, (int)site()->spotify_cache_ttl()->or(option('site.spotify.expires', 360))->value());
    $cacheKey = faSpotifyCacheKey('playlist-v2-' . $playlistId);
    $cache = faSpotifyCache();

    if ($force === false && ($cached = $cache->get($cacheKey))) {
        return ['ok' => true, 'data' => $cached, 'cached' => true];
    }

    $tokenResult = faSpotifyAccessToken();
    if ($tokenResult['ok'] !== true) {
        return ['ok' => false, 'error' => $tokenResult['error']];
    }

    $clientToken = $tokenResult['token'];
    $market = site()->spotify_market()->or('TR')->value();
    $playlistResponse = faSpotifyGetJson('/playlists/' . rawurlencode($playlistId), $clientToken, [
        'market' => $market,
        'fields' => 'id,name,description,external_urls,images,tracks(total)',
    ]);

    if ($playlistResponse['ok'] !== true || !is_array($playlistResponse['data'])) {
        return ['ok' => false, 'error' => $playlistResponse['error'] ?? 'Spotify playlist metadata alınamadı.'];
    }

    $items = [];
    $skipped = [];
    $trackError = null;

    $validTrackCount = count($items);
    $apiTrackTotal = max(0, (int)($playlistResponse['data']['tracks']['total'] ?? 0));
    $totalMs = array_sum(array_map(fn ($track) => (int)($track['duration_ms'] ?? 0), $items));
    $images = $playlistResponse['data']['images'] ?? [];
    $coverUrl = is_array($images) && isset($images[0]['url']) ? (string)$images[0]['url'] : '';

    $data = [
        'playlist' => $playlistResponse['data'],
        'tracks' => $items,
        'skipped' => array_slice($skipped, 0, 20),
        'duration' => $validTrackCount > 0 ? faSpotifyFormatDuration($totalMs) : '',
        'track_count' => $validTrackCount > 0 ? $validTrackCount : $apiTrackTotal,
        'cover_url' => $coverUrl,
        'fetched_at' => date('c'),
        'track_error' => $trackError,
    ];

    $cache->set($cacheKey, $data, $ttl);

    return ['ok' => true, 'data' => $data, 'cached' => false];
}

function faSpotifyFormatDuration(int $durationMs): string
{
    $seconds = (int)floor(max(0, $durationMs) / 1000);
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $remainingSeconds = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    return sprintf('%d:%02d', $minutes, $remainingSeconds);
}

function faSpotifyParseDurationToMs(string|int|float|null $duration): int
{
    if (is_int($duration) || is_float($duration)) {
        return max(0, (int)$duration);
    }

    $duration = trim((string)$duration);
    if ($duration === '') {
        return 0;
    }

    if (preg_match('/^\d+$/', $duration)) {
        return (int)$duration;
    }

    $parts = array_map('intval', explode(':', $duration));
    if (count($parts) < 2 || count($parts) > 3) {
        return 0;
    }

    $seconds = 0;
    foreach ($parts as $part) {
        $seconds = ($seconds * 60) + max(0, $part);
    }

    return $seconds * 1000;
}

function faSpotifyNormalizeJsonTracks(array $payload): array
{
    $rawTracks = $payload['tracks'] ?? $payload;
    if (!is_array($rawTracks)) {
        return ['ok' => false, 'error' => 'JSON içinde tracks dizisi bulunamadı.'];
    }

    $tracks = [];
    $skipped = [];
    foreach ($rawTracks as $index => $track) {
        if (!is_array($track)) {
            $skipped[] = 'Geçersiz satır atlandı: #' . ($index + 1);
            continue;
        }

        $title = trim((string)($track['title'] ?? $track['name'] ?? ''));
        if ($title === '') {
            $skipped[] = 'Başlıksız satır atlandı: #' . ($index + 1);
            continue;
        }

        $artists = $track['artist'] ?? $track['artists'] ?? '';
        if (is_array($artists)) {
            $artists = implode(', ', array_filter(array_map(fn ($artist) => is_array($artist) ? ($artist['name'] ?? '') : (string)$artist, $artists)));
        }

        $durationMs = faSpotifyParseDurationToMs($track['duration_ms'] ?? null);
        if ($durationMs === 0) {
            $durationMs = faSpotifyParseDurationToMs($track['duration'] ?? null);
        }

        $duration = trim((string)($track['duration'] ?? ''));
        if ($duration === '' && $durationMs > 0) {
            $duration = faSpotifyFormatDuration($durationMs);
        }

        $tracks[] = [
            'position' => (int)($track['position'] ?? $track['track_number'] ?? count($tracks) + 1),
            'title' => $title,
            'artist' => trim((string)$artists),
            'album' => trim((string)($track['album'] ?? '')),
            'duration' => $duration,
            'duration_ms' => $durationMs > 0 ? (string)$durationMs : '',
        ];
    }

    $totalMs = array_sum(array_map(fn ($track) => (int)($track['duration_ms'] ?? 0), $tracks));

    return [
        'ok' => true,
        'tracks' => $tracks,
        'track_count' => count($tracks),
        'duration' => $totalMs > 0 ? faSpotifyFormatDuration($totalMs) : '',
        'skipped' => array_slice($skipped, 0, 20),
    ];
}

function faSpotifyImportTracksJson(Page $page, bool $force = false): array
{
    if ($page->intendedTemplate()->name() !== 'playlist') {
        return ['ok' => false, 'error' => 'Bu sayfa playlist template kullanmıyor.'];
    }

    $json = trim((string)$page->spotify_tracks_json()->value());
    if ($json === '') {
        return ['ok' => false, 'error' => 'Spotify parça JSON alanı boş.'];
    }

    $payload = json_decode($json, true);
    if (!is_array($payload)) {
        return ['ok' => false, 'error' => 'JSON okunamadı: ' . json_last_error_msg()];
    }

    if ($page->tracks()->isNotEmpty() && $force === false) {
        return ['ok' => false, 'error' => 'Parçalar alanı dolu. Mevcut listeyi değiştirmek için Force refresh / manuel alanları yenile seçeneğini açın.'];
    }

    $normalized = faSpotifyNormalizeJsonTracks($payload);
    if ($normalized['ok'] !== true) {
        return ['ok' => false, 'error' => $normalized['error'] ?? 'JSON parça listesi dönüştürülemedi.'];
    }

    if ($normalized['track_count'] < 1) {
        return ['ok' => false, 'error' => 'JSON içinde aktarılabilir parça bulunamadı.'];
    }

    $content = [
        'tracks' => $normalized['tracks'],
        'track_count' => $normalized['track_count'],
        'spotify_skip_notes' => implode("\n", $normalized['skipped'] ?? []),
        'spotify_json_import_requested' => false,
        'spotify_force_refresh' => false,
        'spotify_last_error' => '',
    ];

    if ($normalized['duration'] !== '') {
        $content['duration'] = $normalized['duration'];
    }

    $updatedPage = $page->update($content);

    return [
        'ok' => true,
        'message' => sprintf('JSON içe aktarımı tamamlandı: %d parça.', $normalized['track_count']),
        'page' => $updatedPage,
    ];
}

function faSpotifySyncPlaylist(Page $page, bool $force = false): array
{
    if ($page->intendedTemplate()->name() !== 'playlist') {
        return ['ok' => false, 'error' => 'Bu sayfa playlist template kullanmıyor.'];
    }

    $enabled = site()->spotify_enabled()->isEmpty() ? true : site()->spotify_enabled()->toBool(true);
    if ($enabled !== true) {
        return ['ok' => false, 'error' => 'Spotify entegrasyonu Panel ayarlarında kapalı.'];
    }

    $source = $page->spotify_url()->or($page->spotify_playlist_source())->value();
    $playlistId = faSpotifyPlaylistId($source);
    if ($playlistId === '') {
        return ['ok' => false, 'error' => 'Geçerli bir Spotify playlist URL, URI veya ID girin.'];
    }

    $result = faSpotifyFetchPlaylist($playlistId, $force);
    if ($result['ok'] !== true) {
        return ['ok' => false, 'error' => $result['error'] ?? 'Spotify sync başarısız.'];
    }

    $data = $result['data'];
    $playlist = $data['playlist'];
    $content = [
        'spotify_playlist_id' => $playlistId,
        'spotify_last_synced_at' => date('c'),
        'spotify_last_error' => '',
        'spotify_sync_requested' => false,
        'spotify_force_refresh' => false,
        'spotify_cover_url' => $data['cover_url'] ?? '',
        'spotify_skip_notes' => implode("\n", $data['skipped'] ?? []),
    ];

    if (!empty($data['track_error'])) {
        $content['spotify_last_error'] = $data['track_error'];
    }

    $spotifyUrl = $playlist['external_urls']['spotify'] ?? ('https://open.spotify.com/playlist/' . $playlistId);
    if ($page->spotify_url()->isEmpty() || $force) {
        $content['spotify_url'] = $spotifyUrl;
    }
    if ($page->platform()->isEmpty() || $force) {
        $content['platform'] = 'spotify';
    }
    if ($page->playlist_title()->isEmpty() || $force) {
        $content['playlist_title'] = (string)($playlist['name'] ?? '');
    }
    if ($page->description()->isEmpty() || $force) {
        $content['description'] = strip_tags((string)($playlist['description'] ?? ''));
    }
    if ($page->track_count()->isEmpty() || $force) {
        $content['track_count'] = $data['track_count'];
    }
    if ($data['duration'] !== '' && ($page->duration()->isEmpty() || $force)) {
        $content['duration'] = $data['duration'];
    }
    $updatedPage = $page->update($content);

    return [
        'ok' => true,
        'message' => sprintf(
            'Spotify sync tamamlandı: metadata alındı, parça sayısı %d%s. Parça listesi JSON içe aktarımı ile yönetilir.',
            $data['track_count'],
            $result['cached'] ? ' (cache)' : ''
        ),
        'page' => $updatedPage,
    ];
}

function faSpotifyPageStatus(Page $page): string
{
    $lines = [];
    $lines[] = 'Spotify ID: ' . ($page->spotify_playlist_id()->value() ?: 'henüz yok');
    $lines[] = 'Son sync: ' . ($page->spotify_last_synced_at()->isNotEmpty() ? $page->spotify_last_synced_at()->toDate('d.m.Y H:i') : 'henüz yok');
    $lines[] = 'Son hata: ' . ($page->spotify_last_error()->value() ?: 'yok');
    if ($page->spotify_skip_notes()->isNotEmpty()) {
        $lines[] = 'Atlanan item notları: ' . $page->spotify_skip_notes()->value();
    }

    return implode("\n", $lines);
}

function faSpotifySiteStatus(): string
{
    $clientId = trim((string)option('spotify.client_id', faSpotifyEnv('SPOTIFY_CLIENT_ID', '')));
    $clientSecret = trim((string)option('spotify.client_secret', faSpotifyEnv('SPOTIFY_CLIENT_SECRET', '')));
    $refreshToken = trim((string)option('spotify.refresh_token', faSpotifyEnv('SPOTIFY_REFRESH_TOKEN', '')));
    $lines = [];
    $lines[] = 'Durum: ' . (site()->spotify_enabled()->toBool(true) ? 'aktif' : 'kapalı');
    $lines[] = 'Client ID: ' . ($clientId !== '' ? 'tanımlı' : 'eksik');
    $lines[] = 'Client Secret: ' . ($clientSecret !== '' ? 'tanımlı' : 'eksik');
    $lines[] = 'Refresh Token: ' . ($refreshToken !== '' ? 'tanımlı' : 'eksik - track listesi için gerekli');
    $lines[] = 'Cache TTL: ' . site()->spotify_cache_ttl()->or(option('site.spotify.expires', 360))->value() . ' dakika';
    $lines[] = 'Market: ' . site()->spotify_market()->or('TR')->value();
    $lines[] = 'Son test: ' . (site()->spotify_last_tested_at()->isNotEmpty() ? site()->spotify_last_tested_at()->toDate('d.m.Y H:i') : 'henüz yok');
    $lines[] = 'Son hata: ' . (site()->spotify_last_error()->value() ?: 'yok');

    return implode("\n", $lines);
}

Kirby::plugin('site/spotify-integration', [
    'options' => [
        'expires' => 360,
    ],
    'siteMethods' => [
        'spotifyIntegrationStatus' => fn (): string => faSpotifySiteStatus(),
    ],
    'pageMethods' => [
        'spotifySyncStatus' => fn (): string => faSpotifyPageStatus($this),
    ],
    'hooks' => [
        'page.update:after' => function (Page $newPage) {
            static $syncing = false;

            if ($syncing || $newPage->intendedTemplate()->name() !== 'playlist') {
                return;
            }

            $wantsJsonImport = $newPage->spotify_json_import_requested()->toBool() === true;
            $wantsSpotifySync = $newPage->spotify_sync_requested()->toBool() === true;

            if ($wantsJsonImport === false && $wantsSpotifySync === false) {
                return;
            }

            $syncing = true;
            try {
                $workingPage = page($newPage->id()) ?? $newPage;

                if ($wantsSpotifySync) {
                    $result = faSpotifySyncPlaylist($workingPage, $workingPage->spotify_force_refresh()->toBool(false));
                    if ($result['ok'] !== true) {
                        $workingPage = page($workingPage->id()) ?? $workingPage;
                        $workingPage = $workingPage->update([
                            'spotify_last_error' => $result['error'] ?? 'Spotify sync başarısız.',
                            'spotify_sync_requested' => false,
                            'spotify_force_refresh' => false,
                        ]);
                    } elseif (($result['page'] ?? null) instanceof Page) {
                        $workingPage = $result['page'];
                    }
                }

                if ($wantsJsonImport) {
                    $workingPage = page($workingPage->id()) ?? $workingPage;
                    $result = faSpotifyImportTracksJson($workingPage, $workingPage->spotify_force_refresh()->toBool(false));
                    if ($result['ok'] !== true) {
                        $workingPage = page($workingPage->id()) ?? $workingPage;
                        $workingPage = $workingPage->update([
                            'spotify_last_error' => $result['error'] ?? 'JSON içe aktarımı başarısız.',
                            'spotify_json_import_requested' => false,
                            'spotify_force_refresh' => false,
                        ]);
                    } elseif (($result['page'] ?? null) instanceof Page) {
                        $workingPage = $result['page'];
                    }
                }
            } catch (Throwable $e) {
                $errorPage = page($newPage->id()) ?? $newPage;
                $errorPage->update([
                    'spotify_last_error' => $e->getMessage(),
                    'spotify_sync_requested' => false,
                    'spotify_json_import_requested' => false,
                    'spotify_force_refresh' => false,
                ]);
            } finally {
                $syncing = false;
            }
        },
        'site.update:after' => function () {
            static $testing = false;

            if ($testing || site()->spotify_test_connection_requested()->toBool() !== true) {
                return;
            }

            $testing = true;
            try {
                $result = faSpotifyAccessToken(true);
                site()->update([
                    'spotify_last_tested_at' => date('c'),
                    'spotify_last_error' => $result['ok'] === true ? '' : ($result['error'] ?? 'Spotify test başarısız.'),
                    'spotify_test_connection_requested' => false,
                ]);
            } catch (Throwable $e) {
                site()->update([
                    'spotify_last_tested_at' => date('c'),
                    'spotify_last_error' => $e->getMessage(),
                    'spotify_test_connection_requested' => false,
                ]);
            } finally {
                $testing = false;
            }
        },
    ],
]);
