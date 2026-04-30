<?php
/**
 * Archive Listing Snippet
 * 
 * Shared listing layout for section index pages.
 * 
 * Usage: snippet('components/archive-listing', [
 *     'page'      => $page,
 *     'gridClass' => 'grid grid-cols-3'  // or 'masonry-grid'
 * ])
 */
$gridClass = $gridClass ?? 'grid grid-cols-3';
$items = $page->children()->listed()->sortBy('date', 'desc');
?>
<div class="container archive-header">
  <h1><?= $page->title() ?></h1>
  <?php if ($page->description()->isNotEmpty() || $page->summary()->isNotEmpty()): ?>
    <p class="archive-description"><?= $page->description()->or($page->summary()) ?></p>
  <?php endif ?>
</div>

<div class="container archive-grid <?= $gridClass ?>">
  <?php foreach ($items as $item): ?>
    <?php snippet('components/card', ['item' => $item]) ?>
  <?php endforeach ?>
  <?php if ($items->isEmpty()): ?>
    <p class="empty-state"><?= fa_t('archive.empty', 'Bu bölümde henüz katalog kaydı yok.') ?></p>
  <?php endif ?>
</div>
