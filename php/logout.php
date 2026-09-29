<?php
/**
 * logout.php
 * Deletes the session token from Redis. The client also clears
 * localStorage on its side.
 */
header('Content-Type: application/json');
require_once 'config.php';

$headers = function_exists('getallheaders') ? getallheaders() : [];
$authHeader = $headers['Authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
$token = trim(str_replace('Bearer', '', $authHeader));

if ($token !== '') {
    $redis = getRedisConnection();
    $redis->del("session:$token");
}

echo json_encode(['success' => true, 'message' => 'Logged out']);
