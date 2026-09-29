<?php
// Lightweight health check for the hosting platform (no database calls).
header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
