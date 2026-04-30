<?php
/**
 * Manifesto Strip / Closing Statement
 *
 * Renders all configured quotes and lets the frontend cycle through them.
 */
$home = page('home');
$quoteEntries = [];

foreach ($home->manifesto_quotes()->toStructure() as $entry) {
    if ($entry->text()->isNotEmpty()) {
        $quoteEntries[] = $entry;
    }
}

if (empty($quoteEntries)) {
    $quoteEntries[] = [
        'text' => $home->manifesto_text()->or(site()->manifesto_strip_text())->or('Bu site klasik anlamda bir portfolyo de&#287;il; dikkatin, tak&#305;nt&#305;lar&#305;n ve iz s&uuml;rmelerin ki&#351;isel indeksidir.'),
        'source' => null,
    ];
}

$linkText = $home->manifesto_link_text();
$linkUrl = $home->manifesto_link();
$fontSize = $home->manifesto_font_size()->or('small')->value();
$fontSize = in_array($fontSize, ['small', 'medium', 'large', 'display'], true) ? $fontSize : 'small';
?>
<section class="home-manifesto-strip home-manifesto-strip--font-<?= $fontSize ?> container" data-manifesto-strip data-interval="6500">
  <div class="home-manifesto-strip__items" aria-live="polite">
    <?php foreach ($quoteEntries as $index => $entry): ?>
      <?php
        $isArray = is_array($entry);
        $text = $isArray ? $entry['text'] : $entry->text();
        $source = $isArray ? $entry['source'] : $entry->source();
      ?>
      <figure class="home-manifesto-strip__item<?= $index === 0 ? ' is-active' : '' ?>" data-manifesto-item>
        <blockquote class="home-manifesto-strip__text"><?= $text ?></blockquote>
        <?php if ($source && $source->isNotEmpty()): ?>
          <figcaption class="home-manifesto-strip__source"><?= $source ?></figcaption>
        <?php endif ?>
      </figure>
    <?php endforeach ?>
  </div>
  <?php if (count($quoteEntries) > 1): ?>
    <div class="home-manifesto-strip__dots" aria-label="Manifesto seçimi">
      <?php foreach ($quoteEntries as $index => $entry): ?>
        <button
          class="home-manifesto-strip__dot<?= $index === 0 ? ' is-active' : '' ?>"
          type="button"
          data-manifesto-dot
          data-index="<?= $index ?>"
          aria-label="Manifesto <?= $index + 1 ?>"
          aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
        ></button>
      <?php endforeach ?>
    </div>
  <?php endif ?>
  <?php if ($linkUrl->isNotEmpty()): ?>
    <a href="<?= $linkUrl ?>" class="home-manifesto-strip__link"><?= $linkText->or('Hakk&#305;nda') ?></a>
  <?php endif ?>
</section>
