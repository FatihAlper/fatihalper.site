<?php

/**
 * Lightweight PO translation bridge.
 *
 * - Loads site/translations/{locale}.po into Kirby translations
 * - Provides fa_t($key, $fallback, $locale) for templates/helpers
 * - Allows Panel-managed overrides via site_i18n_overrides
 */

use Kirby\Cms\App as Kirby;
use Kirby\Http\Url;

const FA_PO_TRANSLATIONS_VERSION = '0.1b';

function fa_po_unquote(string $value): string
{
    $value = trim($value);

    if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
        $value = substr($value, 1, -1);
    }

    return stripcslashes($value);
}

function fa_po_catalog_from_file(string $path): array
{
    if (is_file($path) === false) {
        return [];
    }

    $entries = [];
    $msgid = null;
    $msgstr = '';
    $active = null;

    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            if ($msgid !== null && $msgid !== '') {
                $entries[$msgid] = $msgstr;
            }

            $msgid = null;
            $msgstr = '';
            $active = null;
            continue;
        }

        if (str_starts_with($line, 'msgid ')) {
            if ($msgid !== null && $msgid !== '') {
                $entries[$msgid] = $msgstr;
            }

            $msgid = fa_po_unquote(substr($line, 6));
            $msgstr = '';
            $active = 'msgid';
            continue;
        }

        if (str_starts_with($line, 'msgstr ')) {
            $msgstr = fa_po_unquote(substr($line, 7));
            $active = 'msgstr';
            continue;
        }

        if (str_starts_with($line, '"')) {
            if ($active === 'msgid') {
                $msgid .= fa_po_unquote($line);
            } elseif ($active === 'msgstr') {
                $msgstr .= fa_po_unquote($line);
            }
        }
    }

    if ($msgid !== null && $msgid !== '') {
        $entries[$msgid] = $msgstr;
    }

    return $entries;
}

function fa_po_catalogs(): array
{
    static $catalogs = null;

    if ($catalogs !== null) {
        return $catalogs;
    }

    $root = dirname(__DIR__, 2) . '/translations';
    $catalogs = [
        'tr' => fa_po_catalog_from_file($root . '/tr.po'),
        'en' => fa_po_catalog_from_file($root . '/en.po'),
    ];

    return $catalogs;
}

function fa_locale(string|null $locale = null): string
{
    if (in_array($locale, ['tr', 'en'], true)) {
        return $locale;
    }

    // Primary: Kirby native multi-language
    try {
        $kirbyLang = kirby()->language()?->code();
        if (in_array($kirbyLang, ['tr', 'en'], true)) {
            return $kirbyLang;
        }
    } catch (Throwable) {
    }

    // Fallback: query parameter
    $queryLocale = get('lang');
    if (in_array($queryLocale, ['tr', 'en'], true)) {
        return $queryLocale;
    }

    // Fallback: cookie (legacy)
    $cookieLocale = $_COOKIE['fa_locale'] ?? null;
    if (in_array($cookieLocale, ['tr', 'en'], true)) {
        return $cookieLocale;
    }

    return 'tr';
}

function fa_t(string $key, string|null $fallback = null, string|null $locale = null): string
{
    $locale = fa_locale($locale);

    // PO catalog lookup
    $catalogs = fa_po_catalogs();
    $translated = $catalogs[$locale][$key] ?? null;

    if (is_string($translated) && $translated !== '') {
        return $translated;
    }

    return $fallback ?? $key;
}

function fa_field($model, string $base, string|null $locale = null)
{
    // With Kirby multi-lang, content is resolved per-language automatically
    return $model->content()->get($base);
}

function fa_structure_label($entry, string $base = 'label', string|null $locale = null): string
{
    $field = $entry->{$base}();
    return $field->isNotEmpty() ? $field->value() : '';
}

function fa_language_url(string $locale): string
{
    // Use Kirby native multi-language URL system
    try {
        $page = page() ?? site()->homePage();
        if ($page) {
            return $page->url($locale);
        }
    } catch (Throwable) {
    }

    // Fallback: language root URL
    $lang = kirby()->language($locale);
    return $lang ? $lang->url() : '/';
}

Kirby::plugin('fatihalper/po-translations', [
    'translations' => fa_po_catalogs(),
    'options' => [
        'version' => FA_PO_TRANSLATIONS_VERSION,
        'locale' => 'tr'
    ],
    'siteMethods' => [
        'poTranslationsVersion' => fn (): string => FA_PO_TRANSLATIONS_VERSION,
    ],
]);
