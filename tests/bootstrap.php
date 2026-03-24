<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

define('TESTER_ENVIRONMENT', true);

Tester\Environment::setup();
date_default_timezone_set('UTC');
