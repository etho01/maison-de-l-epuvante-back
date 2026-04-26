<?php
header('Content-Type: application/json');

echo json_encode([
    'getenv_DATABASE_HOST' => getenv('DATABASE_HOST'),
    'getenv_DATABASE_PORT' => getenv('DATABASE_PORT'),
    'getenv_DATABASE_NAME' => getenv('DATABASE_NAME'),
    'getenv_DATABASE_USER' => getenv('DATABASE_USER'),
    'getenv_DATABASE_PASSWORD' => getenv('DATABASE_PASSWORD') ? '***SET***' : false,
    '_ENV_DATABASE_HOST' => $_ENV['DATABASE_HOST'] ?? null,
    '_SERVER_DATABASE_HOST' => $_SERVER['DATABASE_HOST'] ?? null,
], JSON_PRETTY_PRINT);
