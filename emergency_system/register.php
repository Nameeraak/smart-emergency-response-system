<?php
require_once 'db.php';
if (isset($_SESSION['user_id'])) { header("Location: user/dashboard.php"); exit(); }

$errors      = [];
$field_errors = []; // tracks which fields are in error for red-border highlighting
$success     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $blood   = trim($_POST['blood_group'] ?? '');
    $ec      = trim($_POST['emergency_contact'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $cpass   = $_POST['confirm_password'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');
    $expected = $_SESSION['login_captcha'] ?? '';
    // Unset captcha after getting it to prevent reuse
    unset($_SESSION['login_captcha']);

    if ($captcha !== $expected) {
        $errors[] = "Incorrect CAPTCHA answer.";
    }

    // ── Format Validations ───────────────────────────────────────
    if (empty($name) || !preg_match("/^[a-zA-Z\s]{3,50}$/", $name)) {
        $errors[] = "Please enter a valid full name (letters only, min 3 chars).";
        $field_errors['name'] = true;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
        $field_errors['email'] = true;
    }
    if (empty($phone) || !preg_match("/^[6-9]\d{9}$/", $phone)) {
        $errors[] = "Please enter a valid 10-digit mobile number.";
        $field_errors['phone'] = true;
    }

    // ── Password Complexity ──────────────────────────────────────
    if (strlen($pass) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
        $field_errors['password'] = true;
    } elseif (!preg_match("/[A-Z]/", $pass)) {
        $errors[] = "Password must contain at least one uppercase letter.";
        $field_errors['password'] = true;
    } elseif (!preg_match("/[a-z]/", $pass)) {
        $errors[] = "Password must contain at least one lowercase letter.";
        $field_errors['password'] = true;
    } elseif (!preg_match("/\d/", $pass)) {
        $errors[] = "Password must contain at least one number.";
        $field_errors['password'] = true;
    } elseif (!preg_match("/[\W_]/", $pass)) {
        $errors[] = "Password must contain at least one special character.";
        $field_errors['password'] = true;
    }

    if ($pass !== $cpass) {
        $errors[] = "Passwords do not match.";
        $field_errors['confirm_password'] = true;
    }

    // ── Duplicate Check (name + email together) ──────────────────
    if (empty($errors)) {
        // Check both name and email in one query
        $dup = mysqli_prepare($conn,
            "SELECT
               SUM(CASE WHEN LOWER(name)=LOWER(?) THEN 1 ELSE 0 END) AS name_count,
               SUM(CASE WHEN LOWER(email)=LOWER(?) THEN 1 ELSE 0 END) AS email_count
             FROM users");
        mysqli_stmt_bind_param($dup, 'ss', $name, $email);
        mysqli_stmt_execute($dup);
        $dup_row = mysqli_fetch_assoc(mysqli_stmt_get_result($dup));

        if ($dup_row['name_count'] > 0) {
            $errors[] = "This username \"" . htmlspecialchars($name) . "\" is already taken. Please choose a different name.";
            $field_errors['name'] = true;
        }
        if ($dup_row['email_count'] > 0) {
            $errors[] = "This email address is already registered. <a href=\"login.php\" style=\"color:#f87171;font-weight:700;\">Login instead?</a> or <a href=\"forgot_password.php\" style=\"color:#f87171;font-weight:700;\">Reset password?</a>";
            $field_errors['email'] = true;
        }

        // ── Insert if still no errors ────────────────────────────
        if (empty($errors)) {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            try {
                $ins = mysqli_prepare($conn,
                    "INSERT INTO users (name,email,phone,address,blood_group,emergency_contact,password)
                     VALUES (?,?,?,?,?,?,?)");
                mysqli_stmt_bind_param($ins, 'sssssss', $name, $email, $phone, $address, $blood, $ec, $hashed);
                if (mysqli_stmt_execute($ins)) {
                    $success = "Account created! You can now login.";
                } else {
                    $errors[] = "Registration failed. Please try again.";
                }
            } catch (mysqli_sql_exception $e) {
                // Catch duplicate key exceptions (Code 1062)
                if ($e->getCode() == 1062) {
                    $errors[] = "This email or phone number is already registered.";
                } else {
                    $errors[] = "Database error: " . $e->getMessage();
                }
            } catch (Exception $e) {
                $errors[] = "An unexpected error occurred: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register — Smart Emergency Response and Accident Assistance System</title>
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
    <a href="login.php" class="btn btn-ghost btn-sm">Log In</a>
  </div>
</nav>

<div class="form-card form-card-wide" style="margin:40px auto;">
  <div class="form-logo-pro" style="text-align:center;">
    <div class="icon-wrap-pro" style="margin:0 auto 16px; background: rgba(0,0,0,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.2); background: linear-gradient(135deg,var(--red),#dc2626); box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-size: 1.5rem;">👤</div>
    <h2>Create Account</h2>
    <p>Register to send emergency alerts instantly</p>
  </div>

  <?php if ($errors): ?>
    <div class="alert alert-error" data-auto-dismiss="8000">
      <span class="alert-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg></span>
      <div><?= implode('<br>', $errors) ?></div>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success">
      <span class="alert-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg></span>
      <div><?= htmlspecialchars($success) ?> <a href="login.php" style="color:#4ade80;font-weight:700;margin-left:8px;">Login now →</a></div>
    </div>
  <?php endif; ?>

  <form method="POST">
    <?= csrf_field() ?>
    <div class="form-grid-2">
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="name" placeholder="e.g. Rahul Sharma"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
               pattern="^[a-zA-Z\s]{3,50}$"
               title="Please enter a valid full name (letters only, min 3 chars)."
               required
               style="<?= !empty($field_errors['name']) ? 'border-color:var(--red);' : '' ?>">
        <?php if (!empty($field_errors['name'])): ?>
        <div style="font-size:.76rem;color:var(--red);margin-top:5px;display:flex;align-items:center;gap:4px;">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
          Username already taken — try a different name
        </div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" placeholder="you@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required
               style="<?= !empty($field_errors['email']) ? 'border-color:var(--red);' : '' ?>">
        <?php if (!empty($field_errors['email'])): ?>
        <div style="font-size:.76rem;color:var(--red);margin-top:5px;display:flex;align-items:center;gap:4px;">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
          Email already registered — <a href="login.php" style="color:var(--red);font-weight:700;margin-left:3px;">Login?</a>&nbsp;or&nbsp;<a href="forgot_password.php" style="color:var(--red);font-weight:700;">Reset password?</a>
        </div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Phone Number *</label>
        <input type="tel" name="phone" placeholder="9876543210"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
               pattern="^[6-9]\d{9}$"
               title="Please enter a valid 10-digit mobile number starting with 6-9."
               required
               style="<?= !empty($field_errors['phone']) ? 'border-color:var(--red);' : '' ?>">
      </div>
      <div class="form-group">
        <label>Emergency Contact</label>
        <input type="tel" name="emergency_contact" placeholder="Alternate phone number" value="<?= htmlspecialchars($_POST['emergency_contact'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Blood Group</label>
        <select name="blood_group">
          <option value="">Select blood group</option>
          <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
            <option value="<?= $bg ?>" <?= ($_POST['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Home Address</label>
        <input type="text" name="address" placeholder="Your address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password * <small style="color:var(--text3)">(8+ chars, upper, lower, num, special)</small></label>
        <div class="input-icon-wrap" style="position:relative;">
          <input type="password" name="password" placeholder="Create a strong password" pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$" title="Must be at least 8 chars, containing uppercase, lowercase, number, and special character." required style="padding-left:14px; padding-right:40px;">
          <button type="button" class="toggle-password" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;padding:0;outline:none;" aria-label="Toggle password visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button>
        </div>
          </div>
      <div class="form-group">
        <label>Confirm Password *</label>
        <div class="input-icon-wrap" style="position:relative;">
          <input type="password" name="confirm_password" placeholder="Repeat password" required style="padding-left:14px; padding-right:40px;">
          <button type="button" class="toggle-password" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;padding:0;outline:none;" aria-label="Toggle password visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button>
        </div>
          </div>
      <div class="form-group">
        <label>Security Check: Type the characters below *</label>
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
          <img src="captcha_image.php" alt="CAPTCHA" style="border-radius:6px; border:1px solid var(--border); box-shadow:0 2px 4px rgba(0,0,0,0.2); cursor:pointer;" onclick="this.src='captcha_image.php?rand='+Math.random()" title="Click to refresh">
          <span style="font-size:0.8rem; color:var(--text3); cursor:pointer; text-decoration:underline;" onclick="this.previousElementSibling.src='captcha_image.php?rand='+Math.random()">Refresh Image</span>
        </div>
        <div class="input-icon-wrap">
          <span class="input-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
          <input type="text" name="captcha" placeholder="Enter characters exactly" required>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:12px;">
      Create My Account
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </button>
  </form>

  <div class="form-link" style="margin-top:24px;">Already have an account? <a href="login.php">Login here</a></div>

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
<script>
document.addEventListener("DOMContentLoaded", function() {
    const emailInput = document.querySelector('input[name="email"]');
    const phoneInput = document.querySelector('input[name="phone"]');

    function checkExists(field, value, element) {
        if (!value) return;
        fetch('check_exists.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ field: field, value: value })
        })
        .then(res => res.json())
        .then(data => {
            let errorMsg = element.nextElementSibling;
            if (data.exists) {
                element.style.borderColor = 'var(--red)';
                if (!errorMsg || !errorMsg.classList.contains('ajax-error')) {
                    errorMsg = document.createElement('div');
                    errorMsg.classList.add('ajax-error');
                    errorMsg.style.css?v=5.0Text = 'font-size:.76rem;color:var(--red);margin-top:5px;display:flex;align-items:center;gap:4px;';
                    errorMsg.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg> This ${field} is already registered.`;
                    element.parentNode.insertBefore(errorMsg, element.nextSibling);
                }
            } else {
                element.style.borderColor = '';
                if (errorMsg && errorMsg.classList.contains('ajax-error')) {
                    errorMsg.remove();
                }
            }
        }).catch(err => console.error(err));
    }

    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            checkExists('email', this.value, this);
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            checkExists('phone', this.value, this);
        });
    }
});
</script>
</body>
</html>
