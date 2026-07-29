<?php
declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/../lib/db.php';

$token = trim((string)($_GET['token'] ?? ''));

function htmlPage(string $title, string $content, bool $success = false): void {
    $statusColor = $success ? '#4ade80' : '#f87171';
    $bgGradient  = $success ? 'linear-gradient(135deg, #0f172a, #1e1b4b)' : 'linear-gradient(135deg, #0f172a, #3b0f0f)';
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . htmlspecialchars($title) . ' | SAM Traffic Pro</title>';
    echo '<style>';
    echo '*{box-sizing:border-box;margin:0;padding:0}body{font-family:Segoe UI,system-ui,-apple-system,sans-serif;background:' . $bgGradient . ';color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}';
    echo '.card{background:rgba(15,23,42,0.85);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.06);border-radius:20px;padding:40px;max-width:520px;width:100%;text-align:center;box-shadow:0 25px 50px rgba(0,0,0,0.4)}';
    echo '.icon{font-size:56px;margin-bottom:16px}';
    echo 'h1{font-size:24px;font-weight:700;margin-bottom:12px;color:' . $statusColor . '}';
    echo 'p{color:#94a3b8;line-height:1.6;margin-bottom:20px;font-size:15px}';
    echo '.code-box{background:rgba(139,92,246,0.12);border:1px dashed rgba(139,92,246,0.5);border-radius:12px;padding:16px 24px;margin:16px 0;font-family:monospace;font-size:18px;color:#c4b5fd;letter-spacing:1px;word-break:break-all}';
    echo '.btn{display:inline-block;background:linear-gradient(135deg,#8b5cf6,#ec4899);color:#fff;text-decoration:none;padding:12px 28px;border-radius:10px;font-weight:600;font-size:15px;margin-top:8px;transition:opacity .2s}';
    echo '.btn:hover{opacity:.9}';
    echo '.footer{margin-top:24px;font-size:12px;color:#64748b;border-top:1px solid rgba(255,255,255,0.06);padding-top:16px;display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap}';
    echo '.footer a{color:#a78bfa;text-decoration:none}';
    echo '.wa-btn{display:inline-flex;align-items:center;gap:6px;background:#25D366;color:#fff;padding:6px 14px;border-radius:20px;font-weight:600;text-decoration:none;font-size:13px}';
    echo '.wa-btn svg{width:16px;height:16px;fill:currentColor}';
    echo '.qr-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.7);display:none;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(4px)}';
    echo '.qr-overlay.active{display:flex}';
    echo '.qr-box{background:#1e1b4b;border:1px solid rgba(139,92,246,0.3);border-radius:16px;padding:24px;text-align:center;max-width:320px;width:90%}';
    echo '.qr-box img{width:200px;height:200px;border-radius:8px;margin:12px 0}';
    echo '.qr-box p{font-size:13px;color:#94a3b8;margin-bottom:12px}';
    echo '.qr-close{background:linear-gradient(135deg,#8b5cf6,#ec4899);color:#fff;border:none;padding:10px 24px;border-radius:8px;cursor:pointer;font-weight:600}';
    echo '</style></head><body>';
    echo '<div class="card">' . $content;
    echo '<div class="footer">';
    echo 'SAM Traffic Pro';
    echo '<a class="wa-btn" href="https://wa.me/216396022017" target="_blank" rel="noopener">';
    echo '<svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.36-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.3A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
    echo 'WhatsApp Support';
    echo '</a>';
    echo '</div>';
    echo '</div>';
    echo '<div class="qr-overlay" id="qrOverlay" onclick="if(event.target===this)this.classList.remove(\'active\')">';
    echo '<div class="qr-box">';
    echo '<h3 style="color:#c4b5fd;margin-bottom:4px">Scan with your phone</h3>';
    echo '<p>Open WhatsApp and scan this QR code to chat with us.</p>';
    echo '<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://wa.me/216396022017" alt="WhatsApp QR" />';
    echo '<br><button class="qr-close" onclick="document.getElementById(\'qrOverlay\').classList.remove(\'active\')">Close</button>';
    echo '</div></div>';
    echo '</body></html>';
}

// ── No token ──
if ($token === '') {
    header('HTTP/1.1 400 Bad Request');
    htmlPage('Invalid Link',
        '<div class="icon">❌</div>' .
        '<h1>Invalid Link</h1>' .
        '<p>The verification link is missing or malformed. Please go back to the website and request a new trial.</p>' .
        '<a class="btn" href="/">Go to Website</a>'
    );
    exit;
}

try {
    init_db();
    $pdo = db();
} catch (Exception $e) {
    header('HTTP/1.1 503 Service Unavailable');
    htmlPage('Service Unavailable',
        '<div class="icon">⚠️</div>' .
        '<h1>Service Temporarily Unavailable</h1>' .
        '<p>Our trial activation service is experiencing issues. Please try again in a few minutes.</p>' .
        '<a class="btn" href="/">Go to Website</a>'
    );
    exit;
}

$now = gmdate('Y-m-d H:i:s');

// Look up the verification record
$stmt = $pdo->prepare('SELECT * FROM trial_verifications WHERE token = ? LIMIT 1');
$stmt->execute([$token]);
$record = $stmt->fetch();

