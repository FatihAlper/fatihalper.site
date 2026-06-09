<?php
/**
 * Frontend Crawler and URL/HTML Validation Script
 */

error_reporting(E_ALL & ~E_DEPRECATED);

echo "=============================================\n";
echo "🌐 Starting Frontend Crawler & Link Validator\n";
echo "=============================================\n\n";

$startUrl = 'http://127.0.0.1:8080';
$visited = [];
$queue = [$startUrl];
$brokenLinks = [];
$langSwitcherFound = [];
$redirectedUrls = [];

function fetchPage(string $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return ['status' => 0, 'body' => '', 'headers' => '', 'effective_url' => $url, 'error' => $err];
    }
    
    $headerSize = $info['header_size'];
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    return [
        'status' => $info['http_code'],
        'body' => $body,
        'headers' => $headers,
        'effective_url' => $info['url']
    ];
}

while (!empty($queue)) {
    $url = array_shift($queue);
    
    // Normalize URL
    $cleanUrl = strtok($url, '#');
    if (in_array($cleanUrl, $visited)) {
        continue;
    }
    $visited[] = $cleanUrl;
    
    echo "🔗 Fetching: {$cleanUrl}...\n";
    $res = fetchPage($cleanUrl);
    
    if ($res['status'] !== 200) {
        echo "   ❌ ERROR: HTTP Status {$res['status']} on {$cleanUrl}\n";
        $brokenLinks[$cleanUrl] = $res['status'];
        continue;
    }
    
    // Check if redirect changed the URL to ?lang=en or something else
    if ($res['effective_url'] !== $cleanUrl) {
        echo "   🔀 REDIRECTED to: {$res['effective_url']}\n";
        if (str_contains($res['effective_url'], 'lang=en')) {
            $redirectedUrls[$cleanUrl] = $res['effective_url'];
        }
    }
    
    // Check for language switcher in body
    if (str_contains($res['body'], 'language-switcher')) {
        echo "   ⚠️ WARNING: 'language-switcher' element found in HTML!\n";
        $langSwitcherFound[] = $cleanUrl;
    }
    
    // Parse links
    $dom = new DOMDocument();
    @$dom->loadHTML($res['body']);
    $anchors = $dom->getElementsByTagName('a');
    
    foreach ($anchors as $anchor) {
        $href = $anchor->getAttribute('href');
        
        // Exclude external links and specific anchors
        if (empty($href) || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, 'javascript:')) {
            continue;
        }
        
        // Resolve relative URL
        $parsedHref = parse_url($href);
        if (isset($parsedHref['host'])) {
            // Only crawl our local domain
            if ($parsedHref['host'] !== '127.0.0.1' && $parsedHref['host'] !== 'localhost') {
                continue;
            }
        }
        
        // Build absolute URL for local crawling
        if (!isset($parsedHref['host'])) {
            $absoluteUrl = rtrim($startUrl, '/') . '/' . ltrim($href, '/');
        } else {
            $absoluteUrl = $href;
        }
        
        // Check if already visited or queued
        $cleanAbsUrl = strtok($absoluteUrl, '#');
        if (!in_array($cleanAbsUrl, $visited) && !in_array($cleanAbsUrl, $queue)) {
            $queue[] = $cleanAbsUrl;
        }
    }
}

echo "\n=============================================\n";
echo "📊 Frontend Crawl & Validation Summary\n";
echo "=============================================\n";
echo "Total Visited Pages: " . count($visited) . "\n";
echo "Broken Links (non-200): " . count($brokenLinks) . "\n";
echo "Redirects containing 'lang=en': " . count($redirectedUrls) . "\n";
echo "Pages with Language Switcher HTML: " . count($langSwitcherFound) . "\n";
echo "=============================================\n\n";

if (!empty($brokenLinks)) {
    echo "❌ Broken Links List:\n";
    foreach ($brokenLinks as $u => $code) {
        echo "  - {$u} (HTTP {$code})\n";
    }
}

if (!empty($redirectedUrls)) {
    echo "❌ Redirects with lang=en List:\n";
    foreach ($redirectedUrls as $u => $eff) {
        echo "  - {$u} -> {$eff}\n";
    }
}

if (!empty($langSwitcherFound)) {
    echo "❌ Pages containing Language Switcher HTML:\n";
    foreach ($langSwitcherFound as $u) {
        echo "  - {$u}\n";
    }
}

if (!empty($brokenLinks) || !empty($redirectedUrls) || !empty($langSwitcherFound)) {
    exit(1);
} else {
    echo "🎉 All frontend pages are clean, load with HTTP 200, and show no bilingual traces!\n";
    exit(0);
}
