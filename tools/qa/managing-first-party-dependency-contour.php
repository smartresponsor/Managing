<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerFile = $root.'/composer.json';

if (!is_file($composerFile)) {
    fwrite(STDERR, "composer.json not found\n");
    exit(1);
}

$composer = json_decode((string) file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
$require = $composer['require'] ?? [];
$repositories = $composer['repositories'] ?? [];

if (!is_array($require) || !is_array($repositories)) {
    fwrite(STDERR, "Invalid Composer dependency structure\n");
    exit(1);
}

$directPackages = [
    'administering/administration',
    'collectioning/collection',
    'cruding/crud',
    'interfacing/interface',
    'objecting/object',
    'rolling/role',
    'tabling/table',
    'viewing/view',
];

$allowedPathPackages = [
    'administering/administration' => '../Administering',
    'collectioning/collection' => '../Collectioning',
    'cruding/crud' => '../Cruding',
    'gating/gate' => '../Gating',
    'interfacing/interface' => '../Interfacing',
    'objecting/object' => '../Objecting',
    'tabling/table' => '../Tabling',
    'viewing/view' => '../Viewing',
];

$errors = [];

foreach ($directPackages as $package) {
    if (($require[$package] ?? null) !== 'dev-master') {
        $errors[] = sprintf('Expected %s: dev-master in require', $package);
    }
}

foreach ($repositories as $repository) {
    if (!is_array($repository) || 'path' !== ($repository['type'] ?? null)) {
        continue;
    }

    $path = $repository['url'] ?? null;
    if (!is_string($path)) {
        continue;
    }

    $package = array_search($path, $allowedPathPackages, true);
    if (false === $package) {
        $errors[] = sprintf(
            'Non-canonical sibling path repository %s; use packaged/VCS resolution for non-helper dependencies',
            $path,
        );
        continue;
    }

    if (($repository['options']['symlink'] ?? null) !== true) {
        $errors[] = sprintf('Expected symlink=true for path repository %s', $path);
    }

    if ('dev-master' !== ($repository['options']['versions'][$package] ?? null)) {
        $errors[] = sprintf('Expected explicit dev-master path version for %s', $package);
    }
}

if ([] !== $errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error."\n");
    }

    exit(1);
}

fwrite(STDOUT, "Managing first-party dependency contour: PASS\n");
