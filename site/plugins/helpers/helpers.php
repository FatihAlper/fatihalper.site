<?php
/**
 * Content Type Helpers
 * 
 * Shared lookup maps and helper functions used across templates and snippets.
 */

/**
 * Canonical content type registry for the archive desk.
 *
 * The current Panel uses Kirby-native blueprint sections. A future custom Panel
 * area can read this map to decide which parent, template and blueprint belong
 * to each editorial content type.
 */
function contentTypeRegistry(): array {
    return [
        'writing' => [
            'label' => 'Yazi / makale',
            'translation_key' => 'content.type.writing',
            'parent' => 'fragmanlar',
            'template' => 'writing',
            'blueprint' => 'pages/writing',
            'create_label' => 'Yeni yazi',
            'required_fields' => ['title', 'date', 'body'],
            'optional_fields' => ['subtitle', 'summary', 'quote_highlight', 'writing_type', 'tags'],
            'frontend_fields' => ['title', 'subtitle', 'summary', 'body', 'text', 'quote_highlight', 'quotes', 'writing_type', 'date', 'tags', 'cover'],
            'seo_fields' => ['seo_title', 'seo_description', 'og_image'],
            'image_fields' => ['cover'],
            'tag_fields' => ['tags'],
            'body_field' => 'body',
        ],
        'book-review' => [
            'label' => 'Kitap kaydi',
            'translation_key' => 'content.type.book',
            'parent' => 'marginalia',
            'template' => 'book-review',
            'blueprint' => 'pages/book-review',
            'create_label' => 'Yeni kitap kaydi',
            'required_fields' => ['title', 'book_title', 'author', 'body'],
            'optional_fields' => ['review_summary', 'date', 'rating', 'tags', 'original_year', 'publisher', 'translator', 'page_count', 'isbn'],
            'frontend_fields' => ['title', 'book_title', 'author', 'review_summary', 'body', 'date', 'rating', 'tags', 'cover', 'original_year', 'publisher', 'translator', 'page_count', 'isbn'],
            'seo_fields' => ['seo_title', 'seo_description', 'og_image'],
            'image_fields' => ['cover'],
            'tag_fields' => ['tags'],
            'body_field' => 'body',
        ],
        'film-review' => [
            'label' => 'Film kaydi',
            'translation_key' => 'content.type.film',
            'parent' => 'perde',
            'template' => 'film-review',
            'blueprint' => 'pages/film-review',
            'create_label' => 'Yeni film kaydi',
            'required_fields' => ['title', 'date'],
            'optional_fields' => ['film_title', 'original_title', 'tmdb_id', 'rating', 'director', 'release_year', 'runtime', 'countries', 'genres', 'short_review', 'body', 'tags'],
            'frontend_fields' => ['title', 'film_title', 'original_title', 'tmdb_id', 'rating', 'date', 'director', 'release_year', 'runtime', 'countries', 'genres', 'short_review', 'body', 'tags', 'poster', 'backdrop'],
            'seo_fields' => ['seo_title', 'seo_description', 'og_image'],
            'image_fields' => ['poster', 'backdrop'],
            'tag_fields' => ['tags'],
            'body_field' => 'body',
        ],
        'playlist' => [
            'label' => 'Playlist kaydi',
            'translation_key' => 'content.type.playlist',
            'parent' => 'rezonans',
            'template' => 'playlist',
            'blueprint' => 'pages/playlist',
            'create_label' => 'Yeni playlist',
            'required_fields' => ['title', 'date'],
            'optional_fields' => ['playlist_title', 'description', 'body', 'tracks', 'mood_tags', 'platform', 'spotify_url', 'spotify_playlist_id', 'spotify_cover_url', 'track_count', 'duration', 'embed_code'],
            'frontend_fields' => ['title', 'playlist_title', 'description', 'body', 'tracks', 'mood_tags', 'platform', 'spotify_url', 'spotify_playlist_id', 'spotify_cover_url', 'track_count', 'duration', 'embed_code', 'cover'],
            'seo_fields' => ['seo_title', 'seo_description', 'og_image'],
            'image_fields' => ['cover'],
            'tag_fields' => ['mood_tags'],
            'body_field' => 'body',
        ],
        'photo-album' => [
            'label' => 'Fotograf albumu',
            'translation_key' => 'content.type.photo',
            'parent' => 'kadraj',
            'template' => 'photo-album',
            'blueprint' => 'pages/photo-album',
            'create_label' => 'Yeni fotograf albumu',
            'required_fields' => ['title', 'gallery'],
            'optional_fields' => ['subtitle', 'date', 'location', 'statement', 'cover', 'tags', 'camera', 'lens', 'film_stock'],
            'frontend_fields' => ['title', 'subtitle', 'date', 'location', 'statement', 'gallery', 'cover', 'tags', 'camera', 'lens', 'film_stock'],
            'seo_fields' => ['seo_title', 'seo_description', 'og_image'],
            'image_fields' => ['cover', 'gallery'],
            'tag_fields' => ['tags'],
            'file_blueprint' => 'files/photo',
        ],
        'art-project' => [
            'label' => 'Exhibit kaydi',
            'translation_key' => 'content.type.art',
            'parent' => 'exhibit',
            'template' => 'art-project',
            'blueprint' => 'pages/art-project',
            'create_label' => 'Yeni exhibit kaydi',
            'required_fields' => ['title', 'gallery'],
            'optional_fields' => ['body', 'statement', 'cover', 'curator_note', 'date', 'year', 'production_duration', 'materials', 'paper', 'technique', 'dimensions', 'edition', 'inventory_code', 'status', 'tags'],
            'frontend_fields' => ['title', 'body', 'statement', 'gallery', 'cover', 'curator_note', 'year', 'production_duration', 'materials', 'paper', 'technique', 'dimensions', 'edition', 'inventory_code', 'status', 'tags'],
            'seo_fields' => ['seo_title', 'seo_description', 'og_image'],
            'image_fields' => ['cover', 'gallery'],
            'tag_fields' => ['tags'],
            'body_field' => 'body',
            'file_blueprint' => 'files/art-image',
        ],
    ];
}

