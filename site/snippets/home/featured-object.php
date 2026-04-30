<?php
/**
 * Featured Object / Vitrin
 * 
 * Reads from home page blueprint (pages/home.yml).
 * Supports image/description overrides from Panel.
 */
$home = page('home');
$featured = $home->featured_page()->toPage() ?? site()->featured_object()->toPage();
if (!$featured) return;

$tpl = $featured->intendedTemplate()->name();
$label = $home->featured_label()->or('Vitrindeki Nesne');
$cta = contentCtaLabel($tpl);

// Allow Panel overrides for image and description
$coverOverride = $home->featured_image_override()->toFile();
$descOverride = $home->featured_description_override();

$coverField = contentCoverField($tpl);
$cover = $coverOverride ?? $featured->{$coverField}()->toFile();
$desc = $descOverride->isNotEmpty() 
    ? $descOverride 
    : $featured->summary()->or($featured->review_summary())->or($featured->description());
?>
<section class="home-featured container">
  <div class="home-featured__inner">
    <?php if ($cover): ?>
      <div class="home-featured__image">
        <a href="<?= $featured->url() ?>">
          <?php snippet('components/picture', ['file' => $cover, 'crop' => [800, 600]]) ?>
        </a>
      </div>
    <?php endif ?>
    <div class="home-featured__content">
      <span class="meta-label"><?= $label ?></span>
      <h3><a href="<?= $featured->url() ?>"><?= $featured->title() ?></a></h3>
      <?php if ($desc->isNotEmpty()): ?>
        <p class="home-featured__desc"><?= $desc->excerpt(200) ?></p>
      <?php endif ?>
      <a href="<?= $featured->url() ?>" class="btn-primary"><?= $cta ?></a>
    </div>
  </div>
</section>
