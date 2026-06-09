<?php
$pagination = $pagination ?? null;
$baseUrl = $baseUrl ?? $page->url();

if (!$pagination || !$pagination->hasPages()) return;
?>

<nav class="tags-pagination" aria-label="Sayfalama">
  <?php if ($pagination->hasPrevPage()): ?>
    <a href="<?= contentPaginationUrl($baseUrl, $pagination->prevPage()) ?>" class="tags-pagination__link">Önceki</a>
  <?php endif ?>

  <?php foreach ($pagination->range(5) as $pageNumber): ?>
    <?php if ($pageNumber === $pagination->page()): ?>
      <span class="tags-pagination__link is-current" aria-current="page"><?= $pageNumber ?></span>
    <?php else: ?>
      <a href="<?= contentPaginationUrl($baseUrl, $pageNumber) ?>" class="tags-pagination__link"><?= $pageNumber ?></a>
    <?php endif ?>
  <?php endforeach ?>

  <?php if ($pagination->hasNextPage()): ?>
    <a href="<?= contentPaginationUrl($baseUrl, $pagination->nextPage()) ?>" class="tags-pagination__link">Sonraki</a>
  <?php endif ?>
</nav>
