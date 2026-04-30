<div id="lightbox" 
     role="dialog" 
     aria-modal="true" 
     aria-label="<?= esc(fa_t('lightbox.label', 'Görsel görüntüleyici'), 'attr') ?>" 
     aria-hidden="true" 
     tabindex="-1" 
     hidden
     data-label-camera="<?= esc(fa_t('meta.camera', 'Kamera'), 'attr') ?>"
     data-label-lens="<?= esc(fa_t('meta.lens', 'Lens'), 'attr') ?>"
     data-label-focal-length="<?= esc(fa_t('meta.focal_length', 'Odak uzaklığı'), 'attr') ?>"
     data-label-iso="<?= esc(fa_t('meta.iso', 'ISO'), 'attr') ?>"
     data-label-shutter="<?= esc(fa_t('meta.shutter', 'Enstantane'), 'attr') ?>"
     data-label-aperture="<?= esc(fa_t('meta.aperture', 'Diyafram'), 'attr') ?>"
     data-label-date="<?= esc(fa_t('meta.date', 'Tarih'), 'attr') ?>"
     data-label-role="<?= esc(fa_t('meta.image_role', 'Görsel niteliği'), 'attr') ?>"
     data-label-technical-note="<?= esc(fa_t('meta.technical_note', 'Teknik not'), 'attr') ?>"
     data-label-material-note="<?= esc(fa_t('meta.material_note', 'Malzeme notu'), 'attr') ?>"
     data-label-image-date="<?= esc(fa_t('meta.image_date', 'Görsel tarihi'), 'attr') ?>"
     data-label-image-note="<?= esc(fa_t('meta.image_note', 'Not'), 'attr') ?>"
>
  <button class="lightbox-close" aria-label="<?= esc(fa_t('lightbox.close', 'Kapat'), 'attr') ?>">&times;</button>
  <button class="lightbox-prev" aria-label="<?= esc(fa_t('lightbox.prev', 'Önceki görsel'), 'attr') ?>">&#8249;</button>
  <button class="lightbox-next" aria-label="<?= esc(fa_t('lightbox.next', 'Sonraki görsel'), 'attr') ?>">&#8250;</button>
  <div class="lightbox-content">
    <div class="lightbox-image-wrap">
      <img src="" alt="" class="lightbox-image" decoding="async">
    </div>
    <div class="lightbox-meta">
      <h3 class="lightbox-title"></h3>
      <p class="lightbox-desc"></p>
      <dl class="lightbox-exif">
        <!-- Populated by JS -->
      </dl>
    </div>
  </div>
</div>
