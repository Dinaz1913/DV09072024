<?php

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__ . '/../../vendor/autoload.php';

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
} catch (Exception $e) {
    echo 'Could not load environment variables. Please check your .env file.';
    exit(1);
}

try {
    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions([
        Logger::class => function() {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log',
                Logger::DEBUG));
            return $logger;
        },
    ]);

    $container = $containerBuilder->build();
} catch (Exception $e) {
    echo 'Could not build the container. Please check your configuration.';
    exit(1);
}
