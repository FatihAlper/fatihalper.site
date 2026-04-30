<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\File;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

const FA_CONTENT_TRANSFER_VERSION = '0.1b';

function faContentTransferRoot(): string
{
    $root = kirby()->root('cache') . '/content-transfer';
    Dir::make($root, true);

    return $root;
}

function faContentTransferManifest(): array
{
    $path = faContentTransferRoot() . '/manifest.json';
    if (is_file($path) !== true) {
        return [];
    }

    $data = json_decode((string)file_get_contents($path), true);

    return is_array($data) ? $data : [];
}

function faContentTransferWriteManifest(array $data): void
{
    F::write(
        faContentTransferRoot() . '/manifest.json',
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
}

function faContentTransferZipName(string $prefix): string
{
    return $prefix . '-' . date('Ymd-His') . '.zip';
}

function faContentTransferAssertZip(): void
{
    if (class_exists('ZipArchive') !== true && class_exists('PharData') !== true) {
        throw new RuntimeException('ZIP desteği bulunamadı. Import/export için ext-zip veya PharData gerekli.');
    }
}

function faContentTransferFormatBytes(int|float $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max(0, (float)$bytes);
    $index = 0;

    while ($bytes >= 1024 && $index < count($units) - 1) {
        $bytes /= 1024;
        $index++;
    }

    return round($bytes, $index === 0 ? 0 : 1) . ' ' . $units[$index];
}

function faContentTransferAddDirectoryToZipArchive(ZipArchive $zip, string $root, string $base): int
{
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();
        $relative = str_replace('\\', '/', substr($path, strlen($base) + 1));

        if ($item->isDir()) {
            $zip->addEmptyDir($relative);
            continue;
        }

        if ($item->isFile()) {
            $zip->addFile($path, $relative);
            $count++;
        }
    }

    return $count;
}

function faContentTransferAddDirectoryToPhar(PharData $zip, string $root, string $base): int
{
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();
        $relative = str_replace('\\', '/', substr($path, strlen($base) + 1));

        if ($item->isDir()) {
            $zip->addEmptyDir($relative);
            continue;
        }

        if ($item->isFile()) {
            $zip->addFile($path, $relative);
            $count++;
        }
    }

    return $count;
}

