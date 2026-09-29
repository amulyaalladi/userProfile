<?php
/**
 * login.php
 * Verifies username/password against MySQL (prepared statements).
 * On success, generates a random session token, stores the session
 * data in Redis (NOT PHP $_SESSION), and returns the token to the
 * client. The client is responsible for storing it in localStorage
 * and sending it back as an Authorization: Bearer <token> header.
 */
header('Content-Type: application/json');
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

$identifier = trim($input['username'] ?? ''); // username or email
$password   = $input['password'] ?? '';

if ($identifier === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

try {
    $pdo = getMySQLConnection();

    // Prepared statement - look up by username OR email
    $stmt = $pdo->prepare('SELECT id, username, email, password FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }

    // Build session, store it in Redis keyed by a random token
    $token = generateSessionToken();
    $redis = getRedisConnection();

    $sessionData = json_encode([
        'user_id'  => (int) $user['id'],
        'username' => $user['username'],
        'email'    => $user['email'],
    ]);

    $redis->setex("session:$token", SESSION_TTL_SECONDS, $sessionData);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token'   => $token, // client stores this in localStorage
        'user'    => [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
}
