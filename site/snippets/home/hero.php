<?php
/**
 * Home Hero / Opening Threshold
 *
 * Reads fields from the home page blueprint (pages/home.yml).
 * Legacy site-level hero fields remain as fallbacks for existing content.
 */
$home = page('home');
$kicker = $home->hero_kicker();
$heroText = $home->hero_text()
    ->or(site()->hero_manifesto())
    ->or(site()->hero_title())
    ->or('Yaz&#305;lar&#305;n, imgelerin, filmlerin, kitaplar&#305;n, seslerin ve yar&#305;m kalm&#305;&#351; saplant&#305;lar&#305;n ki&#351;isel ar&#351;ivi.');
$bgColor = $home->hero_background_color()->or('#260c1a');
$bgImage = $home->hero_background_image()->toFile() ?? site()->hero_background_image()->toFile();
$enableGrain = $home->hero_enable_grain()->toBool();
$overlayOpacity = $home->hero_overlay_opacity()->or(0.4);
$heroStyle = "background-color: {$bgColor};";
if ($home->hero_min_height()->isNotEmpty()) {
    $heroStyle .= "--home-hero-min-height: {$home->hero_min_height()};";
}
if ($home->hero_text_max_width()->isNotEmpty()) {
    $heroStyle .= "--home-hero-text-max-width: {$home->hero_text_max_width()};";
}
if ($bgImage) {
    $heroStyle .= "background-image: linear-gradient(rgba(0,0,0,{$overlayOpacity}), rgba(0,0,0,{$overlayOpacity})), " . optimizedBackgroundImage($bgImage, 1920) . ";";
}
$animationField = $home->hero_animation_enabled();
$animationEnabled = $animationField->isNotEmpty()
    ? $animationField->toBool(true)
    : site()->hero_animation_enabled()->toBool(true);
?>
<section class="home-hero <?= $animationEnabled ? 'is-animated' : '' ?> <?= $enableGrain ? 'has-grain' : '' ?>"
         style="<?= esc($heroStyle, 'attr') ?>">
  <div class="container home-hero__content">
    <?php if ($kicker->isNotEmpty()): ?>
      <span class="home-hero__kicker"><?= $kicker ?></span>
    <?php endif ?>
    <p class="home-hero__text"><?= $heroText ?></p>
  </div>
</section>
