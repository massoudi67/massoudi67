<?php
declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/../lib/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'status' => 'method_not_allowed',
        'message' => 'Method not allowed',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode((string)$raw, true);
$code = strtoupper(trim((string)($body['code'] ?? '')));
$deviceId = trim((string)($body['device_id'] ?? ''));

if ($code === '' || $deviceId === '') {
    echo json_encode([
        'ok' => false,
        'status' => 'missing_data',
        'message' => 'Missing code or device id',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    init_db();
    $pdo = db();
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'status' => 'server_error',
        'message' => 'Activation service temporarily unavailable',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function resolve_client_ip(): string
{
    $candidates = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];
    foreach ($candidates as $key) {
        $value = trim((string)($_SERVER[$key] ?? ''));
        if ($value === '') {
            continue;
        }
        if (strpos($value, ',') !== false) {
            $parts = explode(',', $value);
            $value = trim((string)($parts[0] ?? ''));
        }
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return $value;
        }
    }
    return '';
}

function resolve_country_code(string $ip): string
{
    $headerCountry = strtoupper(trim((string)($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')));
    if ($headerCountry !== '' && preg_match('/^[A-Z]{2}$/', $headerCountry) === 1) {
        return $headerCountry;
    }
    if ($ip === '') {
        return '';
    }

    // GeoIP PHP extension (if installed)
    if (function_exists('geoip_country_code_by_name')) {
        $code = @geoip_country_code_by_name($ip);
        if ($code !== false && $code !== '') {
            return strtoupper($code);
        }
    }

    // Fallback: ipapi.co (free, no key)
    $ch = curl_init("https://ipapi.co/{$ip}/country/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response !== false && is_string($response)) {
        $code = strtoupper(trim($response));
        if (preg_match('/^[A-Z]{2}$/', $code) === 1) {
            return $code;
        }
    }

    // Additional Fallback: ip-api.com
    try {
        $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
        $res = @file_get_contents("http://ip-api.com/json/" . urlencode($ip), false, $ctx);
        if ($res !== false && $res !== '') {
            $data = json_decode($res, true);
            if (is_array($data) && isset($data['countryCode'])) {
                $cc = strtoupper(trim($data['countryCode']));
                if (preg_match('/^[A-Z]{2}$/', $cc) === 1) {
                    return $cc;
                }
            }
        }
    } catch (\Throwable $e) {}

    return '';
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE activation_code = ? LIMIT 1');
$stmt->execute([$code]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode([
        'ok' => false,
        'status' => 'invalid_code',
        'message' => 'Activation code not found',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($user['status'] ?? 'inactive') !== 'active') {
    echo json_encode([
        'ok' => false,
        'status' => 'inactive_code',
        'message' => 'Activation code is inactive',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$clientIp = resolve_client_ip();
$countryCode = resolve_country_code($clientIp);
error_log("[Activate] IP={$clientIp} Country={$countryCode} Code={$code} Device={$deviceId}");
$now = gmdate('Y-m-d H:i:s');
$expiresAt = trim((string)($user['expires_at'] ?? ''));
$accountType = trim((string)($user['account_type'] ?? 'paid')) === 'trial' ? 'trial' : 'paid';
$trialEndsAt = trim((string)($user['trial_ends_at'] ?? ''));
$trialEmail = trim((string)($user['trial_email'] ?? ''));
if ($trialEndsAt === '' && $accountType === 'trial' && $expiresAt !== '') {
    $trialEndsAt = $expiresAt;
}
if ($expiresAt !== '' && strtotime($expiresAt) !== false && strtotime($now) > strtotime($expiresAt)) {
    echo json_encode([
        'ok' => false,
        'status' => 'expired_code',
        'message' => 'Activation code has expired',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$userId = (int)$user['id'];
$savedDevice = trim((string)($user['device_id'] ?? ''));
$maxDevices = max(1, (int)($user['max_devices'] ?? 1));
$seedStmt = $pdo->prepare('SELECT COUNT(*) FROM user_devices WHERE user_id = ?');
$seedStmt->execute([$userId]);
$deviceRowsCount = (int)$seedStmt->fetchColumn();
if ($deviceRowsCount === 0 && $savedDevice !== '') {
    $seedActivatedAt = trim((string)($user['activated_at'] ?? '')) !== '' ? (string)$user['activated_at'] : $now;
    $seedLastSeenAt = trim((string)($user['last_seen_at'] ?? '')) !== '' ? (string)$user['last_seen_at'] : $now;
    $seedInsert = $pdo->prepare('
        INSERT INTO user_devices (user_id, device_id, country_code, last_ip, first_activated_at, last_seen_at)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE last_seen_at = VALUES(last_seen_at)
    ');
    $seedInsert->execute([
        $userId,
        $savedDevice,
        trim((string)($user['country_code'] ?? '')),
        trim((string)($user['last_ip'] ?? '')),
        $seedActivatedAt,
        $seedLastSeenAt
    ]);
}
$currentDeviceStmt = $pdo->prepare('SELECT id FROM user_devices WHERE user_id = ? AND device_id = ? LIMIT 1');
$currentDeviceStmt->execute([$userId, $deviceId]);
$currentDevice = $currentDeviceStmt->fetch();
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM user_devices WHERE user_id = ?');
$countStmt->execute([$userId]);
$activeDevicesCount = (int)$countStmt->fetchColumn();

if ($accountType === 'trial' && !$currentDevice) {
    $claimCheck = $pdo->prepare('SELECT user_id FROM trial_device_claims WHERE device_id = ? LIMIT 1');
    $claimCheck->execute([$deviceId]);
    $claim = $claimCheck->fetch();
    if ($claim && (int)$claim['user_id'] !== $userId) {
        echo json_encode([
            'ok' => false,
            'status' => 'trial_already_used',
            'message' => 'This device has already received a free trial. Each device is limited to one trial only.',
            'support_email' => 'massoudisameh@gmail.com'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!$currentDevice && $activeDevicesCount >= $maxDevices) {
    echo json_encode([
        'ok' => false,
        'status' => 'device_limit_reached',
        'message' => 'Maximum devices reached for this activation code',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$wasFirstActivation = false;
if (!$currentDevice) {
    $insertDevice = $pdo->prepare('
        INSERT INTO user_devices (user_id, device_id, country_code, last_ip, first_activated_at, last_seen_at)
        VALUES (?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE last_seen_at = VALUES(last_seen_at), country_code = VALUES(country_code), last_ip = VALUES(last_ip)
    ');
    $insertDevice->execute([$userId, $deviceId, $countryCode, $clientIp]);
    $activeDevicesCount += 1;
    $wasFirstActivation = $activeDevicesCount === 1;

    if ($accountType === 'trial') {
        $insertClaim = $pdo->prepare('
            INSERT INTO trial_device_claims (device_id, email, user_id, created_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE email = VALUES(email), user_id = VALUES(user_id)
        ');
        $insertClaim->execute([$deviceId, $trialEmail ?: $user['email'], $userId]);
    }
} else {
    $updateDevice = $pdo->prepare('UPDATE user_devices SET country_code = ?, last_ip = ?, last_seen_at = NOW() WHERE id = ?');
    $updateDevice->execute([$countryCode, $clientIp, (int)$currentDevice['id']]);
}
$update = $pdo->prepare('
    UPDATE users
    SET device_id = CASE WHEN COALESCE(device_id, \'\') = \'\' THEN ? ELSE device_id END,
        country_code = ?,
        last_ip = ?,
        activated_at = CASE WHEN activated_at IS NULL OR activated_at = \'\' THEN ? ELSE activated_at END,
        last_seen_at = ?
    WHERE id = ?
');
$update->execute([$deviceId, $countryCode, $clientIp, $now, $now, $userId]);
echo json_encode([
    'ok' => true,
    'status' => $wasFirstActivation ? 'activated_first_time' : 'activated',
    'message' => $wasFirstActivation ? 'Activated successfully' : 'Activation valid',
    'support_email' => 'massoudisameh@gmail.com',
    'license' => [
        'account_type' => $accountType,
        'full_name' => trim((string)($user['full_name'] ?? '')),
        'max_devices' => $maxDevices,
        'active_devices' => $activeDevicesCount,
        'expires_at' => $expiresAt !== '' ? $expiresAt : null,
        'trial_email' => $trialEmail !== '' ? $trialEmail : null,
        'trial_ends_at' => $trialEndsAt !== '' ? $trialEndsAt : null
    ]
], JSON_UNESCAPED_UNICODE);
