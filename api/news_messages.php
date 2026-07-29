<?php
declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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
        'message' => 'Service temporarily unavailable',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ─────────────────────────────────────────────────────────────────────────────
// GET ?action=list&device_id=XXX
//   Returns all active messages + the unread count + which messages THIS
//   device has already marked as read. The desktop app uses this for both
//   the badge counter and the chat-popup list.
// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    $deviceId = trim((string)($_GET['device_id'] ?? ''));

    if ($action !== 'list') {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'status' => 'unknown_action',
            'message' => 'Unsupported GET action. Use ?action=list'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        // Pull the 50 most recent active messages — plenty for the chat list.
        $rows = $pdo->query(
            'SELECT id, title, message, message_en, message_fr, frequency, is_active, created_at, updated_at
               FROM app_news_messages
               WHERE is_active = 1
               ORDER BY id DESC
               LIMIT 50'
        )->fetchAll(PDO::FETCH_ASSOC);

        // Fetch which of these this device has already read (single query).
        $readIds = [];
        if ($deviceId !== '' && is_array($rows) && count($rows) > 0) {
            $ids = array_column($rows, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare(
                "SELECT message_id FROM user_news_reads
                   WHERE device_id = ? AND message_id IN ($placeholders)"
            );
            $stmt->execute(array_merge([$deviceId], $ids));
            $readIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }

        $unreadCount = 0;
        $messages = [];
        foreach ($rows as $r) {
            $isRead = in_array((int)$r['id'], $readIds, true);
            if (!$isRead) $unreadCount++;
            $messages[] = [
                'id'         => (int)$r['id'],
                'title'      => (string)($r['title'] ?? ''),
                'message'    => (string)($r['message'] ?? ''),
                'message_en' => (string)($r['message_en'] ?? ''),
                'message_fr' => (string)($r['message_fr'] ?? ''),
                'frequency'  => (string)($r['frequency'] ?? 'manual'),
                'created_at' => (string)($r['created_at'] ?? ''),
                'updated_at' => (string)($r['updated_at'] ?? ''),
                'is_read'    => $isRead,
            ];
        }

        echo json_encode([
            'ok' => true,
            'status' => 'success',
            'unread_count' => $unreadCount,
            'messages' => $messages,
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'status' => 'server_error',
            'message' => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// POST { device_id, message_ids: [1,2,3] }   ← mark messages as read
// Inserts one row per (device_id, message_id). Idempotent — duplicate (device,
// message) pairs are silently swallowed by the UNIQUE key.
// ─────────────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];

    $deviceId = trim((string)($data['device_id'] ?? ''));
    $messageIds = isset($data['message_ids']) && is_array($data['message_ids'])
        ? array_filter(array_map('intval', $data['message_ids']))
        : [];

    if ($deviceId === '' || count($messageIds) === 0) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'status' => 'missing_data',
            'message' => 'device_id and message_ids (non-empty array) are required',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO user_news_reads (device_id, message_id) VALUES (?, ?)'
        );
        foreach ($messageIds as $mid) {
            if ($mid > 0) $stmt->execute([$deviceId, $mid]);
        }
        echo json_encode([
            'ok' => true,
            'status' => 'success',
            'marked' => count($messageIds),
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'status' => 'server_error',
            'message' => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

http_response_code(405);
echo json_encode([
    'ok' => false,
    'status' => 'method_not_allowed',
    'message' => 'Method not allowed',
], JSON_UNESCAPED_UNICODE);
