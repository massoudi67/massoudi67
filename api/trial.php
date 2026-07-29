<?php
declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/mailer.php';

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
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'method_not_allowed',
        'message' => 'Method not allowed',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode((string)$raw, true);
$email = strtolower(trim((string)($body['email'] ?? '')));
$deviceId = trim((string)($body['device_id'] ?? ''));

if ($email === '') {
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'missing_data',
        'message' => 'Missing email address',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// device_id is optional — provided when opened from the app, absent when opened from browser directly

// ── Only Gmail allowed ──
if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'invalid_email',
        'message' => 'Only Gmail addresses (@gmail.com) are accepted for the free trial.',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Normalize: strip Gmail aliases (sameh+1@gmail.com → sameh@gmail.com)
$email = preg_replace('/\+.*(?=@gmail\.com$)/', '', $email);

// ── VPN / Proxy / Datacenter detection ──
function isVpnOrProxy(string $ip): array {
    $result = ['blocked' => false, 'reason' => ''];
    if ($ip === '') return $result;

    // Check common proxy headers
    $proxyHeaders = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CF_CONNECTING_IP',
        'HTTP_VIA',
        'HTTP_X_PROXY_ID',
        'HTTP_FORWARDED',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_X_COMING_FROM',
        'HTTP_X_FORWARDED_HOST',
        'HTTP_X_REMOTE_ADDR',
        'HTTP_CLIENT_IP'
    ];
    $proxyCount = 0;
    foreach ($proxyHeaders as $h) {
        if (!empty($_SERVER[$h])) $proxyCount++;
    }
    if ($proxyCount >= 3) {
        return ['blocked' => true, 'reason' => 'Proxy headers detected'];
    }

    // Check known VPN/Hosting ASN ranges via ip-api (free, no key)
    $ctx = stream_context_create(['http' => ['timeout' => 2, 'user_agent' => 'SAMTraffic/1.0']]);
    $res = @file_get_contents('http://ip-api.com/json/' . urlencode($ip) . '?fields=isp,org,as,mobile,proxy,hosting', false, $ctx);
    if ($res !== false) {
        $data = json_decode($res, true);
        if (!empty($data['proxy']) && $data['proxy'] === true) {
            return ['blocked' => true, 'reason' => 'Known proxy IP'];
        }
        if (!empty($data['hosting']) && $data['hosting'] === true) {
            return ['blocked' => true, 'reason' => 'Datacenter / hosting IP detected'];
        }
        if (!empty($data['mobile']) && $data['mobile'] === true) {
            // Mobile is usually fine, but flag if combined with other signals
        }
        $isp = strtolower(($data['isp'] ?? '') . ' ' . ($data['org'] ?? ''));
        $blockedKeywords = ['vpn', 'proxy', 'tor', 'datacenter', 'hosting', 'cloudflare', 'ovh', 'digitalocean', 'linode', 'vultr', 'hetzner', 'aws', 'amazon', 'azure', 'google cloud', 'contabo', 'terrabyte', 'hide', 'nord', 'expressvpn', 'surfshark', 'cyberghost', 'proton', 'windscribe'];
        foreach ($blockedKeywords as $kw) {
            if (strpos($isp, $kw) !== false) {
                return ['blocked' => true, 'reason' => 'VPN/Hosting provider detected (' . ($data['isp'] ?? 'unknown') . ')'];
            }
        }
    }

    return $result;
}