function contentTypeDefinition(string $template): array|null {
    return contentTypeRegistry()[$template] ?? null;
}

function dashboardFormatBytes(int|float $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max(0, (float)$bytes);
    $index = 0;

    while ($bytes >= 1024 && $index < count($units) - 1) {
        $bytes /= 1024;
        $index++;
    }

    return round($bytes, $index === 0 ? 0 : 1) . ' ' . $units[$index];
}

function dashboardDirectorySize(string $path): int {
    if (is_dir($path) !== true) {
        return 0;
    }

    $size = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }

    return $size;
}

function dashboardContentPages(): array {
    $pages = [];

    foreach (contentTypeRegistry() as $type => $definition) {
        $parent = page($definition['parent']);
        if (!$parent) {
            continue;
        }

        foreach ($parent->childrenAndDrafts() as $item) {
            if ($item->intendedTemplate()->name() === $definition['template']) {
                $pages[] = $item;
            }
        }
    }

    return $pages;
}

function dashboardContentStats(): string {
    $lines = [];
    $total = $listed = $drafts = $unlisted = 0;
    $latestModified = null;

    foreach (contentTypeRegistry() as $type => $definition) {
        $parent = page($definition['parent']);
        $children = $parent ? $parent->childrenAndDrafts()->filterBy('intendedTemplate', $definition['template']) : new Kirby\Cms\Pages([]);
        $typeTotal = $children->count();
        $typeListed = $children->listed()->count();
        $typeDrafts = $children->drafts()->count();
        $typeUnlisted = $children->unlisted()->count();

        $total += $typeTotal;
        $listed += $typeListed;
        $drafts += $typeDrafts;
        $unlisted += $typeUnlisted;

        foreach ($children as $page) {
            if ($latestModified === null || $page->modified() > $latestModified->modified()) {
                $latestModified = $page;
            }
        }

        $latest = $children->sortBy('date', 'desc', 'modified', 'desc')->limit(3)->pluck('title');
        $lines[] = sprintf(
            '%s: %d toplam / %d yayında / %d taslak / %d görünmez. Son: %s',
            $definition['label'],
            $typeTotal,
            $typeListed,
            $typeDrafts,
            $typeUnlisted,
            implode(', ', $latest) ?: 'yok'
        );
    }

    array_unshift($lines, sprintf(
        "Toplam içerik: %d\nYayında: %d\nTaslak: %d\nGörünmez/unlisted: %d\nSon güncellenen: %s\n",
        $total,
        $listed,
        $drafts,
        $unlisted,
        $latestModified ? $latestModified->title()->value() . ' (' . $latestModified->modified('d.m.Y H:i') . ')' : 'yok'
    ));

    return implode("\n", $lines);
}

