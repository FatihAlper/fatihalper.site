<?php
/**
 * Test Pagination Output Differences
 */

require __DIR__ . '/../kirby/bootstrap.php';
$kirby = new Kirby([
    'roots' => [
        'index' => dirname(__DIR__)
    ]
]);
$kirby->plugins();

echo "=============================================\n";
echo "🔍 Testing Tags Archive Pagination\n";
echo "=============================================\n\n";

// Fetch tag page 1
$_GET['page'] = 1;
$htmlPage1 = $kirby->site()->page('tags')->render();

// Fetch tag page 2
$_GET['page'] = 2;
// Re-instantiate Kirby or clear request/cache state to ensure page parameter is read
$kirby2 = new Kirby([
    'roots' => [
        'index' => dirname(__DIR__)
    ]
]);
$kirby2->plugins();
$htmlPage2 = $kirby2->site()->page('tags')->render();

$len1 = strlen($htmlPage1);
$len2 = strlen($htmlPage2);

echo "Page 1 HTML Length: $len1 bytes\n";
echo "Page 2 HTML Length: $len2 bytes\n";

$archiveSuccess = false;
if ($htmlPage1 === $htmlPage2) {
    echo "❌ ERROR: HTML for Page 1 and Page 2 is identical! Pagination is broken.\n";
} else {
    echo "✅ SUCCESS: Page 1 and Page 2 are different!\n";
    $archiveSuccess = true;
}

// Let's dynamically find a tag that has > 12 items
$tags = contentTagIndex();
$testTag = null;
foreach ($tags as $tag) {
    if ($tag['count'] > 12) {
        $testTag = $tag['name'];
        break;
    }
}

if ($testTag) {
    echo "\nFound tag '$testTag' with count > 12. Testing detail pagination...\n";
    $_GET['page'] = 1;
    $kirby3 = new Kirby(['roots' => ['index' => dirname(__DIR__)]]);
    $kirby3->plugins();
    $htmlTag1 = $kirby3->site()->page('tags')->render(['tagSlug' => contentTagSlug($testTag)]);

    $_GET['page'] = 2;
    $kirby4 = new Kirby(['roots' => ['index' => dirname(__DIR__)]]);
    $kirby4->plugins();
    $htmlTag2 = $kirby4->site()->page('tags')->render(['tagSlug' => contentTagSlug($testTag)]);

    $lenTag1 = strlen($htmlTag1);
    $lenTag2 = strlen($htmlTag2);

    echo "Tag '$testTag' Page 1 HTML Length: $lenTag1 bytes\n";
    echo "Tag '$testTag' Page 2 HTML Length: $lenTag2 bytes\n";

    if ($htmlTag1 === $htmlTag2) {
        echo "❌ ERROR: HTML for Tag '$testTag' Page 1 and Page 2 is identical! Pagination is broken.\n";
        exit(1);
    } else {
        echo "✅ SUCCESS: Tag '$testTag' Page 1 and Page 2 are different!\n";
    }
} else {
    echo "\nℹ️ INFO: No tags have > 12 items in local content. Detail pagination page 2 skipped.\n";
}

if ($archiveSuccess) {
    exit(0);
} else {
    exit(1);
}
