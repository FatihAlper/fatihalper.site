<?php snippet('layout/header') ?>

<div class="container reading-container">
  <h1><?= $page->title() ?></h1>

  <div class="reading-content">
    <?= $page->text()->toBlocks() ?>
  </div>
</div>

<?php snippet('layout/footer') ?>
