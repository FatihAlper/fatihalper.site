<?php
/**
 * Card Component
 * 
 * Reusable card for archive listing pages.
 * Usage: snippet('components/card', ['item' => $item])
 */
$item = $item ?? null;
if (!$item) return;

$showFallback = $showFallback ?? false;
$tpl = $item->intendedTemplate()->name();
$typeLabel = contentTypeLabel($tpl, $item);
$image = contentPrimaryImage($item);
$metadata = contentCardMetadata($item);
?>

<article class="card card-<?= $tpl ?>">
  <a href="<?= $item->url() ?>" class="card-link-overlay" aria-label="<?= $item->title() ?>"></a>
  
  <?php if ($image['file'] || $image['url']): ?>
    <div class="card-image-wrapper">
      <?php if ($image['file']): ?>
        <?php snippet('components/picture', [
          'file' => $image['file'],
          'crop' => [600, 400],
          'class' => 'card-image',
          'alt' => ''
        ]) ?>
      <?php else: ?>
        <img src="<?= esc($image['url'], 'attr') ?>" alt="" class="card-image" loading="lazy" decoding="async">
      <?php endif ?>
    </div>
  <?php elseif ($showFallback): ?>
    <div class="card-image-wrapper">
      <div class="card-image card-image--fallback" aria-hidden="true">
        <?= esc(mb_substr($item->title()->value(), 0, 1, 'UTF-8')) ?>
      </div>
    </div>
  <?php endif ?>
  
  <div class="card-content">
    <div class="card-eyebrow">
      <span class="meta-label card-type">
        <?= $typeLabel ?>
        <?php if ($item->date()->isNotEmpty()): ?>
          &middot; <?= contentDisplayDate($item) ?>
        <?php endif ?>
      </span>
      <?php if ($item->rating()->isNotEmpty()): ?>
        <span class="card-rating">
          <?php snippet('components/rating-stars', ['rating' => $item->rating()->toFloat()]) ?>
        </span>
      <?php endif ?>
    </div>
    
    <h2><?= $item->title() ?></h2>

    <?php if (!empty($metadata)): ?>
      <dl class="card-metadata">
        <?php foreach ($metadata as $fact): ?>
          <div>
            <dt><?= $fact['label'] ?></dt>
            <dd><?= $fact['value'] ?></dd>
          </div>
        <?php endforeach ?>
      </dl>
    <?php endif ?>
    
    <?php if ($item->subtitle()->isNotEmpty()): ?>
      <p class="card-subtitle"><?= $item->subtitle() ?></p>
    <?php elseif ($item->review_summary()->isNotEmpty()): ?>
      <p class="card-subtitle"><?= $item->review_summary()->excerpt(100) ?></p>
    <?php endif ?>
  </div>
</article>
