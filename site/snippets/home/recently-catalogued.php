<?php
/**
 * Recently Catalogued
 * 
 * Shows latest items across all content types.
 * Reads title/limit from home page blueprint.
 */
$home = page('home');
$title = $home->recent_title()->or(fa_t('home.recently_catalogued', 'Son Kataloglananlar'));
$limit = $home->recent_limit()->or(site()->recently_catalogued_limit())->or(6)->toInt();

$recent = site()->index()->listed()->filterBy('intendedTemplate', 'in', [
    'writing', 'book-review', 'film-review', 'playlist', 'photo-album', 'art-project'
])->sortBy('date', 'desc')->limit($limit);

if ($recent->isEmpty()) return;
?>
<section class="home-recent container">
  <h2 class="section-title"><?= $title ?></h2>
  <div class="home-recent__grid">
    <?php foreach ($recent as $item): ?>
      <?php $tpl = $item->intendedTemplate()->name(); ?>
      <?php $metadata = contentCardMetadata($item, 2); ?>
      <a href="<?= $item->url() ?>" class="home-recent-card">
        <span class="home-recent-card__type"><?= contentTypeLabel($tpl, $item) ?></span>
        <h3><?= $item->title() ?></h3>
        <?php if (!empty($metadata)): ?>
          <dl class="home-recent-card__metadata">
            <?php foreach ($metadata as $fact): ?>
              <div>
                <dt><?= $fact['label'] ?></dt>
                <dd><?= $fact['value'] ?></dd>
              </div>
            <?php endforeach ?>
          </dl>
        <?php endif ?>
        <?php if ($date = contentDisplayDate($item)): ?>
          <span class="home-recent-card__date"><?= $date ?></span>
        <?php endif ?>
      </a>
    <?php endforeach ?>
  </div>
</section>
