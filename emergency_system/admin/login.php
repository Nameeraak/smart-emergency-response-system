<?php
require_once '../db.php';
if (isset($_SESSION['admin_id'])) {
  header("Location: dashboard.php");
  exit();
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $email = $_POST['email'] ?? '';
  $pass = $_POST['password'] ?? '';
  $captcha = trim($_POST['captcha'] ?? '');
  $expected = $_SESSION['login_captcha'] ?? '';
  unset($_SESSION['login_captcha']);
  if ($captcha !== $expected) {
    $error = "Incorrect CAPTCHA answer.";
  } else {
    $st = mysqli_prepare($conn, "SELECT * FROM admin WHERE email=?");
    mysqli_stmt_bind_param($st, 's', $email);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    if ($row && password_verify($pass, $row['password'])) {
      $_SESSION['admin_id'] = $row['id'];
      $_SESSION['admin_name'] = $row['name'];
      header("Location: dashboard.php");
      exit();
    }
    $error = "Invalid admin credentials.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=5.0">
</head>

<body class="login-bg-admin">
  <div class="login-bg-overlay"></div>
  <nav class="navbar">
    <div class="nav-brand"><svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M7 18v-6a5 5 0 1 1 10 0v6" />
        <path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z" />
        <path d="M21 12h1" />
        <path d="M18.5 4.5 18 5" />
        <path d="M2 12h1" />
        <path d="M12 2v1" />
        <path d="m4.929 4.929.707.707" />
        <path d="M12 12v6" />
      </svg> SERAAS</div>
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
      </button><a href="../index.php">← Home</a>
    </div>
  </nav>
  <div class="form-card glass-card">
    <div class="form-logo-pro" style="text-align:center;">
      <div class="icon-wrap-pro" style="margin:0 auto 16px; background: rgba(0,0,0,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.2); background: linear-gradient(135deg,var(--purple),#7c3aed); box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-size: 1.5rem;">🛡️</div>
      <h2>Admin Portal</h2>
      <p>System administration access</p>
    </div>
    <?php if ($error): ?><div class="alert alert-error"><span class="alert-icon">❌</span><span><?= htmlspecialchars($error) ?></span></div><?php endif; ?>
    <form method="POST"><?= csrf_field() ?>
      <div class="form-group">
        <label>Admin Email</label>
        <div class="input-icon-wrap">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="20" height="16" x="2" y="4" rx="2" />
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            </svg></span>
          <input type="email" name="email" placeholder="admin@emergency.com" required autofocus>
        </div>
      </div>
      <div class="form-group">
        <label>Security Check: Type the characters below</label>
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
          <img src="../captcha_image.php" alt="CAPTCHA" style="border-radius:6px; border:1px solid var(--border); box-shadow:0 2px 4px rgba(0,0,0,0.2); cursor:pointer;" onclick="this.src='../captcha_image.php?rand='+Math.random()" title="Click to refresh">
          <span style="font-size:0.8rem; color:var(--text3); cursor:pointer; text-decoration:underline;" onclick="this.previousElementSibling.src='../captcha_image.php?rand='+Math.random()">Refresh Image</span>
        </div>
        <div class="input-icon-wrap">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg></span>
          <input type="text" name="captcha" placeholder="Enter characters exactly" required>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-icon-wrap">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
              <path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg></span>
          <input type="password" name="password" placeholder="Enter password" required>
          <button type="button" class="toggle-password" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;padding:0;outline:none;" aria-label="Toggle password visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
              <circle cx="12" cy="12" r="3" />
            </svg></button>
        </div>
      </div>
      <button type="submit" class="btn btn-purple btn-full btn-lg">🔓 Login as Admin</button>
    </form>
    <div class="form-link"><a href="../login.php">← Back to User Login</a></div>
  </div>
  <script src="../assets/js/app.js?v=2.1"></script>
</body>

</html>