if (!$record) {
    header('HTTP/1.1 400 Bad Request');
    htmlPage('Invalid Link',
        '<div class="icon">❌</div>' .
        '<h1>Invalid or Used Link</h1>' .
        '<p>This verification link is invalid or has already been used. Please go back to the website and request a new trial.</p>' .
        '<a class="btn" href="/">Go to Website</a>'
    );
    exit;
}

if ($record['verified_at'] !== null) {
    header('HTTP/1.1 400 Bad Request');
    htmlPage('Already Used',
        '<div class="icon">✅</div>' .
        '<h1>Already Activated</h1>' .
        '<p>This verification link has already been used. If you need help, please contact support.</p>' .
        '<a class="btn" href="/">Go to Website</a>'
    );
    exit;
}

if ($record['expires_at'] < $now) {
    header('HTTP/1.1 410 Gone');
    htmlPage('Expired Link',
        '<div class="icon">⏰</div>' .
        '<h1>Link Expired</h1>' .
        '<p>This verification link has expired. Please go back to the website and request a new trial code.</p>' .
        '<a class="btn" href="/">Go to Website</a>'
    );
    exit;
}

$email = $record['email'];
$deviceId = $record['device_id'];
$clientIp = $record['client_ip'];
$countryCode = $record['country_code'] ?? '';

// ── Final safety checks (skip device claim check if no device_id — opened from browser) ──
if ($deviceId !== '') {
    $claimStmt = $pdo->prepare('SELECT * FROM trial_device_claims WHERE device_id = ? LIMIT 1');
    $claimStmt->execute([$deviceId]);
    if ($claimStmt->fetch()) {
        header('HTTP/1.1 403 Forbidden');
        htmlPage('Trial Used',
            '<div class="icon">🚫</div>' .
            '<h1>Trial Already Used</h1>' .
            '<p>This device has already received a free trial. Each device is limited to one trial only.</p>' .
            '<a class="btn" href="/">Go to Website</a>'
        );
        exit;
    }
}

// Fetch trial duration
$trialDurationSetting = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$trialDurationSetting->execute(['trial_duration_hours']);
$trialDurationRow = $trialDurationSetting->fetch();
$trialDurationHours = $trialDurationRow ? (int)($trialDurationRow['setting_value'] ?? 24) : 24;

$trialUnitSetting = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$trialUnitSetting->execute(['trial_duration_unit']);
$trialUnitRow = $trialUnitSetting->fetch();
$trialDurationUnit = $trialUnitRow ? (string)($trialUnitRow['setting_value'] ?? 'hours') : 'hours';

$code = generate_activation_code();
$multiplier = $trialDurationUnit === 'days' ? 86400 : 3600;
$expiresAt = gmdate('Y-m-d H:i:s', time() + ($trialDurationHours * $multiplier));
$fullName = 'Trial - ' . $email;

// ── Create trial user ──
$insertUser = $pdo->prepare('
    INSERT INTO users (
      full_name, email, activation_code, account_type, trial_email, trial_started_at, trial_ends_at,
      status, activation_duration_days, max_devices, expires_at, device_id, country_code, last_ip, activated_at, last_seen_at
    ) VALUES (?, ?, ?, "trial", ?, ?, ?, "active", 1, 1, ?, NULL, ?, ?, NULL, ?)
');
$insertUser->execute([
    $fullName,
    $email,
    $code,
    $email,
    $now,
    $expiresAt,
    $expiresAt,
    $countryCode,
    $clientIp,
    $now
]);
$userId = (int)$pdo->lastInsertId();

// ── Claim device (only if device_id was provided — from app) ──
if ($deviceId !== '') {
    $insertClaim = $pdo->prepare('
        INSERT INTO trial_device_claims (device_id, email, user_id, created_at)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE email = VALUES(email), user_id = VALUES(user_id)
    ');
    $insertClaim->execute([$deviceId, $email, $userId, $now]);
}

// ── Mark verification as used ──
$pdo->prepare('UPDATE trial_verifications SET verified_at = ? WHERE id = ?')
    ->execute([$now, (int)$record['id']]);

// ── Show beautiful success page ──
htmlPage('Trial Activated',
    '<div class="icon">🎉</div>' .
    '<h1>Free Trial Activated!</h1>' .
    '<p>Your trial has been successfully activated. Copy the code below and paste it into the SAM Traffic Pro application.</p>' .
    '<div class="code-box">' . htmlspecialchars($code) . '</div>' .
    '<p><strong>Expires:</strong> ' . htmlspecialchars($expiresAt) . ' UTC<br>' .
    '<strong>Email:</strong> ' . htmlspecialchars($email) . '</p>' .
    '<button class="btn" onclick="navigator.clipboard.writeText(\'' . htmlspecialchars($code) . '\');this.textContent=\'Copied!\'">📋 Copy Code</button><br>' .
    '<a class="btn" href="/" style="margin-top:8px;background:linear-gradient(135deg,#6366f1,#8b5cf6)">Go to Website</a><br>' .
    '<button class="btn" onclick="document.getElementById(\'qrOverlay\').classList.add(\'active\')" style="margin-top:8px;background:linear-gradient(135deg,#25D366,#128C7E)">💬 WhatsApp Support</button>',
    true
);
