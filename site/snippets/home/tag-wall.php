<?php
/**
 * Tag Wall / Mental Map
 * 
 * Reads mode/tags from home page blueprint.
 */
$home = page('home');
$title = $home->tag_wall_title()->or(site()->tag_wall_title())->or('Zihinsel Harita');
$mode = $home->tag_wall_mode()->or(site()->tag_wall_mode())->or('from_tags')->value();
$tags = [];

$manualTags = $home->manual_tags()->isNotEmpty() ? $home->manual_tags() : site()->manual_tags();

if ($mode === 'manual' && $manualTags->isNotEmpty()) {
    $tags = $manualTags->split(',');
} else {
    $tags = array_map(fn ($tag) => $tag['name'], array_values(contentTagIndex()));
    $tags = array_slice($tags, 0, $home->tag_wall_limit()->or(40)->toInt());
}

if (count($tags) === 0) return;
$alignment = $home->tag_wall_alignment()->or('center');
?>
<section class="home-tag-wall container" style="--home-tag-wall-align: <?= esc($alignment, 'attr') ?>;">
  <h2 class="section-title"><?= $title ?></h2>
  <div class="home-tag-wall__wrapper" role="list" aria-label="<?= $title ?>">
    <?php foreach ($tags as $tag): ?>
      <?php $tag = trim($tag); ?>
      <a href="<?= esc(contentTagUrl($tag), 'attr') ?>" class="home-tag-wall__tag" role="listitem"><?= esc($tag) ?></a>
    <?php endforeach ?>
  </div>
</section>
