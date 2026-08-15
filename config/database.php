<?php
/**
 * Database connection (PDO / MySQL)
 * Reads from .env when present (see .env.example); otherwise falls back
 * to the XAMPP defaults below, so local development needs zero setup.
 */

require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/../.env');

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'pms_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));       // default XAMPP root password is empty
define('DB_CHARSET', 'utf8mb4');

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $detail = env('APP_ENV', 'local') === 'production'
                ? 'Please contact the system administrator.'
                : 'Details: ' . htmlspecialchars($e->getMessage());
            http_response_code(500);
            die('Database connection failed. Make sure MySQL is running and that '
                . 'the "pms_db" database has been imported from database/schema.sql. ' . $detail);
        }
    }

    return $pdo;
}
