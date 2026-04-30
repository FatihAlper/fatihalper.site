<?php snippet('layout/header') ?>

<?php
$cover = $page->cover()->toFile();
$typeLabel = contentTypeLabel('writing', $page);
$date = contentDisplayDate($page, 'd M Y');
$body = $page->body()->isNotEmpty() ? $page->body() : $page->text();
$quoteHighlight = $page->quote_highlight()->isNotEmpty() ? $page->quote_highlight() : $page->quotes();
?>

<?php if ($cover): ?>
  <div class="writing-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.2), var(--color-bg-dark)), url(<?= $cover->url() ?>);">
    <div class="container writing-hero__content">
      <div class="meta-tags">
        <span class="meta-label"><?= $typeLabel ?: 'Yaz&#305;' ?></span>
        <?php if ($date): ?><span class="meta-date"><?= $date ?></span><?php endif ?>
      </div>
      <h1><?= $page->title() ?></h1>
      <?php if ($page->subtitle()->isNotEmpty()): ?>
        <p class="dek"><?= $page->subtitle() ?></p>
      <?php endif ?>
    </div>
  </div>
<?php else: ?>
  <div class="container archive-header">
    <div class="meta-tags">
      <span class="meta-label"><?= $typeLabel ?: 'Yaz&#305;' ?></span>
      <?php if ($date): ?><span class="meta-date"><?= $date ?></span><?php endif ?>
    </div>
    <h1><?= $page->title() ?></h1>
    <?php if ($page->subtitle()->isNotEmpty()): ?>
      <p class="dek"><?= $page->subtitle() ?></p>
    <?php endif ?>
  </div>
<?php endif ?>

<article class="reading-container single-writing">
  <?php if ($page->summary()->isNotEmpty()): ?>
    <div class="writing-summary curatorial-statement">
      <?= $page->summary()->kt() ?>
    </div>
  <?php endif ?>

  <div class="writing-body">
    <?= contentRenderBody($body) ?>
  </div>

  <?php if ($quoteHighlight->isNotEmpty()): ?>
    <div class="writing-highlight">
      <blockquote>
        <?= $quoteHighlight->kt() ?>
      </blockquote>
    </div>
  <?php endif ?>

  <footer class="writing-footer">
    <?php snippet('components/tags', ['tags' => $page->tags()]) ?>
  </footer>
</article>

<?php snippet('layout/footer') ?>
