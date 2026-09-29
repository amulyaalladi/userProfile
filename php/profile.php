<?php
/**
 * profile.php
 * GET  -> returns the logged-in user's account info (MySQL) + profile
 *         details such as age, dob, contact (MongoDB).
 * POST/PUT -> updates the profile details in MongoDB.
 *
 * The caller must send: Authorization: Bearer <token>
 * The token is validated against the session stored in Redis.
 */
header('Content-Type: application/json');
require_once 'config.php';

$redis   = getRedisConnection();
$session = requireValidSession($redis); // dies with 401 JSON if invalid
$userId  = (int) $session['user_id'];

$method  = $_SERVER['REQUEST_METHOD'];
$mongoDb = getMongoDB();

if ($method === 'GET') {
    try {
        $pdo = getMySQLConnection();
        $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        $profileDoc = $mongoDb->profiles->findOne(['user_id' => $userId]);

        echo json_encode([
            'success' => true,
            'account' => $account,
            'profile' => [
                'age'     => $profileDoc['age'] ?? null,
                'dob'     => $profileDoc['dob'] ?? null,
                'contact' => $profileDoc['contact'] ?? null,
                'address' => $profileDoc['address'] ?? null,
            ],
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not load profile']);
    }
    exit;
}

if ($method === 'POST' || $method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);

    $age     = isset($input['age']) && $input['age'] !== '' ? (int) $input['age'] : null;
    $dob     = trim($input['dob'] ?? '');
    $contact = trim($input['contact'] ?? '');
    $address = trim($input['address'] ?? '');

    try {
        $mongoDb->profiles->updateOne(
            ['user_id' => $userId],
            ['$set' => [
                'age'        => $age,
                'dob'        => $dob,
                'contact'    => $contact,
                'address'    => $address,
                'updated_at' => new MongoDB\BSON\UTCDateTime(),
            ]],
            ['upsert' => true]
        );

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
