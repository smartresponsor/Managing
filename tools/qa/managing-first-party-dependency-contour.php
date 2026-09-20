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
    'administering/administration' => '../Administering',
    'cruding/crud' => '../Cruding',
    'interfacing/interface' => '../Interfacing',
    'objecting/object' => '../Objecting',
    'rolling/role' => '../Rolling',
    'viewing/view' => '../Viewing',
];

$transitivePathPackages = [
    'collectioning/collection' => '../Collectioning',
    'configuring/config' => '../Configuring',
    'tabling/table' => '../Tabling',
];

$errors = [];

foreach ($directPackages as $package => $path) {
    if (($require[$package] ?? null) !== 'dev-master') {
        $errors[] = sprintf('Expected %s: dev-master in require', $package);
    }
}

$expectedRepositories = $directPackages + $transitivePathPackages;

foreach ($expectedRepositories as $package => $path) {
    $matched = false;

    foreach ($repositories as $repository) {
        if (!is_array($repository)) {
            continue;
        }

        if (($repository['type'] ?? null) !== 'path' || ($repository['url'] ?? null) !== $path) {
            continue;
        }

        if (($repository['options']['symlink'] ?? null) !== true) {
            $errors[] = sprintf('Expected symlink=true for path repository %s', $path);
        }

        $version = $repository['options']['versions'][$package] ?? null;
        if ('dev-master' !== $version) {
            $errors[] = sprintf('Expected explicit dev-master path version for %s', $package);
        }

        $matched = true;
        break;
    }

    if (!$matched) {
        $errors[] = sprintf('Missing path repository for %s (%s)', $package, $path);
    }
}

if ([] !== $errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error."\n");
    }

    exit(1);
}

fwrite(STDOUT, "Managing first-party dependency contour: PASS\n");