function dashboardTagStats(): string {
    $tags = function_exists('contentTagIndex') ? contentTagIndex() : [];
    uasort($tags, fn ($a, $b) => $b['count'] <=> $a['count']);

    $moodTags = [];
    foreach (page('rezonans')?->childrenAndDrafts() ?? [] as $playlist) {
        foreach ($playlist->mood_tags()->split(',') as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
                $moodTags[contentTagNormalize($tag)] = $tag;
            }
        }
    }

    $distribution = [];
    foreach (contentTypeRegistry() as $type => $definition) {
        $distribution[] = $type . ': ' . implode(', ', $definition['tag_fields']);
    }

    return implode("\n", [
        'Toplam tag: ' . count($tags),
        'Mood tag: ' . count($moodTags),
        'En çok kullanılanlar: ' . (implode(', ', array_slice(array_map(fn ($tag) => $tag['name'] . ' (' . $tag['count'] . ')', $tags), 0, 10)) ?: 'yok'),
        'Tag alanları: ' . implode(' / ', $distribution),
    ]);
}

function dashboardSeoWarnings(): string {
    $warnings = [];

    foreach (dashboardContentPages() as $page) {
        $template = $page->intendedTemplate()->name();
        $definition = contentTypeDefinition($template);

        if (($definition['body_field'] ?? null) && $page->{$definition['body_field']}()->isEmpty()) {
            $warnings[] = $page->title()->value() . ': body boş';
        }

        foreach ($definition['image_fields'] ?? [] as $field) {
            if ($field !== 'gallery' && $page->{$field}()->isEmpty()) {
                $warnings[] = $page->title()->value() . ': ' . $field . ' eksik';
            }
        }

        if ($page->seo_title()->isEmpty()) {
            $warnings[] = $page->title()->value() . ': SEO title eksik';
        }

        if ($page->seo_description()->isEmpty()) {
            $warnings[] = $page->title()->value() . ': SEO description eksik';
        }

        if (in_array($template, ['book-review', 'film-review'], true) && $page->rating()->isEmpty()) {
            $warnings[] = $page->title()->value() . ': rating eksik';
        }
    }

    return $warnings === []
        ? 'Kritik içerik metadata uyarısı yok.'
        : implode("\n", array_slice($warnings, 0, 20)) . (count($warnings) > 20 ? "\n+" . (count($warnings) - 20) . ' uyarı daha' : '');
}

function dashboardMediaStats(): string {
    $files = [];
    foreach ([kirby()->root('content'), kirby()->root('media')] as $root) {
        if (is_dir($root) !== true) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match('/\.(jpe?g|png|gif|webp|avif|svg)$/i', $file->getFilename())) {
                $files[] = $file;
            }
        }
    }

    usort($files, fn ($a, $b) => $b->getSize() <=> $a->getSize());
    $large = array_slice(array_map(
        fn ($file) => $file->getFilename() . ' (' . dashboardFormatBytes($file->getSize()) . ')',
        array_filter($files, fn ($file) => $file->getSize() > 1024 * 1024)
    ), 0, 8);

    $missingAlt = 0;
    $missingLightbox = 0;
    foreach (site()->index()->files()->filterBy('type', 'image') as $file) {
        if ($file->alt()->isEmpty()) {
            $missingAlt++;
        }

        if ($file->template() && in_array($file->template(), ['photo', 'art-image'], true) && $file->lightbox_caption()->isEmpty()) {
            $missingLightbox++;
        }
    }

    return implode("\n", [
        'Görsel dosya sayısı: ' . count($files),
        'Content medya boyutu: ' . dashboardFormatBytes(dashboardDirectorySize(kirby()->root('content'))),
        'Generated media boyutu: ' . dashboardFormatBytes(dashboardDirectorySize(kirby()->root('media'))),
        'Büyük görseller: ' . (implode(', ', $large) ?: 'yok'),
        'Alt text eksik Kirby görselleri: ' . $missingAlt,
        'Lightbox caption eksik görseller: ' . $missingLightbox,
    ]);
}

