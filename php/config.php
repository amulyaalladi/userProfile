<?php

// ---------- CORS: lets the Netlify frontend call this backend ----------
$allowedOrigins = array_filter(array_map('trim', explode(',', getenv('ALLOWED_ORIGIN') ?: '')));
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($requestOrigin !== '' && in_array($requestOrigin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
    header('Vary: Origin');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}
// Browsers send an OPTIONS "preflight" request first; answer it and stop.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------- Settings ----------
// Priority: config.local.php (your private local file) > environment variables (Render) > defaults
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

function envOr(string $name, $default = '')
{
    $value = getenv($name);
    return ($value === false || $value === '') ? $default : $value;
}

$defaults = [
    'DB_HOST'        => 'localhost',
    'DB_PORT'        => '3306',
    'DB_NAME'        => 'userProfile',
    'DB_USER'        => 'root',
    'DB_PASS'        => '',
    'DB_SSL'         => 'false',
    'MONGO_URI'      => 'mongodb://localhost:27017',
    'MONGO_DB_NAME'  => 'userProfile',
    'REDIS_HOST'     => '127.0.0.1',
    'REDIS_PORT'     => '6379',
    'REDIS_USERNAME' => '',
    'REDIS_PASSWORD' => '',
    'REDIS_TLS'      => 'false',
];
foreach ($defaults as $name => $default) {
    if (!defined($name)) {
        define($name, envOr($name, $default));
    }
}
if (!defined('SESSION_TTL_SECONDS')) {
    define('SESSION_TTL_SECONDS', 3600);
}

require_once __DIR__ . '/../vendor/autoload.php';

function getMySQLConnection(): PDO
{
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [];
        if (filter_var(DB_SSL, FILTER_VALIDATE_BOOLEAN)) {
            // Hosted MySQL (e.g. Aiven) requires an encrypted connection
            $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        error_log('MySQL connection failed: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'MySQL connection failed']));
    }
}

function getMongoDB(): MongoDB\Database
{
    $client = new MongoDB\Client(MONGO_URI);
    return $client->selectDatabase(MONGO_DB_NAME);
}

function getRedisConnection(): Redis
{
    $redis = new Redis();
    $host = filter_var(REDIS_TLS, FILTER_VALIDATE_BOOLEAN) ? 'tls://' . REDIS_HOST : REDIS_HOST;
    $redis->connect($host, (int) REDIS_PORT, 5);
    if (REDIS_PASSWORD !== '') {
        $redis->auth(REDIS_USERNAME !== '' ? [REDIS_USERNAME, REDIS_PASSWORD] : REDIS_PASSWORD);
    }
    return $redis;
}

function generateSessionToken(): string
{
    return bin2hex(random_bytes(32));
}

function requireValidSession(Redis $redis): array
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authHeader = $headers['Authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    $token = trim(str_replace('Bearer', '', $authHeader));

    if (empty($token)) {
        http_response_code(401);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Missing session token']));
    }

    $sessionJson = $redis->get("session:$token");
    if ($sessionJson === false) {
        http_response_code(401);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Session expired or invalid, please login again']));
    }

    $redis->expire("session:$token", SESSION_TTL_SECONDS);

    return json_decode($sessionJson, true);
}