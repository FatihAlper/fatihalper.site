<?php
require __DIR__ . '/../kirby/bootstrap.php';
$kirby = new Kirby([
    'roots' => [
        'index' => dirname(__DIR__)
    ]
]);

$kirby->impersonate('kirby');

$tests = [
    'fragmanlar/test-writing',
    'perde/test-film',
    'kadraj/test-album',
    'marginalia/test-book',
    'rezonans/test-playlist',
    'exhibit/test-art'
];

foreach ($tests as $path) {
    try {
        $page = $kirby->page($path);
        if ($page) {
            $page->delete(true);
            echo "Deleted: " . $path . "\n";
        } else {
            echo "Not found: " . $path . "\n";
        }
    } catch (Exception $e) {
        echo "Error deleting " . $path . ": " . $e->getMessage() . "\n";
    }
}