function dashboardCacheStats(): string {
    $site = site();
    $cacheRoot = kirby()->root('cache');
    $mediaRoot = kirby()->root('media');
    $contentRoot = kirby()->root('content');
    $sessionRoot = kirby()->root('sessions');

    return implode("\n", [
        'Page cache config: ' . (option('cache.pages.active') ? 'aktif' : 'pasif/local'),
        'Cache klasörü yazılabilir: ' . (is_writable($cacheRoot) ? 'evet' : 'hayır'),
        'Media klasörü yazılabilir: ' . (is_writable($mediaRoot) ? 'evet' : 'hayır'),
        'Content klasörü yazılabilir: ' . (is_writable($contentRoot) ? 'evet' : 'hayır'),
        'Sessions klasörü yazılabilir: ' . (is_writable($sessionRoot) ? 'evet' : 'hayır'),
        'Lazyload varsayılanı: ' . $site->image_default_loading()->or('lazy')->value(),
        'Image optimization: ' . ($site->image_optimization_enabled()->toBool(true) ? 'aktif' : 'pasif'),
        'Production assetler: main.min.css / main.min.js mevcut',
        'Cache boyutu: ' . dashboardFormatBytes(dashboardDirectorySize($cacheRoot)),
        'Media/generated boyutu: ' . dashboardFormatBytes(dashboardDirectorySize($mediaRoot)),
        'Son cache temizleme: ' . ($site->cache_last_cleared_at()->isNotEmpty() ? $site->cache_last_cleared_at()->toDate('d.m.Y H:i') : 'henüz yok'),
        'Son cache temizleme sonucu: ' . ($site->cache_last_clear_status()->value() ?: 'henüz yok'),
    ]);
}

function dashboardClearCaches(): array {
    $kirby = kirby();
    $cacheRoot = $kirby->root('cache');
    $sizeBefore = dashboardDirectorySize($cacheRoot);
    $errors = [];

    foreach (['pages', 'site.tmdb', 'site.spotify'] as $cacheName) {
        try {
            $kirby->cache($cacheName)->flush();
        } catch (Throwable $e) {
            $errors[] = $cacheName . ': ' . $e->getMessage();
        }
    }

    try {
        if (is_dir($cacheRoot) === true) {
            \Kirby\Filesystem\Dir::remove($cacheRoot);
        }

        \Kirby\Filesystem\Dir::make($cacheRoot, true);
    } catch (Throwable $e) {
        $errors[] = 'cache root: ' . $e->getMessage();
    }

    $sizeAfter = dashboardDirectorySize($cacheRoot);

    if ($errors !== []) {
        return [
            'ok' => false,
            'message' => 'Cache kısmen temizlendi. Hata: ' . implode(' | ', $errors),
            'size_before' => $sizeBefore,
            'size_after' => $sizeAfter,
        ];
    }

    return [
        'ok' => true,
        'message' => 'Cache temizlendi: ' . dashboardFormatBytes($sizeBefore) . ' -> ' . dashboardFormatBytes($sizeAfter),
        'size_before' => $sizeBefore,
        'size_after' => $sizeAfter,
    ];
}

function dashboardSystemStats(): string {
    $server = $_SERVER['SERVER_SOFTWARE'] ?? 'CLI / bilinmiyor';
    $isLiteSpeed = stripos($server, 'litespeed') !== false || stripos($server, 'openlitespeed') !== false;
    $gdInfo = extension_loaded('gd') ? gd_info() : [];

    return implode("\n", [
        'PHP: ' . PHP_VERSION . ' / ' . PHP_SAPI,
        'Server: ' . $server,
        'LiteSpeed/OpenLiteSpeed: ' . ($isLiteSpeed ? 'evet' : 'hayır / tespit edilmedi'),
        'memory_limit: ' . ini_get('memory_limit'),
        'max_execution_time: ' . ini_get('max_execution_time') . ' sn',
        'upload_max_filesize: ' . ini_get('upload_max_filesize'),
        'post_max_size: ' . ini_get('post_max_size'),
        'max_file_uploads: ' . ini_get('max_file_uploads'),
        'OPcache: ' . (extension_loaded('Zend OPcache') ? 'aktif' : 'kapalı'),
        'Imagick: ' . (extension_loaded('imagick') ? 'aktif' : 'kapalı'),
        'GD: ' . (extension_loaded('gd') ? 'aktif' : 'kapalı'),
        'WebP support: ' . (($gdInfo['WebP Support'] ?? false) ? 'evet' : 'hayır'),
        'AVIF support: ' . (($gdInfo['AVIF Support'] ?? false) ? 'evet' : 'hayır'),
        'intl: ' . (extension_loaded('intl') ? 'aktif' : 'kapalı'),
        'mbstring: ' . (extension_loaded('mbstring') ? 'aktif' : 'kapalı'),
        'curl: ' . (extension_loaded('curl') ? 'aktif' : 'kapalı'),
        'fileinfo: ' . (extension_loaded('fileinfo') ? 'aktif' : 'kapalı'),
        'zip: ' . (extension_loaded('zip') ? 'aktif' : 'kapalı'),
    ]);
}

