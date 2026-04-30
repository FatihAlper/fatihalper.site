<?php snippet('layout/header') ?>

<div class="container single-book layout-sidebar-main">
  <div class="sidebar-poster-group">
    <div class="sidebar-poster">
      <?php if ($cover = $page->cover()->toFile()): ?>
        <?php snippet('components/picture', [
          'file' => $cover,
          'class' => 'book-cover-large',
          'resize' => 600,
          'alt' => $page->book_title()->or($page->title())->value(),
          'lazy' => false,
          'fetchpriority' => 'high'
        ]) ?>
      <?php endif ?>
    </div>

    <div class="sidebar-meta">
      <div class="large-rating">
        <?php snippet('components/rating-stars', ['rating' => $page->rating()->toFloat()]) ?>
      </div>

      <dl class="museum-label">
        <?php if ($page->author()->isNotEmpty()): ?>
          <div><dt><?= fa_t('meta.author', 'Yazar') ?></dt><dd><?= $page->author() ?></dd></div>
        <?php endif ?>
        <?php if ($page->original_year()->isNotEmpty()): ?>
          <div><dt><?= fa_t('meta.year', 'Yıl') ?></dt><dd><?= $page->original_year() ?></dd></div>
        <?php endif ?>
        <?php if ($page->publisher()->isNotEmpty()): ?>
          <div><dt><?= fa_t('meta.publisher', 'Yayınevi') ?></dt><dd><?= $page->publisher() ?></dd></div>
        <?php endif ?>
        <?php if ($page->translator()->isNotEmpty()): ?>
          <div><dt>Çevirmen</dt><dd><?= $page->translator() ?></dd></div>
        <?php endif ?>
        <?php if ($page->page_count()->isNotEmpty()): ?>
          <div><dt><?= fa_t('meta.page', 'Sayfa') ?></dt><dd><?= $page->page_count() ?></dd></div>
        <?php endif ?>
        <?php if ($page->isbn()->isNotEmpty()): ?>
          <div><dt>ISBN</dt><dd><?= $page->isbn() ?></dd></div>
        <?php endif ?>
      </dl>
    </div>
  </div>

  <article class="book-review-body reading-content">
    <header>
      <span class="meta-label"><?= fa_t('book.catalog', 'Edebi Katalog') ?></span>
      <h1><?= $page->book_title()->or($page->title()) ?></h1>
      <?php if ($page->author()->isNotEmpty()): ?>
        <p class="author-name"><?= $page->author() ?></p>
      <?php endif ?>
    </header>
    
    <?php if ($page->review_summary()->isNotEmpty()): ?>
      <div class="review-summary">
        <?= $page->review_summary()->kt() ?>
      </div>
    <?php endif ?>

    <div class="review-text writing-body">
      <?= contentRenderBody($page->body()) ?>
    </div>

    <?php snippet('components/tags', ['tags' => $page->tags()]) ?>
  </article>
</div>

<?php snippet('layout/footer') ?>
