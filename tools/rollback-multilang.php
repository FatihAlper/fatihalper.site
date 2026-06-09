#!/usr/bin/env php
<?php
/**
 * Kirby Multi-Language Rollback Script
 * 
 * Reverts Kirby multi-language format (.tr.txt / .en.txt) back to single-language (.txt).
 * - Renames .tr.txt to .txt
 * - Deletes .en.txt
 *
 * Usage:
 *   php tools/rollback-multilang.php --dry-run          Preview changes
 *   php tools/rollback-multilang.php --backup           Backup + rollback
 *   php tools/rollback-multilang.php                    Rollback directly
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
echo "║  Kirby Multi-Language Rollback               ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

if ($dryRun) {
    echo "🔍 DRY RUN — no files will be modified\n\n";
}

// ── Step 1: Backup ──
if ($backup && !$dryRun) {
    $backupDir = $root . '/content-backup-rollback-' . date('Y-m-d-His');
    echo "📦 Creating backup: $backupDir\n";
    recurseCopy($contentDir, $backupDir);
    echo "   ✅ Backup complete\n\n";
}

// ── Step 2: Traverse and gather files ──
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($contentDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$toRename = [];
$toDelete = [];

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $path = $file->getPathname();
        
        if (str_ends_with($path, '.tr.txt')) {
            $toRename[] = $path;
        } elseif (str_ends_with($path, '.en.txt')) {
            $toDelete[] = $path;
        }
    }
}

echo "📂 Found " . count($toRename) . " Turkish (.tr.txt) files to rename to (.txt)\n";
echo "📂 Found " . count($toDelete) . " English (.en.txt) files to delete\n\n";

// ── Step 3: Execute Actions ──
$renameCount = 0;
$deleteCount = 0;

foreach ($toRename as $trPath) {
    $dir = dirname($trPath);
    $basename = basename($trPath, '.tr.txt');
    $normalPath = $dir . '/' . $basename . '.txt';
    
    $relTr = str_replace($root . '/', '', $trPath);
    $relNormal = str_replace($root . '/', '', $normalPath);
    
    echo "📝 RENAME: $relTr → $relNormal\n";
    
    if (!$dryRun) {
        if (rename($trPath, $normalPath)) {
            $renameCount++;
        } else {
            echo "   ❌ ERROR: Could not rename $relTr\n";
        }
    } else {
        $renameCount++;
    }
}

foreach ($toDelete as $enPath) {
    $relEn = str_replace($root . '/', '', $enPath);
    
    echo "🗑️  DELETE: $relEn\n";
    
    if (!$dryRun) {
        if (unlink($enPath)) {
            $deleteCount++;
        } else {
            echo "   ❌ ERROR: Could not delete $relEn\n";
        }
    } else {
        $deleteCount++;
    }
}

echo "\n╔══════════════════════════════════════════════╗\n";
echo "║  Summary                                      ║\n";
echo "╠══════════════════════════════════════════════╣\n";
printf("║  Renamed:  %-33s║\n", $renameCount . " files");
printf("║  Deleted:  %-33s║\n", $deleteCount . " files");
echo "╚══════════════════════════════════════════════╝\n";

if ($dryRun) {
    echo "\n🔍 This was a DRY RUN. Run without --dry-run to apply changes.\n";
    echo "   Recommended: php tools/rollback-multilang.php --backup\n";
}

// ── Helper Functions ──

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
