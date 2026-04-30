<?php snippet('layout/header') ?>

<?php
$photos = $page->gallery()->toFiles();
if ($photos->isEmpty()) {
    $photos = $page->images()->filterBy('template', 'photo');
}
?>

<div class="container single-album">
  <header class="album-header">
    <span class="meta-label">Görsel Kayıt</span>
    <h1><?= $page->title() ?></h1>
    <?php if ($page->subtitle()->isNotEmpty()): ?>
      <p class="album-subtitle"><?= $page->subtitle() ?></p>
    <?php endif ?>
    
    <div class="album-facts">
      <div class="album-meta">
        <?php if ($page->location()->isNotEmpty()): ?>
          <span><?= $page->location() ?></span><?php if ($page->date()->isNotEmpty()): ?> &middot;<?php endif ?>
        <?php endif ?>
        <?php if ($page->date()->isNotEmpty()): ?>
          <span><?= $page->date()->toDate('d M Y') ?></span>
        <?php endif ?>
      </div>

      <div class="gear-strip">
         <?php if ($page->camera()->isNotEmpty()): ?>
           <span><strong>Cam:</strong> <?= $page->camera() ?></span>
         <?php endif ?>
         <?php if ($page->lens()->isNotEmpty()): ?>
           <span><strong>Lens:</strong> <?= $page->lens() ?></span>
         <?php endif ?>
         <?php if ($page->film_stock()->isNotEmpty()): ?>
           <span><strong>Film:</strong> <?= $page->film_stock() ?></span>
         <?php endif ?>
      </div>
    </div>

    <?php if ($page->statement()->isNotEmpty()): ?>
      <div class="album-summary curatorial-statement">
        <?= $page->statement()->kt() ?>
      </div>
    <?php endif ?>
  </header>

  <div class="photo-grid masonry-grid">
    <?php foreach ($photos as $photo): ?>
      <?php $photoDate = $photo->photo_date()->isNotEmpty() ? $photo->photo_date()->toDate('d M Y') : ''; ?>
      <?php $lightboxEnabled = $photo->lightbox_enabled()->toBool(true); ?>
      <div class="photo-item <?= $lightboxEnabled ? 'js-lightbox-trigger' : '' ?>" 
           <?php if ($lightboxEnabled): ?>
           data-src="<?= esc($photo->url(), 'attr') ?>"
           data-title="<?= esc($photo->title()->or($photo->name()), 'attr') ?>"
           data-caption="<?= esc($photo->lightbox_caption()->or($photo->caption()), 'attr') ?>"
           data-alt-text="<?= esc($photo->alt(), 'attr') ?>"
           data-camera="<?= esc($photo->camera(), 'attr') ?>"
           data-lens="<?= esc($photo->lens(), 'attr') ?>"
           data-focal-length="<?= esc($photo->focal_length(), 'attr') ?>"
           data-iso="<?= esc($photo->iso(), 'attr') ?>"
           data-shutter="<?= esc($photo->shutter_speed(), 'attr') ?>"
           data-aperture="<?= esc($photo->aperture(), 'attr') ?>"
           data-date="<?= esc($photoDate, 'attr') ?>"
           data-image-note="<?= esc($photo->visual_descriptors(), 'attr') ?>"
           data-technical-note="<?= esc($photo->atmosphere(), 'attr') ?>"
           data-material-note="<?= esc($photo->credit(), 'attr') ?>"
           <?php endif ?>>
        
        <?php snippet('components/picture', [
          'file' => $photo,
          'resize' => 800,
          'class' => 'gallery-item'
        ]) ?>

        <div class="photo-overlay">
          <span><?= $photo->title()->or($photo->name()) ?></span>
        </div>
      </div>
    <?php endforeach ?>
    <?php if ($photos->isEmpty()): ?>
      <p class="empty-state">Bu albüme henüz fotoğraf eklenmedi.</p>
    <?php endif ?>
  </div>

  <footer class="album-footer">
    <?php snippet('components/tags', ['tags' => $page->tags()]) ?>
  </footer>
</div>

<?php snippet('components/lightbox') ?>
<?php snippet('layout/footer') ?>
