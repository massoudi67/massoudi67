<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['ok' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

init_db();
$pdo = db();
$row = $pdo->query('SELECT version, direct_url, file_size, release_notes, created_at FROM app_updates WHERE is_active = 1 ORDER BY id DESC LIMIT 1')->fetch();

if (!$row) {
    echo json_encode([
        'ok'            => true,
        'has_update'    => false,
        'version'       => '',
        'latest_version'=> '',
        'download_url'  => '',
        'download_mode' => 'direct',
        'file_size'     => '',
        'release_notes' => '',
        'published_at'  => '',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'ok'            => true,
    'has_update'    => true,
    'version'       => (string)($row['version'] ?? ''),
    'latest_version'=> (string)($row['version'] ?? ''),
    'download_url'  => (string)($row['direct_url'] ?? ''),
    'download_mode' => 'direct',
    'file_size'     => (string)($row['file_size'] ?? ''),
    'release_notes' => (string)($row['release_notes'] ?? ''),
    'published_at'  => (string)($row['created_at'] ?? ''),
], JSON_UNESCAPED_UNICODE);
