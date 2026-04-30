<?php
/**
 * Rooms / Content Sections
 * 
 * If the home page has manually defined rooms, use those.
 * Otherwise, fall back to listed children with default accent colors.
 */
$home = page('home');
$defaultColors = roomAccentColors();
$manualRooms = $home->rooms()->toStructure();
?>

<section class="home-rooms container">
  <div class="home-rooms__grid">
    <?php if ($manualRooms->isNotEmpty()): ?>
      <?php foreach ($manualRooms as $room): ?>
        <?php $color = $room->accent_color()->or('var(--color-accent-gold)'); ?>
        <a href="<?= $room->link()->or('#') ?>" class="home-room-card" style="--accent: <?= $color ?>;">
          <div class="home-room-card__content">
            <h3><?= $room->title() ?></h3>
            <?php if ($room->description()->isNotEmpty()): ?>
              <p><?= $room->description() ?></p>
            <?php endif ?>
          </div>
        </a>
      <?php endforeach ?>
    <?php else: ?>
      <?php foreach (site()->children()->listed() as $room): ?>
        <?php $color = $defaultColors[$room->uid()] ?? 'var(--color-accent-gold)'; ?>
        <a href="<?= $room->url() ?>" class="home-room-card" style="--accent: <?= $color ?>;">
          <div class="home-room-card__content">
            <h3><?= $room->title() ?></h3>
            <p><?= $room->description()->or($room->summary()) ?></p>
          </div>
        </a>
      <?php endforeach ?>
    <?php endif ?>
  </div>
</section>
