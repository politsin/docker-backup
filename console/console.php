#!/usr/bin/env php
<?php

if (!is_dir(__DIR__ . "/vendor")) {
  shell_exec("composer install --no-dev  -o -d " . __DIR__);
}

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

// Sup .env vars.
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Symfony app.
$app = new Application('Console App', 'v1.2.3');
print_r($_ENV);
// Run.
$app->run();
