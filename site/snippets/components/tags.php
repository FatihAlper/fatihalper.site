<?php
/**
 * Tags Component
 * 
 * Usage: snippet('components/tags', ['tags' => $page->tags()])
 */
$tags = $tags ?? null;
if (!$tags || $tags->isEmpty()) return;
?>
<div class="page-tags">
  <?php foreach ($tags->split() as $tag): ?>
    <a class="tag" href="<?= esc(contentTagUrl($tag), 'attr') ?>">#<?= esc($tag) ?></a>
  <?php endforeach ?>
</div>
