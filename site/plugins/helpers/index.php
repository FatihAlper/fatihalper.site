<?php

use Kirby\Cms\App as Kirby;

const FA_ARCHIVE_HELPERS_VERSION = '0.1b';

/**
 * Site Helpers Plugin
 * 
 * Centralized helper functions for content type resolution,
 * CTA labels, and room accent colors.
 */

require_once __DIR__ . '/helpers.php';

Kirby::plugin('fatihalper/archive-helpers', [
    'options' => [
        'version' => FA_ARCHIVE_HELPERS_VERSION,
    ],
    'siteMethods' => [
        'archiveHelpersVersion' => function (): string {
            return FA_ARCHIVE_HELPERS_VERSION;
        },
        'panelArchiveReports' => function (): array {
            return dashboardArchiveReports();
        },
        'panelArchiveStats' => function (): string {
            return dashboardContentStats();
        },
        'panelTagStats' => function (): string {
            return dashboardTagStats();
        },
        'panelEnvironmentSummary' => function (): string {
            return dashboardSystemStats();
        },
        'panelGoogleAnalyticsSummary' => function (): string {
            return dashboardAnalyticsStatus();
        },
        'panelMediaSummary' => function (): string {
            return dashboardMediaStats();
        },
        'panelCacheSummary' => function (): string {
            return dashboardCacheStats();
        },
        'panelSeoWarnings' => function (): string {
            return dashboardSeoWarnings();
        },
        'panelHealthChecks' => function (): string {
            return dashboardHealthChecks();
        },
    ],
    'hooks' => [
        'site.update:after' => function () {
            static $clearing = false;

            if ($clearing === true || site()->cache_clear_requested()->toBool() !== true) {
                return;
            }

            $clearing = true;

            try {
                $result = dashboardClearCaches();

                site()->update([
                    'cache_clear_requested' => false,
                    'cache_last_cleared_at' => date('c'),
                    'cache_last_clear_status' => $result['message'],
                ]);
            } catch (Throwable $e) {
                site()->update([
                    'cache_clear_requested' => false,
                    'cache_last_cleared_at' => date('c'),
                    'cache_last_clear_status' => 'Cache temizleme başarısız: ' . $e->getMessage(),
                ]);
            } finally {
                $clearing = false;
            }
        },
    ],
]);
