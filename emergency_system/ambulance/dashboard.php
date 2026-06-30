<?php
require_once '../db.php';
global $conn;
if (!isset($_SESSION['ambulance_id'])) { header("Location: login.php"); exit(); }

$aid     = (int)$_SESSION['ambulance_id'];
$aname   = $_SESSION['ambulance_name'];
$vehicle = $_SESSION['ambulance_vehicle'] ?? '';

function human_time_diff(string $datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)   return $diff.'s ago';
    if ($diff < 3600) return floor($diff/60).'m ago';
    if ($diff < 86400)return floor($diff/3600).'h ago';
    return floor($diff/86400).'d ago';
}
function ac(mysqli $conn, string $w){ return mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM emergency_requests WHERE service_type='Ambulance' AND $w"))['c']; }

$total    = ac($conn,"1=1");
$pending  = ac($conn,"status='Pending'");
$accepted = ac($conn,"status='Accepted'");
$resolved = ac($conn,"status='Resolved'");
$my_cases = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM emergency_requests WHERE service_type='Ambulance' AND accepted_by_id=$aid AND accepted_by_role='ambulance'"))['c'];

$filter  = $_GET['filter'] ?? 'all';
$where   = "service_type='Ambulance'";
if ($filter==='pending')  $where .= " AND status='Pending'";
if ($filter==='accepted') $where .= " AND status='Accepted' AND accepted_by_id=$aid AND accepted_by_role='ambulance'";
if ($filter==='resolved') $where .= " AND status='Resolved'";

$requests = mysqli_query($conn,"SELECT * FROM emergency_requests WHERE $where ORDER BY
    CASE status WHEN 'Pending' THEN 1 WHEN 'Accepted' THEN 2 ELSE 3 END,
    CASE severity WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Medium' THEN 3 ELSE 4 END,
    request_time DESC");

$fmap=['accepted'=>['success','✅ Dispatch accepted! Navigate to location immediately.'],
       'resolved'=>['success','✅ Case marked resolved.'],
       'error'=>['error','❌ Action failed.']];
[$ft,$fm]= isset($_GET['msg'])&&isset($fmap[$_GET['msg']]) ? $fmap[$_GET['msg']] : ['',''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ambulance Dashboard — Smart Emergency Response and Accident Assistance System</title>
<link rel="stylesheet" href="../assets/css/style.css?v=5.0">
</head>
<body class="login-bg-ambulance">
<div class="login-bg-overlay"></div>
<nav class="navbar">
  <div class="nav-brand"><svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M21 12h1"/><path d="M18.5 4.5 18 5"/><path d="M2 12h1"/><path d="M12 2v1"/><path d="m4.929 4.929.707.707"/><path d="M12 12v6"/></svg> SERAAS</div>
  <div class="nav-links">
    <button id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Toggle Theme" style="padding:6px;margin-right:8px;">
      <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
      <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
    </button>
    <div style="font-size:.75rem;color:var(--text3);display:flex;align-items:center;gap:6px;">
      <div style="width:7px;height:7px;border-radius:50%;background:var(--green);animation:pulse-anim 1.5s infinite;"></div>Live
    </div>
    <span style="color:var(--text3);font-size:.85rem;margin-left:12px;">🚑 <?=htmlspecialchars($aname)?><?=$vehicle?" · $vehicle":''?></span>
    <a href="../logout.php" class="btn btn-ghost btn-sm">Logout</a>
  </div>
</nav>

<div class="layout">
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-avatar" style="background:linear-gradient(135deg,var(--red),var(--orange));">🚑</div>
      <div class="sidebar-name"><?=htmlspecialchars($aname)?></div>
      <div class="sidebar-role">Ambulance Driver<?=$vehicle?" · $vehicle":''?></div>
    </div>
    <div class="sidebar-nav">
      <div class="nav-section-label">Navigation</div>
      <a href="dashboard.php" class="active"><span class="nav-icon">📊</span> Dashboard
        <?php if($pending>0): ?><span class="nav-badge"><?=$pending?></span><?php endif; ?>
      </a>
      <a href="dashboard.php?filter=pending"><span class="nav-icon">⏳</span> Pending Calls</a>
      <a href="dashboard.php?filter=accepted"><span class="nav-icon">🔵</span> My Active</a>
      <a href="dashboard.php?filter=resolved"><span class="nav-icon">✅</span> Resolved</a>
    </div>
    <div class="sidebar-footer"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></div>
  </div>

  <div class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <h1>🚑 Ambulance Dispatch Dashboard</h1>
        <p>Medical emergency response · <?=date('D, d M Y, h:i A')?></p>
      </div>
      <?php if($pending > 0): ?>
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:10px 18px;display:flex;align-items:center;gap:10px;">
          <div class="pulse-dot"></div>
          <span style="color:var(--red);font-weight:700;font-size:.9rem;"><?=$pending?> Medical Emergency<?=$pending>1?'s':''?> Pending</span>
        </div>
      <?php endif; ?>
    </div>

    <?php if($fm): ?>
      <div class="alert alert-<?=$ft?>" data-auto-dismiss="6000"><span class="alert-icon"><?=$ft==='success'?'✅':'❌'?></span><span><?=$fm?></span></div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon-wrap">📋</div><div class="stat-num" data-target="<?=$total?>"><?=$total?></div><div class="stat-label">Total Medical Calls</div></div>
      <div class="stat-card orange"><div class="stat-icon-wrap">⏳</div><div class="stat-num" data-target="<?=$pending?>"><?=$pending?></div><div class="stat-label">Pending</div></div>
      <div class="stat-card blue"><div class="stat-icon-wrap">🚑</div><div class="stat-num" data-target="<?=$accepted?>"><?=$accepted?></div><div class="stat-label">Dispatched</div></div>
      <div class="stat-card green"><div class="stat-icon-wrap">✅</div><div class="stat-num" data-target="<?=$resolved?>"><?=$resolved?></div><div class="stat-label">Resolved</div></div>
      <div class="stat-card purple"><div class="stat-icon-wrap">🏅</div><div class="stat-num" data-target="<?=$my_cases?>"><?=$my_cases?></div><div class="stat-label">My Responses</div></div>
    </div>

    <!-- FILTER TABS -->
    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
      <?php foreach([['all','All'],['pending','⏳ Pending'],['accepted','🚑 My Active'],['resolved','✅ Resolved']] as [$v,$l]): ?>
        <a href="dashboard.php?filter=<?=$v?>" class="btn <?=$filter===$v?'btn-primary':'btn-ghost'?> btn-sm"><?=$l?></a>
      <?php endforeach; ?>
    </div>

    <!-- REQUEST CARDS -->
    <?php if(mysqli_num_rows($requests)===0): ?>
      <div class="card"><div class="empty-state">
        <div class="empty-icon"><?=$filter==='pending'?'🏥':'📭'?></div>
        <div class="empty-title"><?=$filter==='pending'?'No pending medical calls — standby!':'No requests found.'?></div>
      </div></div>
    <?php else: while($req=mysqli_fetch_assoc($requests)):
      $sev=strtolower($req['severity']); ?>
      <div class="req-card <?=$sev?>">
        <div class="req-card-header">
          <div class="req-user">
            <div class="req-avatar">👤</div>
            <div>
              <div class="req-user-name"><?=htmlspecialchars($req['user_name'])?></div>
              <div class="req-user-phone">📞 <?=htmlspecialchars($req['user_phone']??'N/A')?></div>
            </div>
          </div>
          <div class="req-badges">
            <span class="badge badge-<?=strtolower($req['status'])?>"><?=$req['status']?></span>
            <span class="badge badge-<?=$sev?>"><?=$req['severity']?></span>
            <span style="font-size:.75rem;color:var(--text3);"><?=human_time_diff($req['request_time'])?></span>
          </div>
        </div>

        <div class="req-body">
          <div class="req-info-item">
            <div class="req-info-label">Case ID</div>
            <div class="req-info-value">#<?=str_pad($req['id'],4,'0',STR_PAD_LEFT)?></div>
          </div>
          <div class="req-info-item">
            <div class="req-info-label">Reported</div>
            <div class="req-info-value"><?=date('h:i A, d M',strtotime($req['request_time']))?></div>
          </div>
          <?php if($req['description']): ?>
          <div class="req-info-item" style="grid-column:1/-1;">
            <div class="req-info-label">🩺 Medical Details</div>
            <div class="req-info-value"><?=htmlspecialchars($req['description'])?></div>
          </div>
          <?php endif; ?>
          <div class="req-info-item req-location">
            <div class="req-info-label">📍 Patient Location</div>
            <div class="req-info-value"><?=htmlspecialchars($req['location_text']??'Location not provided')?></div>
            <?php if($req['latitude']&&$req['longitude']): ?>
              <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                <a href="https://www.google.com/maps?q=<?=$req['latitude']?>,<?=$req['longitude']?>" target="_blank" class="btn btn-cyan btn-sm">🗺️ Open Map</a>
                <a href="https://www.google.com/maps/dir/current+location/<?=$req['latitude']?>,<?=$req['longitude']?>" target="_blank" class="btn btn-ghost btn-sm">🧭 Navigate</a>
                <span style="font-family:monospace;font-size:.75rem;color:var(--cyan);background:var(--bg3);border:1px solid var(--border);padding:4px 10px;border-radius:6px;align-self:center;">
                  <?=$req['latitude']?>, <?=$req['longitude']?>
                </span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="req-actions">
          <div class="req-time">🕐 <?=date('d M Y, h:i A',strtotime($req['request_time']))?></div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if($req['status']==='Pending'): ?>
              <a href="../actions/accept_request.php?id=<?=$req['id']?>"
                 class="btn btn-primary"
                 onclick="return confirm('Accept this medical emergency? Your vehicle will be dispatched.')">
                🚑 Dispatch Ambulance
              </a>
            <?php elseif($req['status']==='Accepted' && $req['accepted_by_id']==$aid && $req['accepted_by_role']==='ambulance'): ?>
              <span style="font-size:.8rem;color:var(--blue);">🚑 Dispatched at <?=date('h:i A',strtotime($req['accepted_at']))?></span>
              <a href="../actions/resolve_request.php?id=<?=$req['id']?>"
                 class="btn btn-success"
                 onclick="return confirm('Confirm patient has been transported and case is resolved?')">
                🏁 Patient Transported
              </a>
            <?php elseif($req['status']==='Accepted'): ?>
              <span style="font-size:.8rem;color:var(--text3);">Another unit is responding</span>
            <?php else: ?>
              <span style="font-size:.82rem;color:var(--green);">✔ Case closed</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; endif; ?>
  </div>
</div>
<script src="../assets/js/app.js?v=2.1"></script>
<script>
// AJAX Auto-refresh every 5 seconds
setInterval(() => {
    // Don't refresh if a modal is open
    if (document.querySelector('.modal-overlay.show')) return;
    
    fetch(window.location.href)
    .then(res => res.text())
    .then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        document.querySelector('.main-content').innerHTML = doc.querySelector('.main-content').innerHTML;
        document.querySelector('.sidebar-nav').innerHTML = doc.querySelector('.sidebar-nav').innerHTML;
    })
    .catch(err => console.error('Auto-refresh failed:', err));
}, 5000);
</script>

</body>
</html>
