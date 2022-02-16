<?php

declare(strict_types=1);

use \DI\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . './../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load('../.env');

$containerBuilder = (new ContainerBuilder())
        ->useAnnotations(true)
        ->useAutowiring(true)
        ->addDefinitions(__DIR__ . '/../config/container.php');

$container = $containerBuilder->build();

set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
});