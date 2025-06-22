<?php
// Load environment variables from .env file
function loadEnv()
{
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse valid environment variables
            list($name, $value) = explode('=', $line, 2);
            if (!empty($name)) {
                $name = trim($name);
                $value = trim($value);

                // Remove quotes if present
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }

                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

// Load environment variables
loadEnv();

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'elearning');

// Application configuration
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8080/elearning_restructured_updated');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Google Auth configuration
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI'));

// Connect to the database
function getDbConnection()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

// Helper function to get site URL
function getSiteUrl($path = '')
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}
