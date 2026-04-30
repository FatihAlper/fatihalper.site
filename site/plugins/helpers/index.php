<?php

use Kirby\Cms\App as Kirby;

/**
 * Site Helpers Plugin
 * 
 * Centralized helper functions for content type resolution,
 * CTA labels, and room accent colors.
 */

require_once __DIR__ . '/helpers.php';

Kirby::plugin('site/helpers', [
    'siteMethods' => [
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
]);
