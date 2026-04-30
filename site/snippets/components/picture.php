<?php
/**
 * Project picture snippet backed by ImageX.
 *
 * Keeps older calls with `resize`, `crop`, `class`, `alt` and `lazy` working while
 * rendering responsive `<picture>` markup with WebP sources for optimizable images.
 */
if (!isset($file) || !$file) return;

$alt = $alt ?? $file->alt()->or($file->caption())->value() ?? '';
$class = $class ?? '';
$classAttr = is_array($class) ? implode(' ', array_filter($class)) : (string)$class;
$lazy = $lazy ?? true;
$crop = $crop ?? null;
$resize = $resize ?? null;
$extension = strtolower($file->extension());
$fetchpriority = $fetchpriority ?? null;
$decoding = $decoding ?? 'async';

$fallback = function () use ($file, $alt, $classAttr, $lazy, $crop, $resize, $extension, $fetchpriority, $decoding) {
    $target = $file;
    $width = $file->width();
    $height = $file->height();
    $canTransform = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

    if ($canTransform && isset($crop) && is_array($crop)) {
        $targetWidth = (int)$crop[0];
        $targetHeight = (int)$crop[1];
        $target = $file->crop($targetWidth, $targetHeight);
        $width = $targetWidth;
        $height = $targetHeight;
    } elseif ($canTransform && isset($resize)) {
        $targetWidth = (int)$resize;
        $target = $file->resize($targetWidth);
        $width = $targetWidth;
        $height = $file->width() > 0
            ? (int)round($file->height() * ($targetWidth / $file->width()))
            : $file->height();
    }

    $attrs = [
        'src' => $target->url(),
        'alt' => $alt,
        'class' => $classAttr ?: null,
        'loading' => $lazy ? 'lazy' : null,
        'fetchpriority' => $fetchpriority,
        'decoding' => $decoding,
        'width' => $width ?: null,
        'height' => $height ?: null,
    ];
    ?>
    <img<?php foreach ($attrs as $key => $value): ?><?php if ($value !== null && ($value !== '' || $key === 'alt')): ?> <?= $key ?>="<?= esc((string)$value, 'attr') ?>"<?php endif ?><?php endforeach ?>>
    <?php
};

$siteOptimization = site()->image_optimization_enabled()->isEmpty()
    ? true
    : site()->image_optimization_enabled()->toBool(true);
$fileOptimization = $file->image_optimization_enabled()->isEmpty()
    ? true
    : $file->image_optimization_enabled()->toBool(true);
$canOptimize = $siteOptimization && $fileOptimization && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

if (!$canOptimize) {
    $fallback();
    return;
}

$ratio = $ratio ?? null;
$fileRatio = $file->image_ratio()->value();
if ($ratio === null && $fileRatio !== '' && $fileRatio !== 'auto') {
    $ratio = $fileRatio;
}

if ($ratio === null && isset($crop) && is_array($crop)) {
    $ratioWidth = max(1, (int)$crop[0]);
    $ratioHeight = max(1, (int)$crop[1]);
    $a = $ratioWidth;
    $b = $ratioHeight;

    while ($b !== 0) {
        $tmp = $b;
        $b = $a % $b;
        $a = $tmp;
    }

    $ratio = ($ratioWidth / $a) . '/' . ($ratioHeight / $a);
}
$ratio = $ratio ?: 'intrinsic';

$fieldLoading = $file->image_loading()->value();
$siteLoading = site()->image_default_loading()->or('lazy')->value();
$loading = $loading ?? ($lazy ? ($fieldLoading && $fieldLoading !== 'default' ? $fieldLoading : $siteLoading) : 'eager');
$loading = in_array($loading, ['eager', 'lazy'], true) ? $loading : 'lazy';

$srcset = $srcset ?? null;
$fileSrcset = $file->image_srcset()->value();
if ($srcset === null && $fileSrcset !== '' && $fileSrcset !== 'auto') {
    $srcset = $fileSrcset;
}

if ($srcset === null) {
    $siteSrcset = site()->image_default_srcset()->or('default')->value();
    $targetWidth = isset($resize) ? (int)$resize : (isset($crop[0]) ? (int)$crop[0] : 0);
    $srcset = $siteSrcset ?: 'default';

    if ($targetWidth >= 1600 || str_contains($classAttr, 'hero')) {
        $srcset = 'hero';
    } elseif ($targetWidth >= 1000) {
        $srcset = 'wide';
    } elseif ($targetWidth > 0 && $targetWidth <= 700) {
        $srcset = 'card';
    }
}

$sizes = $sizes ?? null;
if (!$sizes) {
    $sizes = $file->image_sizes()->value();
}
if (!$sizes) {
    $sizes = site()->image_default_sizes()->value();
}
if (!$sizes) {
    $sizes = match ($srcset) {
        'card' => '(min-width: 900px) 33vw, 100vw',
        'wide' => '(min-width: 900px) 1200px, 100vw',
        'hero' => '100vw',
        default => '(min-width: 900px) 800px, 100vw',
    };
}

$attributes = $attributes ?? [];
$loadingModeKeys = ['shared', 'eager', 'lazy'];
$imgAttributes = $attributes['img'] ?? [];
$sourceAttributes = $attributes['sources'] ?? [];
$sharedImgAttributes = [
    'alt' => $alt,
    'class' => $class,
    'sizes' => $sizes,
    'decoding' => $decoding,
];
if ($fetchpriority) {
    $sharedImgAttributes['fetchpriority'] = $fetchpriority;
}
$sharedSourceAttributes = [
    'sizes' => $sizes,
];

if (!empty(array_intersect(array_keys($imgAttributes), $loadingModeKeys))) {
    $imgAttributes['shared'] = array_merge($imgAttributes['shared'] ?? [], $sharedImgAttributes);
} else {
    $imgAttributes = array_merge($imgAttributes, $sharedImgAttributes);
}

if (!empty(array_intersect(array_keys($sourceAttributes), $loadingModeKeys))) {
    $sourceAttributes['shared'] = array_merge($sourceAttributes['shared'] ?? [], $sharedSourceAttributes);
} else {
    $sourceAttributes = array_merge($sourceAttributes, $sharedSourceAttributes);
}

$attributes['img'] = $imgAttributes;
$attributes['sources'] = $sourceAttributes;

try {
    snippet('imagex-picture', [
        'image' => $file,
        'attributes' => $attributes,
        'compareFormats' => false,
        'loading' => $loading,
        'ratio' => $ratio,
        'srcset' => $srcset,
    ]);
} catch (Throwable $e) {
    if (option('debug') === true) {
        echo '<!-- ImageX error: ' . esc($e->getMessage(), 'html') . ' -->';
    }

    $fallback();
}
