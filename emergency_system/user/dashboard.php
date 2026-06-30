<?php
require_once '../db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php?msg=session_expired"); exit(); }

$uid  = (int)$_SESSION['user_id'];
$name = $_SESSION['user_name'];

// Stats via prepared statements
function count_req($conn, $uid, $status = null) {
    $sql = $status
        ? "SELECT COUNT(*) FROM emergency_requests WHERE user_id=? AND status=?"
        : "SELECT COUNT(*) FROM emergency_requests WHERE user_id=?";
    $st = mysqli_prepare($conn, $sql);
    $status ? mysqli_stmt_bind_param($st,'is',$uid,$status) : mysqli_stmt_bind_param($st,'i',$uid);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st,$c); mysqli_stmt_fetch($st);
    return $c;
}
$total    = count_req($conn, $uid);
$pending  = count_req($conn, $uid, 'Pending');
$accepted = count_req($conn, $uid, 'Accepted');
$resolved = count_req($conn, $uid, 'Resolved');

// Recent requests
$stmt = mysqli_prepare($conn,
    "SELECT * FROM emergency_requests WHERE user_id=? ORDER BY request_time DESC LIMIT 20");
mysqli_stmt_bind_param($stmt,'i',$uid);
mysqli_stmt_execute($stmt);
$requests = mysqli_stmt_get_result($stmt);

// Flash
$flash_type = $flash_msg = '';
if (isset($_GET['msg'])) {
    $map = [
        'sent'    => ['success','🚨 Emergency alert sent! Help is on the way.'],
        'error'   => ['error',  '❌ Failed to send request. Try again.'],
    ];
    if (isset($map[$_GET['msg']])) [$flash_type, $flash_msg] = $map[$_GET['msg']];
}

// User profile
$ustmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($ustmt,'i',$uid);
mysqli_stmt_execute($ustmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($ustmt));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Dashboard — Smart Emergency Response and Accident Assistance System</title>
<link rel="stylesheet" href="../assets/css/style.css?v=5.0">
</head>
<body class="login-bg-user">
<div class="login-bg-overlay"></div>
<nav class="navbar">
  <div class="nav-brand"><svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M21 12h1"/><path d="M18.5 4.5 18 5"/><path d="M2 12h1"/><path d="M12 2v1"/><path d="m4.929 4.929.707.707"/><path d="M12 12v6"/></svg> SERAAS</div>
  <div class="nav-links">
    <button id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Toggle Theme" style="padding:6px;margin-right:8px;">
      <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
      <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
    </button>
    <span style="color:var(--text3);font-size:.85rem;">👤 <?= htmlspecialchars($name) ?></span>
    <a href="../logout.php" class="btn btn-ghost btn-sm">Logout</a>
  </div>
</nav>

