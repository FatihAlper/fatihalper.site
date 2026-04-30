<?php
require __DIR__ . '/../kirby/bootstrap.php';
$kirby = new Kirby([
    'roots' => [
        'index' => dirname(__DIR__)
    ]
]);

$kirby->plugins();

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

echo "--- Kirby Bootstrap Check ---\n";
echo "Site title: " . $kirby->site()->title() . "\n";

echo "\n--- Route Simulation Test ---\n";
$routes = ['/'];

foreach ($routes as $path) {
    try {
        $response = $kirby->call($path);
        echo "PATH: {$path} | STATUS: " . $response->code() . "\n";
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
        echo "TRACE:\n" . $e->getTraceAsString() . "\n";
    }
}
