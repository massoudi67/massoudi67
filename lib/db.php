<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function init_db(): void
{
    $pdo = db();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL DEFAULT '',
            activation_code VARCHAR(255) NOT NULL UNIQUE,
            account_type VARCHAR(50) NOT NULL DEFAULT 'paid',
            trial_email VARCHAR(255) DEFAULT NULL,
            trial_started_at DATETIME DEFAULT NULL,
            trial_ends_at DATETIME DEFAULT NULL,
            device_id VARCHAR(255) DEFAULT NULL,
            country_code VARCHAR(10) DEFAULT NULL,
            last_ip VARCHAR(45) DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            activation_duration_days INT NOT NULL DEFAULT 0,
            max_devices INT NOT NULL DEFAULT 1,
            expires_at DATETIME DEFAULT NULL,
            activated_at DATETIME DEFAULT NULL,
            last_seen_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_activation_code (activation_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_devices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            device_id VARCHAR(255) NOT NULL,
            country_code VARCHAR(10) DEFAULT NULL,
            last_ip VARCHAR(45) DEFAULT NULL,
            first_activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_user_device (user_id, device_id),
            INDEX idx_user_id (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS trial_device_claims (
            id INT AUTO_INCREMENT PRIMARY KEY,
            device_id VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL,
            user_id INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_device_id (device_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS trial_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(255) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL,
            device_id VARCHAR(255) NOT NULL,
            client_ip VARCHAR(45) NOT NULL,
            country_code VARCHAR(10) DEFAULT NULL,
            verified_at TIMESTAMP NULL DEFAULT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_token (token),
            INDEX idx_email (email),
            INDEX idx_device (device_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS app_updates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(50) NOT NULL,
            release_notes TEXT DEFAULT NULL,
            developer_message TEXT DEFAULT NULL,
            update_news TEXT DEFAULT NULL,
            download_mode VARCHAR(50) NOT NULL DEFAULT 'direct',
            direct_url TEXT DEFAULT NULL,
            external_url TEXT DEFAULT NULL,
            uploaded_file_path TEXT DEFAULT NULL,
            uploaded_file_name VARCHAR(255) DEFAULT NULL,
            file_size VARCHAR(50) DEFAULT NULL,
            force_update TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    try {
        $pdo->exec("ALTER TABLE app_updates ADD COLUMN file_size VARCHAR(50) DEFAULT NULL AFTER uploaded_file_name");
    } catch (Exception $e) {}
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS app_news_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) DEFAULT NULL,
            message TEXT NOT NULL,
            message_en TEXT DEFAULT NULL,
            message_fr TEXT DEFAULT NULL,
            frequency VARCHAR(50) NOT NULL DEFAULT 'manual',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    // Soft upgrade for existing tables (added later). Each ALTER wrapped in a
    // try/catch so the migration is idempotent on already-upgraded databases.
    foreach ([
        "ALTER TABLE app_news_messages ADD COLUMN message_en TEXT DEFAULT NULL AFTER message",
        "ALTER TABLE app_news_messages ADD COLUMN message_fr TEXT DEFAULT NULL AFTER message_en",
    ] as $alterSql) {
        try { $pdo->exec($alterSql); } catch (Exception $e) {}
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_news_reads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            device_id VARCHAR(128) NOT NULL,
            message_id INT NOT NULL,
            read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_device_message (device_id, message_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('proxy_url', '')");
$stmt->execute();
$stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('trial_duration_hours', '24')");
$stmt->execute();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ai_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            raw_code TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $stmt = $pdo->prepare("INSERT IGNORE INTO ai_settings (setting_key, setting_value) VALUES ('nvidia_config', '')");
$stmt->execute();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wallet_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            currency VARCHAR(50) NOT NULL,
            network VARCHAR(50) NOT NULL DEFAULT '',
            address VARCHAR(255) NOT NULL,
            display_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_currency_network (currency, network)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $defaultWallets = [
        ['USDT', 'TRC20', 'YOUR_TRC20_ADDRESS'],
        ['USDT', 'BEP20', 'YOUR_BEP20_ADDRESS'],
        ['BTC', '', 'YOUR_BTC_ADDRESS'],
        ['ETH', 'ERC20', 'YOUR_ERC20_ADDRESS'],
    ];
    $insertWallet = $pdo->prepare("INSERT IGNORE INTO wallet_addresses (currency, network, address, display_order, is_active) VALUES (?, ?, ?, ?, 1)");
    foreach ($defaultWallets as $i => $w) {
        $insertWallet->execute([$w[0], $w[1], $w[2], $i]);
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            price_regular DECIMAL(10, 2) NOT NULL,
            price_discount DECIMAL(10, 2) DEFAULT NULL,
            is_discount_active TINYINT(1) NOT NULL DEFAULT 0,
            duration_text VARCHAR(255) NOT NULL DEFAULT 'Lifetime',
            plan_type VARCHAR(50) NOT NULL DEFAULT 'paid',
            features TEXT NOT NULL,
            display_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            whatsapp_text TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    try {
        $plansCount = (int)($pdo->query("SELECT COUNT(*) FROM plans")->fetchColumn() ?? 0);
        if ($plansCount === 0) {
            $pdo->exec("
                INSERT INTO plans (name, price_regular, price_discount, is_discount_active, duration_text, plan_type, features, display_order, is_featured, whatsapp_text) VALUES
                ('Free Trial', 0.00, NULL, 0, '24 Hours', 'trial', '[\"all_features_unlocked\", \"full_campaign_builder\", \"youtube_website_campaigns\", \"adsense_ad_interaction\", \"parallel_visits\", \"proxy_rotation_health\", \"realtime_visit_monitor\", \"no_credit_card\"]', 0, 0, ''),
                ('Lifetime License', 34.00, NULL, 0, 'Lifetime', 'paid', '[\"lifetime_license\", \"free_updates_forever\", \"all_features_unlocked\", \"youtube_website_campaigns\", \"adsense_ad_interaction\", \"parallel_visits\", \"search_engine_visits\", \"trilingual_ui\", \"templates_rpa\", \"priority_support\", \"device_transfer\"]', 1, 1, 'Hi, I want to buy SAM Traffic Pro.')
            ");
        }
    } catch (Exception $e) {}
}

function generate_activation_code(): string
{
    $chunks = [];
    for ($i = 0; $i < 4; $i++) {
        $chunks[] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
    return implode('-', $chunks);
}
