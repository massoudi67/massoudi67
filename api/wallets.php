<?php
declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Method not allowed'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    init_db();
    $pdo = db();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'message' => 'Service temporarily unavailable'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->query('
    SELECT id, currency, network, address, display_order, is_active
    FROM wallet_addresses
    WHERE is_active = 1
    ORDER BY display_order ASC, id ASC
');
$wallets = $stmt->fetchAll();

echo json_encode([
    'ok' => true,
    'wallets' => $wallets
], JSON_UNESCAPED_UNICODE);
