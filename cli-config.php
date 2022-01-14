<?php

use \DI\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load('.env');

$containerBuilder = (new ContainerBuilder())
    ->useAnnotations(true)
    ->useAutowiring(true)
    ->addDefinitions(__DIR__ . '/config/container.php');

$container = $containerBuilder->build();

$entityManager = $container->get(Doctrine\ORM\EntityManager::class);
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
