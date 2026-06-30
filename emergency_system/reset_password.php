<?php
require_once 'db.php';
global $conn; // Added to resolve IDE "Undefined variable" warnings
if (isset($_SESSION['user_id'])) { header("Location: user/dashboard.php"); exit(); }

$email = trim($_GET['email'] ?? '');
if (!$email && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
}

$step   = $email ? 'form' : 'invalid'; // invalid | form | success
$error  = '';
$user   = null;

if ($email) {
    $chk_user = mysqli_prepare($conn, "SELECT id, name FROM users WHERE email=? AND status='active'");
    mysqli_stmt_bind_param($chk_user, 's', $email);
    mysqli_stmt_execute($chk_user);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($chk_user));
    if (!$user) {
        $step = 'invalid';
    }
}

// Handle password reset POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'form') {
    verify_csrf();
    $token = trim($_POST['token'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $cpass = $_POST['confirm_password'] ?? '';

    // Validate OTP
    $chk = mysqli_prepare($conn,
        "SELECT * FROM password_reset_tokens 
         WHERE email=? AND token=? AND used=0 AND expires_at > NOW()");
    mysqli_stmt_bind_param($chk, 'ss', $email, $token);
    mysqli_stmt_execute($chk);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));

    if (!$row) {
        $error = 'Invalid or expired OTP.';
    } elseif (strlen($pass) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $pass)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $pass)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/\d/', $pass)) {
        $error = 'Password must contain at least one number.';
    } elseif (!preg_match('/[\W_]/', $pass)) {
        $error = 'Password must contain at least one special character.';
    } elseif ($pass !== $cpass) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);

        // Update password
        $upd = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
        mysqli_stmt_bind_param($upd, 'si', $hashed, $user['id']);
        mysqli_stmt_execute($upd);

        // Mark token as used
        $mark = mysqli_prepare($conn, "UPDATE password_reset_tokens SET used=1 WHERE token=?");
        mysqli_stmt_bind_param($mark, 's', $token);
        mysqli_stmt_execute($mark);

        $step = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password — SERAAS</title>
<link rel="stylesheet" href="assets/css/style.css?v=5.0">
</head>
<body class="login-bg-user">
<div class="login-bg-overlay"></div>

<nav class="navbar">
  <div class="nav-brand">
    <svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M21 12h1"/><path d="M18.5 4.5 18 5"/><path d="M2 12h1"/><path d="M12 2v1"/><path d="m4.929 4.929.707.707"/><path d="M12 12v6"/></svg>
    SERAAS
  </div>
  <div class="nav-links">
    <button id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Toggle Theme" style="padding:6px;margin-right:8px;">
      <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
      <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
    </button>
    <a href="login.php" class="nav-link">← Login</a>
  </div>
</nav>

<div class="form-card glass-card">

<?php if ($step === 'invalid'): ?>
  <div class="form-logo-pro" style="text-align:center;">
    <div class="icon-wrap-pro" style="margin:0 auto 16px;background:linear-gradient(135deg,var(--red),#dc2626);font-size:1.5rem;">⛔</div>
    <h2>Link Expired</h2>
    <p>This password reset link is invalid or has already been used.</p>
  </div>
  <div class="alert alert-error" style="margin-top:16px;">
    <span class="alert-icon">⚠️</span>
    <span>Reset links expire after <strong>1 hour</strong> and can only be used once.</span>
  </div>
  <a href="forgot_password.php" class="btn btn-primary btn-full" style="margin-top:16px;">🔑 Request New Link</a>
  <div class="form-link" style="margin-top:16px;"><a href="login.php">← Back to Login</a></div>

<?php elseif ($step === 'success'): ?>
  <div class="form-logo-pro" style="text-align:center;">
    <div class="icon-wrap-pro" style="margin:0 auto 16px;background:linear-gradient(135deg,var(--green),#15803d);font-size:1.5rem;">✅</div>
    <h2>Password Reset!</h2>
    <p>Your password has been updated successfully.</p>
  </div>
  <div class="alert alert-success" style="margin-top:16px;">
    <span class="alert-icon">🎉</span>
    <span>You can now log in with your new password.</span>
  </div>
  <a href="login.php" class="btn btn-primary btn-full btn-lg" style="margin-top:16px;">
    Log In Now →
  </a>

