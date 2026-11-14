<?php
/**
 * CSCK543 Networks and Web Technology - Group B
 * Universal Multi-OS Database Configuration & Auto-Initialization Module
 *
 * Compatible with:
 * - Windows 10/11 (XAMPP, WAMP, Laragon, IIS)
 * - macOS Intel & Apple Silicon (XAMPP, MAMP, Homebrew, Built-in PHP Server)
 * - Linux Ubuntu/Debian/Fedora (LAMP stack, Apache/Nginx)
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'chef_recipes_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

/**
 * Returns a singleton PDO connection that works across all Operating Systems:
 * 1. Connects to MySQL/MariaDB across Windows/macOS/Linux (ports 3306, 8889, or Unix sockets).
 * 2. Auto-bootstraps `chef_recipes_db` from `sql/chef_recipes_db.sql` on fresh MySQL setups.
 */
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

    // Candidate MySQL endpoints across Windows (XAMPP/WAMP), macOS (XAMPP/MAMP/Homebrew), and Linux (LAMP)
    $candidates = [
        ['dsn_db' => sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME), 'dsn_server' => sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT), 'user' => DB_USER, 'pass' => DB_PASS],
        ['dsn_db' => sprintf('mysql:host=127.0.0.1;port=3306;dbname=%s;charset=utf8mb4', DB_NAME), 'dsn_server' => 'mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'user' => 'root', 'pass' => 'root'],
        ['dsn_db' => sprintf('mysql:host=127.0.0.1;port=8889;dbname=%s;charset=utf8mb4', DB_NAME), 'dsn_server' => 'mysql:host=127.0.0.1;port=8889;charset=utf8mb4', 'user' => 'root', 'pass' => 'root'],
    ];

    $sockets = [
        '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
        '/Applications/MAMP/tmp/mysql/mysql.sock',
        '/tmp/mysql_csck543.sock',
        '/tmp/mysql.sock',
        '/var/run/mysqld/mysqld.sock',
        '/var/lib/mysql/mysql.sock',
        '/opt/homebrew/var/mysql/mysql.sock',
    ];
    foreach ($sockets as $sock) {
        if (@file_exists($sock)) {
            $candidates[] = [
                'dsn_db'     => sprintf('mysql:unix_socket=%s;dbname=%s;charset=utf8mb4', $sock, DB_NAME),
                'dsn_server' => sprintf('mysql:unix_socket=%s;charset=utf8mb4', $sock),
                'user'       => DB_USER,
                'pass'       => DB_PASS,
            ];
        }
    }

    if (extension_loaded('pdo_mysql')) {
        foreach ($candidates as $cand) {
            try {
                $pdo = new PDO($cand['dsn_db'], $cand['user'], $cand['pass'], $options);
                $pdo->query('SELECT 1 FROM recipes LIMIT 1');
                return $pdo;
            } catch (PDOException $e) {
                try {
                    $bootstrapPdo = new PDO($cand['dsn_server'], $cand['user'], $cand['pass'], $options);
                    $sqlFile = __DIR__ . '/../sql/chef_recipes_db.sql';
                    if (is_readable($sqlFile)) {
                        $sql = (string)file_get_contents($sqlFile);
                        $bootstrapPdo->exec($sql);
                        $pdo = new PDO($cand['dsn_db'], $cand['user'], $cand['pass'], $options);
                        $pdo->query('SELECT 1 FROM recipes LIMIT 1');
                        return $pdo;
                    }
                } catch (PDOException $inner) {
                    // Try next candidate
                }
            }
        }
    }

    http_response_code(500);
    die('Database connection unavailable. Please start MySQL in XAMPP and check your database configuration.');
}
