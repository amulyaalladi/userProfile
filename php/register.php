<?php
/**
 * register.php
 * Receives JSON via jQuery AJAX (no form submission).
 * Inserts the new user into MySQL using PDO prepared statements
 * and creates an empty profile document in MongoDB for that user.
 */
header('Content-Type: application/json');
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

$username = trim($input['username'] ?? '');
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// ---- Validation ----
if ($username === '' || $email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    $pdo = getMySQLConnection();

    // Prepared statement - check for existing username/email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Prepared statement - insert new user
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$username, $email, $hashedPassword]);

    $userId = (int) $pdo->lastInsertId();

    // Create a blank profile document in MongoDB tied to this user id
    $mongoDb = getMongoDB();
    $mongoDb->profiles->insertOne([
        'user_id'    => $userId,
        'age'        => null,
        'dob'        => null,
        'contact'    => null,
        'address'    => null,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
    ]);

    echo json_encode(['success' => true, 'message' => 'Registration successful. Please log in.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
