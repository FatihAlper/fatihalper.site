<?php
$site = site();
$page = $page ?? page();

// SEO Fallback logic
$seoTitle = $page->seo_title()->or($page->title())->value();
$defaultTitle = $site->default_seo_title()->or($site->site_title())->or($site->title())->value();

if ($page->isHomePage()) {
    $title = $defaultTitle;
} else {
    $title = $seoTitle . ' | ' . $site->site_title()->or($site->title());
}

$description = $page->seo_description()
    ->or($page->summary())
    ->or($page->short_review())
    ->or($page->review_summary())
    ->or($page->description())
    ->or($site->default_seo_description())
    ->or($site->site_description())
    ->value();

if (empty($description) && $page->body()->isNotEmpty()) {
    $description = $page->body()->excerpt(160);
} elseif (empty($description) && $page->text()->isNotEmpty()) {
    $description = $page->text()->excerpt(160);
}

$ogImage = $page->og_image()->toFile() 
    ?? $page->cover()->toFile() 
    ?? $page->poster()->toFile()
    ?? $page->backdrop()->toFile()
    ?? $site->default_og_image()->toFile();

$ogImageUrl = $ogImage ? $ogImage->url() : null;
if (!$ogImageUrl && $page->intendedTemplate()->name() === 'film-review' && function_exists('tmdbMovie')) {
    $tmdb = tmdbMovie($page->tmdb_id()->value());
    $ogImageUrl = tmdbImageUrl($tmdb['backdrop_path'] ?? $tmdb['poster_path'] ?? null, 'w500');
}

$author = $site->site_author()->or($site->title());
$twitterCard = $site->twitter_card_type()->or('summary_large_image');

$favicon = $site->favicon()->toFile();
?>

<?php if ($favicon): ?>
<link rel="shortcut icon" type="<?= $favicon->mime() ?>" href="<?= $favicon->url() ?>">
<?php endif ?>


<title><?= $title ?></title>
<meta name="description" content="<?= $description ?>">
<meta name="author" content="<?= $author ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?= $page->url() ?>">
<meta property="og:title" content="<?= $title ?>">
<meta property="og:description" content="<?= $description ?>">
<?php if ($ogImageUrl): ?>
<meta property="og:image" content="<?= esc($ogImageUrl, 'attr') ?>">
<?php endif ?>

<!-- Twitter -->
<meta property="twitter:card" content="<?= $twitterCard ?>">
<meta property="twitter:url" content="<?= $page->url() ?>">
<meta property="twitter:title" content="<?= $title ?>">
<meta property="twitter:description" content="<?= $description ?>">
<?php if ($ogImageUrl): ?>
<meta property="twitter:image" content="<?= esc($ogImageUrl, 'attr') ?>">
<?php endif ?>

<?php if ($site->canonical_base_url()->isNotEmpty()): 
    $canonicalUrl = rtrim($site->canonical_base_url()->value(), '/') . $page->url(null, true);
?>
<link rel="canonical" href="<?= esc($canonicalUrl, 'attr') ?>">
<?php endif ?>

<?php
$gaMeasurementId = trim((string)$site->ga_measurement_id()->value());
$gaEnabled = $site->ga_enabled()->toBool(false)
  && $gaMeasurementId !== ''
  && (option('site.production') === true || $site->ga_force_local()->toBool(false));
?>
<?php if ($gaEnabled): ?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc($gaMeasurementId, 'attr') ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?= esc($gaMeasurementId) ?>');
</script>
<?php endif ?>

<!-- Design Tokens (Panel Controlled) -->
<style>
:root {
  --color-bg-dark: <?= $site->color_background()->or('#260c1a') ?>;
  --color-text-light: <?= $site->color_text()->or('#f5e9e2') ?>;
  --color-text-muted: <?= $site->color_muted()->or('#bca9a8') ?>;
  --color-accent-gold: <?= $site->color_accent()->or('#c9a45c') ?>;
  --color-accent-wine: <?= $site->color_accent_secondary()->or('#8f2f4a') ?>;
  
  --max-width-content: <?= $site->site_max_width()->or('1200px') ?>;
  --max-width-reading: <?= $site->content_max_width_ch()->or(68) ?>ch;
  --space-lg: <?= $site->section_spacing()->or('clamp(2.5rem, 6vw, 4rem)') ?>;
  
  --page-padding-mobile: 1.5rem;
  --page-padding-desktop: 4rem;
  
  --card-radius: <?= $site->card_radius()->or(0) ?>px;
  --card-border-opacity: <?= $site->card_border_opacity()->or(0.1) ?>;
  
  --hero-height-desktop: clamp(40vh, 50vw, 60vh);
  --hero-height-mobile: clamp(30vh, 40vw, 50vh);
}

<?php if ($site->header_style() == 'sticky'): ?>
.site-header-wrapper {
  position: sticky;
  top: 0;
}
<?php endif ?>

<?php if ($site->enable_soft_hover()->toBool() === false): ?>
.card:hover { transform: none !important; }
<?php endif ?>
</style>
