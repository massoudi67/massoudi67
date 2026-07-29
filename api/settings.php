<?php
declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    init_db();
    $pdo = db();
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'status' => 'server_error',
        'message' => 'Service temporarily unavailable'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute(['proxy_url']);
        $row = $stmt->fetch();
        $proxyUrl = $row ? ($row['setting_value'] ?? '') : '';
        
        echo json_encode([
            'ok' => true,
            'status' => 'success',
            'proxy_url' => $proxyUrl
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'ok' => false,
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    
    $proxyUrl = isset($data['proxy_url']) ? trim($data['proxy_url']) : '';
    
    try {
        $stmt = $pdo->prepare('UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?');
        $stmt->execute([$proxyUrl, 'proxy_url']);
        
        echo json_encode([
            'ok' => true,
            'status' => 'success',
            'message' => 'Proxy URL updated successfully'
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'ok' => false,
            'status' => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

http_response_code(405);
echo json_encode([
    'ok' => false,
    'status' => 'method_not_allowed',
    'message' => 'Method not allowed'
], JSON_UNESCAPED_UNICODE);