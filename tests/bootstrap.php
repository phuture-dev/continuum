<?php

declare(strict_types=1);

define('TESTER_ENVIRONMENT', true);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
date_default_timezone_set('UTC');
