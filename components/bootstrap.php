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
