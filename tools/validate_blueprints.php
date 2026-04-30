<?php
require __DIR__ . '/../kirby/bootstrap.php';
$kirby = new Kirby([
    'roots' => [
        'index' => dirname(__DIR__)
    ]
]);

$kirby->plugins();

echo "--- Blueprint Validation ---\n";
$errors = [];

function checkBlueprint($bp, $name) {
    try {
        $bp->toArray();
        return true;
    } catch (Throwable $e) {
        echo "ERROR in {$name}: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "Checking Site blueprint...\n";
checkBlueprint($kirby->site()->blueprint(), "site");

echo "Checking Page blueprints...\n";
foreach ($kirby->site()->index()->listed() as $page) {
    checkBlueprint($page->blueprint(), "page: " . $page->id());
}

// Check all blueprints in the directory
$bpDir = dirname(__DIR__) . '/site/blueprints';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($bpDir));
foreach ($iterator as $file) {
    if ($file->getExtension() === 'yml') {
        $id = str_replace([$bpDir . '/', '.yml'], '', $file->getPathname());
        try {
            // Kirby 3/4 doesn't have a direct "validate this yaml" without a context
            // but we can try to load it as a blueprint
            $data = Kirby\Data\Yaml::read($file->getPathname());
            echo "YAML OK: {$id}\n";
        } catch (Throwable $e) {
            echo "YAML ERROR: {$id} - " . $e->getMessage() . "\n";
        }
    }
}
