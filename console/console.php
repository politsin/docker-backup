#!/usr/bin/env php
<?php

if (!is_dir(__DIR__ . "/vendor")) {
  shell_exec("composer install --no-dev  -o -d " . __DIR__);
}

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use App\Command\S3Backup;

// Sup .env vars.
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Symfony app.
$app = new Application('Console App', 'v0.1.0');
$app->add(new S3Backup());
if (!empty($_ENV['TEMPLATE'])) {
  $app->setDefaultCommand($_ENV['TEMPLATE'], TRUE);
}
// Run.
$app->run();
