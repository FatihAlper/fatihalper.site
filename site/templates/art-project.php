<?php snippet('layout/header') ?>

<?php
$cover = $page->cover()->toFile();
$selectedGallery = $page->gallery()->toFiles();
$gallery = $selectedGallery->isNotEmpty()
    ? $selectedGallery
    : $page->images()->filterBy('template', 'art-image');
if ($cover) {
    $filteredGallery = $gallery->filter(fn ($image) => $image->id() !== $cover->id());
    if ($filteredGallery->isNotEmpty()) {
        $gallery = $filteredGallery;
    }
}
$galleryCount = $gallery->count();
$galleryCountClass = match (true) {
    $galleryCount === 1 => 'art-gallery--count-1',
    $galleryCount === 2 => 'art-gallery--count-2',
    $galleryCount === 3 => 'art-gallery--count-3',
    $galleryCount === 4 => 'art-gallery--count-4',
    $galleryCount >= 5 => 'art-gallery--count-many',
    default => 'art-gallery--count-0',
};
$catalogFields = [
    'year' => fa_t('art.production_year', 'Yapım yılı'),
    'production_duration' => fa_t('art.production_duration', 'Yapım süresi'),
    'materials' => fa_t('art.material_surface', 'Malzeme / yüzey'),
    'technique' => fa_t('meta.technique', 'Teknik'),
    'dimensions' => fa_t('meta.dimensions', 'Boyut'),
    'edition' => fa_t('art.edition', 'Edisyon'),
    'inventory_code' => fa_t('art.inventory_code', 'Arşiv numarası'),
    'status' => fa_t('art.status', 'Durum'),
];
$contentField = function (string $key) use ($page) {
    if ($key === 'materials' && $page->materials()->isEmpty()) {
        return $page->paper();
    }

    if ($key === 'status') {
        $statusField = $page->content()->get('status');
        if ($statusField->isEmpty()) {
            return $statusField;
        }

        $status = $statusField->value();
        $labels = [
            'draft' => fa_t('art.status_draft', 'Taslak / üretimde'),
            'completed' => fa_t('art.status_completed', 'Tamamlandı'),
            'exhibited' => fa_t('art.status_exhibited', 'Sergilendi'),
            'archived' => fa_t('art.status_archived', 'Arşivde'),
            'unavailable' => fa_t('art.status_unavailable', 'Mevcut değil'),
        ];

        return new Kirby\Content\Field($page, 'status_label', $labels[$status] ?? $status);
    }

    return $page->content()->get($key);
};
$hasBody = $page->body()->isNotEmpty();
$hasStatement = $page->statement()->isNotEmpty();
?>

<div class="container single-art layout-sidebar-main layout-sidebar-main--wide">
  <aside class="sticky-meta-panel art-catalog-panel">
    <span class="meta-label"><?= fa_t('art.archive', 'Sanat Arşivi') ?></span>
    <h1><?= $page->title() ?></h1>

    <?php if ($page->curator_note()->isNotEmpty()): ?>
      <div class="art-curator-note">
        <?= $page->curator_note()->kt() ?>
      </div>
    <?php endif ?>

    <dl class="museum-label art-catalog-list">
      <?php foreach ($catalogFields as $field => $label): ?>
        <?php $value = $contentField($field); ?>
        <?php if ($value->isNotEmpty()): ?>
          <div>
            <dt><?= $label ?></dt>
            <dd><?= $value ?></dd>
          </div>
        <?php endif ?>
      <?php endforeach ?>
    </dl>
  </aside>

  <div class="art-content">
    <?php if ($galleryCount > 0): ?>
      <div class="art-gallery <?= $galleryCountClass ?>">
        <?php foreach ($gallery as $image): ?>
          <?php
          $imageTitle = $image->image_title()->or($image->title())->or($image->name())->value() ?? '';
          $imageCaption = $image->lightbox_caption()->or($image->image_caption())->value() ?? '';
          $figureCaption = $image->image_caption()->or($image->caption());
          $altText = $image->alt()->value() ?? '';
          $lightboxEnabled = $image->lightbox_enabled()->toBool(true);
          $imageWidth = max(1, (int)$image->width());
          $imageHeight = max(1, (int)$image->height());
          $orientation = match (true) {
              $imageWidth > $imageHeight => 'landscape',
              $imageHeight > $imageWidth => 'portrait',
              default => 'square',
          };
          ?>
          <figure
            class="art-figure art-figure--<?= $orientation ?> <?= $lightboxEnabled ? 'js-lightbox-trigger' : '' ?>"
            style="--art-image-width: <?= $imageWidth ?>; --art-image-height: <?= $imageHeight ?>;"
            <?php if ($lightboxEnabled): ?>
            data-src="<?= esc($image->url(), 'attr') ?>"
            data-title="<?= esc($imageTitle, 'attr') ?>"
            data-caption="<?= esc($imageCaption, 'attr') ?>"
            data-alt-text="<?= esc($altText, 'attr') ?>"
            data-image-role="<?= esc($image->image_role(), 'attr') ?>"
            data-technical-note="<?= esc($image->technical_note(), 'attr') ?>"
            data-material-note="<?= esc($image->material_note(), 'attr') ?>"
            data-image-date="<?= esc($image->image_date()->isNotEmpty() ? $image->image_date()->toDate('d M Y') : '', 'attr') ?>"
            <?php endif ?>>
            <?php snippet('components/picture', [
              'file' => $image,
              'resize' => 1200,
              'class' => 'art-image',
              'sizes' => '(min-width: 1024px) 70vw, 100vw'
            ]) ?>
            <?php if ($figureCaption->isNotEmpty()): ?>
              <figcaption><?= $figureCaption ?></figcaption>
            <?php endif ?>
          </figure>
        <?php endforeach ?>
      </div>
    <?php endif ?>

    <?php snippet('components/tags', ['tags' => $page->tags()]) ?>
  </div>

  <?php if ($hasBody || $hasStatement): ?>
    <aside class="sticky-meta-panel art-description-panel">
      <span class="meta-label"><?= fa_t('art.description', 'Eser açıklaması') ?></span>
      <div class="writing-body art-description-text">
        <?php if ($hasBody): ?>
          <?= contentRenderBody($page->body()) ?>
        <?php else: ?>
          <?= $page->statement()->kt() ?>
        <?php endif ?>
      </div>
    </aside>
  <?php endif ?>
</div>

<?php snippet('components/lightbox') ?>
<?php snippet('layout/footer') ?>
