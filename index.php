<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';

init_db();
require_admin();

$pdo = db();

function panel_base_url(): string
{
    $scheme = 'https';
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    $scriptDir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')));
    $scriptDir = trim($scriptDir, '/');
    $basePath = $scriptDir === '' ? '' : '/' . $scriptDir;
    return $scheme . '://' . $host . $basePath;
}

function absolute_url(string $value, string $baseUrl): string
{
    $trimmed = trim($value);
    if ($trimmed === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $trimmed) === 1) {
        return $trimmed;
    }
    return rtrim($baseUrl, '/') . '/' . ltrim($trimmed, '/');
}

function latest_uploaded_update_relative_path(): string
{
    $updatesDir = realpath(__DIR__ . '/uploads/updates');
    if ($updatesDir === false || !is_dir($updatesDir)) {
        return '';
    }
    $files = glob($updatesDir . '/*');
    if (!is_array($files) || count($files) === 0) {
        return '';
    }
    $allowed = ['exe', 'zip', 'msi', 'rar', '7z', 'tar', 'gz'];
    $candidates = [];
    foreach ($files as $filePath) {
        if (!is_string($filePath) || !is_file($filePath)) {
            continue;
        }
        $ext = strtolower((string)pathinfo($filePath, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            continue;
        }
        $mtime = filemtime($filePath);
        $candidates[] = [
            'path' => $filePath,
            'mtime' => is_int($mtime) ? $mtime : 0
        ];
    }
    if (count($candidates) === 0) {
        return '';
    }
    usort($candidates, static function (array $a, array $b): int {
        return (int)$b['mtime'] <=> (int)$a['mtime'];
    });
    $latestPath = (string)($candidates[0]['path'] ?? '');
    if ($latestPath === '') {
        return '';
    }
    return 'uploads/updates/' . basename($latestPath);
}

function country_flag_emoji(string $countryCode): string
{
    $code = strtoupper(trim($countryCode));
    if (preg_match('/^[A-Z]{2}$/', $code) !== 1) {
        return '';
    }
    $base = 127397;
    $chars = preg_split('//u', $code, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($chars) || count($chars) !== 2) {
        return '';
    }
    return mb_chr($base + ord($chars[0]), 'UTF-8') . mb_chr($base + ord($chars[1]), 'UTF-8');
}

function get_country_name_ar(string $code): string
{
    $code = strtoupper(trim($code));
    $countries = [
        'US' => 'الولايات المتحدة',
        'GB' => 'المملكة المتحدة',
        'CA' => 'كندا',
        'FR' => 'فرنسا',
        'DE' => 'ألمانيا',
        'IT' => 'إيطاليا',
        'ES' => 'إسبانيا',
        'RU' => 'روسيا',
        'CN' => 'الصين',
        'JP' => 'اليابان',
        'SA' => 'السعودية',
        'EG' => 'مصر',
        'AE' => 'الإمارات',
        'DZ' => 'الجزائر',
        'MA' => 'المغرب',
        'TN' => 'تونس',
        'LY' => 'ليبيا',
        'SD' => 'السودان',
        'IQ' => 'العراق',
        'SY' => 'سوريا',
        'JO' => 'الأردن',
        'LB' => 'لبنان',
        'PS' => 'فلسطين',
        'KW' => 'الكويت',
        'QA' => 'قطر',
        'OM' => 'عمان',
        'YE' => 'اليمن',
        'BH' => 'البحرين',
        'TR' => 'تركيا',
        'IN' => 'الهند',
        'BR' => 'البرازيل',
        'NL' => 'هولندا',
        'BE' => 'بلجيكا',
        'CH' => 'سويسرا',
        'SE' => 'السويد',
        'NO' => 'النرويج',
        'FI' => 'فنلندا',
        'DK' => 'الدانمارك',
        'PL' => 'بولندا',
        'UA' => 'أوكرانيا',
        'SG' => 'سنغافورة',
        'MY' => 'ماليزيا',
        'ID' => 'إندونيسيا',
        'TH' => 'تايلاند',
        'VN' => 'فيتنام',
        'PH' => 'الفلبين',
        'AU' => 'أستراليا',
        'NZ' => 'نيوزيلندا',
        'ZA' => 'جنوب أفريقيا',
        'NG' => 'نيجيريا',
        'KE' => 'كينيا',
        'MX' => 'المكسيك',
        'AR' => 'الأرجنتين',
        'CO' => 'كولومبيا',
        'PE' => 'بيرو',
        'CL' => 'تشيلي',
        'VE' => 'فنزويلا'
    ];
    return $countries[$code] ?? $code;
}

function get_country_badge(string $countryCode): string
{
    $code = strtoupper(trim($countryCode));
    if ($code === '') {
        return '<span class="country-badge empty">--</span>';
    }
    $emoji = country_flag_emoji($code);
    $name = get_country_name_ar($code);
    return '<span class="country-badge" title="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '">' . $emoji . ' ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span>';
}

function activation_duration_label(array $user): string
{
    $days = (int)($user['activation_duration_days'] ?? 0);
    if ($days <= 0) {
        return 'مدى الحياة';
    }
    return $days . ' يوم';
}

function set_flash(string $message): void
{
    start_admin_session();
    $_SESSION['flash'] = $message;
}

function get_flash(): string
{
    start_admin_session();
    $flash = (string)($_SESSION['flash'] ?? '');
    unset($_SESSION['flash']);
    return $flash;
}

$flash = get_flash();
$page = (string)($_GET['page'] ?? 'home');
$allowedPages = ['home', 'updates', 'trials', 'wallets', 'plans', 'add_proxy'];
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}
$autoDeletedTrialsCount = 0;
$autoDeleteTrialsStmt = $pdo->prepare('
    DELETE FROM users
    WHERE COALESCE(account_type, \'paid\') = \'trial\'
      AND TRIM(COALESCE(trial_ends_at, expires_at, \'\')) <> \'\'
      AND COALESCE(NULLIF(trial_ends_at, \'\'), NULLIF(expires_at, \'\')) <= NOW()
');
$autoDeleteTrialsStmt->execute();
$autoDeletedTrialsCount = max(0, (int)$autoDeleteTrialsStmt->rowCount());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_user') {
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $durationType = (string)($_POST['duration_type'] ?? 'lifetime');
    $durationDays = max(1, (int)($_POST['duration_days'] ?? 3));
    $maxDevices = max(1, (int)($_POST['max_devices'] ?? 1));
    if (!in_array($durationType, ['lifetime', 'days'], true)) {
        $durationType = 'lifetime';
    }
    if ($fullName !== '') {
        $code = generate_activation_code();
        $expiresAt = null;
        if ($durationType === 'days') {
            $expiresAt = gmdate('Y-m-d H:i:s', time() + ($durationDays * 86400));
        }
        $storedDuration = $durationType === 'days' ? $durationDays : 0;
        $stmt = $pdo->prepare('
            INSERT INTO users (full_name, email, activation_code, account_type, status, activation_duration_days, max_devices, expires_at)
            VALUES (?, ?, ?, \'paid\', ?, ?, ?, ?)
        ');
        $stmt->execute([$fullName, '', $code, 'active', $storedDuration, $maxDevices, $expiresAt]);
        set_flash('تم إنشاء المستخدم وكود التفعيل بنجاح');
    } else {
        set_flash('الرجاء إدخال اسم المستخدم');
    }
    header('Location: index.php?page=home');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_status') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('UPDATE users SET status = CASE WHEN status = \'active\' THEN \'inactive\' ELSE \'active\' END WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('تم تحديث حالة المستخدم');
    header('Location: index.php?page=home');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('تم حذف المستخدم نهائياً');
    }
    header('Location: index.php?page=home');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_trial_user') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND COALESCE(account_type, \'paid\') = \'trial\'');
        $stmt->execute([$id]);
        if ((int)$stmt->rowCount() > 0) {
            set_flash('تم حذف الحساب التجريبي نهائياً');
        } else {
            set_flash('الحساب التجريبي غير موجود أو تم حذفه مسبقاً');
        }
    } else {
        set_flash('الحساب التجريبي غير موجود أو تم حذفه مسبقاً');
    }
    header('Location: index.php?page=trials');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_trial_duration') {
    $newDuration = max(1, (int)($_POST['trial_duration_value'] ?? $_POST['trial_duration_hours'] ?? 24));
    $newUnit = (string)($_POST['trial_duration_unit'] ?? 'hours');
    if (!in_array($newUnit, ['hours', 'days'], true)) $newUnit = 'hours';

    $stmt = $pdo->prepare('UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?');
    $stmt->execute([$newDuration, 'trial_duration_hours']);

    $stmtUnitCheck = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE setting_key = ?');
    $stmtUnitCheck->execute(['trial_duration_unit']);
    if ($stmtUnitCheck->fetchColumn() > 0) {
        $stmtUnit = $pdo->prepare('UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?');
        $stmtUnit->execute([$newUnit, 'trial_duration_unit']);
    } else {
        $stmtUnit = $pdo->prepare('INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        $stmtUnit->execute(['trial_duration_unit', $newUnit]);
    }

    $unitLabel = $newUnit === 'days' ? 'يوم' : 'ساعة';
    set_flash('تم تحديث المدة التجريبية إلى ' . $newDuration . ' ' . $unitLabel);
    header('Location: index.php?page=trials');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_expired_trials') {
    $stmt = $pdo->prepare('
        DELETE FROM users
        WHERE COALESCE(account_type, \'paid\') = \'trial\'
          AND TRIM(COALESCE(trial_ends_at, expires_at, \'\')) <> \'\'
          AND COALESCE(NULLIF(trial_ends_at, \'\'), NULLIF(expires_at, \'\')) <= NOW()
    ');
    $stmt->execute();
    $deletedCount = max(0, (int)$stmt->rowCount());
    set_flash($deletedCount > 0
        ? 'تم مسح ' . $deletedCount . ' حساب تجريبي منتهي'
        : 'لا توجد حسابات تجريبية منتهية للمسح حالياً');
    header('Location: index.php?page=trials');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'extend_trial') {
    $id = (int)($_POST['id'] ?? 0);
    $extendHours = max(1, (int)($_POST['extend_hours'] ?? 24));
    if ($id > 0) {
        $stmt = $pdo->prepare('
            UPDATE users 
            SET trial_ends_at = CASE 
                    WHEN trial_ends_at IS NOT NULL AND trial_ends_at > NOW() 
                    THEN DATE_ADD(trial_ends_at, INTERVAL ? HOUR)
                    ELSE DATE_ADD(NOW(), INTERVAL ? HOUR)
                END,
                expires_at = CASE 
                    WHEN expires_at IS NOT NULL AND expires_at > NOW() 
                    THEN DATE_ADD(expires_at, INTERVAL ? HOUR)
                    ELSE DATE_ADD(NOW(), INTERVAL ? HOUR)
                END
            WHERE id = ? AND COALESCE(account_type, \'paid\') = \'trial\'
        ');
        $stmt->execute([$extendHours, $extendHours, $extendHours, $extendHours, $id]);
        if ((int)$stmt->rowCount() > 0) {
            set_flash('تم تمديد المدة التجريبية بنجاح');
        } else {
            set_flash('لم يتم العثور على الحساب التجريبي');
        }
    } else {
        set_flash('معرف الحساب غير صالح');
    }
    header('Location: index.php?page=trials');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_update') {
    $version    = trim((string)($_POST['version']    ?? ''));
    $fileSize   = trim((string)($_POST['file_size']  ?? ''));
    $directUrl  = trim((string)($_POST['direct_url'] ?? ''));
    $releaseNotes = trim((string)($_POST['release_notes'] ?? ''));

    if ($version === '') {
        set_flash('رقم النسخة مطلوب');
        header('Location: index.php?page=updates');
        exit;
    }
    if ($directUrl === '') {
        set_flash('رابط التحميل المباشر مطلوب');
        header('Location: index.php?page=updates');
        exit;
    }

    $pdo->beginTransaction();
    $pdo->exec('UPDATE app_updates SET is_active = 0 WHERE is_active = 1');
    $stmt = $pdo->prepare('
        INSERT INTO app_updates (version, file_size, direct_url, release_notes, download_mode, is_active)
        VALUES (?, ?, ?, ?, \'direct\', 1)
    ');
    $stmt->execute([$version, $fileSize, $directUrl, $releaseNotes]);
    $pdo->commit();
    set_flash('✅ تم نشر التحديث v' . htmlspecialchars($version, ENT_QUOTES, 'UTF-8') . ' بنجاح');
    header('Location: index.php?page=updates');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_news_message') {
    $newsTitle = trim((string)($_POST['news_title'] ?? ''));
    $newsMessage = trim((string)($_POST['news_message'] ?? ''));
    // Multi-language variants (optional). At minimum Arabic `message` is required.
    $newsMessageEn = trim((string)($_POST['news_message_en'] ?? ''));
    $newsMessageFr = trim((string)($_POST['news_message_fr'] ?? ''));
    $newsFrequency = trim((string)($_POST['news_frequency'] ?? 'manual'));
    if (!in_array($newsFrequency, ['manual', 'daily', 'weekly'], true)) {
        $newsFrequency = 'manual';
    }
    if ($newsMessage === '' && $newsMessageEn === '' && $newsMessageFr === '') {
        set_flash('نص الرسالة (عربي / إنجليزي / فرنسي) مطلوب في حقل واحد على الأقل');
    } else {
        // Fallback: if any language is missing, fall back to the Arabic copy
        // so the desktop app always has *something* to display.
        if ($newsMessageEn === '') $newsMessageEn = $newsMessage;
        if ($newsMessageFr === '') $newsMessageFr = $newsMessage;
        $stmt = $pdo->prepare(
            'INSERT INTO app_news_messages (title, message, message_en, message_fr, frequency, is_active, updated_at) VALUES (?, ?, ?, ?, ?, 1, NOW())'
        );
        $stmt->execute([$newsTitle, $newsMessage, $newsMessageEn, $newsMessageFr, $newsFrequency]);
        set_flash('تم إرسال الرسالة الإخبارية بنجاح');
    }
    header('Location: index.php?page=updates');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_news_message') {
    $newsId = (int)($_POST['id'] ?? 0);
    if ($newsId > 0) {
        $stmt = $pdo->prepare('DELETE FROM app_news_messages WHERE id = ?');
        $stmt->execute([$newsId]);
        set_flash('تم حذف الرسالة الإخبارية');
    }
    header('Location: index.php?page=updates');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_wallet') {
    $walletId = (int)($_POST['wallet_id'] ?? 0);
    $currency = strtoupper(trim((string)($_POST['currency'] ?? '')));
    $network = strtoupper(trim((string)($_POST['network'] ?? '')));
    $address = trim((string)($_POST['address'] ?? ''));
    $displayOrder = (int)($_POST['display_order'] ?? 0);
    if ($currency === '' || $address === '') {
        set_flash('العملة والعنوان مطلوبان');
    } else {
        if ($walletId > 0) {
            $stmt = $pdo->prepare('UPDATE wallet_addresses SET currency = ?, network = ?, address = ?, display_order = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$currency, $network, $address, $displayOrder, $walletId]);
            set_flash('تم تحديث المحفظة بنجاح');
        } else {
            $stmt = $pdo->prepare('INSERT INTO wallet_addresses (currency, network, address, display_order, is_active) VALUES (?, ?, ?, ?, 1)');
            $stmt->execute([$currency, $network, $address, $displayOrder]);
            set_flash('تم إضافة المحفظة بنجاح');
        }
    }
    header('Location: index.php?page=wallets');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_wallet') {
    $walletId = (int)($_POST['id'] ?? 0);
    if ($walletId > 0) {
        $stmt = $pdo->prepare('DELETE FROM wallet_addresses WHERE id = ?');
        $stmt->execute([$walletId]);
        set_flash('تم حذف المحفظة');
    }
    header('Location: index.php?page=wallets');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_wallet') {
    $walletId = (int)($_POST['id'] ?? 0);
    if ($walletId > 0) {
        $stmt = $pdo->prepare('UPDATE wallet_addresses SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$walletId]);
        set_flash('تم تحديث حالة المحفظة');
    }
    header('Location: index.php?page=wallets');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_plan') {
    $planId = (int)($_POST['plan_id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? ''));
    $priceRegular = (float)($_POST['price_regular'] ?? 0.0);
    $priceDiscount = trim((string)($_POST['price_discount'] ?? '')) !== '' ? (float)$_POST['price_discount'] : null;
    $isDiscountActive = isset($_POST['is_discount_active']) ? 1 : 0;
    $durationText = trim((string)($_POST['duration_text'] ?? 'Lifetime'));
    $planType = (string)($_POST['plan_type'] ?? 'paid');
    $displayOrder = (int)($_POST['display_order'] ?? 0);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $whatsappText = trim((string)($_POST['whatsapp_text'] ?? ''));
    
    // Read features checkboxes
    $features_checked = [];
    if (isset($_POST['features']) && is_array($_POST['features'])) {
        foreach ($_POST['features'] as $fk => $val) {
            $features_checked[] = $fk;
        }
    }
    $featuresJson = json_encode($features_checked);

    if ($name === '') {
        set_flash('اسم الخطة مطلوب');
    } else {
        if ($planId > 0) {
            $stmt = $pdo->prepare('
                UPDATE plans 
                SET name = ?, price_regular = ?, price_discount = ?, is_discount_active = ?, 
                    duration_text = ?, plan_type = ?, features = ?, display_order = ?, 
                    is_featured = ?, whatsapp_text = ? 
                WHERE id = ?
            ');
            $stmt->execute([
                $name, $priceRegular, $priceDiscount, $isDiscountActive, 
                $durationText, $planType, $featuresJson, $displayOrder, 
                $isFeatured, $whatsappText, $planId
            ]);
            set_flash('تم تحديث الخطة بنجاح');
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO plans (name, price_regular, price_discount, is_discount_active, duration_text, plan_type, features, display_order, is_featured, whatsapp_text, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ');
            $stmt->execute([
                $name, $priceRegular, $priceDiscount, $isDiscountActive, 
                $durationText, $planType, $featuresJson, $displayOrder, 
                $isFeatured, $whatsappText
            ]);
            set_flash('تم إضافة الخطة بنجاح');
        }
    }
    header('Location: index.php?page=plans');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_plan') {
    $planId = (int)($_POST['id'] ?? 0);
    if ($planId > 0) {
        $stmt = $pdo->prepare('DELETE FROM plans WHERE id = ?');
        $stmt->execute([$planId]);
        set_flash('تم حذف الخطة نهائياً');
    }
    header('Location: index.php?page=plans');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_plan_status') {
    $planId = (int)($_POST['id'] ?? 0);
    if ($planId > 0) {
        $stmt = $pdo->prepare('UPDATE plans SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?');
        $stmt->execute([$planId]);
        set_flash('تم تحديث حالة الخطة');
    }
    header('Location: index.php?page=plans');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_plan_featured') {
    $planId = (int)($_POST['id'] ?? 0);
    if ($planId > 0) {
        $stmt = $pdo->prepare('UPDATE plans SET is_featured = CASE WHEN is_featured = 1 THEN 0 ELSE 1 END WHERE id = ?');
        $stmt->execute([$planId]);
        set_flash('تم تحديث حالة الخطة المميزة');
    }
    header('Location: index.php?page=plans');
    exit;
}

// ---- Proxy management handlers ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_proxy_url') {
    $proxyUrl = isset($_POST['proxy_url']) ? trim((string)$_POST['proxy_url']) : '';
    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()');
    $stmt->execute(['proxy_url', $proxyUrl]);
    set_flash('تم حفظ رابط البروكسي');
    header('Location: index.php?page=add_proxy');
    exit;
}


// Auto-backfill empty country codes using saved last_ip (5 at a time)
try {
    $backfillStmt = $pdo->query("
        SELECT id, last_ip FROM users 
        WHERE (country_code IS NULL OR TRIM(country_code) = '' OR TRIM(country_code) = '--') 
          AND last_ip IS NOT NULL AND TRIM(last_ip) <> '' 
          AND last_ip NOT IN ('127.0.0.1', '::1') 
        LIMIT 5
    ");
    $backfillUsers = $backfillStmt->fetchAll();
    foreach ($backfillUsers as $bu) {
        $ip = trim($bu['last_ip']);
        $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
        $res = @file_get_contents("http://ip-api.com/json/" . urlencode($ip), false, $ctx);
        if ($res !== false && $res !== '') {
            $data = json_decode($res, true);
            if (is_array($data) && isset($data['countryCode'])) {
                $cc = strtoupper(trim($data['countryCode']));
                if (preg_match('/^[A-Z]{2}$/', $cc) === 1) {
                    $up = $pdo->prepare("UPDATE users SET country_code = ? WHERE id = ?");
                    $up->execute([$cc, $bu['id']]);
                }
            }
        }
    }
} catch (\Throwable $e) {}

try {
    $backfillDeviceStmt = $pdo->query("
        SELECT id, last_ip FROM user_devices 
        WHERE (country_code IS NULL OR TRIM(country_code) = '' OR TRIM(country_code) = '--') 
          AND last_ip IS NOT NULL AND TRIM(last_ip) <> '' 
          AND last_ip NOT IN ('127.0.0.1', '::1') 
        LIMIT 5
    ");
    $backfillDevices = $backfillDeviceStmt->fetchAll();
    foreach ($backfillDevices as $bd) {
        $ip = trim($bd['last_ip']);
        $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
        $res = @file_get_contents("http://ip-api.com/json/" . urlencode($ip), false, $ctx);
        if ($res !== false && $res !== '') {
            $data = json_decode($res, true);
            if (is_array($data) && isset($data['countryCode'])) {
                $cc = strtoupper(trim($data['countryCode']));
                if (preg_match('/^[A-Z]{2}$/', $cc) === 1) {
                    $up = $pdo->prepare("UPDATE user_devices SET country_code = ? WHERE id = ?");
                    $up->execute([$cc, $bd['id']]);
                }
            }
        }
    }
} catch (\Throwable $e) {}

$paidUsers = $pdo->query('
    SELECT u.*,
           (SELECT COUNT(*) FROM user_devices d WHERE d.user_id = u.id) AS active_devices_count,
           (SELECT d.country_code FROM user_devices d WHERE d.user_id = u.id ORDER BY d.last_seen_at DESC LIMIT 1) AS device_country_code
    FROM users u
    WHERE COALESCE(u.account_type, \'paid\') <> \'trial\'
    ORDER BY u.id DESC
')->fetchAll();
$trialUsers = $pdo->query('
    SELECT u.*,
           (SELECT COUNT(*) FROM user_devices d WHERE d.user_id = u.id) AS active_devices_count,
           (SELECT d.country_code FROM user_devices d WHERE d.user_id = u.id ORDER BY d.last_seen_at DESC LIMIT 1) AS device_country_code
    FROM users u
    WHERE COALESCE(u.account_type, \'paid\') = \'trial\'
      AND u.device_id IS NOT NULL AND u.device_id <> \'\'
    ORDER BY u.id DESC
')->fetchAll();
$latestUpdate = $pdo->query('SELECT * FROM app_updates WHERE is_active = 1 ORDER BY id DESC LIMIT 1')->fetch();
$latestNewsMessages = $pdo->query('SELECT * FROM app_news_messages WHERE is_active = 1 ORDER BY id DESC LIMIT 10')->fetchAll();
$baseUrl = panel_base_url();
$activeDownloadUrl = '';
$latestUploadedFallbackPath = latest_uploaded_update_relative_path();
if (is_array($latestUpdate)) {
    if (($latestUpdate['download_mode'] ?? '') === 'external') {
        $activeDownloadUrl = (string)($latestUpdate['external_url'] ?? '');
        if (trim($activeDownloadUrl) === '' && $latestUploadedFallbackPath !== '') {
            $activeDownloadUrl = absolute_url($latestUploadedFallbackPath, $baseUrl);
        }
    } else {
        $activeDownloadUrl = (string)($latestUpdate['direct_url'] ?? '');
        if ($activeDownloadUrl === '') {
            $activeDownloadUrl = (string)($latestUpdate['uploaded_file_path'] ?? '');
        }
        if ($activeDownloadUrl === '' && $latestUploadedFallbackPath !== '') {
            $activeDownloadUrl = $latestUploadedFallbackPath;
        }
        $activeDownloadUrl = absolute_url($activeDownloadUrl, $baseUrl);
    }
} elseif ($latestUploadedFallbackPath !== '') {
    $activeDownloadUrl = absolute_url($latestUploadedFallbackPath, $baseUrl);
}

$wallets = $pdo->query('
    SELECT id, currency, network, address, display_order, is_active
    FROM wallet_addresses
    ORDER BY display_order ASC, id ASC
')->fetchAll();

$plans = $pdo->query('
    SELECT *
    FROM plans
    ORDER BY display_order ASC, id ASC
')->fetchAll();

$proxyUrlSetting = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$proxyUrlSetting->execute(['proxy_url']);
$proxyUrlRow = $proxyUrlSetting->fetch();
$storedProxyUrl = $proxyUrlRow ? ($proxyUrlRow['setting_value'] ?? '') : '';

$trialDurationSetting = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$trialDurationSetting->execute(['trial_duration_hours']);
$trialDurationRow = $trialDurationSetting->fetch();
$storedTrialDuration = $trialDurationRow ? (int)($trialDurationRow['setting_value'] ?? 24) : 24;

$trialUnitSetting = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$trialUnitSetting->execute(['trial_duration_unit']);
$trialUnitRow = $trialUnitSetting->fetch();
$storedTrialUnit = $trialUnitRow ? (string)($trialUnitRow['setting_value'] ?? 'hours') : 'hours';
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <title>SAM Traffic Pro | لوحة الإدارة</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg-primary: #05050c;
      --bg-secondary: #0c0d19;
      --bg-card: rgba(16, 17, 34, 0.7);
      --bg-card-hover: rgba(22, 23, 44, 0.85);
      --border-color: rgba(255, 255, 255, 0.05);
      --border-glow: rgba(124, 58, 237, 0.3);
      --text-primary: #f8fafc;
      --text-secondary: #94a3b8;
      --text-muted: #475569;
      --accent-purple: #7c3aed;
      --accent-pink: #db2777;
      --accent-cyan: #06b6d4;
      --accent-green: #10b981;
      --accent-red: #ef4444;
      --accent-amber: #f59e0b;
      --shadow-sm: 0 4px 12px rgba(0,0,0,0.4);
      --shadow-lg: 0 16px 40px rgba(0,0,0,0.5);
      --shadow-glow: 0 0 25px rgba(124, 58, 237, 0.15);
      --radius-sm: 12px;
      --radius-md: 16px;
      --radius-lg: 24px;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: var(--bg-primary);
      color: var(--text-primary);
      min-height: 100vh;
      line-height: 1.6;
    }

    .dashboard {
      min-height: 100vh;
      background:
        radial-gradient(circle at 10% 20%, rgba(124, 58, 237, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 90% 80%, rgba(219, 39, 119, 0.05) 0%, transparent 40%),
        var(--bg-primary);
      padding-bottom: 60px;
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(5, 5, 12, 0.8);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border-color);
      padding: 16px 32px;
    }

    .header-inner {
      max-width: 1440px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .logo-icon {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, var(--accent-purple), var(--accent-pink));
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      box-shadow: var(--shadow-glow);
    }

    .logo-text h1 {
      font-size: 18px;
      font-weight: 800;
      background: linear-gradient(135deg, var(--text-primary), #c084fc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .logo-text span {
      font-size: 11px;
      color: var(--text-muted);
      letter-spacing: 0.05em;
    }

    .nav {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .nav a {
      padding: 10px 20px;
      border-radius: var(--radius-sm);
      color: var(--text-secondary);
      text-decoration: none;
      font-weight: 700;
      font-size: 13px;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid transparent;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .nav a:hover {
      background: rgba(255, 255, 255, 0.03);
      color: var(--text-primary);
      border-color: rgba(255, 255, 255, 0.05);
    }

    .nav a.active {
      background: linear-gradient(135deg, rgba(124, 58, 237, 0.15), rgba(219, 39, 119, 0.05));
      border-color: rgba(124, 58, 237, 0.25);
      color: #c084fc;
      box-shadow: 0 4px 15px rgba(124, 58, 237, 0.1);
    }

    .nav a.logout {
      background: rgba(239, 68, 68, 0.08);
      color: #f87171;
      border-color: rgba(239, 68, 68, 0.15);
    }

    .nav a.logout:hover {
      background: rgba(239, 68, 68, 0.15);
      color: #ef4444;
    }

    .main {
      max-width: 1440px;
      margin: 0 auto;
      padding: 32px;
    }

    .flash {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(5, 150, 105, 0.06));
      border: 1px solid rgba(16, 185, 129, 0.2);
      color: #34d399;
      padding: 16px 24px;
      border-radius: var(--radius-md);
      margin-bottom: 28px;
      font-size: 14px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.05);
      animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: var(--shadow-sm);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-4px);
      background: var(--bg-card-hover);
      border-color: rgba(124, 58, 237, 0.2);
      box-shadow: var(--shadow-lg), var(--shadow-glow);
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--accent-purple);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .stat-card:hover::before {
      opacity: 1;
    }

    .stat-card.purple::before { background: var(--accent-purple); }
    .stat-card.pink::before { background: var(--accent-pink); }
    .stat-card.cyan::before { background: var(--accent-cyan); }
    .stat-card.green::before { background: var(--accent-green); }

    .stat-info {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .stat-label {
      font-size: 13px;
      color: var(--text-secondary);
      font-weight: 700;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 800;
      color: var(--text-primary);
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .stat-icon.purple { background: rgba(124, 58, 237, 0.1); color: #c084fc; border-color: rgba(124, 58, 237, 0.15); }
    .stat-icon.pink { background: rgba(219, 39, 119, 0.1); color: #f472b6; border-color: rgba(219, 39, 119, 0.15); }
    .stat-icon.cyan { background: rgba(6, 182, 212, 0.1); color: #22d3ee; border-color: rgba(6, 182, 212, 0.15); }
    .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.15); }

    .grid-container {
      display: grid;
      grid-template-columns: 1fr;
      gap: 32px;
      margin-bottom: 32px;
    }

    @media (min-width: 1024px) {
      .grid-container {
        grid-template-columns: 2fr 1fr;
      }
    }

    .card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: border-color 0.3s ease;
      backdrop-filter: blur(16px);
      margin-bottom: 24px;
    }

    .card:hover {
      border-color: rgba(255, 255, 255, 0.08);
    }

    .card-header {
      padding: 20px 28px;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      background: rgba(255, 255, 255, 0.01);
    }

    .card-title {
      font-size: 15px;
      font-weight: 800;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card-title-icon {
      font-size: 18px;
    }

    .card-body {
      padding: 28px;
    }

    /* Forms */
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    label {
      font-size: 12px;
      font-weight: 700;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    input, select, textarea {
      padding: 12px 16px;
      border-radius: var(--radius-sm);
      border: 1px solid rgba(255, 255, 255, 0.06);
      background: #090a14;
      color: var(--text-primary);
      font-size: 13px;
      font-weight: 600;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      width: 100%;
    }

    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--accent-purple);
      background: #0e101f;
      box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.15);
    }

    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 24px;
      border-radius: var(--radius-sm);
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid transparent;
      white-space: nowrap;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--accent-purple), #6d28d9);
      color: #fff;
      box-shadow: 0 4px 15px rgba(124, 58, 237, 0.2);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(124, 58, 237, 0.3);
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.03);
      border-color: rgba(255, 255, 255, 0.06);
      color: var(--text-primary);
    }

    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.07);
      border-color: rgba(255, 255, 255, 0.1);
    }

    .btn-danger {
      background: rgba(239, 68, 68, 0.1);
      border-color: rgba(239, 68, 68, 0.2);
      color: #f87171;
    }

    .btn-danger:hover {
      background: rgba(239, 68, 68, 0.2);
      border-color: rgba(239, 68, 68, 0.3);
      color: #ef4444;
    }

    /* Modern Tables */
    .table-wrapper {
      overflow-x: auto;
      width: 100%;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
    }

    .table-wrapper::-webkit-scrollbar {
      height: 6px;
    }

    .table-wrapper::-webkit-scrollbar-track {
      background: transparent;
    }

    .table-wrapper::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 3px;
    }

    table {
      width: 100%;
      min-width: 1000px;
      border-collapse: collapse;
      text-align: right;
    }

    th {
      padding: 14px 16px;
      font-size: 11px;
      font-weight: 700;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      background: rgba(255, 255, 255, 0.01);
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    td {
      padding: 14px 16px;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-primary);
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
      white-space: nowrap;
    }

    tr {
      transition: background-color 0.2s ease;
    }

    tr:hover td {
      background: rgba(255, 255, 255, 0.015);
    }

    tr:last-child td {
      border-bottom: none;
    }

    @media (max-width: 768px) {
      th, td {
        padding: 10px 12px;
        font-size: 12px;
      }
    }

    /* Code Block & Click-to-copy */
    .code {
      font-family: 'Courier New', Courier, monospace;
      font-size: 12px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.06);
      padding: 4px 8px;
      border-radius: 6px;
      color: #c084fc;
      font-weight: 700;
      display: inline-block;
    }

    .code.copyable {
      cursor: pointer;
      transition: all 0.25s ease;
      position: relative;
    }

    .code.copyable:hover {
      background: rgba(124, 58, 237, 0.1);
      border-color: rgba(124, 58, 237, 0.4);
      color: #e9d5ff;
      transform: scale(1.03);
    }

    .code.copyable.copied {
      background: rgba(16, 185, 129, 0.15) !important;
      border-color: rgba(16, 185, 129, 0.5) !important;
      color: #34d399 !important;
      animation: popSuccess 0.3s ease;
    }

    @keyframes popSuccess {
      0% { transform: scale(1); }
      50% { transform: scale(1.08); }
      100% { transform: scale(1); }
    }

    /* Badges / Pills */
    .pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 700;
    }

    .pill-ok {
      background: rgba(16, 185, 129, 0.1);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .pill-off {
      background: rgba(239, 68, 68, 0.1);
      color: #f87171;
      border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .muted {
      color: var(--text-muted);
    }

    .actions {
      display: flex;
      gap: 8px;
    }

    .info-box {
      background: rgba(124, 58, 237, 0.03);
      border: 1px solid rgba(124, 58, 237, 0.1);
      border-radius: var(--radius-md);
      padding: 20px;
      color: var(--text-secondary);
      font-size: 13px;
    }

    @media (max-width: 768px) {
      .header-inner {
        flex-direction: column;
        align-items: stretch;
      }
      .nav {
        justify-content: center;
      }
      .actions {
        flex-direction: column;
      }
    }

    /* Country badge improvements */
    .country-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      background: rgba(139, 92, 246, 0.08);
      border: 1px solid rgba(139, 92, 246, 0.15);
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      color: var(--text-primary);
      white-space: nowrap;
    }
    .country-badge.empty {
      background: rgba(148, 163, 184, 0.05);
      border-color: rgba(148, 163, 184, 0.1);
      color: var(--text-muted);
    }

    /* Scrollbars */
    ::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }
    ::-webkit-scrollbar-track {
      background: var(--bg-primary);
    }
    ::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, var(--accent-purple), var(--accent-pink));
      border-radius: 4px;
    }
  </style>
</head>
<body>
<div class="dashboard">
  <header class="header">
    <div class="header-inner">
      <div class="logo">
        <div class="logo-icon">🚀</div>
        <div class="logo-text">
          <h1>SAM Traffic Pro</h1>
          <span>لوحة الإدارة والتحكم</span>
        </div>
      </div>
      <nav class="nav">
        <a href="index.php?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">المستخدمون</a>
        <a href="index.php?page=trials" class="<?= $page === 'trials' ? 'active' : '' ?>">التجارب</a>
        <a href="index.php?page=wallets" class="<?= $page === 'wallets' ? 'active' : '' ?>">المحافظ</a>
        <a href="index.php?page=plans" class="<?= $page === 'plans' ? 'active' : '' ?>">الخطط والأسعار</a>
        <a href="index.php?page=updates" class="<?= $page === 'updates' ? 'active' : '' ?>">التحديثات</a>
        <a href="index.php?page=add_proxy" class="<?= $page === 'add_proxy' ? 'active' : '' ?>">البروكسي</a>
        <a href="logout.php" class="logout">خروج</a>
      </nav>
    </div>
  </header>

  <main class="main">
    <?php if ($flash !== ''): ?>
      <div class="flash">✓ <?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($page === 'home'): ?>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon purple">👥</div>
          <div class="stat-info">
            <h3><?= count($paidUsers) ?></h3>
            <p>المستخدمون المدفوعون</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">✓</div>
          <div class="stat-info">
            <h3><?= count(array_filter($paidUsers, fn($u) => ($u['status'] ?? '') === 'active')) ?></h3>
            <p>المستخدمون النشطون</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cyan">📱</div>
          <div class="stat-info">
            <h3><?= count($trialUsers) ?></h3>
            <p>الحسابات التجريبية</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon amber">⏱️</div>
          <div class="stat-info">
            <h3><?= $autoDeletedTrialsCount ?></h3>
            <p>تم حذفها تلقائياً</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <div class="card-title-icon">➕</div>
            إضافة مستخدم جديد
          </div>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="create_user">
          <div class="form-grid">
            <div class="form-group">
              <label>اسم المستخدم</label>
              <input name="full_name" placeholder="أدخل اسم المستخدم" required>
            </div>
            <div class="form-group">
              <label>نوع الكود</label>
              <select name="duration_type">
                <option value="lifetime">مدى الحياة</option>
                <option value="days">عدد أيام محددة</option>
              </select>
            </div>
            <div class="form-group">
              <label>عدد الأيام</label>
              <input name="duration_days" type="number" min="1" value="3">
            </div>
            <div class="form-group">
              <label>عدد الأجهزة</label>
              <input name="max_devices" type="number" min="1" value="1">
            </div>
            <div class="form-group" style="justify-content: flex-end;">
              <button type="submit" class="btn btn-primary">إنشاء المستخدم</button>
            </div>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <div class="card-title-icon">📋</div>
            قائمة المستخدمين
          </div>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>كود التفعيل</th>
                <th>الحالة</th>
                <th>المدة</th>
                <th>تاريخ الانتهاء</th>
                <th>الأجهزة</th>
                <th>Device ID</th>
                <th>الدولة</th>
                <th>أول تفعيل</th>
                <th>إجراء</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($paidUsers as $u): ?>
                <tr>
                  <td><?= (int)$u['id'] ?></td>
                  <td><?= htmlspecialchars((string)$u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="code copyable" onclick="copyCode(this)"><?= htmlspecialchars((string)$u['activation_code'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td>
                    <span class="pill <?= ($u['status'] === 'active') ? 'pill-ok' : 'pill-off' ?>">
                      <?= ($u['status'] === 'active') ? 'نشط' : 'متوقف' ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars(activation_duration_label($u), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars(trim((string)($u['expires_at'] ?? '')) !== '' ? (string)$u['expires_at'] : '∞', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int)($u['active_devices_count'] ?? 0) ?> / <?= max(1, (int)($u['max_devices'] ?? 1)) ?></td>
                  <td><span class="code" style="font-size:10px;max-width:100px;overflow:hidden;text-overflow:ellipsis;display:block;"><?= htmlspecialchars((string)($u['device_id'] ?? '--'), ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td>
                    <?= get_country_badge((string)($u['device_country_code'] ?? $u['country_code'] ?? '')) ?>
                  </td>
                  <td><?= htmlspecialchars(mb_substr((string)($u['activated_at'] ?? ''), 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <div class="actions">
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                        <button type="submit" class="btn btn-secondary" style="padding:8px 12px;font-size:12px;">
                          <?= ($u['status'] === 'active') ? 'إيقاف' : 'تفعيل' ?>
                        </button>
                      </form>
                      <form method="post" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟');" style="display:inline;">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                        <button type="submit" class="btn btn-danger" style="padding:8px 12px;font-size:12px;">حذف</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (count($paidUsers) === 0): ?>
                <tr>
                  <td colspan="12" class="muted" style="text-align:center;padding:40px;">لا يوجد مستخدمون بعد</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'trials'): ?>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon cyan">⏳</div>
          <div class="stat-info">
            <h3><?= count($trialUsers) ?></h3>
            <p>إجمالي الحسابات التجريبية</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon amber">⏱️</div>
          <div class="stat-info">
            <h3><?= $autoDeletedTrialsCount ?></h3>
            <p>حسابات محذوفة تلقائياً</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="info-box">
          <p>📌 رابط API للتجربة:</p>
          <a href="<?= htmlspecialchars($baseUrl . '/api/trial.php', ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($baseUrl . '/api/trial.php', ENT_QUOTES, 'UTF-8') ?></a>
        </div>
        <div class="card-header">
          <div class="card-title">
            <div class="card-title-icon">🧪</div>
            الحسابات التجريبية (<?= (int)$storedTrialDuration ?> <?= $storedTrialUnit === 'days' ? 'يوم' : 'ساعة' ?>)
          </div>
          <div style="display:flex;align-items:center;gap:16px;">
            <form method="post" style="display:flex;align-items:center;gap:8px;">
              <input type="hidden" name="action" value="save_trial_duration">
              <label style="font-size:12px;white-space:nowrap;">المدة التجريبية:</label>
              <input type="number" name="trial_duration_value" value="<?= (int)$storedTrialDuration ?>" min="1" max="720" style="width:70px;padding:6px 10px;font-size:12px;">
              <select name="trial_duration_unit" style="padding:6px;font-size:12px;width:auto;border-radius:var(--radius-sm);background:#090a14;color:var(--text-primary);border:1px solid rgba(255,255,255,0.06);">
                <option value="hours" <?= $storedTrialUnit === 'hours' ? 'selected' : '' ?>>ساعة</option>
                <option value="days" <?= $storedTrialUnit === 'days' ? 'selected' : '' ?>>يوم</option>
              </select>
              <button type="submit" class="btn btn-primary" style="padding:6px 14px;font-size:12px;">تحديث</button>
            </form>
            <form method="post" onsubmit="return confirm('هل تريد حذف الحسابات المنتهية؟');" style="display:inline;">
              <input type="hidden" name="action" value="delete_expired_trials">
              <button type="submit" class="btn btn-danger" style="padding:6px 14px;font-size:12px;">مسح المنتهية</button>
            </form>
          </div>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>الإيميل</th>
                <th>كود التجربة</th>
                <th>الحالة</th>
                <th>ينتهي في</th>
                <th>الوقت المتبقي</th>
                <th>الأجهزة</th>
                <th>Device ID</th>
                <th>الدولة</th>
                <th>أول تفعيل</th>
                <th>إجراء</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($trialUsers as $u): ?>
                <?php
                  $trialEnd = trim((string)($u['trial_ends_at'] ?? $u['expires_at'] ?? ''));
                  $remainingText = 'منتهية';
                  $isExpired = false;
                  if ($trialEnd !== '' && strtotime($trialEnd) !== false) {
                    $diff = strtotime($trialEnd) - time();
                    if ($diff > 0) {
                      $hours = floor($diff / 3600);
                      $minutes = floor(($diff % 3600) / 60);
                      $seconds = $diff % 60;
                      $remainingText = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    } else {
                      $isExpired = true;
                    }
                  }
                  $statusText = ($u['status'] === 'active') ? ($isExpired ? 'متوقفة' : 'نشط') : 'متوقف';
                ?>
                <tr>
                  <td><?= (int)$u['id'] ?></td>
                  <td><?= htmlspecialchars((string)($u['trial_email'] ?: $u['email']), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="code copyable" onclick="copyCode(this)"><?= htmlspecialchars((string)$u['activation_code'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td>
                    <span class="pill <?= ($u['status'] === 'active' && !$isExpired) ? 'pill-ok' : 'pill-off' ?>">
                      <?= $statusText ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($trialEnd !== '' ? $trialEnd : '-', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="code"><?= htmlspecialchars($remainingText, ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= (int)($u['active_devices_count'] ?? 0) ?> / 1</td>
                  <td><span class="code" style="font-size:10px;max-width:100px;overflow:hidden;text-overflow:ellipsis;display:block;"><?= htmlspecialchars((string)($u['device_id'] ?? '--'), ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td>
                    <?= get_country_badge((string)($u['device_country_code'] ?? $u['country_code'] ?? '')) ?>
                  </td>
                  <td><?= htmlspecialchars(mb_substr((string)($u['activated_at'] ?? ''), 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <div class="actions">
                      <form method="post" style="display:inline-flex;align-items:center;gap:4px;">
                        <input type="hidden" name="action" value="extend_trial">
                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                        <input type="number" name="extend_hours" value="<?= (int)$storedTrialDuration ?>" min="1" max="720" style="width:50px;padding:4px 6px;font-size:11px;" title="عدد الساعات">
                        <button type="submit" class="btn btn-secondary" style="padding:6px 10px;font-size:11px;">تمديد</button>
                      </form>
                      <form method="post" onsubmit="return confirm('هل تريد حذف هذا الحساب؟');" style="display:inline;">
                        <input type="hidden" name="action" value="delete_trial_user">
                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                        <button type="submit" class="btn btn-danger" style="padding:6px 10px;font-size:11px;">حذف</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (count($trialUsers) === 0): ?>
                <tr>
                  <td colspan="12" class="muted" style="text-align:center;padding:40px;">لا توجد حسابات تجريبية بعد</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($page === 'updates'): ?>
      <div style="background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(236,72,153,0.08));border:1px solid rgba(139,92,246,0.2);border-radius:20px;padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div>
          <div style="font-size:12px;font-weight:700;color:var(--accent-purple);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">إدارة التحديثات</div>
          <h2 style="font-size:24px;font-weight:800;margin-bottom:6px;">نشر تحديث جديد</h2>
          <p style="color:var(--text-muted);font-size:14px;">أدخل بيانات النسخة الجديدة وسيتم إعلام المستخدمين تلقائياً</p>
        </div>
        <?php if (is_array($latestUpdate)): ?>
          <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:12px;padding:12px 20px;text-align:center;">
            <div style="font-size:11px;color:var(--accent-green);font-weight:700;margin-bottom:4px;">النسخة النشطة</div>
            <div style="font-size:28px;font-weight:900;color:var(--accent-green);">v<?= htmlspecialchars((string)$latestUpdate['version'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($latestUpdate['file_size'])): ?>
              <div style="font-size:12px;color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars((string)$latestUpdate['file_size'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="grid-2">
        <div class="card" style="border-color:rgba(139,92,246,0.2);">
          <div class="card-header">
            <div class="card-title"><div class="card-title-icon">🚀</div>إعدادات النسخة الجديدة</div>
          </div>
          <form method="post">
            <input type="hidden" name="action" value="save_update">
            <div class="two-col">
              <div class="form-group">
                <label style="color:var(--accent-purple);">📌 رقم الإصدار *</label>
                <input name="version" placeholder="1.3.5" required style="border-color:rgba(139,92,246,0.35);font-size:16px;font-weight:700;letter-spacing:1px;">
              </div>
              <div class="form-group">
                <label style="color:var(--accent-amber);">📦 حجم الملف</label>
                <input name="file_size" placeholder="105 MB" style="border-color:rgba(245,158,11,0.3);">
              </div>
            </div>
            <div class="form-group" style="margin-top:16px;">
              <label style="color:var(--accent-cyan);">🔗 رابط التحميل المباشر *</label>
              <input name="direct_url" required placeholder="https://example.com/SAM-Traffic-Pro-Setup-1.3.5.exe" style="border-color:rgba(34,211,238,0.3);direction:ltr;text-align:left;">
              <span style="font-size:11px;color:var(--text-muted);margin-top:4px;display:block;">رابط مباشر لملف الإعداد (.exe)</span>
            </div>
            <div class="form-group" style="margin-top:16px;">
              <label style="color:var(--text-secondary);">📝 ملاحظات النسخة (اختياري)</label>
              <textarea name="release_notes" placeholder="- تحسينات&#10;- إصلاح أخطاء&#10;- ميزات جديدة" style="min-height:90px;"></textarea>
            </div>
            <div style="margin-top:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
              <button type="submit" class="btn btn-primary" style="font-size:15px;padding:14px 28px;">🚀 نشر التحديث</button>
              <span style="font-size:12px;color:var(--text-muted);">سيتم إلغاء التحديث السابق تلقائياً</span>
            </div>
          </form>
        </div>

        <div class="card" style="border-color:rgba(34,197,94,0.2);">
          <div class="card-header">
            <div class="card-title"><div class="card-title-icon">✅</div>التحديث النشط حالياً</div>
          </div>
          <?php if (is_array($latestUpdate)):
            $upVersion = htmlspecialchars((string)($latestUpdate['version'] ?? ''), ENT_QUOTES, 'UTF-8');
            $upSize    = htmlspecialchars((string)($latestUpdate['file_size'] ?? ''), ENT_QUOTES, 'UTF-8');
            $upDate    = htmlspecialchars((string)($latestUpdate['created_at'] ?? ''), ENT_QUOTES, 'UTF-8');
            $upNotes   = (string)($latestUpdate['release_notes'] ?? '');
            $upUrl     = htmlspecialchars($activeDownloadUrl, ENT_QUOTES, 'UTF-8');
          ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
              <div style="background:linear-gradient(135deg,rgba(139,92,246,0.15),rgba(139,92,246,0.05));border:1px solid rgba(139,92,246,0.25);border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:11px;color:var(--accent-purple);font-weight:700;margin-bottom:6px;">رقم الإصدار</div>
                <div style="font-size:26px;font-weight:900;">v<?= $upVersion ?></div>
              </div>
              <div style="background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(245,158,11,0.05));border:1px solid rgba(245,158,11,0.25);border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:11px;color:var(--accent-amber);font-weight:700;margin-bottom:6px;">حجم الملف</div>
                <div style="font-size:22px;font-weight:800;"><?= $upSize !== '' ? $upSize : '—' ?></div>
              </div>
            </div>
            <div style="background:rgba(34,211,238,0.06);border:1px solid rgba(34,211,238,0.2);border-radius:12px;padding:14px;margin-bottom:12px;">
              <div style="font-size:11px;color:var(--accent-cyan);font-weight:700;margin-bottom:8px;">🔗 رابط التحميل</div>
              <a href="<?= $upUrl ?>" target="_blank" style="font-size:12px;color:var(--accent-cyan);word-break:break-all;direction:ltr;display:block;"><?= $upUrl ?></a>
            </div>
            <div style="background:rgba(34,197,94,0.06);border:1px solid rgba(34,197,94,0.2);border-radius:12px;padding:12px 14px;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
              <span style="font-size:20px;">📅</span>
              <div>
                <div style="font-size:11px;color:var(--accent-green);font-weight:700;">تاريخ النشر</div>
                <div style="font-size:13px;color:var(--text-secondary);"><?= $upDate ?></div>
              </div>
            </div>
            <?php if ($upNotes !== ''): ?>
              <div style="background:rgba(15,23,42,0.6);border:1px solid var(--border-color);border-radius:12px;padding:14px;">
                <div style="font-size:11px;color:var(--text-muted);font-weight:700;margin-bottom:8px;">📝 ملاحظات</div>
                <div style="font-size:13px;color:var(--text-secondary);line-height:1.8;"><?= nl2br(htmlspecialchars($upNotes, ENT_QUOTES, 'UTF-8')) ?></div>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div style="text-align:center;padding:60px 20px;">
              <div style="font-size:52px;margin-bottom:16px;">📦</div>
              <p style="color:var(--text-muted);font-size:15px;">لم يتم نشر أي تحديث بعد</p>
            </div>
          <?php endif; ?>
          <div class="info-box" style="margin-top:16px;">
            <p style="font-size:12px;">🔗 رابط API التحديث:</p>
            <a href="<?= htmlspecialchars($baseUrl . '/api/update.php', ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="font-size:11px;"><?= htmlspecialchars($baseUrl . '/api/update.php', ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        </div>
      </div>

      <div class="grid-2" style="margin-top:20px;">
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <div class="card-title-icon">📢</div>
              إرسال رسالة إخبارية
            </div>
          </div>
          <form method="post">
            <input type="hidden" name="action" value="save_news_message">
            <div class="two-col">
              <div class="form-group">
                <label>عنوان الرسالة (اختياري)</label>
                <input name="news_title" placeholder="عنوان">
              </div>
              <div class="form-group">
                <label>التكرار</label>
                <select name="news_frequency">
                  <option value="manual">مرة واحدة</option>
                  <option value="daily">يومي</option>
                  <option value="weekly">أسبوعي</option>
                </select>
              </div>
            </div>
            <div class="three-col" style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
              <div class="form-group">
                <label>🇸🇦 نص الرسالة بالعربية</label>
                <textarea name="news_message" rows="4" placeholder="اكتب رسالتك هنا..." required></textarea>
              </div>
              <div class="form-group">
                <label>🇬🇧 English version <small style="opacity:.6;">(optional)</small></label>
                <textarea name="news_message_en" rows="4" placeholder="Type your message in English..."></textarea>
              </div>
              <div class="form-group">
                <label>🇫🇷 Version française <small style="opacity:.6;">(optionnel)</small></label>
                <textarea name="news_message_fr" rows="4" placeholder="Tapez votre message en français..."></textarea>
              </div>
            </div>
            <div style="margin-top:12px;font-size:11px;opacity:.7;">
              💡 إذا تُرك حقل الإنجليزي أو الفرنسي فارغاً، سيتم استخدام النص العربي تلقائياً كبديل.
              <br>
              💡 If the English or French field is left empty, the Arabic text is used as a fallback.
            </div>
            <div style="margin-top:16px;">
              <button type="submit" class="btn btn-primary">إرسال</button>
            </div>
          </form>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <div class="card-title-icon">📜</div>
              آخر الرسائل الإخبارية
            </div>
          </div>
          <?php if (is_array($latestNewsMessages) && count($latestNewsMessages) > 0): ?>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>العنوان</th>
                    <th>التكرار</th>
                    <th>التاريخ</th>
                    <th>الرسالة</th>
                    <th>إجراء</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($latestNewsMessages as $news): ?>
                    <tr>
                      <td><?= htmlspecialchars((string)($news['title'] ?? 'بدون عنوان'), ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars((string)($news['frequency'] ?? 'manual'), ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars(mb_substr((string)($news['created_at'] ?? ''), 0, 16), ENT_QUOTES, 'UTF-8') ?></td>
                      <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= nl2br(htmlspecialchars((string)($news['message'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></td>
                      <td>
                        <form method="post" onsubmit="return confirm('حذف هذه الرسالة؟');" style="display:inline;">
                          <input type="hidden" name="action" value="delete_news_message">
                          <input type="hidden" name="id" value="<?= (int)$news['id'] ?>">
                          <button type="submit" class="btn btn-danger" style="padding:6px 10px;font-size:11px;">حذف</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="muted" style="text-align:center;padding:40px;">لا توجد رسائل إخبارية حالياً</p>
          <?php endif; ?>
          </div>
        </div>
      </div>

    <?php elseif ($page === 'add_proxy'): ?>
      <div class="card" style="margin-top:20px;">
        <div class="card-header">
          <div class="card-title">
            <div class="card-title-icon">🔗</div>
            رابط قائمة البروكسي المجانية (للبرنامج)
          </div>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="save_proxy_url">
          <div class="form-group">
            <label>رابط ملف البروكسي (Proxy List URL)</label>
            <input
              name="proxy_url"
              value="<?= htmlspecialchars($storedProxyUrl ?? '', ENT_QUOTES, 'UTF-8') ?>"
              placeholder="https://example.com/proxies.txt"
            >
            <small class="muted">ضع رابط ملف نصي يحتوي على قائمة البروكسيات (IP:PORT في كل سطر). عند النقر على "بروكسي مجاني" في البرنامج، يتم جلب القائمة مباشرة من هذا الرابط دون تخزينها في قاعدة البيانات.</small>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:12px;">
            حفظ الرابط
          </button>
        </form>
      </div>

      <div class="info-box" style="margin-top:20px;">
        <p>📌 كيفية الاستخدام:</p>
        <ul style="margin-right:20px;margin-top:8px;">
          <li>ضع رابط ملف البروكسي (نص عادي، سطر واحد لكل بروكسي بصيغة IP:PORT).</li>
          <li>يتم جلب القائمة مباشرة من الرابط عند استخدام "بروكسي مجاني" في البرنامج — لا حاجة لتخزينها في قاعدة البيانات (لتقليل استهلاك موارد الاستضافة).</li>
          <li>الصيغ المدعومة: IP:PORT — user:pass@IP:PORT — protocol://IP:PORT</li>
        </ul>
      </div>

    <?php elseif ($page === 'wallets'): ?>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon purple">💰</div>
          <div class="stat-info">
            <h3><?= count($wallets) ?></h3>
            <p>إجمالي المحافظ</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">✓</div>
          <div class="stat-info">
            <h3><?= count(array_filter($wallets, fn($w) => ($w['is_active'] ?? 0) == 1)) ?></h3>
            <p>المحافظ النشطة</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <div class="card-title-icon">➕</div>
            إضافة / تعديل محفظة
          </div>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="save_wallet">
          <input type="hidden" name="wallet_id" value="<?= (int)($_GET['edit_wallet'] ?? 0) ?>">
          <?php
          $editWallet = null;
          $editId = (int)($_GET['edit_wallet'] ?? 0);
          if ($editId > 0) {
              foreach ($wallets as $w) {
                  if ((int)$w['id'] === $editId) { $editWallet = $w; break; }
              }
          }
          ?>
          <div class="form-grid">
            <div class="form-group">
              <label>العملة *</label>
              <input name="currency" value="<?= htmlspecialchars((string)($editWallet['currency'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="USDT, BTC, ETH..." required>
            </div>
            <div class="form-group">
              <label>الشبكة</label>
              <input name="network" value="<?= htmlspecialchars((string)($editWallet['network'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="TRC20, BEP20, ERC20...">
            </div>
            <div class="form-group">
              <label>العنوان *</label>
              <input name="address" value="<?= htmlspecialchars((string)($editWallet['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="عنوان المحفظة" required style="direction:ltr;text-align:left;">
            </div>
            <div class="form-group">
              <label>الترتيب</label>
              <input name="display_order" type="number" min="0" value="<?= (int)($editWallet['display_order'] ?? 0) ?>">
            </div>
            <div class="form-group" style="justify-content: flex-end;">
              <button type="submit" class="btn btn-primary"><?= $editWallet ? 'تحديث المحفظة' : 'إضافة محفظة' ?></button>
              <?php if ($editWallet): ?>
                <a href="index.php?page=wallets" class="btn btn-secondary" style="margin-right:8px;text-decoration:none;">إلغاء</a>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <div class="card-title-icon">💳</div>
            قائمة المحافظ
          </div>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>العملة</th>
                <th>الشبكة</th>
                <th>العنوان</th>
                <th>الترتيب</th>
                <th>الحالة</th>
                <th>إجراء</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($wallets as $w): ?>
                <tr>
                  <td><?= (int)$w['id'] ?></td>
                  <td><span class="code"><?= htmlspecialchars((string)$w['currency'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars((string)($w['network'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <span class="code" style="font-size:11px;max-width:200px;overflow:hidden;text-overflow:ellipsis;display:block;direction:ltr;text-align:left;">
                      <?= htmlspecialchars((string)$w['address'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </td>
                  <td><?= (int)$w['display_order'] ?></td>
                  <td>
                    <span class="pill <?= ($w['is_active'] == 1) ? 'pill-ok' : 'pill-off' ?>">
                      <?= ($w['is_active'] == 1) ? 'نشطة' : 'معطلة' ?>
                    </span>
                  </td>
                  <td>
                    <div class="actions">
                      <a href="index.php?page=wallets&edit_wallet=<?= (int)$w['id'] ?>" class="btn btn-secondary" style="padding:6px 12px;font-size:12px;text-decoration:none;">تعديل</a>
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_wallet">
                        <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
                        <button type="submit" class="btn btn-secondary" style="padding:6px 12px;font-size:12px;"><?= ($w['is_active'] == 1) ? 'تعطيل' : 'تفعيل' ?></button>
                      </form>
                      <form method="post" onsubmit="return confirm('هل تريد حذف هذه المحفظة؟');" style="display:inline;">
                        <input type="hidden" name="action" value="delete_wallet">
                        <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
                        <button type="submit" class="btn btn-danger" style="padding:6px 12px;font-size:12px;">حذف</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (count($wallets) === 0): ?>
                <tr>
                  <td colspan="7" class="muted" style="text-align:center;padding:40px;">لا توجد محافظ مضافة بعد</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="info-box" style="margin-top:20px;">
        <p>📌 كيفية الاستخدام:</p>
        <ul style="margin-right:20px;margin-top:8px;">
          <li>أضف عناوين محافظك الرقمية هنا</li>
          <li>يمكنك تعطيل محفظة دون حذفها</li>
          <li>المحافظ النشطة تظهر تلقائياً على الموقع الرسمي عند ضغط زر الدفع</li>
        </ul>
    <?php elseif ($page === 'plans'): ?>
      <?php
        $editId = (int)($_GET['edit_plan'] ?? 0);
        $editPlan = null;
        if ($editId > 0) {
            foreach ($plans as $pl) {
                if ((int)$pl['id'] === $editId) {
                    $editPlan = $pl;
                    break;
                }
            }
        }
        $all_features_list = [
            'all_features_unlocked' => 'جميع الميزات مفتوحة (All features unlocked)',
            'full_campaign_builder' => 'منشئ الحملات الكامل (Full campaign builder access)',
            'youtube_website_campaigns' => 'حملات يوتيوب ومواقع ويب (YouTube & website campaigns)',
            'adsense_ad_interaction' => 'التفاعل مع إعلانات AdSense (AdSense ad interaction)',
            'parallel_visits' => 'زيارات متوازية حتى 10 (Parallel visits up to 10)',
            'proxy_rotation_health' => 'تدوير وفحص البروكسي (Proxy rotation & health checks)',
            'realtime_visit_monitor' => 'مراقبة الزيارات بالوقت الفعلي (Real-time visit monitor)',
            'no_credit_card' => 'لا حاجة لبطاقة ائتمان (No credit card needed)',
            'lifetime_license' => 'رخصة مدى الحياة (Lifetime license)',
            'free_updates_forever' => 'تحديثات مجانية للأبد (Free updates forever)',
            'search_engine_visits' => 'زيارات من محركات البحث (Search engine visits)',
            'trilingual_ui' => 'واجهة ثلاثية اللغات عربي/إنجليزي/فرنسي (Trilingual UI)',
            'templates_rpa' => 'قوالب الحملات وإجراءات RPA (Campaign templates & RPA)',
            'priority_support' => 'دعم فني ذو أولوية (Priority support)',
            'device_transfer' => 'نقل الرخصة لجهاز آخر (Device transfer allowed)',
            'instant_activation' => 'تفعيل فوري (Instant activation)',
            'secure_payment' => 'دفع آمن (Secure payment)',
            'money_back_guarantee' => 'ضمان استرجاع الأموال 30 يوماً (30-Day Money Back Guarantee)',
            'geotargeting' => 'الاستهداف الجغرافي (Geo-targeting support)',
            'advanced_timing' => 'تخصيص مدة البقاء وفترات التأخير (Configurable stay duration & delays)'
        ];
        
        $selected_features = [];
        if ($editPlan) {
            $selected_features = json_decode((string)$editPlan['features'], true) ?: [];
        }
      ?>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon purple">💰</div>
          <div class="stat-info">
            <h3><?= count($plans) ?></h3>
            <p>إجمالي الخطط</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">✓</div>
          <div class="stat-info">
            <h3><?= count(array_filter($plans, fn($p) => ($p['is_active'] ?? 0) == 1)) ?></h3>
            <p>الخطط النشطة</p>
          </div>
        </div>
      </div>

      <div class="card" style="margin-bottom:28px;">
        <div class="card-header">
          <div class="card-title">
            <div class="card-title-icon">🏷️</div>
            <?= $editPlan ? 'تعديل الخطة: ' . htmlspecialchars($editPlan['name']) : 'إضافة خطة جديدة' ?>
          </div>
          <?php if ($editPlan): ?>
            <a href="index.php?page=plans" class="btn btn-secondary" style="padding:6px 12px;font-size:12px;text-decoration:none;">إلغاء التعديل</a>
          <?php endif; ?>
        </div>
        <form method="post" style="padding:20px;display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;">
          <input type="hidden" name="action" value="save_plan">
          <input type="hidden" name="plan_id" value="<?= (int)($editPlan['id'] ?? 0) ?>">

          <div>
            <label style="display:block;margin-bottom:8px;font-weight:700;">اسم الخطة</label>
            <input type="text" name="name" value="<?= htmlspecialchars((string)($editPlan['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="مثال: Lifetime License" required style="width:100%;padding:10px 14px;background:#16162a;border:1px solid var(--border-color);border-radius:8px;color:white;">
          </div>

          <div>
            <label style="display:block;margin-bottom:8px;font-weight:700;">نوع الخطة</label>
            <select name="plan_type" style="width:100%;padding:10px 14px;background:#16162a;border:1px solid var(--border-color);border-radius:8px;color:white;cursor:pointer;">
              <option value="paid" <?= ($editPlan && $editPlan['plan_type'] === 'paid') ? 'selected' : '' ?>>مدفوعة (Paid)</option>
              <option value="trial" <?= ($editPlan && $editPlan['plan_type'] === 'trial') ? 'selected' : '' ?>>تجريبية (Trial)</option>
            </select>
          </div>

          <div>
            <label style="display:block;margin-bottom:8px;font-weight:700;">السعر الأساسي ($)</label>
            <input type="number" step="0.01" min="0" name="price_regular" value="<?= (float)($editPlan['price_regular'] ?? 0) ?>" required style="width:100%;padding:10px 14px;background:#16162a;border:1px solid var(--border-color);border-radius:8px;color:white;">
          </div>

          <div>
            <label style="display:block;margin-bottom:8px;font-weight:700;">سعر التخفيض ($) (اختياري)</label>
            <input type="number" step="0.01" min="0" name="price_discount" value="<?= ($editPlan && $editPlan['price_discount'] !== null) ? (float)$editPlan['price_discount'] : '' ?>" placeholder="اتركه فارغاً إن لم يكن هناك تخفيض" style="width:100%;padding:10px 14px;background:#16162a;border:1px solid var(--border-color);border-radius:8px;color:white;">
          </div>

          <div>
            <label style="display:block;margin-bottom:8px;font-weight:700;">مدة الاشتراك (نص يظهر في البطاقة)</label>
            <input type="text" name="duration_text" value="<?= htmlspecialchars((string)($editPlan['duration_text'] ?? 'Lifetime'), ENT_QUOTES, 'UTF-8') ?>" placeholder="مثال: Lifetime أو 24 Hours" required style="width:100%;padding:10px 14px;background:#16162a;border:1px solid var(--border-color);border-radius:8px;color:white;">
            <small style="color:var(--text-muted);font-size:11px;">ملاحظة: في الخطة التجريبية سيتم استبدال المدة تلقائياً بمدة التجربة المحددة في الإعدادات.</small>
          </div>

          <div>
            <label style="display:block;margin-bottom:8px;font-weight:700;">ترتيب العرض</label>
            <input type="number" min="0" name="display_order" value="<?= (int)($editPlan['display_order'] ?? 0) ?>" required style="width:100%;padding:10px 14px;background:#16162a;border:1px solid var(--border-color);border-radius:8px;color:white;">
          </div>

          <div>
            <label style="display:block;margin-bottom:8px;font-weight:700;">رسالة الشراء للـ WhatsApp (اختياري)</label>
            <input type="text" name="whatsapp_text" value="<?= htmlspecialchars((string)($editPlan['whatsapp_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="الرسالة التلقائية عند طلب الشراء" style="width:100%;padding:10px 14px;background:#16162a;border:1px solid var(--border-color);border-radius:8px;color:white;">
          </div>

          <div style="display:flex;align-items:center;gap:20px;padding-top:20px;">
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:700;">
              <input type="checkbox" name="is_discount_active" value="1" <?= ($editPlan && $editPlan['is_discount_active'] == 1) ? 'checked' : '' ?>>
              تفعيل التخفيض الحالي
            </label>
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:700;">
              <input type="checkbox" name="is_featured" value="1" <?= ($editPlan && $editPlan['is_featured'] == 1) ? 'checked' : '' ?>>
              تمييز الخطة (مميزة - Popular)
            </label>
          </div>

          <div style="grid-column:1/-1;border-top:1px solid var(--border-color);padding-top:16px;">
            <label style="display:block;margin-bottom:12px;font-weight:800;color:var(--accent-purple);">حدد الميزات والخصائص المتوفرة في هذه الخطة:</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:10px;">
              <?php foreach ($all_features_list as $key => $label): ?>
                <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;padding:8px 12px;background:rgba(255,255,255,0.02);border:1px solid var(--border-color);border-radius:6px;font-size:12px;color:var(--text-secondary);transition:all 0.2s;">
                  <input type="checkbox" name="features[<?= htmlspecialchars($key) ?>]" value="1" <?= in_array($key, $selected_features) ? 'checked' : '' ?>>
                  <?= htmlspecialchars($label) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="grid-column:1/-1;text-align:left;padding-top:10px;">
            <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:14px;"><?= $editPlan ? 'حفظ التعديلات' : 'إضافة الخطة' ?></button>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <div class="card-title-icon">📋</div>
            الخطط والأسعار الحالية
          </div>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>الترتيب</th>
                <th>اسم الخطة</th>
                <th>النوع</th>
                <th>السعر الأساسي</th>
                <th>السعر الحالي (بالتخفيض)</th>
                <th>المدة</th>
                <th>مميزة؟</th>
                <th>الحالة</th>
                <th>إجراء</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($plans as $p): ?>
                <?php
                  $hasDiscount = (int)$p['is_discount_active'] === 1 && $p['price_discount'] !== null;
                  $currentPrice = $hasDiscount ? (float)$p['price_discount'] : (float)$p['price_regular'];
                  $typeLabel = $p['plan_type'] === 'trial' ? 'تجريبية (Trial)' : 'مدفوعة (Paid)';
                ?>
                <tr>
                  <td><?= (int)$p['display_order'] ?></td>
                  <td><strong><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                  <td><span class="pill" style="background:rgba(124,58,237,0.06);color:#c084fc;"><?= $typeLabel ?></span></td>
                  <td>$<?= number_format((float)$p['price_regular'], 2) ?></td>
                  <td>
                    <?php if ($hasDiscount): ?>
                      <span style="text-decoration:line-through;color:var(--text-muted);font-size:11px;margin-left:4px;">$<?= number_format((float)$p['price_regular'], 2) ?></span>
                      <strong style="color:var(--accent-green);">$<?= number_format((float)$p['price_discount'], 2) ?></strong>
                      <span class="pill pill-ok" style="font-size:9px;padding:2px 6px;margin-right:4px;">تخفيض نشط</span>
                    <?php else: ?>
                      $<?= number_format((float)$p['price_regular'], 2) ?>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="code">
                      <?php if ($p['plan_type'] === 'trial'): ?>
                        <?= (int)$storedTrialDuration ?> <?= $storedTrialUnit === 'days' ? 'Days' : 'Hours' ?>
                      <?php else: ?>
                        <?= htmlspecialchars($p['duration_text'], ENT_QUOTES, 'UTF-8') ?>
                      <?php endif; ?>
                    </span>
                  </td>
                  <td>
                    <span class="pill <?= ($p['is_featured'] == 1) ? 'pill-ok' : 'pill-off' ?>">
                      <?= ($p['is_featured'] == 1) ? 'نعم (Popular)' : 'لا' ?>
                    </span>
                  </td>
                  <td>
                    <span class="pill <?= ($p['is_active'] == 1) ? 'pill-ok' : 'pill-off' ?>">
                      <?= ($p['is_active'] == 1) ? 'نشطة' : 'معطلة' ?>
                    </span>
                  </td>
                  <td>
                    <div class="actions">
                      <a href="index.php?page=plans&edit_plan=<?= (int)$p['id'] ?>" class="btn btn-secondary" style="padding:6px 12px;font-size:12px;text-decoration:none;">تعديل</a>
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_plan_status">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="btn btn-secondary" style="padding:6px 12px;font-size:12px;"><?= ($p['is_active'] == 1) ? 'تعطيل' : 'تفعيل' ?></button>
                      </form>
                      <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_plan_featured">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="btn btn-secondary" style="padding:6px 12px;font-size:12px;"><?= ($p['is_featured'] == 1) ? 'إلغاء التمييز' : 'تمييز' ?></button>
                      </form>
                      <form method="post" onsubmit="return confirm('هل تريد حذف هذه الخطة؟');" style="display:inline;">
                        <input type="hidden" name="action" value="delete_plan">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="btn btn-danger" style="padding:6px 12px;font-size:12px;">حذف</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (count($plans) === 0): ?>
                <tr>
                  <td colspan="9" class="muted" style="text-align:center;padding:40px;">لا توجد خطط مضافة بعد</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="info-box" style="margin-top:20px;">
        <p>📌 معلومات هامة حول إدارة الخطط:</p>
        <ul style="margin-right:20px;margin-top:8px;line-height:1.7;">
          <li>الخطط المفعلة (نشطة) تظهر تلقائياً في واجهة الموقع الرسمي.</li>
          <li>يمكنك تمييز خطة واحدة كـ <strong>Featured / Popular</strong> لتظهر بتأثير بارز ومختلف على الموقع.</li>
          <li>عند تفعيل التخفيض، سيظهر السعر المشطوب والسعر الجديد بشكل تلقائي وجميل على الموقع الرسمي مع نسب الخصم.</li>
        </ul>
      </div>

    <?php endif; ?>
  </main>
</div>

<script>
function copyCode(element) {
  const text = element.innerText.trim();
  navigator.clipboard.writeText(text).then(() => {
    const originalText = element.innerText;
    element.innerText = '✓ نسخ!';
    element.style.color = '#4ade80';
    setTimeout(() => {
      element.innerText = originalText;
      element.style.color = '';
    }, 1000);
  }).catch(err => {
    console.error('فشل النسخ:', err);
  });
}
</script>

</body>
</html>