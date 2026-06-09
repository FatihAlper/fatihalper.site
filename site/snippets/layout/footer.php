<?php
$site = site();
$logoFile = $site->logo()->toFile() ?? $site->site_logo()->toFile();
$siteAuthor = $site->site_author()->or($site->title());
$copyright = $site->copyright_text()->isNotEmpty()
  ? $site->copyright_text()
  : '&copy; ' . date('Y') . ' ' . $siteAuthor . '.';
$assetVersion = function (string $path): string {
    $root = kirby()->root('index');
    $production = strtolower((string)env('APP_ENV', '')) === 'production';
    if ($production) {
        $minPath = preg_replace('/\.(css|js)$/', '.min.$1', $path);
        if ($minPath && is_file($root . '/' . ltrim($minPath, '/'))) {
            $path = $minPath;
        }
    }
    $file = $root . '/' . ltrim($path, '/');
    $version = is_file($file) ? (string)filemtime($file) : '1';

    return url($path) . '?v=' . $version;
};
?>
</main>

<footer class="site-footer">
  <div class="footer-brand-separator <?= $logoFile ? 'has-logo' : 'no-logo' ?>" aria-hidden="true">
    <?php if ($logoFile): ?>
      <span class="footer-logo">
        <?php if ($logoFile->extension() === 'svg'): ?>
          <?= svg($logoFile) ?>
        <?php else: ?>
          <?php snippet('components/picture', [
            'file' => $logoFile,
            'resize' => 160,
            'alt' => '',
            'lazy' => true,
            'srcset' => 'card',
            'sizes' => '80px'
          ]) ?>
        <?php endif ?>
      </span>
    <?php endif ?>
  </div>

  <div class="container footer-content">
    <div class="footer-manifesto">
      <p><?= $site->footer_text()->or($site->site_subtitle()) ?></p>
    </div>

    <div class="footer-social">
      <?php 
      $socials = $site->social_links()->toStructure();
      if ($socials->isNotEmpty()): ?>
        <nav class="social-nav">
          <?php foreach ($socials as $social): ?>
            <?php if ($social->visible()->toBool()): 
              $label = fa_structure_label($social) ?: $social->platform()->or($social->url());
              $color = $social->icon_color()->value();
              $size = $social->icon_size()->value();
              $style = "";
              if ($color) $style .= "--icon-color: {$color};";
              if ($size) $style .= "--icon-size: {$size}px;";
            ?>
              <a href="<?= $social->url() ?>" 
                 target="_blank" 
                 rel="noopener" 
                 title="<?= esc($label, 'attr') ?>"
                 aria-label="<?= esc($label, 'attr') ?>"
                 class="social-link"
                 style="<?= $style ?>">
                <?php if ($iconSvg = $social->icon_svg()->toFile()): ?>
                  <span aria-hidden="true"><?= svg($iconSvg) ?></span>
                <?php elseif ($social->icon_name()->isNotEmpty()): ?>
                  <i class="<?= $social->icon_name() ?>" aria-hidden="true"></i>
                <?php else: ?>
                  <span class="social-label"><?= $label ?></span>
                <?php endif ?>
              </a>
            <?php endif ?>
          <?php endforeach ?>
        </nav>
      <?php endif ?>
    </div>

    <div class="footer-links">
      <p><?= $copyright ?>
      <?php if ($site->rss_enabled()->toBool()): ?>
        <a href="<?= url('feed') ?>"><?= fa_t('footer.rss', 'RSS') ?></a>
      <?php endif ?>
      </p>
    </div>
  </div>
</footer>

<script data-cfasync="false" src="<?= esc($assetVersion('assets/js/main.js'), 'attr') ?>" defer></script>

</body>
</html>
