<?php
require_once '../db.php';
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php?msg=session_expired");
  exit();
}

$uid   = (int)$_SESSION['user_id'];
$uname = $_SESSION['user_name'];
$uphone = $_SESSION['user_phone'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $services = $_POST['service_type'] ?? [];
  if (!is_array($services)) $services = [$services];
  $loc_txt  = trim($_POST['location_text'] ?? '');
  $lat      = floatval($_POST['latitude'] ?? 0);
  $lng      = floatval($_POST['longitude'] ?? 0);
  $desc     = trim($_POST['description'] ?? '');
  $severity = $_POST['severity'] ?? 'High';

  $allowed_svc = ['Police', 'Ambulance', 'Hospital'];
  $allowed_sev = ['Low', 'Medium', 'High', 'Critical'];

  // Filter to only valid services
  $valid_services = array_filter($services, fn($s) => in_array($s, $allowed_svc));

  if (empty($valid_services))            $error = "Please select at least one emergency service.";
  elseif (!in_array($severity, $allowed_sev)) $error = "Invalid severity.";
  elseif (empty($loc_txt) && ($lat == 0 || $lng == 0)) $error = "Please capture GPS or enter your location.";
  else {
    $all_ok = true;
    foreach ($valid_services as $service) {
      $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO emergency_requests
                 (user_id,user_name,user_phone,service_type,location_text,latitude,longitude,description,severity,status)
                 VALUES (?,?,?,?,?,?,?,?,?,'Pending')"
      );
      mysqli_stmt_bind_param(
        $stmt,
        'issssddss',
        $uid,
        $uname,
        $uphone,
        $service,
        $loc_txt,
        $lat,
        $lng,
        $desc,
        $severity
      );
      if (!mysqli_stmt_execute($stmt)) {
        $all_ok = false;
      }
    }
    if ($all_ok) {
      header("Location: dashboard.php?msg=sent");
      exit();
    } else {
      $error = "Database error. Please try again.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Send Emergency Alert — Smart Emergency Response and Accident Assistance System</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=5.0">
</head>

<body class="login-bg-user">
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
      </button>
      <span style="color:var(--text3);font-size:.85rem;">👤 <?= htmlspecialchars($uname) ?></span>
      <a href="../logout.php" class="btn btn-ghost btn-sm">Logout</a>
    </div>
  </nav>

  <div class="layout">
    <div class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-avatar">👤</div>
        <div class="sidebar-name"><?= htmlspecialchars($uname) ?></div>
        <div class="sidebar-role">Emergency User</div>
      </div>
      <div class="sidebar-nav">
        <div class="nav-section-label">Menu</div>
        <a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
        <a href="send_request.php" class="active"><span class="nav-icon">🆘</span> Send Emergency</a>
        <a href="my_requests.php"><span class="nav-icon">📋</span> My Requests</a>
      </div>
      <div class="sidebar-footer">
        <a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a>
      </div>
    </div>

    <div class="main-content">
      <div class="page-header">
        <div class="page-header-left">
          <h1>🆘 Send Emergency Alert</h1>
          <p>Fill the form below — your GPS location will be shared with responders.</p>
        </div>
        <a href="dashboard.php" class="btn btn-ghost">← Back</a>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><span class="alert-icon">❌</span><span><?= htmlspecialchars($error) ?></span></div>
      <?php endif; ?>

      <!-- CRITICAL NOTICE -->
      <div class="alert alert-error" style="margin-bottom:20px;">
        <span class="alert-icon">🚨</span>
        <div><strong>In a life-threatening emergency:</strong> Also call 112 (National Emergency) or 108 (Ambulance) immediately.</div>
      </div>

      <div class="card">
        <form method="POST" id="emergencyForm">
          <?= csrf_field() ?>
          <input type="hidden" name="latitude" id="lat_field" value="0">
          <input type="hidden" name="longitude" id="lng_field" value="0">

          <!-- STEP 1: SERVICE (Multi-Select) -->
          <div style="margin-bottom:24px;">
            <p style="font-size:.8rem;font-weight:700;color:var(--text3);letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;">Step 1 — Choose Emergency Services *</p>
            <p style="font-size:.75rem;color:var(--text3);margin-bottom:12px;">You can select multiple services if needed</p>
            <div class="service-grid">
              <div class="service-opt">
                <input type="checkbox" name="service_type[]" id="svc_police" value="Police" <?= in_array('Police', $_POST['service_type'] ?? []) ? 'checked' : '' ?>>
                <label for="svc_police">
                  <span class="svc-icon">🚔</span>
                  <span class="svc-name">Police</span>
                  <span class="svc-desc">Accident · Crime · Threat</span>
                </label>
              </div>
              <div class="service-opt">
                <input type="checkbox" name="service_type[]" id="svc_amb" value="Ambulance" <?= in_array('Ambulance', $_POST['service_type'] ?? []) ? 'checked' : '' ?>>
                <label for="svc_amb">
                  <span class="svc-icon">🚑</span>
                  <span class="svc-name">Ambulance</span>
                  <span class="svc-desc">Medical · Injury · Cardiac</span>
                </label>
              </div>
              <div class="service-opt">
                <input type="checkbox" name="service_type[]" id="svc_hosp" value="Hospital" <?= in_array('Hospital', $_POST['service_type'] ?? []) ? 'checked' : '' ?>>
                <label for="svc_hosp">
                  <span class="svc-icon">🏥</span>
                  <span class="svc-name">Hospital</span>
                  <span class="svc-desc">ER · Trauma · Surgery</span>
                </label>
              </div>
            </div>
          </div>

          <!-- STEP 2: SEVERITY -->
          <div style="margin-bottom:24px;">
            <p style="font-size:.8rem;font-weight:700;color:var(--text3);letter-spacing:1px;text-transform:uppercase;margin-bottom:10px;">Step 2 — Severity Level *</p>
            <div class="severity-row">
              <div class="sev-opt low"><input type="radio" name="severity" id="sev_low" value="Low"><label for="sev_low">🟢 Low</label></div>
              <div class="sev-opt medium"><input type="radio" name="severity" id="sev_med" value="Medium"><label for="sev_med">🟡 Medium</label></div>
              <div class="sev-opt high"><input type="radio" name="severity" id="sev_high" value="High" checked><label for="sev_high">🟠 High</label></div>
              <div class="sev-opt critical"><input type="radio" name="severity" id="sev_crit" value="Critical"><label for="sev_crit">🔴 Critical</label></div>
            </div>
          </div>

          <!-- STEP 3: GPS LOCATION -->
          <div style="margin-bottom:24px;">
            <p style="font-size:.8rem;font-weight:700;color:var(--text3);letter-spacing:1px;text-transform:uppercase;margin-bottom:10px;">Step 3 — Your Location *</p>
            <div class="gps-box">
              <div class="gps-title">📍 GPS Location Capture</div>
              <div class="gps-actions">
                <button type="button" class="btn btn-info" id="gpsBtn" onclick="captureGPS()">📡 Auto-Detect My Location</button>
                <button type="button" class="btn btn-ghost" onclick="clearGPS()">✖ Clear</button>
              </div>
              <div class="gps-status" id="gpsStatus"></div>
              <div id="gpsCoords" style="margin-top:8px;display:none;">
                <div class="gps-coords" id="coordsDisplay"></div>
              </div>
              <div class="map-preview" id="mapPreview">
                <iframe id="mapFrame" src="" allowfullscreen loading="lazy"></iframe>
              </div>
            </div>

            <div class="form-group">
              <label>Location Address <small style="color:var(--text3)">(auto-filled · or type manually)</small></label>
              <input type="text" name="location_text" id="loc_text"
                placeholder="e.g. Near bus stand, MG Road, Bangalore — 560001"
                value="<?= htmlspecialchars($_POST['location_text'] ?? '') ?>">
            </div>
          </div>

          <!-- STEP 4: DESCRIPTION -->
          <div class="form-group">
            <label>Step 4 — Describe the Emergency <small style="color:var(--text3)">(optional but helpful)</small></label>
            <textarea name="description" rows="3" placeholder="e.g. Two-vehicle collision on NH-48, one person unconscious and bleeding..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>

          <!-- SUBMIT -->
          <button type="submit" id="submitBtn" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;background:linear-gradient(135deg,var(--red),var(--orange));box-shadow:0 0 30px rgba(239,68,68,.4);">
            🚨 SEND EMERGENCY ALERT NOW
          </button>
          <p style="text-align:center;font-size:.75rem;color:var(--text3);margin-top:10px;">
            Your location and contact details will be shared with the selected emergency service.
          </p>
        </form>
      </div>
    </div>
  </div>

  <script src="../assets/js/app.js?v=2.1"></script>
  <script>
    function captureGPS() {
      const btn = document.getElementById('gpsBtn');
      const status = document.getElementById('gpsStatus');
      if (!navigator.geolocation) {
        status.className = 'gps-status show fail';
        status.textContent = '❌ Geolocation not supported. Please type your address manually.';
        return;
      }
      btn.disabled = true;
      btn.textContent = '⏳ Detecting…';
      status.className = 'gps-status show loading';
      status.textContent = '🔍 Requesting GPS permission and detecting your location…';

      navigator.geolocation.getCurrentPosition(
        pos => {
          const lat = pos.coords.latitude.toFixed(7);
          const lng = pos.coords.longitude.toFixed(7);
          const acc = Math.round(pos.coords.accuracy);

          document.getElementById('lat_field').value = lat;
          document.getElementById('lng_field').value = lng;

          status.className = 'gps-status show ok';
          status.innerHTML = `✅ GPS captured! Accuracy: ±${acc}m`;

          const coords = document.getElementById('gpsCoords');
          document.getElementById('coordsDisplay').textContent = `Lat: ${lat}  ·  Lng: ${lng}`;
          coords.style.display = 'block';

          btn.textContent = '✅ Location Captured';
          btn.style.background = 'var(--green)';
          btn.disabled = false;

          // Reverse geocode
          fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16`)
            .then(r => r.json())
            .then(d => {
              if (d.display_name) {
                const f = document.getElementById('loc_text');
                if (!f.value.trim()) f.value = d.display_name;
                status.innerHTML += `<br>📌 ${d.display_name.substring(0,120)}…`;
              }
            }).catch(() => {});

          // Show map
          const preview = document.getElementById('mapPreview');
          document.getElementById('mapFrame').src =
            `https://www.google.com/maps?q=${lat},${lng}&z=16&output=embed`;
          preview.style.display = 'block';
        },
        err => {
          btn.disabled = false;
          btn.textContent = '📡 Retry GPS';
          status.className = 'gps-status show fail';
          const msgs = {
            1: 'Location access denied. Please allow location and retry, or type your address.',
            2: 'Location unavailable. Please type your address.',
            3: 'Timed out. Please try again.'
          };
          status.textContent = '❌ ' + (msgs[err.code] || 'Unknown error.');
        }, {
          enableHighAccuracy: true,
          timeout: 15000,
          maximumAge: 0
        }
      );
    }

    function clearGPS() {
      document.getElementById('lat_field').value = 0;
      document.getElementById('lng_field').value = 0;
      document.getElementById('gpsStatus').className = 'gps-status';
      document.getElementById('gpsCoords').style.display = 'none';
      document.getElementById('mapPreview').style.display = 'none';
      document.getElementById('mapFrame').src = '';
      const btn = document.getElementById('gpsBtn');
      btn.textContent = '📡 Auto-Detect My Location';
      btn.style.background = '';
      btn.disabled = false;
    }

    document.getElementById('emergencyForm').addEventListener('submit', function(e) {
      const svcs = document.querySelectorAll('input[name="service_type[]"]:checked');
      if (svcs.length === 0) {
        e.preventDefault();
        showToast('Please select at least one emergency service.', 'error');
        return;
      }
      const lat = document.getElementById('lat_field').value;
      const loc = document.getElementById('loc_text').value.trim();
      if ((lat == 0) && !loc) {
        e.preventDefault();
        showToast('Please capture GPS or enter your location.', 'error');
        return;
      }
      const btn = document.getElementById('submitBtn');
      btn.textContent = '⏳ Sending Alert…';
      btn.disabled = true;
    });
  </script>
</body>

</html>