$clientIp = resolve_client_ip();
$vpnCheck = isVpnOrProxy($clientIp);
if ($vpnCheck['blocked']) {
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'vpn_blocked',
        'message' => 'Free trials cannot be activated using a VPN, proxy, or datacenter IP. Please disable your VPN and try again.',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$countryCode = resolve_country_code($clientIp);
error_log("[Trial] IP={$clientIp} Country={$countryCode} Device={$deviceId} Email={$email}");

try {
    init_db();
    $pdo = db();
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
    $isDbConnectionError = stripos($errorMsg, 'connection') !== false || 
                       stripos($errorMsg, 'hostname') !== false ||
                       stripos($errorMsg, 'access denied') !== false;
    $userMessage = $isDbConnectionError 
        ? 'Service temporarily unavailable. Please try again in a few minutes.' 
        : 'Trial service temporarily unavailable';
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'server_error',
        'message' => $userMessage,
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    error_log('Trial API Error: ' . $errorMsg);
    exit;
}

$now = gmdate('Y-m-d H:i:s');

// ── BLOCK 0: Check if this email already used for any trial ──
$emailStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND account_type = "trial" LIMIT 1');
$emailStmt->execute([$email]);
if ($emailStmt->fetch()) {
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'trial_email_used',
        'message' => 'This email has already been used for a free trial. Each email is limited to one trial only.',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── BLOCK 1: Check trial_device_claims (permanent ban after expired trial) ──
$claimStmt = $pdo->prepare('SELECT * FROM trial_device_claims WHERE device_id = ? LIMIT 1');
$claimStmt->execute([$deviceId]);
$existingClaim = $claimStmt->fetch();

if ($existingClaim) {
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'trial_device_used',
        'message' => 'This device has already received a free trial. Each device is limited to one trial only.',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── BLOCK 2: Check user_devices for active or expired trial ──
$existingStmt = $pdo->prepare('
    SELECT u.*, d.id AS device_row_id
    FROM user_devices d
    INNER JOIN users u ON u.id = d.user_id
    WHERE d.device_id = ? AND COALESCE(u.account_type, "paid") = "trial"
    ORDER BY u.id DESC
    LIMIT 1
');
$existingStmt->execute([$deviceId]);
$existingTrial = $existingStmt->fetch();

if ($existingTrial) {
    $expiresAt = trim((string)($existingTrial['trial_ends_at'] ?? $existingTrial['expires_at'] ?? ''));
    $isExpired = $expiresAt !== '' && strtotime($expiresAt) !== false && strtotime($now) > strtotime($expiresAt);

    if ($isExpired) {
        $insertClaim = $pdo->prepare('
            INSERT INTO trial_device_claims (device_id, email, user_id, created_at)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE email = VALUES(email), user_id = VALUES(user_id)
        ');
        $insertClaim->execute([
            $deviceId,
            trim((string)($existingTrial['trial_email'] ?? $existingTrial['email'] ?? $email)),
            (int)$existingTrial['id'],
            $now
        ]);

        echo json_encode([
            'ok' => false,
            'activated' => false,
            'status' => 'trial_device_used',
            'message' => 'This device has already received a free trial. Each device is limited to one trial only.',
            'support_email' => 'massoudisameh@gmail.com'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $updateSeen = $pdo->prepare('UPDATE users SET last_seen_at = ?, country_code = ?, last_ip = ? WHERE id = ?');
    $updateSeen->execute([$now, $countryCode, $clientIp, (int)$existingTrial['id']]);
    $updateDeviceSeen = $pdo->prepare('UPDATE user_devices SET last_seen_at = ?, country_code = ?, last_ip = ? WHERE id = ?');
    $updateDeviceSeen->execute([$now, $countryCode, $clientIp, (int)$existingTrial['device_row_id']]);

    echo json_encode([
        'ok' => true,
        'activated' => true,
        'status' => 'trial_already_active',
        'message' => 'Trial already active on this device',
        'support_email' => 'massoudisameh@gmail.com',
        'code' => (string)($existingTrial['activation_code'] ?? ''),
        'license' => [
            'account_type' => 'trial',
            'trial_email' => trim((string)($existingTrial['trial_email'] ?? $existingTrial['email'] ?? '')),
            'trial_ends_at' => $expiresAt !== '' ? $expiresAt : null,
            'expires_at' => $expiresAt !== '' ? $expiresAt : null,
            'max_devices' => 1,
            'active_devices' => 1
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── BLOCK 3a: Rate limit — max 3 requests per IP per 24h (even unverified) ──
$rateStmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM trial_verifications WHERE client_ip = ? AND created_at > DATE_SUB(?, INTERVAL 24 HOUR)');
$rateStmt->execute([$clientIp, $now]);
$rateCount = (int)($rateStmt->fetch()['cnt'] ?? 0);
if ($rateCount >= 3) {
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'trial_rate_limit',
        'message' => 'Too many trial requests from this network. Please wait 24 hours or contact support.',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── BLOCK 3b: IP protection — limit completed trials per IP ──
$ipStmt = $pdo->prepare('
    SELECT COUNT(*) AS cnt
    FROM trial_device_claims
    WHERE device_id IN (
        SELECT DISTINCT device_id FROM trial_verifications WHERE client_ip = ? AND created_at > DATE_SUB(?, INTERVAL 30 DAY)
    )
');
$ipStmt->execute([$clientIp, $now]);
$ipCount = (int)($ipStmt->fetch()['cnt'] ?? 0);

if ($ipCount >= 1) {
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'trial_ip_limit',
        'message' => 'This network has already received a free trial. Each person is limited to one trial only. Please purchase a license to continue using SAM Traffic Pro.',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── BLOCK 4: Check for pending verification ──
$pendingStmt = $pdo->prepare('
    SELECT * FROM trial_verifications
    WHERE email = ? AND device_id = ? AND verified_at IS NULL AND expires_at > ?
    LIMIT 1
');
$pendingStmt->execute([$email, $deviceId, $now]);
$pending = $pendingStmt->fetch();

if ($pending) {
    echo json_encode([
        'ok' => true,
        'activated' => false,
        'status' => 'otp_sent',
        'message' => 'A verification link has already been sent to your email. Please check your inbox and spam folder.',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── BLOCK 5: Generate OTP token and send email ──
$token = bin2hex(random_bytes(32));
$otpExpires = gmdate('Y-m-d H:i:s', time() + 3600); // 1 hour

$insertOtp = $pdo->prepare('
    INSERT INTO trial_verifications (token, email, device_id, client_ip, country_code, expires_at)
    VALUES (?, ?, ?, ?, ?, ?)
');
$insertOtp->execute([$token, $email, $deviceId, $clientIp, $countryCode, $otpExpires]);

// Build verification URL
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = trim((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'samtrafficbot.com'));
$verifyUrl = $scheme . '://' . $host . '/samtraffic/api/verify-trial.php?token=' . urlencode($token);

$subject = '🚀 SAM Traffic Pro — Your Free Trial Awaits!';

$waLink = 'https://wa.me/216396022017';

$bodyText = "🚀 SAM Traffic Pro\n━━━━━━━━━━━━━━━━━━━━\n\nHi there! 👋\n\nYou're one step away from unlocking the ultimate web traffic automation tool. Click the link below to activate your FREE trial:\n\n👉 " . $verifyUrl . "\n\n⏰ This link expires in 1 hour.\n\n━━━━━━━━━━━━━━━━━━━━\n🌟 Why SAM Traffic Pro?\n• Boost your website traffic effortlessly\n• Smart targeting & real-time analytics\n• Easy setup — no technical skills needed\n\n📲 Need help? Chat with us on WhatsApp:\n" . $waLink . "\n\n━━━━━━━━━━━━━━━━━━━━\nIf you did not request this, please ignore this email.\nSAM Traffic Pro\n";

$bodyHtml = '<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family:Segoe UI,system-ui,Arial,sans-serif; line-height:1.6; color:#e2e8f0; background:#0f172a; margin:0; padding:24px;">
<table role="presentation" style="width:100%; max-width:560px; margin:0 auto; background:#1e1b4b; border-radius:20px; overflow:hidden; border:1px solid rgba(139,92,246,0.15); box-shadow:0 20px 40px rgba(0,0,0,0.35);">
  <tr>
    <td style="background:linear-gradient(135deg,#8b5cf6,#ec4899); padding:28px 24px; text-align:center;">
      <div style="font-size:42px; margin-bottom:8px;">🚀</div>
      <h1 style="margin:0; color:#fff; font-size:22px; font-weight:700;">SAM Traffic Pro</h1>
      <p style="margin:6px 0 0; color:rgba(255,255,255,0.85); font-size:14px;">Ultimate Website Traffic Automation</p>
    </td>
  </tr>
  <tr>
    <td style="padding:28px 24px;">
      <p style="margin:0 0 16px; color:#cbd5e1; font-size:15px;">Hi there! 👋</p>
      <p style="margin:0 0 20px; color:#94a3b8; font-size:15px;">You are <strong>one click away</strong> from unlocking your free trial. Tap the button below to activate it instantly:</p>
      <p style="text-align:center; margin:24px 0;">
        <a href="' . htmlspecialchars($verifyUrl) . '" style="display:inline-block; background:linear-gradient(135deg,#8b5cf6,#ec4899); color:#fff; padding:14px 32px; border-radius:12px; text-decoration:none; font-weight:700; font-size:16px; box-shadow:0 8px 20px rgba(139,92,246,0.35);">🎁 Activate My Free Trial</a>
      </p>
      <p style="color:#64748b; font-size:13px; margin:0 0 8px;">Or paste this link in your browser:</p>
      <p style="background:rgba(15,23,42,0.6); border:1px dashed rgba(139,92,246,0.3); border-radius:10px; padding:10px 14px; font-size:12px; color:#c4b5fd; word-break:break-all; margin:0 0 20px;">' . htmlspecialchars($verifyUrl) . '</p>
      <p style="color:#f87171; font-size:13px; margin:0 0 24px;">⏰ This link expires in <strong>1 hour</strong>.</p>

      <table role="presentation" style="width:100%; background:rgba(139,92,246,0.06); border-radius:12px; padding:16px; border:1px solid rgba(139,92,246,0.1);">
        <tr><td style="padding:0;">
          <h3 style="margin:0 0 10px; color:#a78bfa; font-size:16px;">🌟 Why marketers love SAM Traffic Pro</h3>
          <ul style="margin:0; padding-left:18px; color:#94a3b8; font-size:14px;">
            <li style="margin-bottom:6px;">🎯 Smart targeting &amp; real-time analytics</li>
            <li style="margin-bottom:6px;">⚡ Effortless traffic boost on autopilot</li>
            <li style="margin-bottom:6px;">🔒 Safe, reliable &amp; beginner-friendly</li>
            <li>🌍 Works with any website niche</li>
          </ul>
        </td></tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="padding:0 24px 24px; text-align:center;">
      <p style="color:#64748b; font-size:13px; margin:0 0 12px;">📲 Need help or have questions?</p>
      <a href="' . htmlspecialchars($waLink) . '" style="display:inline-flex; align-items:center; gap:8px; background:#25D366; color:#fff; padding:10px 22px; border-radius:10px; text-decoration:none; font-weight:700; font-size:14px;">
        💬 Chat on WhatsApp +216 39 602 2017
      </a>
    </td>
  </tr>
  <tr>
    <td style="padding:0 24px 24px;">
      <hr style="border:none; border-top:1px solid rgba(255,255,255,0.06); margin:0 0 16px;">
      <p style="color:#475569; font-size:12px; text-align:center; margin:0;">If you did not request this trial, you can safely ignore this email.<br>SAM Traffic Pro &copy; ' . gmdate('Y') . '</p>
    </td>
  </tr>
</table>
</body></html>';

// Send verification email (supports free hosting: tries mail() then SMTP)
$mailSent = sendTrialEmail($email, $subject, $bodyHtml, $bodyText);

if (!$mailSent) {
    error_log("[Trial] Failed to send OTP email to {$email}. Check lib/mailer.php config.");
    
    // Delete the verification record we just inserted so the user can retry
    $deleteOtp = $pdo->prepare('DELETE FROM trial_verifications WHERE token = ?');
    $deleteOtp->execute([$token]);
    
    echo json_encode([
        'ok' => false,
        'activated' => false,
        'status' => 'mail_send_failed',
        'message' => 'Unable to send the verification email. Please try again later or contact support directly.',
        'support_email' => 'massoudisameh@gmail.com'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'ok' => true,
    'activated' => false,
    'status' => 'otp_sent',
    'message' => 'A verification link has been sent to your email. Please check your inbox (and spam) and click the link to activate your trial.',
    'support_email' => 'massoudisameh@gmail.com'
], JSON_UNESCAPED_UNICODE);