function dashboardAnalyticsStatus(): string {
    $site = site();
    $measurementId = trim((string)$site->ga_measurement_id()->value());
    $enabled = $site->ga_enabled()->toBool(false);
    $reportUrl = trim((string)$site->ga_report_url()->value());
    $credentials = option('analytics.ga4.credentials') ?: getenv('GA4_CREDENTIALS');
    $propertyId = option('analytics.ga4.property_id') ?: getenv('GA4_PROPERTY_ID');

    return implode("\n", [
        'Frontend gtag: ' . ($enabled && $measurementId !== '' ? 'hazır' : 'pasif / Measurement ID eksik'),
        'Measurement ID: ' . ($measurementId !== '' ? $measurementId : 'tanımlı değil'),
        'Local/dev davranışı: production değilse ga_force_local kapalıyken pasif',
        'Rapor linki: ' . ($reportUrl !== '' ? $reportUrl : 'eklenmedi'),
        'Data API credentials: ' . ($credentials ? 'tanımlı' : 'tanımlı değil'),
        'GA4 property ID: ' . ($propertyId ?: 'tanımlı değil'),
        'Dashboard veri seviyesi: ' . ($credentials && $propertyId ? 'Data API için hazır iskelet' : 'bağlantı durumu + hızlı link'),
    ]);
}

function dashboardHealthChecks(): string {
    $warnings = [];
    $site = site();
    $memoryLimit = ini_get('memory_limit');
    $memoryMb = preg_match('/^(\d+)/', $memoryLimit, $match) ? (int)$match[1] : 0;

    if (option('debug') === true) {
        $warnings[] = 'Debug açık.';
    }

    if (option('cache.pages.active') !== true && option('site.production') === true) {
        $warnings[] = 'Production cache kapalı görünüyor.';
    }

    foreach ([kirby()->root('cache'), kirby()->root('media'), kirby()->root('content'), kirby()->root('sessions')] as $path) {
        if (is_writable($path) !== true) {
            $warnings[] = basename($path) . ' yazılabilir değil.';
        }
    }

    if ($memoryMb > 0 && $memoryMb < 128) {
        $warnings[] = 'PHP memory_limit düşük: ' . $memoryLimit;
    }

    if ($site->ga_enabled()->toBool(false) && $site->ga_measurement_id()->isEmpty()) {
        $warnings[] = 'GA aktif ama Measurement ID boş.';
    }

    $localNeedles = ['localhost', '127.0.0.1', 'kirby.test', 'kirby.localhost'];
    $content = file_get_contents(kirby()->root('content') . '/site.txt') ?: '';
    foreach ($localNeedles as $needle) {
        if (stripos($content, $needle) !== false) {
            $warnings[] = 'Site içeriğinde local URL bulundu: ' . $needle;
        }
    }

    if (is_file(kirby()->root('index') . '/composer.json') && is_dir(kirby()->root('index') . '/vendor') !== true && is_dir(kirby()->root('kirby') . '/vendor') !== true) {
        $warnings[] = 'Composer vendor bulunamadı.';
    }

    return $warnings === []
        ? 'Kritik health check uyarısı yok.'
        : implode("\n", $warnings);
}

/**
 * Returns a localized label for a given Kirby template name.
 */
function contentTypeLabel(string $template, $page = null): string {
    if ($template === 'writing' && $page) {
        $value = $page->writing_type()->value();
        $labels = [
            'essay'   => fa_t('content.type.essay', 'Deneme'),
            'article' => fa_t('content.type.article', 'Makale'),
            'note'    => fa_t('content.type.note', 'Not'),
        ];

        if (isset($labels[$value])) {
            return $labels[$value];
        }
    }

    if ($definition = contentTypeDefinition($template)) {
        return fa_t($definition['translation_key'], $definition['label']);
    }

    $map = [
        'writing'      => fa_t('content.type.writing', 'Yazı'),
        'book-review'  => fa_t('content.type.book', 'Kitap'),
        'film-review'  => fa_t('content.type.film', 'Film'),
        'playlist'     => fa_t('content.type.playlist', 'Müzik'),
        'photo-album'  => fa_t('content.type.photo', 'Fotoğraf'),
        'art-project'  => fa_t('content.type.art', 'Sanat'),
    ];

    return $map[$template] ?? fa_t('content.type.catalog', 'Katalog Kaydı');
}

/**
 * Renders the shared body field safely during the writer/text -> blocks transition.
 */
function contentRenderBody($field): string {
    if (!$field || $field->isEmpty()) {
        return '';
    }

    $value = trim((string)$field->value());
    if ($value === '') {
        return '';
    }

    if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
        return (string)$field->toBlocks();
    }

    return (string)$field->kt();
}

