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

$all_features = [
    'all_features_unlocked' => [
        'en' => 'All features unlocked',
        'ar' => 'جميع الميزات مفتوحة',
        'fr' => 'Toutes les fonctionnalités débloquées'
    ],
    'full_campaign_builder' => [
        'en' => 'Full campaign builder access',
        'ar' => 'وصول كامل لمنشئ الحملات',
        'fr' => 'Accès complet au créateur de campagnes'
    ],
    'youtube_website_campaigns' => [
        'en' => 'YouTube & website campaigns',
        'ar' => 'حملات يوتيوب ومواقع ويب',
        'fr' => 'Campagnes YouTube & sites web'
    ],
    'adsense_ad_interaction' => [
        'en' => 'AdSense ad interaction',
        'ar' => 'التفاعل مع إعلانات AdSense',
        'fr' => 'Interaction avec les publicités AdSense'
    ],
    'parallel_visits' => [
        'en' => 'Parallel visits (up to 10)',
        'ar' => 'زيارات متوازية (حتى 10)',
        'fr' => 'Visites parallèles (jusqu\'à 10)'
    ],
    'proxy_rotation_health' => [
        'en' => 'Proxy rotation & health checks',
        'ar' => 'تدوير البروكسي وفحص الحالة',
        'fr' => 'Rotation des proxys & tests de santé'
    ],
    'realtime_visit_monitor' => [
        'en' => 'Real-time visit monitor',
        'ar' => 'مراقبة الزيارات في الوقت الفعلي',
        'fr' => 'Moniteur de visites en temps réel'
    ],
    'no_credit_card' => [
        'en' => 'No credit card needed',
        'ar' => 'لا حاجة لبطاقة ائتمان',
        'fr' => 'Aucune carte de crédit requise'
    ],
    'lifetime_license' => [
        'en' => 'Lifetime license',
        'ar' => 'رخصة مدى الحياة',
        'fr' => 'Licence à vie'
    ],
    'free_updates_forever' => [
        'en' => 'Free updates forever',
        'ar' => 'تحديثات مجانية للأبد',
        'fr' => 'Mises à jour gratuites à vie'
    ],
    'search_engine_visits' => [
        'en' => 'Search engine visits',
        'ar' => 'زيارات من محركات البحث',
        'fr' => 'Visites depuis les moteurs de recherche'
    ],
    'trilingual_ui' => [
        'en' => 'Trilingual UI (AR/EN/FR)',
        'ar' => 'واجهة ثلاثية اللغات (عربي/إنجليزي/فرنسي)',
        'fr' => 'Interface trilingue (AR/EN/FR)'
    ],
    'templates_rpa' => [
        'en' => 'Campaign templates & RPA',
        'ar' => 'قوالب الحملات وإجراءات RPA',
        'fr' => 'Modèles de campagnes & actions RPA'
    ],
    'priority_support' => [
        'en' => 'Priority support',
        'ar' => 'دعم فني ذو أولوية',
        'fr' => 'Support prioritaire'
    ],
    'device_transfer' => [
        'en' => 'Device transfer allowed',
        'ar' => 'نقل الرخصة لجهاز آخر متاح',
        'fr' => 'Transfert de licence autorisé'
    ],
    'instant_activation' => [
        'en' => 'Instant activation',
        'ar' => 'تفعيل فوري',
        'fr' => 'Activation instantanée'
    ],
    'secure_payment' => [
        'en' => 'Secure payment',
        'ar' => 'دفع آمن',
        'fr' => 'Paiement sécurisé'
    ],
    'money_back_guarantee' => [
        'en' => '30-Day Money Back Guarantee',
        'ar' => 'ضمان استرجاع الأموال 30 يوماً',
        'fr' => 'Garantie de remboursement de 30 jours'
    ],
    'geotargeting' => [
        'en' => 'Geo-targeting support',
        'ar' => 'دعم الاستهداف الجغرافي',
        'fr' => 'Support du ciblage géographique'
    ],
    'advanced_timing' => [
        'en' => 'Configurable stay duration & delays',
        'ar' => 'تخصيص وقت البقاء وفترات التأخير',
        'fr' => 'Durée de visite & délais configurables'
    ]
];

$trialDurationSetting = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$trialDurationSetting->execute(['trial_duration_hours']);
$trialDurationRow = $trialDurationSetting->fetch();
$trialHours = $trialDurationRow ? (int)($trialDurationRow['setting_value'] ?? 24) : 24;

$trialUnitSetting = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$trialUnitSetting->execute(['trial_duration_unit']);
$trialUnitRow = $trialUnitSetting->fetch();
$trialUnit = $trialUnitRow ? (string)($trialUnitRow['setting_value'] ?? 'hours') : 'hours';

// Fetch active plans ordered by display_order
$stmt = $pdo->prepare('SELECT * FROM plans WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
$stmt->execute();
$plans = $stmt->fetchAll();

$formattedPlans = [];
foreach ($plans as $p) {
    $feature_keys = json_decode((string)$p['features'], true) ?: [];
    $features_list = [];
    foreach ($feature_keys as $fk) {
        if (isset($all_features[$fk])) {
            $features_list[] = [
                'key' => $fk,
                'en' => $all_features[$fk]['en'],
                'ar' => $all_features[$fk]['ar'],
                'fr' => $all_features[$fk]['fr']
            ];
        }
    }
    
    $duration = $p['duration_text'];
    if ($p['plan_type'] === 'trial') {
        $unitText = $trialUnit === 'days' ? 'Days' : 'Hours';
        $duration = "{$trialHours} {$unitText}";
    }

    $formattedPlans[] = [
        'id' => (int)$p['id'],
        'name' => $p['name'],
        'price_regular' => (float)$p['price_regular'],
        'price_discount' => $p['price_discount'] !== null ? (float)$p['price_discount'] : null,
        'is_discount_active' => (int)$p['is_discount_active'] === 1,
        'duration_text' => $duration,
        'plan_type' => $p['plan_type'],
        'features' => $features_list,
        'is_featured' => (int)$p['is_featured'] === 1,
        'whatsapp_text' => $p['whatsapp_text']
    ];
}

echo json_encode([
    'ok' => true,
    'plans' => $formattedPlans
], JSON_UNESCAPED_UNICODE);
