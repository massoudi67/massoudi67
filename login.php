<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';

start_admin_session();
init_db();

if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = $_GET['error'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (login_admin($username, $password)) {
        header('Location: index.php');
        exit;
    }
    $error = 'بيانات الدخول غير صحيحة';
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>تسجيل الدخول | SAM Traffic Pro</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #020617;
      color: #f8fafc;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }

    /* Animated background particles */
    body::before {
      content: '';
      position: fixed;
      top: -25%;
      left: -15%;
      width: 80vw; height: 80vw;
      max-width: 1000px; max-height: 1000px;
      background: radial-gradient(circle, rgba(139,92,246,0.15) 0%, transparent 65%);
      border-radius: 50%;
      z-index: 0;
      animation: float1 12s ease-in-out infinite;
    }
    body::after {
      content: '';
      position: fixed;
      bottom: -25%;
      right: -15%;
      width: 80vw; height: 80vw;
      max-width: 1000px; max-height: 1000px;
      background: radial-gradient(circle, rgba(236,72,153,0.12) 0%, transparent 65%);
      border-radius: 50%;
      z-index: 0;
      animation: float2 15s ease-in-out infinite;
    }

    @keyframes float1 {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(30px, 30px); }
    }
    @keyframes float2 {
      0%, 100% { transform: translate(0, 0); }
      50% { transform: translate(-30px, -20px); }
    }

    /* Extra glow blobs */
    .bg-blob {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      z-index: 0;
      pointer-events: none;
    }
    .bg-blob-1 {
      width: 400px; height: 400px;
      top: 10%; right: 10%;
      background: rgba(59,130,246,0.06);
      animation: float2 18s ease-in-out infinite;
    }
    .bg-blob-2 {
      width: 300px; height: 300px;
      bottom: 20%; left: 20%;
      background: rgba(16,185,129,0.05);
      animation: float1 14s ease-in-out infinite;
    }

    .login-container {
      width: 100%;
      max-width: 430px;
      z-index: 10;
      position: relative;
    }

    /* Card */
    .login-card {
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(32px);
      -webkit-backdrop-filter: blur(32px);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 28px;
      padding: 44px 40px;
      box-shadow:
        0 32px 64px -12px rgba(0,0,0,0.6),
        0 0 0 1px rgba(255,255,255,0.04) inset,
        0 1px 0 rgba(255,255,255,0.08) inset;
      position: relative;
      overflow: hidden;
    }

    /* Shimmer top line */
    .login-card::before {
      content: '';
      position: absolute;
      top: 0; left: 10%; right: 10%;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(139,92,246,0.6), rgba(236,72,153,0.4), transparent);
    }

    .login-header {
      text-align: center;
      margin-bottom: 36px;
    }

    @keyframes logoPulse {
      0%, 100% {
        box-shadow: 0 0 20px rgba(139,92,246,0.5), 0 8px 25px rgba(139,92,246,0.3);
      }
      50% {
        box-shadow: 0 0 35px rgba(236,72,153,0.6), 0 8px 35px rgba(236,72,153,0.3);
      }
    }

    .login-logo {
      width: 80px; height: 80px;
      background: linear-gradient(135deg, #8b5cf6, #ec4899);
      border-radius: 24px;
      display: flex; align-items: center; justify-content: center;
      font-size: 36px;
      margin: 0 auto 20px;
      animation: logoPulse 3s infinite;
      border: 1px solid rgba(255,255,255,0.2);
    }

    .login-header h1 {
      font-size: 28px;
      font-weight: 900;
      margin-bottom: 8px;
      background: linear-gradient(135deg, #ffffff, #c4b5fd, #f0abfc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: -0.8px;
    }

    .login-header p {
      color: #64748b;
      font-size: 15px;
      font-weight: 500;
    }

    .form-group { margin-bottom: 22px; }

    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 700;
      color: #94a3b8;
      margin-bottom: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .input-wrapper { position: relative; }

    .input-icon {
      position: absolute;
      top: 50%; transform: translateY(-50%);
      right: 16px;
      font-size: 18px;
      pointer-events: none;
      opacity: 0.5;
    }

    .input-wrapper input {
      width: 100%;
      padding: 15px 48px 15px 16px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.1);
      background: rgba(2, 6, 23, 0.7);
      color: #f8fafc;
      font-size: 15px;
      font-family: inherit;
      transition: all 0.3s ease;
    }

    .input-wrapper input:focus {
      outline: none;
      border-color: rgba(139,92,246,0.6);
      background: rgba(2, 6, 23, 0.9);
      box-shadow: 0 0 0 3px rgba(139,92,246,0.18), 0 4px 20px rgba(0,0,0,0.3);
    }

    .input-wrapper input::placeholder { color: #475569; }

    .btn-login {
      width: 100%;
      padding: 16px 20px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.12);
      background: linear-gradient(135deg, #8b5cf6, #d946ef, #ec4899);
      color: white;
      font-size: 16px;
      font-weight: 800;
      font-family: inherit;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 8px 24px rgba(139,92,246,0.35);
      letter-spacing: 0.3px;
    }

    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 14px 32px rgba(139,92,246,0.45);
      filter: brightness(1.08);
    }

    .btn-login:active { transform: translateY(0); }

    .error-message {
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.25);
      color: #fca5a5;
      padding: 14px 18px;
      border-radius: 14px;
      margin-bottom: 24px;
      font-size: 14px;
      font-weight: 600;
      display: flex; align-items: center; gap: 10px;
    }
    .error-message::before { content: '⚠️'; font-size: 16px; }

    .login-footer {
      text-align: center;
      margin-top: 28px;
      padding-top: 22px;
      border-top: 1px solid rgba(255,255,255,0.05);
    }

    .login-footer p { color: #475569; font-size: 13px; font-weight: 500; }

    /* Security indicators */
    .security-badges {
      display: flex; justify-content: center; gap: 16px;
      margin-top: 20px;
    }
    .security-badge {
      display: flex; align-items: center; gap: 6px;
      font-size: 12px; color: #334155; font-weight: 600;
    }

    @media (max-width: 480px) {
      .login-card { padding: 32px 24px; border-radius: 22px; }
      .login-logo { width: 68px; height: 68px; font-size: 30px; }
      .login-header h1 { font-size: 24px; }
    }
  </style>
</head>
<body>
  <div class="bg-blob bg-blob-1"></div>
  <div class="bg-blob bg-blob-2"></div>

  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">🚀</div>
        <h1>SAM Traffic Pro</h1>
        <p>لوحة التحكم والإدارة</p>
      </div>

      <?php if ($error !== ''): ?>
        <div class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="on">
        <div class="form-group">
          <label for="username">اسم المستخدم</label>
          <div class="input-wrapper">
            <span class="input-icon">👤</span>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="أدخل اسم المستخدم"
              required
              autocomplete="username"
            >
          </div>
        </div>

        <div class="form-group">
          <label for="password">كلمة المرور</label>
          <div class="input-wrapper">
            <span class="input-icon">🔑</span>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="أدخل كلمة المرور"
              required
              autocomplete="current-password"
            >
          </div>
        </div>

        <button type="submit" class="btn-login">🔐 تسجيل الدخول</button>
      </form>

      <div class="login-footer">
        <div class="security-badges">
          <div class="security-badge">🔒 اتصال آمن</div>
          <div class="security-badge">🛡️ محمي بالتشفير</div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
