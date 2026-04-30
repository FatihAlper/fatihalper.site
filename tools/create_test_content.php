<?php
require __DIR__ . '/../kirby/bootstrap.php';
$kirby = new Kirby([
    'roots' => [
        'index' => dirname(__DIR__)
    ]
]);

$kirby->impersonate('kirby');

$tests = [
    [
        'parent'   => 'fragmanlar',
        'slug'     => 'test-writing',
        'template' => 'writing',
        'content'  => ['title' => 'Test Writing', 'writing_type' => 'essay', 'summary' => 'Test summary']
    ],
    [
        'parent'   => 'perde',
        'slug'     => 'test-film',
        'template' => 'film-review',
        'content'  => ['title' => 'Test Film', 'short_review' => 'Test film review']
    ],
    [
        'parent'   => 'kadraj',
        'slug'     => 'test-album',
        'template' => 'photo-album',
        'content'  => ['title' => 'Test Album', 'statement' => 'Test album statement']
    ],
    [
        'parent'   => 'marginalia',
        'slug'     => 'test-book',
        'template' => 'book-review',
        'content'  => ['title' => 'Test Book', 'summary' => 'Test book summary']
    ],
    [
        'parent'   => 'rezonans',
        'slug'     => 'test-playlist',
        'template' => 'playlist',
        'content'  => ['title' => 'Test Playlist', 'description' => 'Test playlist description']
    ],
    [
        'parent'   => 'exhibit',
        'slug'     => 'test-art',
        'template' => 'art-project',
        'content'  => ['title' => 'Test Art', 'summary' => 'Test art summary']
    ]
];

foreach ($tests as $test) {
    try {
        $parent = $kirby->page($test['parent']);
        if ($parent) {
            $page = $parent->createChild([
                'slug'     => $test['slug'],
                'template' => $test['template'],
                'content'  => $test['content']
            ]);
            echo "Created: " . $page->id() . "\n";
        } else {
            echo "Parent not found: " . $test['parent'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error creating " . $test['slug'] . ": " . $e->getMessage() . "\n";
    }
}
