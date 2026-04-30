<?php
/**
 * Manifesto Strip / Closing Statement
 *
 * Picks one configured quote at random on every uncached page load.
 */
$home = page('home');
$quoteEntries = [];

foreach ($home->manifesto_quotes()->toStructure() as $entry) {
    if ($entry->text()->isNotEmpty()) {
        $quoteEntries[] = $entry;
    }
}

$selectedQuote = !empty($quoteEntries)
    ? $quoteEntries[random_int(0, count($quoteEntries) - 1)]
    : null;
$text = $selectedQuote
    ? $selectedQuote->text()
    : $home->manifesto_text()->or(site()->manifesto_strip_text())->or('Bu site klasik anlamda bir portfolyo de&#287;il; dikkatin, tak&#305;nt&#305;lar&#305;n ve iz s&uuml;rmelerin ki&#351;isel indeksidir.');
$source = $selectedQuote ? $selectedQuote->source() : null;
$linkText = $home->manifesto_link_text();
$linkUrl = $home->manifesto_link();
$fontSize = $home->manifesto_font_size()->or('small')->value();
$fontSize = in_array($fontSize, ['small', 'medium', 'large', 'display'], true) ? $fontSize : 'small';
?>
<section class="home-manifesto-strip home-manifesto-strip--font-<?= $fontSize ?> container">
  <p class="home-manifesto-strip__text"><?= $text ?></p>
  <?php if ($source && $source->isNotEmpty()): ?>
    <cite class="home-manifesto-strip__source"><?= $source ?></cite>
  <?php endif ?>
  <?php if ($linkUrl->isNotEmpty()): ?>
    <a href="<?= $linkUrl ?>" class="home-manifesto-strip__link"><?= $linkText->or('Hakk&#305;nda') ?></a>
  <?php endif ?>
</section>
