<?php
/**
 * Marquee Gallery
 * 
 * Reads mode from home page blueprint (pages/home.yml).
 * Supports 'latest' (auto from the photography section) and 'curated' (manual selection) modes.
 */
$home = page('home');
$legacyMode = site()->marquee_gallery_source()->value();
$mode = $home->marquee_mode()
    ->or($legacyMode === 'selected_files' ? 'curated' : $legacyMode)
    ->or('latest')
    ->value();
$speed = $home->marquee_speed()->or(site()->marquee_speed())->or('medium')->value();
$direction = $home->marquee_direction()->or('left')->value();
$style = '';
if ($home->marquee_height()->isNotEmpty()) {
    $style .= "--home-marquee-height: {$home->marquee_height()};";
}
if ($home->marquee_gap()->isNotEmpty()) {
    $style .= "--home-marquee-gap: {$home->marquee_gap()};";
}
if ($home->marquee_item_ratio()->isNotEmpty()) {
    $style .= "--home-marquee-item-ratio: {$home->marquee_item_ratio()};";
}
$items = [];

if ($mode === 'curated' && $home->marquee_items()->isNotEmpty()) {
    // Curated: manual structure field from Panel
    foreach ($home->marquee_items()->toStructure() as $entry) {
        $img = $entry->image()->toFile();
        if ($img) {
            $items[] = [
                'image' => $img,
                'title' => $entry->title()->or($img->title())->or($img->caption())->or($img->alt())->or($img->filename()),
                'link'  => $entry->link()->or('#')
            ];
        }
    }
} elseif ($mode === 'curated' && site()->marquee_gallery_files()->isNotEmpty()) {
    foreach (site()->marquee_gallery_files()->toFiles() as $img) {
        $items[] = [
            'image' => $img,
            'title' => $img->title()->or($img->caption())->or($img->alt())->or($img->filename()),
            'link'  => '#'
        ];
    }
} else {
    // Latest: pull gallery images from the photography section, newest files first.
    $parent = site()->children()->listed()->filterBy('intendedTemplate', 'photography')->first()
        ?? page('photography')
        ?? page('kadraj');

    if ($parent) {
        foreach ($parent->children()->listed() as $album) {
            $images = $album->gallery()->toFiles();
            if ($images->isEmpty()) {
                $images = $album->images()->filterBy('template', 'photo');
            }

            foreach ($images as $img) {
                $items[] = [
                    'image' => $img,
                    'title' => $img->title()->or($img->caption())->or($img->alt())->or($img->name())->value(),
                    'link'  => $album->url(),
                    'time'  => $img->modified() ?: 0
                ];
            }
        }

        usort($items, function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });
    }
}

if (count($items) === 0) return;
?>
<section class="home-marquee-gallery" aria-label="Görsel galeri" style="<?= esc($style, 'attr') ?>">
  <div class="home-marquee-gallery__wrapper">
    <div class="home-marquee-gallery__track speed-<?= $speed ?> <?= $direction === 'right' ? 'dir-right' : '' ?>">
      <?php
      // Duplicate items for seamless infinite scroll
      $displayItems = array_merge($items, $items);
      $totalOriginal = count($items);
      foreach ($displayItems as $idx => $item):
        if ($img = $item['image']):
          $link = trim((string)$item['link']);
          if ($link === '' || $link === '#') {
              $link = '#';
              $isLightbox = true;
          } else {
              $isLightbox = false;
          }
          $photoDate = $img->photo_date()->isNotEmpty() ? $img->photo_date()->toDate('d M Y') : '';
          // Unique ID per original item (duplicates share same ID)
          $lightboxId = $idx % $totalOriginal;
      ?>
        <a href="<?= $link ?>" 
           class="home-marquee-gallery__item <?= $isLightbox ? 'js-lightbox-trigger' : '' ?>"
           data-lightbox-id="marquee-<?= $lightboxId ?>"
           <?php if ($isLightbox): ?>
           data-src="<?= esc($img->url(), 'attr') ?>"
           data-title="<?= esc($item['title'], 'attr') ?>"
           data-caption="<?= esc($img->lightbox_caption()->or($img->caption()), 'attr') ?>"
           data-alt-text="<?= esc($img->alt(), 'attr') ?>"
           data-camera="<?= esc($img->camera(), 'attr') ?>"
           data-lens="<?= esc($img->lens(), 'attr') ?>"
           data-focal-length="<?= esc($img->focal_length(), 'attr') ?>"
           data-iso="<?= esc($img->iso(), 'attr') ?>"
           data-shutter="<?= esc($img->shutter_speed(), 'attr') ?>"
           data-aperture="<?= esc($img->aperture(), 'attr') ?>"
           data-date="<?= esc($photoDate, 'attr') ?>"
           data-image-note="<?= esc($img->visual_descriptors(), 'attr') ?>"
           data-technical-note="<?= esc($img->atmosphere(), 'attr') ?>"
           data-material-note="<?= esc($img->credit(), 'attr') ?>"
           <?php endif ?>
        >
          <?php snippet('components/picture', [
            'file' => $img,
            'crop' => [500, 350],
            'ratio' => '4/3',
            'alt' => $item['title'],
            'lazy' => true,
            'sizes' => '(min-width: 900px) 420px, 70vw'
          ]) ?>
          <div class="overlay">
            <span><?= $item['title'] ?></span>
          </div>
        </a>
      <?php endif; endforeach ?>
    </div>
  </div>
</section>
