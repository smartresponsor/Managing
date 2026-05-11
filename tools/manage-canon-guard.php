<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$violations = [];

if (is_dir($root.'/src/Domain')) {
    $violations[] = 'Forbidden directory exists: src/Domain';
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src'));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $relative = str_replace($root.'/', '', $file->getPathname());
    $contents = file_get_contents($file->getPathname());

    if (!is_string($contents)) {
        $violations[] = $relative.' could not be read';
        continue;
    }

    if (!str_contains($contents, 'namespace App\\Managing')) {
        $violations[] = $relative.' is outside App\\Managing namespace';
    }

    if (str_contains($contents, 'Interfacing') || str_contains($contents, 'Bridging')) {
        $violations[] = $relative.' depends on Interfacing/Bridging; /manage must stay EasyAdmin-native';
    }

    if (preg_match('/(?:class|interface|trait|enum)\s+(?!ManagingBundle|Configuration|ManagingExtension)([A-Z][A-Za-z0-9_]*)/', $contents, $match)) {
        $name = $match[1];
        if (!str_starts_with($name, 'Manage')) {
            $violations[] = $relative.' declares non-Manage-prefixed symbol '.$name;
        }
    }
}



$twigIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/templates'));
foreach ($twigIterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'twig') {
        continue;
    }

    $relative = str_replace($root.'/', '', $file->getPathname());
    $contents = file_get_contents($file->getPathname());
    if (!is_string($contents)) {
        $violations[] = $relative.' could not be read';
        continue;
    }

    if (str_contains($contents, '<!doctype html>') || str_contains($contents, '<html')) {
        $violations[] = $relative.' declares a standalone HTML shell; /manage must stay inside EasyAdmin native layout';
    }
}

if (is_file($root.'/templates/manage/page/content.html.twig')) {
    $violations[] = 'Forbidden custom shell template exists: templates/manage/page/content.html.twig';
}


$composerPath = $root.'/composer.json';
$composer = is_file($composerPath) ? json_decode((string) file_get_contents($composerPath), true) : null;
if (!is_array($composer) || (($composer['require']['twig/html-extra'] ?? null) === null)) {
    $violations[] = 'composer.json must require twig/html-extra for EasyAdmin html_classes() support';
}

$servicesPath = $root.'/config/services.yaml';
$services = is_file($servicesPath) ? file_get_contents($servicesPath) : '';
if (is_string($services) && str_contains($services, 'Twig\\Extra\\Html\\HtmlExtension')) {
    $violations[] = 'config/services.yaml must not register Twig\Extra\Html\HtmlExtension directly; the host must install twig/html-extra so EasyAdmin can load the extension from vendor';
}

if ($violations !== []) {
    fwrite(STDERR, implode(PHP_EOL, $violations).PHP_EOL);
    exit(1);
}

$dashboardPath = $root.'/src/Controller/Admin/ManageDashboardController.php';
$dashboard = is_file($dashboardPath) ? file_get_contents($dashboardPath) : '';
if (!is_string($dashboard) || !str_contains($dashboard, "#[AdminDashboard(routePath: '/manage', routeName: 'manage_dashboard')]")) {
    fwrite(STDERR, 'Manage dashboard must be mounted at /manage.'.PHP_EOL);
    exit(1);
}

echo 'Managing canon guard passed.'.PHP_EOL;
