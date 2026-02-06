<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Setup test database
if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'test') {
    // Boot the kernel to setup the database schema
    require_once dirname(__DIR__).'/src/Kernel.php';
    
    $kernel = new App\Kernel('test', true);
    $kernel->boot();
    
    $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
    $application->setAutoExit(false);
    
    // Drop and create database schema
    $application->run(new \Symfony\Component\Console\Input\ArrayInput([
        'command' => 'doctrine:schema:drop',
        '--force' => true,
        '--full-database' => true,
        '--quiet' => true,
    ]));
    
    $application->run(new \Symfony\Component\Console\Input\ArrayInput([
        'command' => 'doctrine:schema:create',
        '--quiet' => true,
    ]));
    
    $kernel->shutdown();
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
