<?php
$rating = max(0, min(5, (float)($rating ?? 0)));
$label = $label ?? 'Puan: ' . number_format($rating, 1, '.', '') . ' / 5';
$GLOBALS['site_rating_uid'] = ($GLOBALS['site_rating_uid'] ?? 0) + 1;
$uid = 'rating-star-' . $GLOBALS['site_rating_uid'];
$starPath = 'M12 2.4l2.96 6 6.62.96-4.79 4.67 1.13 6.59L12 18.48 6.08 21.6l1.13-6.59-4.79-4.67 6.62-.96L12 2.4z';
?>
<div class="rating-stars" aria-label="<?= esc($label, 'attr') ?>" title="<?= esc(number_format($rating, 1, '.', '') . ' / 5', 'attr') ?>">
  <?php for ($i = 0; $i < 5; $i++): ?>
    <?php
    $fill = max(0, min(1, $rating - $i));
    $clipWidth = round($fill * 24, 3);
    $clipId = $uid . '-' . $i;
    ?>
    <svg class="rating-star" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <defs>
        <clipPath id="<?= esc($clipId, 'attr') ?>">
          <rect x="0" y="0" width="<?= esc((string)$clipWidth, 'attr') ?>" height="24"></rect>
        </clipPath>
      </defs>
      <path class="rating-star__base" d="<?= esc($starPath, 'attr') ?>"></path>
      <path class="rating-star__fill" clip-path="url(#<?= esc($clipId, 'attr') ?>)" d="<?= esc($starPath, 'attr') ?>"></path>
    </svg>
  <?php endfor ?>
</div>
