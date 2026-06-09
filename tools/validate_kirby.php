<?php
/**
 * Kirby CMS Integration and Route Validation Script
 */

require __DIR__ . '/../kirby/bootstrap.php';
$kirby = new Kirby([
    'roots' => [
        'index' => dirname(__DIR__)
    ]
]);

$kirby->plugins();

// Set strict error handler to catch warnings/notices as exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

echo "=============================================\n";
echo "🔍 Kirby Backend & Template Validation\n";
echo "=============================================\n\n";

echo "🟢 Bootstrapped successfully.\n";
echo "Site Title: " . $kirby->site()->title() . "\n\n";

$pages = $kirby->site()->index();
$successCount = 0;
$failCount = 0;

echo "--- Testing " . $pages->count() . " pages ---\n";

// Add homepage manually since it's the root
$routesToTest = ['/' => $kirby->site()->homePage()];
foreach ($pages as $page) {
    $routesToTest[$page->id()] = $page;
}

foreach ($routesToTest as $path => $page) {
    if (!$page) {
        echo "❌ PATH: $path | Page object is null!\n";
        $failCount++;
        continue;
    }

    $template = $page->intendedTemplate()->name();
    
    try {
        // Render the page to capture template errors
        $html = $page->render();
        
        // Basic check for generated content
        if (empty(trim($html))) {
            throw new Exception("Rendered HTML is empty.");
        }
        
        echo "✅ OK: /{$page->id()} (Template: {$template}) - Length: " . strlen($html) . " bytes\n";
        $successCount++;
    } catch (Throwable $e) {
        echo "❌ FAIL: /{$page->id()} (Template: {$template})\n";
        echo "   💥 Error: " . $e->getMessage() . "\n";
        echo "   📍 Location: " . $e->getFile() . " on line " . $e->getLine() . "\n\n";
        $failCount++;
    }
}

echo "\n=============================================\n";
echo "📊 Validation Summary\n";
echo "=============================================\n";
echo "Successful renders: {$successCount}\n";
echo "Failed renders:     {$failCount}\n";
echo "=============================================\n";

if ($failCount > 0) {
    exit(1);
} else {
    exit(0);
}
