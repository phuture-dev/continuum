<?php

// Fetch components list
$components = (object) [
    'components' => (object) []
];

if (file_exists(__DIR__ . '/components.json')) {
    $file = file_get_contents(__DIR__ . '/components.json');
    $decoded = json_decode($file);

    if (json_last_error() === JSON_ERROR_NONE) {
        $components = $decoded;
    }
}

// Download and extract components
$removeDirectory = function (string $dir) use (&$removeDirectory): void {
    foreach (glob($dir . '/*') ?: [] as $item) {
        is_dir($item) ? $removeDirectory($item) : unlink($item);
    }
    @rmdir($dir);
};

foreach ($components->components as $name => $component) {
    // Set download url
    $url = rtrim($component->git, '/') . '/archive/refs/tags/' . ($component->version ?? 'main') . '.zip';

    $path = __DIR__ . '/' . $name;
    if (is_dir($path)) {
        continue;
    }

    mkdir($path, 0755, true);
    $zip = new \ZipArchive();
    $zipFile = tempnam(sys_get_temp_dir(), 'component_');
    file_put_contents($zipFile, file_get_contents($url));

    if ($zip->open($zipFile)) {
        $temp = sys_get_temp_dir() . '/component_' . uniqid();
        mkdir($temp);
        $zip->extractTo($temp);
        $zip->close();

        $root = glob($temp . '/*')[0];
        foreach (
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            $target = $path . substr($item->getPathname(), strlen($root));
            $item->isDir() ? mkdir($target, 0755, true) : copy($item, $target);
        }

        $removeDirectory($temp);
    }

    unlink($zipFile);
}

// Load components
$loader = new Nette\Loaders\RobotLoader();
foreach ($components->components as $name => $component) {
    $constraint = true;
    if (!empty($component->constraint) && is_array($component->constraint)) {
        foreach ($component->constraint as [$var, $op, $val]) {
            $left = defined($var) ? constant($var) : $var;
            $constraint = match ($op) {
                '>=' => $constraint && $left >= $val,
                '>'  => $constraint && $left > $val,
                '<=' => $constraint && $left <= $val,
                '<'  => $constraint && $left < $val,
                '==' => $constraint && $left == $val,
                '!=' => $constraint && $left != $val,
            };
        }
    }

    if ($constraint) {
        $loader->addDirectory(__DIR__ . '/' . $name . ($component->autoload ?? ''));
    }
}
$loader->setTempDirectory(sys_get_temp_dir())->register();