/**
 * Returns a primary visual for cards, marquees and SEO fallbacks.
 */
function contentPrimaryImage($page): array {
    $template = $page->intendedTemplate()->name();

    foreach ([contentCoverField($template), 'cover', 'poster', 'backdrop'] as $field) {
        if ($file = $page->$field()->toFile()) {
            return ['file' => $file, 'url' => null, 'alt' => $page->title()->value()];
        }
    }

    if ($template === 'photo-album' && $page->gallery()->isNotEmpty()) {
        if ($file = $page->gallery()->toFiles()->first()) {
            return ['file' => $file, 'url' => null, 'alt' => $page->title()->value()];
        }
    }

    if ($template === 'film-review' && function_exists('tmdbMovie')) {
        $tmdb = tmdbMovie($page->tmdb_id()->value());
        $poster = tmdbImageUrl($tmdb['poster_path'] ?? null);
        if ($poster) {
            return ['file' => null, 'url' => $poster, 'alt' => $page->film_title()->or($page->title())->value()];
        }
    }

    return ['file' => null, 'url' => null, 'alt' => ''];
}

/**
 * Returns compact metadata facts for archive and home cards.
 */
function contentCardMetadata($page, int $limit = 3): array {
    $template = $page->intendedTemplate()->name();
    $facts = [];
    $add = function (string $label, $value) use (&$facts): void {
        $value = trim((string)$value);
        if ($value !== '') {
            $facts[] = ['label' => $label, 'value' => $value];
        }
    };
    $tags = function ($field, int $max = 3): string {
        $values = array_values(array_filter(array_map('trim', explode(',', (string)$field->value()))));
        return implode(', ', array_slice($values, 0, $max));
    };

    switch ($template) {
        case 'photo-album':
            $add(fa_t('meta.camera', 'Kamera'), $page->camera());
            $add(fa_t('meta.lens', 'Lens'), $page->lens());
            $add(fa_t('meta.film', 'Film'), $page->film_stock());
            break;

        case 'playlist':
            $tracks = $page->tracks()->toStructure();
            $trackCount = $page->track_count()->isNotEmpty()
                ? $page->track_count()->value()
                : ($tracks->isNotEmpty() ? $tracks->count() : '');
            if ($trackCount !== '') {
                $add(fa_t('meta.track', 'Parça'), $trackCount . ' ' . fa_t('unit.track', 'parça'));
            }
            $add(fa_t('meta.genre', 'Tür'), $tags($page->mood_tags()));
            $add(fa_t('meta.duration', 'Süre'), $page->duration());
            break;

        case 'book-review':
            $add(fa_t('meta.author', 'Yazar'), $page->author());
            $add(fa_t('meta.year', 'Yıl'), $page->original_year());
            $add(fa_t('meta.publisher', 'Yayınevi'), $page->publisher());
            $add(fa_t('meta.page', 'Sayfa'), $page->page_count()->isNotEmpty() ? $page->page_count() . ' ' . fa_t('unit.page.short', 'sf.') : '');
            break;

        case 'film-review':
            $tmdb = [];
            if ($page->tmdb_id()->isNotEmpty() && function_exists('tmdbMovie')) {
                $tmdb = tmdbMovie($page->tmdb_id()->value());
            }
            $priority = site()->tmdb_manual_override_priority()->toBool(true);
            $tmdbDirector = function_exists('tmdbDirector') ? tmdbDirector($tmdb) : '';
            $director = $priority
                ? ($page->director()->value() ?: $tmdbDirector)
                : ($tmdbDirector ?: $page->director()->value());
            $tmdbYear = isset($tmdb['release_date']) ? substr($tmdb['release_date'], 0, 4) : '';
            $year = $priority
                ? ($page->release_year()->value() ?: $tmdbYear)
                : ($tmdbYear ?: $page->release_year()->value());
            $manualRuntime = $page->runtime()->value();
            if (is_numeric($manualRuntime)) {
                $manualRuntime .= ' ' . fa_t('unit.minute.short', 'dk');
            }
            $tmdbRuntime = ($tmdb['runtime'] ?? null) ? $tmdb['runtime'] . ' ' . fa_t('unit.minute.short', 'dk') : '';
            $runtime = $priority ? ($manualRuntime ?: $tmdbRuntime) : ($tmdbRuntime ?: $manualRuntime);
            $tmdbGenres = function_exists('tmdbGenres') ? tmdbGenres($tmdb) : [];
            $tmdbGenres = !empty($tmdbGenres) ? implode(', ', array_slice($tmdbGenres, 0, 3)) : '';
            $genres = $priority
                ? ($page->genres()->value() ?: $tmdbGenres)
                : ($tmdbGenres ?: $page->genres()->value());

            $add(fa_t('meta.director', 'Yönetmen'), $director);
            $add(fa_t('meta.year', 'Yıl'), $year);
            $add(fa_t('meta.duration', 'Süre'), $runtime);
            $add(fa_t('meta.genre', 'Tür'), $genres);
            break;

        case 'art-project':
            $add(fa_t('meta.year', 'Yıl'), $page->artwork_date()->or($page->year()));
            $add(fa_t('meta.technique', 'Teknik'), $page->technique()->or($page->medium()));
            $add(fa_t('meta.dimensions', 'Boyut'), $page->dimensions());
            $add(fa_t('meta.material', 'Malzeme'), $page->materials());
            break;

        case 'writing':
            $add(fa_t('meta.genre', 'Tür'), contentTypeLabel('writing', $page));
            $add(fa_t('meta.tags', 'Etiket'), $tags($page->tags()));
            break;
    }

    return array_slice($facts, 0, $limit);
}