function faContentTransferExport(string $prefix = 'content-export'): array
{
    faContentTransferAssertZip();

    $contentRoot = kirby()->root('content');
    if (is_dir($contentRoot) !== true) {
        throw new RuntimeException('content/ klasörü bulunamadı.');
    }

    $filename = faContentTransferZipName($prefix);
    $path = faContentTransferRoot() . '/' . $filename;
    $manifest = json_encode([
        'plugin' => 'fatihalper/content-transfer',
        'version' => FA_CONTENT_TRANSFER_VERSION,
        'created_at' => date('c'),
        'root' => 'content',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if (class_exists('ZipArchive') === true) {
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Export ZIP oluşturulamadı.');
        }

        $count = faContentTransferAddDirectoryToZipArchive($zip, $contentRoot, dirname($contentRoot));
        $zip->addFromString('manifest.json', (string)$manifest);
        $zip->close();
    } else {
        @unlink($path);
        $zip = new PharData($path);
        $count = faContentTransferAddDirectoryToPhar($zip, $contentRoot, dirname($contentRoot));
        $zip->addFromString('manifest.json', (string)$manifest);
    }

    faContentTransferWriteManifest([
        'latest_export' => $filename,
        'latest_export_at' => date('c'),
        'latest_export_files' => $count,
    ]);

    return [
        'filename' => $filename,
        'path' => $path,
        'files' => $count,
        'size' => filesize($path) ?: 0,
    ];
}

function faContentTransferSafeEntry(string $name): string|null
{
    $name = str_replace('\\', '/', $name);
    $name = ltrim($name, '/');

    if ($name === '' || str_contains($name, "\0") || str_contains($name, '../') || str_starts_with($name, '../')) {
        return null;
    }

    if ($name === 'manifest.json') {
        return null;
    }

    if (str_starts_with($name, 'content/') !== true) {
        return null;
    }

    return $name;
}

function faContentTransferImport(File $file, bool $overwrite = false): array
{
    faContentTransferAssertZip();

    $source = $file->root();
    if (is_file($source) !== true || strtolower($file->extension()) !== 'zip') {
        throw new RuntimeException('İçe aktarım için geçerli bir ZIP dosyası seçin.');
    }

    $backup = faContentTransferExport('content-backup-before-import');
    $contentRoot = kirby()->root('content');
    $indexRoot = dirname($contentRoot);
    $imported = 0;
    $skipped = 0;
    $blocked = 0;
    $writeEntry = function (string $entry, string $contents) use ($indexRoot, $contentRoot, $overwrite, &$imported, &$skipped, &$blocked): void {
        $safe = faContentTransferSafeEntry($entry);

        if ($safe === null || str_ends_with($safe, '/')) {
            $blocked++;
            return;
        }

        $target = $indexRoot . '/' . $safe;
        $contentReal = realpath($contentRoot);

        Dir::make(dirname($target), true);
        $realParent = realpath(dirname($target));
        if ($contentReal === false || $realParent === false || str_starts_with($realParent, $contentReal) !== true) {
            $blocked++;
            return;
        }

        if (is_file($target) && $overwrite !== true) {
            $skipped++;
            return;
        }

        if (F::write($target, $contents) !== true) {
            $skipped++;
            return;
        }

        $imported++;
    };

    if (class_exists('ZipArchive') === true) {
        $zip = new ZipArchive();

        if ($zip->open($source) !== true) {
            throw new RuntimeException('ZIP dosyası açılamadı.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (is_string($entry) !== true || str_ends_with($entry, '/')) {
                continue;
            }

            $stream = $zip->getStream($entry);
            if ($stream === false) {
                $skipped++;
                continue;
            }

            $writeEntry($entry, stream_get_contents($stream));
            fclose($stream);
        }

        $zip->close();
    } else {
        $zip = new PharData($source);
        $iterator = new RecursiveIteratorIterator($zip, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            $entry = $iterator->getSubPathName();
            if ($item->isDir()) {
                continue;
            }

            $writeEntry($entry, (string)file_get_contents($item->getPathname()));
        }
    }

    return [
        'imported' => $imported,
        'skipped' => $skipped,
        'blocked' => $blocked,
        'backup' => $backup['filename'],
    ];
}

function faContentTransferStatus(): string
{
    $site = site();
    $manifest = faContentTransferManifest();
    $latest = $manifest['latest_export'] ?? $site->content_transfer_last_export_file()->value();
    $lines = [];
    $lines[] = 'Plugin: fatihalper/content-transfer v' . FA_CONTENT_TRANSFER_VERSION;
    $lines[] = 'ZIP engine: ' . (class_exists('ZipArchive') ? 'ZipArchive' : (class_exists('PharData') ? 'PharData fallback' : 'eksik'));
    $lines[] = 'Export klasörü: ' . faContentTransferRoot();
    $lines[] = 'Son export: ' . ($latest ?: 'henüz yok');
    if ($latest) {
        $lines[] = 'İndirme: ' . url('panel/content-transfer/download/' . basename($latest));
    }
    $lines[] = 'Son işlem: ' . ($site->content_transfer_last_status()->value() ?: 'henüz yok');

    return implode("\n", $lines);
}

Kirby::plugin('fatihalper/content-transfer', [
    'options' => [
        'version' => FA_CONTENT_TRANSFER_VERSION,
    ],
    'siteMethods' => [
        'contentTransferVersion' => fn (): string => FA_CONTENT_TRANSFER_VERSION,
        'contentTransferStatus' => fn (): string => faContentTransferStatus(),
    ],
    'routes' => [
        [
            'pattern' => 'panel/content-transfer/download/(:any)',
            'method' => 'GET',
            'action' => function (string $filename) {
                if (kirby()->user() === null) {
                    return new Response('Unauthorized', 'text/plain', 401);
                }

                $filename = basename($filename);
                $path = faContentTransferRoot() . '/' . $filename;
                if (is_file($path) !== true || preg_match('/^(content-export|content-backup-before-import)-\d{8}-\d{6}\.zip$/', $filename) !== 1) {
                    return new Response('Not found', 'text/plain', 404);
                }

                return Response::download($path, $filename);
            }
        ],
    ],
    'hooks' => [
        'site.update:after' => function () {
            static $running = false;
            if ($running === true) {
                return;
            }

            $site = site();
            $wantsExport = $site->content_transfer_export_requested()->toBool() === true;
            $wantsImport = $site->content_transfer_import_requested()->toBool() === true;
            if ($wantsExport === false && $wantsImport === false) {
                return;
            }

            $running = true;
            try {
                $updates = [
                    'content_transfer_export_requested' => false,
                    'content_transfer_import_requested' => false,
                ];

                if ($wantsExport) {
                    $export = faContentTransferExport();
                    $updates['content_transfer_last_export_file'] = $export['filename'];
                    $updates['content_transfer_last_status'] = sprintf(
                        'Export tamamlandı: %s (%d dosya, %s).',
                        $export['filename'],
                        $export['files'],
                        faContentTransferFormatBytes((int)$export['size'])
                    );
                }

                if ($wantsImport) {
                    $file = $site->content_transfer_import_file()->toFile();
                    if (!$file) {
                        throw new RuntimeException('İçe aktarılacak ZIP dosyası seçilmedi.');
                    }

                    $result = faContentTransferImport($file, $site->content_transfer_overwrite()->toBool(false));
                    $updates['content_transfer_last_import_at'] = date('c');
                    $updates['content_transfer_last_status'] = sprintf(
                        'Import tamamlandı: %d yazıldı, %d atlandı, %d engellendi. Backup: %s',
                        $result['imported'],
                        $result['skipped'],
                        $result['blocked'],
                        $result['backup']
                    );
                }

                site()->update($updates);
            } catch (Throwable $e) {
                site()->update([
                    'content_transfer_export_requested' => false,
                    'content_transfer_import_requested' => false,
                    'content_transfer_last_status' => 'Transfer hatası: ' . $e->getMessage(),
                ]);
            } finally {
                $running = false;
            }
        },
    ],
]);
