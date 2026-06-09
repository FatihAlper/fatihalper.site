<?php snippet('layout/header') ?>

<?php
$tagSlug = $tagSlug ?? null;
$query = trim((string)get('q'));
$normalizedQuery = $query !== '' ? contentTagNormalize($query) : '';
$type = trim((string)get('type'));
$typeFilters = contentTagTypeFilters();
if (!isset($typeFilters[$type])) {
    $type = '';
}
$perPage = $tagSlug ? 12 : 24;
?>

<section class="tags-page">
  <?php if ($tagSlug): ?>
    <?php
    $tag = contentTagBySlug($tagSlug);
    $items = $tag
        ? contentTaggedItems($tag)
        : contentTaggableItems()->filter(fn ($item) => false);
    if ($type !== '') {
        $items = $items->filter(fn ($item) => $item->intendedTemplate()->name() === $type);
    }
    $paginatedItems = $items->paginate([
        'limit'    => $perPage,
        'method'   => 'query',
        'variable' => 'page',
        'page'     => get('page', 1),
    ]);
    $pagination = $paginatedItems->pagination();
    $detailTitle = $tag['name'] ?? str_replace('-', ' ', $tagSlug);
    ?>

    <div class="container tags-header">
      <a href="<?= esc(contentTagsUrl(), 'attr') ?>" class="tags-back-link">Tum etiketler</a>
      <p class="meta-label"><?= fa_t('tags.label', 'Etiket') ?></p>
      <h1><?= esc($detailTitle) ?></h1>
      <p class="archive-description">
        <?= $pagination->total() ?> katalog kaydi bu etikete bagli.
      </p>
    </div>

    <?php if ($tag): ?>
      <nav class="container tags-filter" aria-label="Icerik turu filtresi">
        <a class="tags-filter__link <?= $type === '' ? 'is-current' : '' ?>" href="<?= esc(contentTagUrl($tag['name']), 'attr') ?>" <?= $type === '' ? 'aria-current="page"' : '' ?>>Tumu</a>
        <?php foreach ($typeFilters as $template => $label): ?>
          <?php $href = contentTagUrl($tag['name']) . '?type=' . rawurlencode($template); ?>
          <a class="tags-filter__link <?= $type === $template ? 'is-current' : '' ?>" href="<?= esc($href, 'attr') ?>" <?= $type === $template ? 'aria-current="page"' : '' ?>>
            <?= esc($label) ?>
          </a>
        <?php endforeach ?>
      </nav>
    <?php endif ?>

    <div class="container archive-grid tags-result-grid masonry-grid">
      <?php foreach ($paginatedItems as $item): ?>
        <?php snippet('components/card', ['item' => $item, 'showFallback' => true]) ?>
      <?php endforeach ?>

      <?php if ($paginatedItems->isEmpty()): ?>
        <p class="empty-state">Bu filtreyle eslesen yayinlanmis katalog kaydi bulunamadi.</p>
      <?php endif ?>
    </div>

    <?php snippet('components/pagination', [
      'pagination' => $pagination,
      'baseUrl' => contentTagUrl($tag['name'] ?? $tagSlug),
    ]) ?>
  <?php else: ?>
    <?php
    $tags = array_values(contentTagIndex());

    if ($normalizedQuery !== '') {
        $tags = array_values(array_filter($tags, function ($tag) use ($normalizedQuery) {
            return str_contains($tag['key'], $normalizedQuery);
        }));
    }

    $tagCollection = new \Kirby\Toolkit\Collection($tags);
    $paginatedTags = $tagCollection->paginate([
        'limit'    => $perPage,
        'method'   => 'query',
        'variable' => 'page',
        'page'     => get('page', 1),
    ]);
    $pagination = $paginatedTags->pagination();
    ?>

    <div class="container tags-header">
      <p class="meta-label">Arsiv</p>
      <h1><?= $page->title()->or(fa_t('tags.title', 'Etiketler')) ?></h1>
      <?php if ($page->intro()->isNotEmpty()): ?>
        <p class="archive-description"><?= $page->intro() ?></p>
      <?php elseif ($page->description()->isNotEmpty()): ?>
        <p class="archive-description"><?= $page->description() ?></p>
      <?php endif ?>
    </div>

    <div class="container tags-search-wrap">
      <form class="tags-search" action="<?= esc(contentTagsUrl(), 'attr') ?>" method="get" data-tags-search>
        <label for="tag-search"><?= fa_t('tags.search', 'Etiket ara') ?></label>
        <div class="tags-search__row">
          <input
            id="tag-search"
            name="q"
            type="search"
            value="<?= esc($query, 'attr') ?>"
            placeholder="<?= esc(fa_t('tags.placeholder', 'Etiket adi'), 'attr') ?>"
            autocomplete="off"
            data-tags-search-input
          >
          <button type="submit">Ara</button>
          <?php if ($query !== ''): ?>
            <a href="<?= esc(contentTagsUrl(), 'attr') ?>" class="tags-search__clear">Temizle</a>
          <?php endif ?>
        </div>
      </form>
    </div>

    <div class="container tags-index-grid" data-tags-grid>
      <?php foreach ($paginatedTags as $tag): ?>
        <?php $typeLabels = array_values($tag['types']); ?>
        <a
          href="<?= esc(contentTagUrl($tag['name']), 'attr') ?>"
          class="tags-index-card"
          data-tag-name="<?= esc($tag['name'], 'attr') ?>"
        >
          <span class="tags-index-card__name">#<?= esc($tag['name']) ?></span>
          <span class="tags-index-card__count"><?= $tag['count'] ?> kayit</span>
          <?php if (count($typeLabels) > 0): ?>
            <span class="tags-index-card__hint"><?= esc(implode(', ', array_slice($typeLabels, 0, 3))) ?></span>
          <?php endif ?>
        </a>
      <?php endforeach ?>

      <?php if ($paginatedTags->isEmpty()): ?>
        <p class="empty-state">Aramanla eslesen etiket bulunamadi.</p>
      <?php endif ?>
    </div>

    <?php snippet('components/pagination', [
      'pagination' => $pagination,
      'baseUrl' => contentTagsUrl(),
    ]) ?>
  <?php endif ?>
</section>

<?php snippet('layout/footer') ?>
