<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
ensure_session_started();

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'chef_recipes_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

function getDbConnection(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    try {
        $connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        $connection->query('SELECT 1 FROM recipes LIMIT 1');
        $pdo = $connection;
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection or schema check failed (SQLSTATE ' . $e->getCode() . ').');
    }

    http_response_code(500);
    die('Database connection unavailable. Check that MySQL is running and your PHP MySQL extension, database settings and schema are correct. Fresh installations require an explicit database import; see README.md.');
}
