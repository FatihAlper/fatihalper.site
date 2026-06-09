<?php
$site = site();
$logoFile = $site->logo()->toFile() ?? $site->site_logo()->toFile();
$siteTitle = $site->site_title()->or($site->title());
$assetVersion = function (string $path): string {
    $root = kirby()->root('index');
    $production = strtolower((string)env('APP_ENV', '')) === 'production';
    if ($production) {
        $minPath = preg_replace('/\.(css|js)$/', '.min.$1', $path);
        if ($minPath && is_file($root . '/' . ltrim($minPath, '/'))) {
            $path = $minPath;
        }
    }
    $file = $root . '/' . ltrim($path, '/');
    $version = is_file($file) ? (string)filemtime($file) : '1';

    return url($path) . '?v=' . $version;
};
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@300;400;500;600&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
  <link rel="stylesheet" href="<?= esc($assetVersion('assets/css/main.css'), 'attr') ?>">
  <noscript><style>.page-loader{display:none!important}</style></noscript>
  <style>
    :root {
      --logo-size-mobile: 48px;
      --logo-size-tablet: 52px;
      --logo-size-desktop: 56px;
      --color-logo: <?= $site->logo_color()->or('#DDD96A') ?>;
    }
  </style>
  <?php snippet('layout/meta') ?>
</head>
<body>

<div class="page-loader" data-page-loader role="status" aria-label="<?= esc(fa_t('loader.aria', 'Sayfa yükleniyor'), 'attr') ?>">
  <span class="page-loader__spinner" aria-hidden="true"></span>
  <span class="sr-only"><?= fa_t('loader.text', 'Sayfa yükleniyor') ?></span>
</div>

<header class="site-header-wrapper">
  <div class="container site-header">
    <a href="<?= $site->url() ?>" class="site-logo" aria-label="<?= esc(fa_t('nav.aria.home', 'Ana sayfa'), 'attr') ?>">
      <?php if ($logoFile): ?>
        <?php if ($logoFile->extension() === 'svg'): ?>
          <?= svg($logoFile) ?>
        <?php else: ?>
          <?php snippet('components/picture', [
            'file' => $logoFile,
            'resize' => 160,
            'alt' => $siteTitle->value(),
            'lazy' => false,
            'srcset' => 'card',
            'sizes' => '80px'
          ]) ?>
        <?php endif ?>
      <?php else: ?>
        <span class="site-title"><?= $siteTitle ?></span>
      <?php endif ?>
    </a>

    <button class="menu-toggle" aria-label="<?= esc(fa_t('nav.aria.menu', 'Menü'), 'attr') ?>" aria-expanded="false">
      <span></span>
      <span></span>
      <span></span>
    </button>

    <nav class="site-nav">
      <?php if ($site->show_home_link()->toBool()): ?>
        <a href="<?= $site->url() ?>" <?= $page->isHomePage() ? 'class="active"' : '' ?>><?= fa_t('nav.home', 'Giriş') ?></a>
      <?php endif ?>

      <?php 
      $items = $site->main_navigation()->toStructure();
      if ($items->isNotEmpty()):
        foreach ($items as $item): 
          $url = $item->link()->toPage() ? $item->link()->toPage()->url() : $item->external_url()->value();
          if (!$url) continue;
          $linkedPage = $item->link()->toPage();
          $active = $linkedPage && ($page->is($linkedPage) || $page->isDescendantOf($linkedPage));
          ?>
          <a href="<?= $url ?>" 
             <?= $active ? 'class="active"' : '' ?>
             <?= $item->open_in_new_tab()->toBool() ? 'target="_blank"' : '' ?>>
            <?= esc(fa_structure_label($item)) ?>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach ($site->children()->listed() as $item): ?>
          <a href="<?= $item->url() ?>" <?= ($page->is($item) || $page->isDescendantOf($item)) ? 'class="active"' : '' ?>>
            <?= $item->title() ?>
          </a>
        <?php endforeach; ?>
      <?php endif ?>

      <?php if ($site->show_about_link()->toBool()): ?>
        <a href="#about" class="about-trigger"><?= fa_t('nav.about', 'Hakkımda') ?></a>
      <?php endif ?>
    </nav>
  </div>
</header>

<main>
