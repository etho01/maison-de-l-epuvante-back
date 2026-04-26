<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

echo "=== Environment Variables ===\n";
echo "DATABASE_HOST: " . ($_ENV['DATABASE_HOST'] ?? getenv('DATABASE_HOST') ?: 'NOT SET') . "\n";
echo "DATABASE_PORT: " . ($_ENV['DATABASE_PORT'] ?? getenv('DATABASE_PORT') ?: 'NOT SET') . "\n";
echo "DATABASE_NAME: " . ($_ENV['DATABASE_NAME'] ?? getenv('DATABASE_NAME') ?: 'NOT SET') . "\n";
echo "DATABASE_USER: " . ($_ENV['DATABASE_USER'] ?? getenv('DATABASE_USER') ?: 'NOT SET') . "\n";
echo "DATABASE_PASSWORD: " . (($_ENV['DATABASE_PASSWORD'] ?? getenv('DATABASE_PASSWORD')) ? '***SET***' : 'NOT SET') . "\n";
echo "DATABASE_URL: " . ($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: 'NOT SET') . "\n";
echo "\n";

// Tester la connexion directe avec PDO
echo "=== PDO Connection Test ===\n";
$host = $_ENV['DATABASE_HOST'] ?? getenv('DATABASE_HOST');
$port = $_ENV['DATABASE_PORT'] ?? getenv('DATABASE_PORT');
$dbname = $_ENV['DATABASE_NAME'] ?? getenv('DATABASE_NAME');
$user = $_ENV['DATABASE_USER'] ?? getenv('DATABASE_USER');
$password = $_ENV['DATABASE_PASSWORD'] ?? getenv('DATABASE_PASSWORD');

if ($host && $port && $dbname && $user) {
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        echo "DSN: $dsn\n";
        $pdo = new PDO($dsn, $user, $password);
        echo "✅ PDO Connection: SUCCESS\n";
    } catch (PDOException $e) {
        echo "❌ PDO Connection: FAILED - " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Missing connection parameters\n";
}
