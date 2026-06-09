#!/usr/bin/env php
<?php
/**
 * Kirby Multi-Language Migration Script
 * 
 * Converts single-language content files to Kirby multi-language format.
 * - Renames .txt files to .tr.txt (default language)
 * - Creates empty .en.txt counterparts
 * - For site.txt: splits bilingual fields (_tr/_en) into separate language files
 *
 * Usage:
 *   php tools/migrate-multilang.php --dry-run          Preview changes
 *   php tools/migrate-multilang.php --backup           Backup + migrate
 *   php tools/migrate-multilang.php                    Migrate directly
 */

$root = dirname(__DIR__);
$contentDir = $root . '/content';
$dryRun = in_array('--dry-run', $argv);
$backup = in_array('--backup', $argv);

if (!is_dir($contentDir)) {
    fwrite(STDERR, "ERROR: content/ directory not found at: $contentDir\n");
    exit(1);
}

echo "╔══════════════════════════════════════════════╗\n";
echo "║  Kirby Multi-Language Migration              ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

if ($dryRun) {
    echo "🔍 DRY RUN — no files will be modified\n\n";
}

// ── Step 1: Backup ──
if ($backup && !$dryRun) {
    $backupDir = $root . '/content-backup-' . date('Y-m-d-His');
    echo "📦 Creating backup: $backupDir\n";
    recurseCopy($contentDir, $backupDir);
    echo "   ✅ Backup complete\n\n";
}

// ── Step 2: Find all .txt files ──
$txtFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($contentDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'txt') {
        $path = $file->getPathname();
        // Skip files that are already multi-lang formatted
        if (preg_match('/\.(tr|en|de|fr|es)\.txt$/', $path)) {
            continue;
        }
        $txtFiles[] = $path;
    }
}

echo "📂 Found " . count($txtFiles) . " single-language .txt files\n\n";

// ── Step 3: Bilingual field pairs to split for site.txt ──
$bilingualFields = [
    'site_title',
    'site_subtitle',
    'site_description',
    'site_author',
    'footer_text',
    'copyright_text',
    'default_seo_title',
    'default_seo_description',
    'public_name',
    'role_title',
    'short_bio',
    'location',
    'editorial_signature',
];

$renamedCount = 0;
$createdCount = 0;
$splitCount = 0;

foreach ($txtFiles as $txtPath) {
    $dir = dirname($txtPath);
    $basename = basename($txtPath, '.txt');
    $trPath = $dir . '/' . $basename . '.tr.txt';
    $enPath = $dir . '/' . $basename . '.en.txt';
    
    $relPath = str_replace($root . '/', '', $txtPath);
    $relTr = str_replace($root . '/', '', $trPath);
    $relEn = str_replace($root . '/', '', $enPath);

    // Check if it's a site.txt (needs bilingual field splitting)
    $isSiteFile = ($basename === 'site');
    
    if ($isSiteFile) {
        echo "🔀 SPLIT: $relPath\n";
        $content = file_get_contents($txtPath);
        $fields = parseKirbyContent($content);
        
        $trFields = [];
        $enFields = [];
        $processedPairs = [];
        
        foreach ($fields as $key => $value) {
            $matched = false;
            
            foreach ($bilingualFields as $base) {
                if ($key === $base . '_tr') {
                    $trFields[$base] = $value;
                    $processedPairs[$base . '_tr'] = true;
                    $matched = true;
                    $splitCount++;
                    echo "   ↳ {$key} → {$base} (TR)\n";
                    break;
                }
                if ($key === $base . '_en') {
                    $enFields[$base] = $value;
                    $processedPairs[$base . '_en'] = true;
                    $matched = true;
                    echo "   ↳ {$key} → {$base} (EN)\n";
                    break;
                }
            }
            
            if (!$matched) {
                // Keep field in both languages (non-bilingual fields)
                $trFields[$key] = $value;
                $enFields[$key] = $value;
            }
        }
        
        if (!$dryRun) {
            file_put_contents($trPath, buildKirbyContent($trFields));
            file_put_contents($enPath, buildKirbyContent($enFields));
            unlink($txtPath);
        }
        
        echo "   → $relTr\n";
        echo "   → $relEn\n";
        $renamedCount++;
        $createdCount++;
        
    } else {
        // Regular content file: rename to .tr.txt and create empty .en.txt
        echo "📝 RENAME: $relPath → $relTr\n";
        
        if (!$dryRun) {
            rename($txtPath, $trPath);
            
            // Create EN counterpart with just the Title field
            $content = file_get_contents($trPath);
            $fields = parseKirbyContent($content);
            $enContent = '';
            if (isset($fields['Title'])) {
                $enContent = "Title: " . $fields['Title'] . "\n\n----\n";
            } elseif (isset($fields['title'])) {
                $enContent = "Title: " . $fields['title'] . "\n\n----\n";
            }
            file_put_contents($enPath, $enContent);
        }
        
        echo "   + $relEn (EN stub)\n";
        $renamedCount++;
        $createdCount++;
    }
}

echo "\n╔══════════════════════════════════════════════╗\n";
echo "║  Summary                                      ║\n";
echo "╠══════════════════════════════════════════════╣\n";
printf("║  Renamed:  %-33s║\n", $renamedCount . " files");
printf("║  Created:  %-33s║\n", $createdCount . " EN stubs");
printf("║  Split:    %-33s║\n", $splitCount . " bilingual field pairs");
echo "╚══════════════════════════════════════════════╝\n";

if ($dryRun) {
    echo "\n🔍 This was a DRY RUN. Run without --dry-run to apply changes.\n";
    echo "   Recommended: php tools/migrate-multilang.php --backup\n";
}

// ── Helper Functions ──

function parseKirbyContent(string $content): array
{
    $fields = [];
    $blocks = preg_split('/\n----\n/', $content);
    
    foreach ($blocks as $block) {
        $block = trim($block);
        if (empty($block)) continue;
        
        // Match "Key: Value" or "Key:\nMultiline value"
        if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*):\s*(.*)/s', $block, $m)) {
            $fields[$m[1]] = trim($m[2]);
        }
    }
    
    return $fields;
}

function buildKirbyContent(array $fields): string
{
    $lines = [];
    foreach ($fields as $key => $value) {
        if (str_contains($value, "\n")) {
            $lines[] = "$key:\n$value";
        } else {
            $lines[] = "$key: $value";
        }
    }
    return implode("\n\n----\n\n", $lines) . "\n";
}

function recurseCopy(string $src, string $dst): void
{
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            recurseCopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}