/**
 * Returns a formatted date or an empty string when no date is set.
 */
function contentDisplayDate($page, string $format = 'd.m.Y'): string {
    return $page->date()->isNotEmpty() ? $page->date()->toDate($format) : '';
}

/**
 * Returns the cover image field name for a given template.
 */
function contentCoverField(string $template): string {
    return $template === 'film-review' ? 'poster' : 'cover';
}

/**
 * Returns the tag fields used by searchable editorial content.
 */
function contentTagFields($page): array {
    $map = [
        'writing'      => ['tags'],
        'book-review'  => ['tags'],
        'film-review'  => ['tags'],
        'playlist'     => ['mood_tags'],
        'photo-album'  => ['tags'],
        'art-project'  => ['tags'],
    ];

    return $map[$page->intendedTemplate()->name()] ?? [];
}

/**
 * Normalizes a tag for matching while keeping the original display value.
 */
function contentTagNormalize(string $tag): string {
    $tag = trim(html_entity_decode($tag, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $tag = preg_replace('/\s+/u', ' ', $tag) ?? $tag;

    if (extension_loaded('intl') && class_exists('Transliterator')) {
        // Use 'Any-Lower' for maximum compatibility and handle dotted İ manually
        $tag = str_replace('İ', 'i', $tag);
        try {
            $lowered = transliterator_transliterate('Any-Lower', $tag);
            if ($lowered !== false) {
                return $lowered;
            }
        } catch (\Throwable $e) {
            // Fallback if transliterator creation fails despite class existing
        }
    }

    return \Kirby\Toolkit\Str::lower($tag);
}

/**
 * Converts tag labels to URL-safe slugs, including Turkish characters.
 */
function contentTagSlug(string $tag): string {
    $tag = trim($tag);
    $ascii = strtr($tag, [
        'ç' => 'c', 'Ç' => 'c',
        'ğ' => 'g', 'Ğ' => 'g',
        'ı' => 'i', 'I' => 'i', 'İ' => 'i',
        'ö' => 'o', 'Ö' => 'o',
        'ş' => 's', 'Ş' => 's',
        'ü' => 'u', 'Ü' => 'u',
    ]);

    $slug = \Kirby\Toolkit\Str::slug($ascii);

    return $slug !== '' ? $slug : substr(md5($tag), 0, 10);
}

/**
 * Returns the public tag index URL, preferring the Turkish discovery route.
 */
function contentTagsUrl(): string {
    return url('etiketler');
}

/**
 * Returns the public URL for a tag detail page.
 */
function contentTagUrl(string $tag): string {
    return contentTagsUrl() . '/' . contentTagSlug($tag);
}

/**
 * Reads all tag values for a page and deduplicates case variants.
 */
function contentTagsForPage($page): array {
    $tags = [];

    foreach (contentTagFields($page) as $fieldName) {
        $field = $page->{$fieldName}();
        if ($field->isEmpty()) {
            continue;
        }

        foreach ($field->split(',') as $tag) {
            $tag = trim($tag);
            if ($tag === '') {
                continue;
            }

            $key = contentTagNormalize($tag);
            $tags[$key] ??= $tag;
        }
    }

    return array_values($tags);
}

/**
 * Returns all listed pages that can participate in tag search.
 */
function contentTaggableItems() {
    return site()->index()->listed()->filter(function ($item) {
        return count(contentTagFields($item)) > 0;
    });
}

/**
 * Returns template filters used by the tag discovery page.
 */
function contentTagTypeFilters(): array {
    return [
        'writing'      => fa_t('content.type.writing', 'Yazi'),
        'film-review'  => fa_t('content.type.film', 'Film'),
        'book-review'  => fa_t('content.type.book', 'Kitap'),
        'playlist'     => fa_t('content.type.playlist', 'Muzik'),
        'photo-album'  => fa_t('content.type.photo', 'Fotograf'),
        'art-project'  => fa_t('content.type.art', 'Sanat'),
    ];
}

/**
 * Builds the tag index with display names, slugs, counts and type hints.
 */
function contentTagIndex(): array {
    $tags = [];

    foreach (contentTaggableItems() as $item) {
        foreach (contentTagsForPage($item) as $tag) {
            $key = contentTagNormalize($tag);
            $template = $item->intendedTemplate()->name();

            if (!isset($tags[$key])) {
                $tags[$key] = [
                    'key'   => $key,
                    'name'  => $tag,
                    'slug'  => contentTagSlug($tag),
                    'count' => 0,
                    'types' => [],
                ];
            }

            $tags[$key]['count']++;
            $tags[$key]['types'][$template] = contentTypeLabel($template, $item);
        }
    }

    uasort($tags, function ($a, $b) {
        return strcoll($a['name'], $b['name']);
    });

    return $tags;
}

/**
 * Finds a canonical tag entry by URL slug.
 */
function contentTagBySlug(string $slug): array|null {
    $slug = contentTagSlug($slug);

    foreach (contentTagIndex() as $tag) {
        if ($tag['slug'] === $slug) {
            return $tag;
        }
    }

    return null;
}

/**
 * Returns listed content matching the canonical tag entry.
 */
function contentTaggedItems(array $tag) {
    return contentTaggableItems()
        ->filter(function ($item) use ($tag) {
            foreach (contentTagsForPage($item) as $itemTag) {
                if (contentTagNormalize($itemTag) === $tag['key']) {
                    return true;
                }
            }

            return false;
        })
        ->sortBy('date', 'desc', 'title', 'asc');
}

/**
 * Builds query-based pagination links while preserving the current search query.
 */
function contentPaginationUrl(string $baseUrl, int $page): string {
    $query = kirby()->request()->query()->toArray();

    if ($page <= 1) {
        unset($query['page']);
    } else {
        $query['page'] = $page;
    }

    $query = array_filter($query, function ($value) {
        return $value !== null && $value !== '';
    });

    return $baseUrl . (count($query) > 0 ? '?' . http_build_query($query) : '');
}

/**
 * Returns a contextual CTA label for a given template.
 */
function contentCtaLabel(string $template): string {
    $map = [
        'writing'      => fa_t('cta.read', 'Oku'),
        'book-review'  => fa_t('cta.read', 'Oku'),
        'film-review'  => fa_t('cta.review', 'İncele'),
        'playlist'     => fa_t('cta.listen', 'Dinle'),
        'photo-album'  => fa_t('cta.view', 'Gör'),
        'art-project'  => fa_t('cta.explore', 'Keşfet'),
    ];
    return $map[$template] ?? fa_t('cta.details', 'Detaylar');
}

/**
 * Room accent colors — defaults when Panel doesn't override.
 */
// CSS background helper with WebP support.
function optimizedBackgroundImage($file, int $width = 1920): string {
    if (!$file) {
        return '';
    }

    try {
        $fallbackUrl = $file->resize($width)->url();
    } catch (Throwable $e) {
        $fallbackUrl = $file->url();
    }

    $extension = strtolower($file->extension());
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        return "url('{$fallbackUrl}')";
    }

    try {
        $webpUrl = $file->thumb([
            'width' => $width,
            'quality' => 76,
            'format' => 'webp',
            'sharpen' => 10,
        ])->url();

        return "image-set(url('{$webpUrl}') type('image/webp'), url('{$fallbackUrl}'))";
    } catch (Throwable $e) {
        return "url('{$fallbackUrl}')";
    }
}

function roomAccentColors(): array {
    return [
        'fragmanlar'   => '#8f2f4a',
        'marginalia'   => '#c9a45c',
        'perde'        => '#b86b4b',
        'rezonans'     => '#8f7aa8',
        'kadraj'       => '#24344d',
        'exhibit'      => '#6f7650',
        'writings'    => '#8f2f4a',
        'books'       => '#c9a45c',
        'cinema'      => '#b86b4b',
        'playlists'   => '#8f7aa8',
        'photography' => '#24344d',
        'art'         => '#6f7650',
    ];
}
