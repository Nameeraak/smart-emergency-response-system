<?php
require_once 'db.php';
if (isset($_SESSION['user_id'])) { header("Location: user/dashboard.php"); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');
    $expected = $_SESSION['login_captcha'] ?? '';
    unset($_SESSION['login_captcha']);

    if ($captcha !== $expected) {
        $error = "Incorrect CAPTCHA answer.";
    } elseif (empty($email) || empty($pass)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? AND status='active'");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['user_id']    = $row['id'];
            $_SESSION['user_name']  = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_phone'] = $row['phone'];
            header("Location: user/dashboard.php"); exit();
        }
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>User Login — Smart Emergency Response and Accident Assistance System</title>
<link rel="stylesheet" href="assets/css/style.css?v=5.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
    <a href="index.php" class="nav-link">Home</a>
    <a href="register.php" class="btn btn-primary btn-sm">Register Free</a>
  </div>
</nav>

<div class="form-card glass-card">
  <div class="form-logo-pro" style="text-align:center;">
    <div class="icon-wrap-pro" style="margin:0 auto 16px; background: rgba(0,0,0,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.2); background: linear-gradient(135deg,var(--red),#dc2626); box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-size: 1.5rem;">👤</div>
    <h2>Welcome Back</h2>
    <p>Login to your citizen portal</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error" data-auto-dismiss="5000">
      <span class="alert-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg></span>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['msg'])): ?>
    <?php $msgs = ['logged_out'=>['info','You have been logged out.'],'session_expired'=>['warning','Session expired. Please login again.']]; ?>
    <?php if (isset($msgs[$_GET['msg']])): [$t,$m] = $msgs[$_GET['msg']]; ?>
      <div class="alert alert-<?= $t ?>">
        <span class="alert-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg></span>
        <span><?= $m ?></span>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Email Address</label>
      <div class="input-icon-wrap">
        <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>
        <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
      </div>
    </div>
    <div class="form-group">
      <label>Security Check: Type the characters below</label>
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
        <img src="captcha_image.php" alt="CAPTCHA" style="border-radius:6px; border:1px solid var(--border); box-shadow:0 2px 4px rgba(0,0,0,0.2); cursor:pointer;" onclick="this.src='captcha_image.php?rand='+Math.random()" title="Click to refresh">
        <span style="font-size:0.8rem; color:var(--text3); cursor:pointer; text-decoration:underline;" onclick="this.previousElementSibling.src='captcha_image.php?rand='+Math.random()">Refresh Image</span>
      </div>
      <div class="input-icon-wrap">
        <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
        <input type="text" name="captcha" placeholder="Enter characters exactly" required>
      </div>
    </div>
    <div class="form-group">
      <label>Password</label>
      <div class="input-icon-wrap">
        <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
        <input type="password" name="password" placeholder="Your password" required>
            <button type="button" class="toggle-password" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;padding:0;outline:none;" aria-label="Toggle password visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button>
          </div>
      <div style="text-align:right;margin-top:6px;">
        <a href="forgot_password.php" style="font-size:.8rem;color:var(--text3);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--red)'" onmouseout="this.style.color='var(--text3)'">🔑 Forgot password?</a>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:10px;">
      Log In
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </button>
  </form>

  <div class="form-link" style="margin-top:24px;">Don't have an account? <a href="register.php">Register free</a></div>

  <div class="portal-links-wrapper">
    <p class="pl-label">OTHER SECURE PORTALS</p>
    <div class="pl-grid">
      <a href="admin/login.php" class="btn btn-ghost btn-sm pl-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg> Admin
      </a>
      <a href="police/login.php" class="btn btn-ghost btn-sm pl-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Police
      </a>
      <a href="ambulance/login.php" class="btn btn-ghost btn-sm pl-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg> Ambulance
      </a>
      <a href="hospital/login.php" class="btn btn-ghost btn-sm pl-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg> Hospital
      </a>
    </div>
  </div>
</div>

<script src="assets/js/app.js?v=2.1"></script>
</body>
</html>
