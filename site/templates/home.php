<?php snippet('layout/header') ?>

<?php
$home = $page;
$site = site();
$showHomeModule = function (string $field, bool $default = true) use ($home, $site): bool {
    $homeField = $home->{$field}();
    if ($homeField->isNotEmpty()) {
        return $homeField->toBool($default);
    }

    return $site->{$field}()->toBool($default);
};
?>

<div class="home-page">
  <?php snippet('home/hero') ?>

  <?php if ($showHomeModule('show_marquee_gallery')): ?>
    <?php snippet('home/marquee-gallery') ?>
  <?php endif ?>

  <?php if ($showHomeModule('show_rooms')): ?>
    <?php snippet('home/rooms') ?>
  <?php endif ?>

  <?php snippet('home/featured-object') ?>

  <?php if ($showHomeModule('show_recently_catalogued')): ?>
    <?php snippet('home/recently-catalogued') ?>
  <?php endif ?>

  <?php if ($showHomeModule('show_tag_wall')): ?>
    <?php snippet('home/tag-wall') ?>
  <?php endif ?>

  <?php if ($showHomeModule('show_manifesto_strip')): ?>
    <?php snippet('home/manifesto-strip') ?>
  <?php endif ?>
</div>

<?php snippet('layout/footer') ?>
