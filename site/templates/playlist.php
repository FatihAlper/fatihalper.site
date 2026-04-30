<?php snippet('layout/header') ?>

<?php
$cover = $page->cover()->toFile();
$spotifyCoverUrl = $page->spotify_cover_url()->value();
$spotifyUrl = $page->spotify_url()->value();
$showCover = $page->show_cover()->isEmpty() ? true : $page->show_cover()->toBool(true);
$hasCover = $showCover && ($cover || $spotifyCoverUrl);
$tracks = $page->tracks()->toStructure();
$trackCount = $page->track_count()->isNotEmpty() ? $page->track_count()->value() : ($tracks->isNotEmpty() ? $tracks->count() : null);
?>

<div class="container single-playlist layout-sidebar-main">
  <aside class="sticky-meta-panel">
    <?php if ($hasCover): ?>
      <figure class="playlist-cover-frame">
        <?php if ($cover): ?>
          <?php snippet('components/picture', [
            'file' => $cover,
            'class' => 'playlist-cover-large',
            'resize' => 600,
            'alt' => $page->playlist_title()->or($page->title())->value(),
            'lazy' => false,
            'fetchpriority' => 'high'
          ]) ?>
        <?php else: ?>
          <?php if ($spotifyUrl): ?><a href="<?= esc($spotifyUrl, 'attr') ?>" target="_blank" rel="noopener" aria-label="Spotify playlist"><?php endif ?>
            <img src="<?= esc($spotifyCoverUrl, 'attr') ?>" alt="<?= esc($page->playlist_title()->or($page->title())->value(), 'attr') ?>" class="playlist-cover-large" loading="eager" fetchpriority="high" decoding="async">
          <?php if ($spotifyUrl): ?></a><?php endif ?>
        <?php endif ?>
        <figcaption class="playlist-cover-label"><?= fa_t('playlist.archive', 'Müzik Arşivi') ?></figcaption>
      </figure>
    <?php endif ?>

    <div class="playlist-meta">
      <?php if (!$hasCover): ?>
        <span class="meta-label"><?= fa_t('playlist.archive', 'Müzik Arşivi') ?></span>
      <?php endif ?>
      <h1><?= $page->playlist_title()->or($page->title()) ?></h1>

      <?php if ($page->description()->isNotEmpty()): ?>
        <div class="playlist-summary">
          <p><?= $page->description() ?></p>
        </div>
      <?php endif ?>

      <dl class="museum-label">
        <div><dt><?= fa_t('playlist.platform', 'Platform') ?></dt><dd><?= Str::upper($page->platform()->value() ?: 'Spotify') ?></dd></div>
        <?php if ($trackCount): ?>
          <div><dt><?= fa_t('playlist.track_count', 'Parça Sayısı') ?></dt><dd><?= $trackCount ?></dd></div>
        <?php endif ?>
        <?php if ($page->duration()->isNotEmpty()): ?>
          <div><dt><?= fa_t('meta.duration', 'Süre') ?></dt><dd><?= $page->duration() ?></dd></div>
        <?php endif ?>
      </dl>

      <?php if ($spotifyUrl): ?>
        <a href="<?= esc($spotifyUrl, 'attr') ?>" target="_blank" rel="noopener" class="btn-primary btn-primary--spaced"><?= fa_t('playlist.listen', 'Platformda Dinle') ?></a>
      <?php endif ?>
    </div>
  </aside>

  <div class="playlist-content">
    <?php if ($page->embed_code()->isNotEmpty()): ?>
      <div class="spotify-embed">
        <?= $page->embed_code() ?>
      </div>
    <?php endif ?>

    <div class="review-text writing-body">
      <?= contentRenderBody($page->body()) ?>
    </div>

    <?php if ($tracks->isNotEmpty()): ?>
      <ol class="track-list">
        <?php foreach ($tracks as $index => $track): ?>
          <li class="track-item">
            <div class="track-info">
              <span class="track-number"><?php printf('%02d', $track->position()->isNotEmpty() ? $track->position()->toInt() : $index + 1); ?></span>
              <div>
                <?php if ($track->spotify_url()->isNotEmpty()): ?>
                  <a class="track-title" href="<?= esc($track->spotify_url(), 'attr') ?>" target="_blank" rel="noopener"><?= $track->title() ?></a>
                <?php else: ?>
                  <span class="track-title"><?= $track->title() ?></span>
                <?php endif ?>
                <?php
                  $trackMeta = array_filter([
                    $track->artist()->isNotEmpty() ? $track->artist()->value() : null,
                    $track->album()->isNotEmpty() ? $track->album()->value() : null
                  ]);
                ?>
                <?php if ($trackMeta !== []): ?>
                  <span class="track-artist"><?= esc(implode(' — ', $trackMeta)) ?></span>
                <?php endif ?>
              </div>
            </div>
            <div class="track-actions">
              <?php if ($track->duration()->isNotEmpty()): ?>
                <span class="track-duration"><?= $track->duration() ?></span>
              <?php endif ?>
            </div>
          </li>
        <?php endforeach ?>
      </ol>
    <?php endif ?>

    <?php snippet('components/tags', ['tags' => $page->mood_tags()]) ?>
  </div>
</div>

<?php snippet('layout/footer') ?>
