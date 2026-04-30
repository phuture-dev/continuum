<?php

declare(strict_types=1);

if (\PHP_VERSION_ID < 80000) {
    return;
}

require_once __DIR__ . '/../components/bootstrap.php';

if (defined('TESTER_ENVIRONMENT')) {
    return;
}

require_once __DIR__ . '/bootstrap81.php';
require_once __DIR__ . '/bootstrap82.php';
require_once __DIR__ . '/bootstrap83.php';
require_once __DIR__ . '/bootstrap84.php';
require_once __DIR__ . '/bootstrap85.php';
require_once __DIR__ . '/bootstrap-bcmath.php';
require_once __DIR__ . '/bootstrap-mbstring.php';
