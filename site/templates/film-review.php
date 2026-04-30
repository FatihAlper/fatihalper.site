<?php snippet('layout/header') ?>

<?php
$tmdb = tmdbMovie($page->tmdb_id()->value());
$priority = site()->tmdb_manual_override_priority()->toBool(true);

// Manual fields take priority when the site-level toggle is enabled.
$title = $page->film_title()->or($page->title())->value();
$originalTitle = $priority
    ? ($page->original_title()->value() ?: ($tmdb['original_title'] ?? ''))
    : (($tmdb['original_title'] ?? '') ?: $page->original_title()->value());

$tmdbDirector = tmdbDirector($tmdb);
$director = $priority
    ? ($page->director()->value() ?: $tmdbDirector)
    : ($tmdbDirector ?: $page->director()->value());

$tmdbYear = isset($tmdb['release_date']) ? substr($tmdb['release_date'], 0, 4) : '';
$year = $priority
    ? ($page->release_year()->value() ?: $tmdbYear)
    : ($tmdbYear ?: $page->release_year()->value());

$manualRuntime = $page->runtime()->value();
if (is_numeric($manualRuntime)) {
    $manualRuntime .= ' dk';
}
$tmdbRuntime = ($tmdb['runtime'] ?? null) ? $tmdb['runtime'] . ' dk' : '';
$runtime = $priority ? ($manualRuntime ?: $tmdbRuntime) : ($tmdbRuntime ?: $manualRuntime);

$tmdbGenres = tmdbGenres($tmdb);
$tmdbGenres = !empty($tmdbGenres) ? implode(', ', $tmdbGenres) : '';
$genres = $priority
    ? ($page->genres()->value() ?: $tmdbGenres)
    : ($tmdbGenres ?: $page->genres()->value());

$tmdbCountries = !empty($tmdb['production_countries'])
    ? implode(', ', array_column($tmdb['production_countries'], 'name'))
    : '';
$countries = $priority
    ? ($page->countries()->value() ?: $tmdbCountries)
    : ($tmdbCountries ?: $page->countries()->value());

// Resolve media
$poster = $page->poster()->toFile();
$posterUrl = $poster ? $poster->url() : tmdbImageUrl($tmdb['poster_path'] ?? null);

$backdrop = $page->backdrop()->toFile();
$backdropUrl = $backdrop ? $backdrop->url() : tmdbImageUrl($tmdb['backdrop_path'] ?? null, 'original');
$backdropCss = $backdrop
    ? optimizedBackgroundImage($backdrop, 1920)
    : ($backdropUrl ? "url('{$backdropUrl}')" : '');
?>

<?php if ($backdropCss): ?>
  <div class="film-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.3), var(--color-bg-dark)), <?= esc($backdropCss, 'attr') ?>;"></div>
<?php else: ?>
  <div class="film-hero-fallback"></div>
<?php endif ?>

<div class="container single-film layout-sidebar-main layout-sidebar-main--offset">
  <div class="sidebar-poster-group">
    <div class="sidebar-poster">
      <?php if ($posterUrl): ?>
        <?php if ($poster): ?>
          <?php snippet('components/picture', [
            'file' => $poster,
            'class' => 'film-poster-large',
            'resize' => 600,
            'alt' => $title,
            'lazy' => false,
            'fetchpriority' => 'high'
          ]) ?>
        <?php else: ?>
          <img src="<?= esc($posterUrl, 'attr') ?>" alt="<?= esc($title, 'attr') ?>" class="film-poster-large" loading="eager" fetchpriority="high" decoding="async">
        <?php endif ?>
      <?php endif ?>
    </div>

    <div class="sidebar-meta">
      <div class="large-rating">
        <?php snippet('components/rating-stars', ['rating' => $page->rating()->toFloat()]) ?>
      </div>

      <dl class="museum-label">
        <?php if ($director): ?>
          <div><dt>Y&#246;netmen</dt><dd><?= $director ?></dd></div>
        <?php endif ?>
        <?php if ($year): ?>
          <div><dt>Y&#305;l</dt><dd><?= $year ?></dd></div>
        <?php endif ?>
        <?php if ($runtime): ?>
          <div><dt>S&#252;re</dt><dd><?= $runtime ?></dd></div>
        <?php endif ?>
        <?php if ($genres): ?>
          <div><dt>Janr</dt><dd><?= $genres ?></dd></div>
        <?php endif ?>
        <?php if ($countries): ?>
          <div><dt>&#220;lke</dt><dd><?= $countries ?></dd></div>
        <?php endif ?>
      </dl>
    </div>
  </div>

  <article class="film-review-body reading-content">
    <header>
      <span class="meta-label">Sinematik Kay&#305;t</span>
      <h1><?= $title ?></h1>
      <?php if ($originalTitle && $originalTitle !== $title): ?>
        <p class="original-title"><?= $originalTitle ?></p>
      <?php endif ?>
    </header>

    <?php if ($page->short_review()->isNotEmpty()): ?>
      <div class="review-summary">
        <?= $page->short_review()->kt() ?>
      </div>
    <?php endif ?>

    <div class="review-text writing-body">
      <?= contentRenderBody($page->body()) ?>
    </div>

    <?php snippet('components/tags', ['tags' => $page->tags()]) ?>
  </article>
</div>

<?php snippet('layout/footer') ?>