<?php else: /* form */ ?>
  <div class="form-logo-pro" style="text-align:center;">
    <div class="icon-wrap-pro" style="margin:0 auto 16px;background:linear-gradient(135deg,var(--orange),#ea580c);font-size:1.5rem;">🔐</div>
    <h2>Set New Password</h2>
    <p>Hello <strong><?= htmlspecialchars($user['name']) ?></strong> — choose a strong new password</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-error" data-auto-dismiss="6000">
    <span class="alert-icon">❌</span>
    <span><?= htmlspecialchars($error) ?></span>
  </div>
  <?php endif; ?>

  <!-- Password strength bar -->
  <div style="margin-bottom:16px;">
    <div style="height:4px;background:var(--bg3);border-radius:4px;overflow:hidden;">
      <div id="strengthBar" style="height:100%;width:0%;transition:width .3s,background .3s;border-radius:4px;"></div>
    </div>
    <div id="strengthLabel" style="font-size:.72rem;color:var(--text3);margin-top:4px;text-align:right;"></div>
  </div>

  <form method="POST" action="reset_password.php" id="resetForm">
    <?= csrf_field() ?>
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    <div class="form-group">
      <label>6-Digit OTP</label>
      <div class="input-icon-wrap" style="position:relative;">
        <span class="input-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
        <input type="text" name="token" placeholder="Enter 6-digit OTP" required style="letter-spacing:2px; font-weight:bold;">
      </div>
    </div>
    <div class="form-group">
      <label>New Password <small style="color:var(--text3)">(8+ chars, upper, lower, number, special)</small></label>
      <div class="input-icon-wrap" style="position:relative;">
        <span class="input-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
        <input type="password" name="password" id="newPass"
               placeholder="Create strong password"
               pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$"
               required style="padding-right:44px;">
        <button type="button" class="toggle-password" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;padding:0;" aria-label="Toggle visibility">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
    </div>
    <div class="form-group">
      <label>Confirm New Password</label>
      <div class="input-icon-wrap" style="position:relative;">
        <span class="input-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
        <input type="password" name="confirm_password" id="confPass"
               placeholder="Repeat password" required style="padding-right:44px;">
        <button type="button" class="toggle-password" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;padding:0;" aria-label="Toggle visibility">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div id="matchMsg" style="font-size:.76rem;margin-top:4px;"></div>
    </div>
    <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;background:linear-gradient(135deg,var(--orange),#ea580c);">
      🔐 Reset My Password
    </button>
  </form>
  <div class="form-link" style="margin-top:20px;"><a href="login.php">← Cancel</a></div>
<?php endif; ?>
</div>

<script src="assets/js/app.js?v=2.1"></script>
<script>
// Password strength meter
const passInput = document.getElementById('newPass');
const confInput = document.getElementById('confPass');
const bar       = document.getElementById('strengthBar');
const lbl       = document.getElementById('strengthLabel');
const matchMsg  = document.getElementById('matchMsg');

if (passInput) {
  passInput.addEventListener('input', function() {
    const v = this.value;
    let score = 0;
    if (v.length >= 8)           score++;
    if (/[A-Z]/.test(v))         score++;
    if (/[a-z]/.test(v))         score++;
    if (/\d/.test(v))            score++;
    if (/[\W_]/.test(v))         score++;
    const levels = [
      { w:'0%',   bg:'transparent', t:'' },
      { w:'20%',  bg:'#ef4444',     t:'Very Weak' },
      { w:'40%',  bg:'#f97316',     t:'Weak' },
      { w:'60%',  bg:'#eab308',     t:'Fair' },
      { w:'80%',  bg:'#22c55e',     t:'Strong' },
      { w:'100%', bg:'#10b981',     t:'Very Strong 💪' },
    ];
    const l = levels[score] || levels[0];
    bar.style.width      = l.w;
    bar.style.background = l.bg;
    lbl.textContent      = l.t;
    lbl.style.color      = l.bg;
    checkMatch();
  });
}

if (confInput) {
  confInput.addEventListener('input', checkMatch);
}

function checkMatch() {
  if (!confInput || !passInput || !confInput.value) { matchMsg.textContent = ''; return; }
  if (passInput.value === confInput.value) {
    matchMsg.textContent = '✅ Passwords match';
    matchMsg.style.color = 'var(--green)';
  } else {
    matchMsg.textContent = '❌ Passwords do not match';
    matchMsg.style.color = 'var(--red)';
  }
}

// Block submit if passwords don't match
const form = document.getElementById('resetForm');
if (form) {
  form.addEventListener('submit', function(e) {
    if (passInput.value !== confInput.value) {
      e.preventDefault();
      showToast('Passwords do not match!', 'error');
    }
  });
}
</script>
</body>
</html>