<div class="layout">
  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-avatar">👤</div>
      <div class="sidebar-name"><?= htmlspecialchars($name) ?></div>
      <div class="sidebar-role">Emergency User <?php if($user['blood_group']): ?> · <?= htmlspecialchars($user['blood_group']) ?><?php endif; ?></div>
    </div>
    <div class="sidebar-nav">
      <div class="nav-section-label">Menu</div>
      <a href="dashboard.php" class="active"><span class="nav-icon">📊</span> Dashboard</a>
      <a href="send_request.php"><span class="nav-icon">🆘</span> Send Emergency
        <?php if($pending > 0): ?><span class="nav-badge"><?= $pending ?></span><?php endif; ?>
      </a>
      <a href="my_requests.php"><span class="nav-icon">📋</span> My Requests</a>
    </div>
    <div class="sidebar-footer">
      <a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a>
    </div>
  </div>

  <!-- MAIN -->
  <div class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <h1>👋 Welcome, <?= htmlspecialchars(explode(' ',$name)[0]) ?>!</h1>
        <p><?= date('l, d F Y') ?> — Stay safe, help is always one tap away.</p>
      </div>
      <a href="send_request.php" class="btn btn-primary btn-lg" style="animation:pulse-anim 2s infinite;">
        🆘 Send Emergency Alert
      </a>
    </div>

    <?php if ($flash_msg): ?>
      <div class="alert alert-<?= $flash_type ?>" data-auto-dismiss="6000">
        <span class="alert-icon"><?= $flash_type==='success'?'✅':'❌' ?></span>
        <span><?= $flash_msg ?></span>
      </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap">📋</div>
        <div class="stat-num" data-target="<?= $total ?>"><?= $total ?></div>
        <div class="stat-label">Total Requests</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon-wrap">⏳</div>
        <div class="stat-num" data-target="<?= $pending ?>"><?= $pending ?></div>
        <div class="stat-label">Pending</div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon-wrap">🚒</div>
        <div class="stat-num" data-target="<?= $accepted ?>"><?= $accepted ?></div>
        <div class="stat-label">Accepted</div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon-wrap">✅</div>
        <div class="stat-num" data-target="<?= $resolved ?>"><?= $resolved ?></div>
        <div class="stat-label">Resolved</div>
      </div>
    </div>

    <!-- PROFILE QUICK INFO -->
    <?php if ($user['blood_group'] || $user['emergency_contact']): ?>
    <div class="card" style="margin-bottom:20px;background:rgba(239,68,68,.05);border-color:rgba(239,68,68,.2);">
      <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:center;">
        <div style="font-size:.8rem;color:var(--text3);">YOUR EMERGENCY INFO</div>
        <?php if($user['blood_group']): ?>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:1.1rem;">🩸</span>
            <div><div style="font-size:.72rem;color:var(--text3);">BLOOD GROUP</div>
            <div style="font-weight:700;color:var(--red);"><?= htmlspecialchars($user['blood_group']) ?></div></div>
          </div>
        <?php endif; ?>
        <?php if($user['emergency_contact']): ?>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:1.1rem;">📞</span>
            <div><div style="font-size:.72rem;color:var(--text3);">EMERGENCY CONTACT</div>
            <div style="font-weight:700;"><?= htmlspecialchars($user['emergency_contact']) ?></div></div>
          </div>
        <?php endif; ?>
        <?php if($user['phone']): ?>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:1.1rem;">📱</span>
            <div><div style="font-size:.72rem;color:var(--text3);">YOUR PHONE</div>
            <div style="font-weight:700;"><?= htmlspecialchars($user['phone']) ?></div></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- RECENT REQUESTS -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">📋 Recent Emergency Requests</div>
        <a href="send_request.php" class="btn btn-primary btn-sm">+ New Alert</a>
      </div>
      <?php if (mysqli_num_rows($requests) === 0): ?>
        <div class="empty-state">
          <div class="empty-icon">🚨</div>
          <div class="empty-title">No requests yet</div>
          <div class="empty-desc">When you send an emergency alert, it will appear here.</div>
          <a href="send_request.php" class="btn btn-primary" style="margin-top:16px;">Send First Alert</a>
        </div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Service</th><th>Severity</th><th>Location</th><th>GPS</th><th>Date & Time</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php $i=1; while($req = mysqli_fetch_assoc($requests)): ?>
            <?php
              $sicons = ['Police'=>'🚔','Ambulance'=>'🚑','Hospital'=>'🏥'];
              $scls   = ['Police'=>'badge-police','Ambulance'=>'badge-ambulance','Hospital'=>'badge-hospital'];
              $sevmap = ['Critical'=>'badge-critical','High'=>'badge-high','Medium'=>'badge-medium','Low'=>'badge-low'];
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><span class="badge <?= $scls[$req['service_type']]??'' ?>"><?= ($sicons[$req['service_type']]??'').' '.$req['service_type'] ?></span></td>
              <td><span class="badge <?= $sevmap[$req['severity']]??'' ?>"><?= $req['severity'] ?></span></td>
              <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;">
                <?= $req['location_text'] ? htmlspecialchars(substr($req['location_text'],0,50)).'…' : '—' ?>
              </td>
              <td>
                <?php if($req['latitude'] && $req['longitude']): ?>
                  <a href="https://www.google.com/maps?q=<?= $req['latitude'] ?>,<?= $req['longitude'] ?>" target="_blank" class="btn btn-cyan btn-xs">📍 Map</a>
                <?php else: ?><span style="color:var(--text3)">—</span><?php endif; ?>
              </td>
              <td style="font-size:.8rem;white-space:nowrap;"><?= date('d M Y, h:i A', strtotime($req['request_time'])) ?></td>
              <td><span class="badge badge-<?= strtolower($req['status']) ?>"><?= $req['status'] ?></span></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="../assets/js/app.js?v=2.1"></script>
</body>
</html>
