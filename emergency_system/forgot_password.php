<?php
require_once 'db.php';
global $conn; // Added to resolve IDE "Undefined variable" warnings
if (isset($_SESSION['user_id'])) {
  header("Location: user/dashboard.php");
  exit();
}

$step    = 'form';   // form | sent | error
$msg     = '';
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $email = trim($_POST['email'] ?? '');

  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $step = 'error';
    $msg  = 'Please enter a valid email address.';
  } else {
    // Check user exists
    $chk = mysqli_prepare($conn, "SELECT id, name FROM users WHERE email=? AND status='active'");
    mysqli_stmt_bind_param($chk, 's', $email);
    mysqli_stmt_execute($chk);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));

    if (!$user) {
      // Email not found — show clear error and redisplay form
      $step = 'error';
      $msg  = 'No active account found with that email address. Please check and try again.';
    } else {
      // Delete old tokens for this email
      $del = mysqli_prepare($conn, "DELETE FROM password_reset_tokens WHERE email=?");
      mysqli_stmt_bind_param($del, 's', $email);
      mysqli_stmt_execute($del);

      // Generate 6-digit OTP
      $token = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);

      $ins = mysqli_prepare(
        $conn,
        "INSERT INTO password_reset_tokens (user_id, email, token, expires_at) VALUES (?,?,?, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
      );
      mysqli_stmt_bind_param($ins, 'iss', $user['id'], $email, $token);

      if (mysqli_stmt_execute($ins)) {
        $step = 'sent';
        $reset_link = 'reset_password.php?email=' . urlencode($email);
      } else {
        $step = 'error';
        $msg  = 'Something went wrong. Please try again.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Forgot Password — SERAAS</title>
  <link rel="stylesheet" href="assets/css/style.css?v=5.0">
</head>

<body class="login-bg-user">
  <div class="login-bg-overlay"></div>

  <nav class="navbar">
    <div class="nav-brand">
      <svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M7 18v-6a5 5 0 1 1 10 0v6" />
        <path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z" />
        <path d="M21 12h1" />
        <path d="M18.5 4.5 18 5" />
        <path d="M2 12h1" />
        <path d="M12 2v1" />
        <path d="m4.929 4.929.707.707" />
        <path d="M12 12v6" />
      </svg>
      SERAAS
    </div>
    <div class="nav-links">
      <button id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Toggle Theme" style="padding:6px;margin-right:8px;">
        <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="4" />
          <path d="M12 2v2" />
          <path d="M12 20v2" />
          <path d="m4.93 4.93 1.41 1.41" />
          <path d="m17.66 17.66 1.41 1.41" />
          <path d="M2 12h2" />
          <path d="M20 12h2" />
          <path d="m6.34 17.66-1.41 1.41" />
          <path d="m19.07 4.93-1.41 1.41" />
        </svg>
        <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
        </svg>
      </button>
      <a href="login.php" class="nav-link">← Back to Login</a>
    </div>
  </nav>

  <div class="form-card glass-card">
    <div class="form-logo-pro" style="text-align:center;">
      <div class="icon-wrap-pro" style="margin:0 auto 16px;background:linear-gradient(135deg,var(--orange),#ea580c);box-shadow:0 4px 15px rgba(0,0,0,0.3);font-size:1.5rem;">🔑</div>
      <h2>Forgot Password</h2>
      <p>Enter your registered email to get a reset link</p>
    </div>

    <?php if ($step === 'error'): ?>
      <div class="alert alert-error" data-auto-dismiss="8000">
        <span class="alert-icon">❌</span>
        <span><?= htmlspecialchars($msg) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($step === 'sent'): ?>
      <div class="alert alert-success" style="flex-direction:column;align-items:flex-start;gap:12px;">
        <div style="display:flex;align-items:center;gap:8px;">
          <span style="font-size:1.4rem;">✅</span>
          <strong>Reset link generated!</strong>
        </div>
        <?php if (isset($token)): ?>
          <p style="font-size:.85rem;color:var(--text2);margin:0;">An OTP has been sent to your email. (Simulated below). This OTP expires in <strong>1 hour</strong>.</p>
          <div style="background:var(--bg3);border:1px solid var(--border);border-radius:8px;padding:12px;width:100%;box-sizing:border-box;word-break:break-all;text-align:center;">
            <div style="font-size:.7rem;color:var(--text3);margin-bottom:6px;font-weight:700;letter-spacing:1px;">YOUR 6-DIGIT OTP</div>
            <div style="color:var(--cyan);font-size:1.5rem;font-family:monospace;font-weight:bold;letter-spacing:4px;"><?= htmlspecialchars($token) ?></div>
          </div>
          <a href="<?= htmlspecialchars($reset_link) ?>" class="btn btn-primary btn-full" style="margin-top:4px;">
            🔐 Enter OTP to Reset
          </a>
        <?php else: ?>
          <p style="font-size:.85rem;color:var(--text2);margin:0;">If that email is registered, a reset link has been generated. Please contact the administrator to get the link.</p>
        <?php endif; ?>
      </div>
      <div class="form-link" style="margin-top:20px;"><a href="login.php">← Back to Login</a></div>

    <?php else: /* form OR error — show form in both cases */ ?>
      <form method="POST" id="forgotForm">
        <?= csrf_field() ?>
        <div class="form-group">
          <label>Registered Email Address</label>
          <div class="input-icon-wrap">
            <span class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="20" height="16" x="2" y="4" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
              </svg>
            </span>
            <input type="email" name="email" id="emailInput"
              placeholder="you@example.com"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              required autofocus
              style="<?= $step === 'error' ? 'border-color:var(--red);' : '' ?>">
          </div>
          <?php if ($step === 'error'): ?>
            <div style="font-size:.78rem;color:var(--red);margin-top:6px;display:flex;align-items:center;gap:4px;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v4" />
                <path d="M12 16h.01" />
              </svg>
              This email is not registered. <a href="register.php" style="color:var(--red);font-weight:700;margin-left:4px;">Register now →</a>
            </div>
          <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:6px;background:linear-gradient(135deg,var(--orange),#ea580c);">
          🔑 Send Reset Link
        </button>
      </form>
      <div class="form-link" style="margin-top:20px;">Remembered it? <a href="login.php">Back to Login</a></div>
    <?php endif; ?>
  </div>

  <script src="assets/js/app.js?v=2.1"></script>
</body>

</html>
