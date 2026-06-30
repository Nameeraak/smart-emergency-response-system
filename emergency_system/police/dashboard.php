<?php
require_once '../db.php';
global $conn;
if (!isset($_SESSION['police_id'])) { header("Location: login.php"); exit(); }

$pid     = (int)$_SESSION['police_id'];
$pname   = $_SESSION['police_name'];
$station = $_SESSION['police_station'] ?? 'Police Station';
$badge   = $_SESSION['police_badge']   ?? '';

function human_time_diff(string $datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)    return $diff . 's ago';
    if ($diff < 3600)  return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    return floor($diff/86400) . 'd ago';
}

function pc(mysqli $conn, string $w){ return mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM emergency_requests WHERE service_type='Police' AND $w"))['c']; }
$total    = pc($conn,"1=1");
$pending  = pc($conn,"status='Pending'");
$accepted = pc($conn,"status='Accepted'");
$resolved = pc($conn,"status='Resolved'");
$my_accepted = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM emergency_requests WHERE service_type='Police' AND accepted_by_id=$pid AND accepted_by_role='police'"))['c'];

// Filter
$filter = $_GET['filter'] ?? 'all';
$where  = "service_type='Police'";
if ($filter === 'pending')  $where .= " AND status='Pending'";
if ($filter === 'accepted') $where .= " AND status='Accepted' AND accepted_by_id=$pid AND accepted_by_role='police'";
if ($filter === 'resolved') $where .= " AND status='Resolved'";

$requests = mysqli_query($conn,"SELECT * FROM emergency_requests WHERE $where ORDER BY
    CASE status WHEN 'Pending' THEN 1 WHEN 'Accepted' THEN 2 ELSE 3 END,
    CASE severity WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Medium' THEN 3 ELSE 4 END,
    request_time DESC");

$fmap=['accepted'=>['success','✅ Request accepted! Head to the location.'],
       'resolved'=>['success','✅ Marked as resolved.'],
       'error'=>['error','❌ Action failed. Request may have been updated by someone else.'],
       'already_updated'=>['warning','⚠️ This request was already updated.']];
[$ft,$fm]= isset($_GET['msg'])&&isset($fmap[$_GET['msg']]) ? $fmap[$_GET['msg']] : ['',''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Police Dashboard — Smart Emergency Response and Accident Assistance System</title>
<link rel="stylesheet" href="../assets/css/style.css?v=5.0">
<style>
.auto-refresh { font-size:.75rem; color:var(--text3); display:flex; align-items:center; gap:6px; }
.refresh-dot { width:7px;height:7px;border-radius:50%;background:var(--green);animation:pulse-anim 1.5s infinite; }
</style>
</head>
<body class="login-bg-police">
<div class="login-bg-overlay"></div>
<nav class="navbar">
  <div class="nav-brand"><svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M21 12h1"/><path d="M18.5 4.5 18 5"/><path d="M2 12h1"/><path d="M12 2v1"/><path d="m4.929 4.929.707.707"/><path d="M12 12v6"/></svg> SERAAS</div>
  <div class="nav-links">
    <button id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Toggle Theme" style="padding:6px;margin-right:8px;">
      <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
      <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
    </button>
    <div class="auto-refresh"><div class="refresh-dot"></div> Live — refreshes every 30s</div>
    <span style="color:var(--text3);font-size:.85rem;margin-left:12px;">🚔 <?=htmlspecialchars($pname)?></span>
    <a href="../logout.php" class="btn btn-ghost btn-sm">Logout</a>
  </div>
</nav>

<div class="layout">
  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-avatar" style="background:linear-gradient(135deg,var(--blue),#1d4ed8);">🚔</div>
      <div class="sidebar-name"><?=htmlspecialchars($pname)?></div>
      <div class="sidebar-role"><?=htmlspecialchars($station)?><?=$badge?" · Badge #$badge":''?></div>
    </div>
    <div class="sidebar-nav">
      <div class="nav-section-label">Navigation</div>
      <a href="dashboard.php" class="active"><span class="nav-icon">📊</span> Dashboard
        <?php if($pending>0): ?><span class="nav-badge"><?=$pending?></span><?php endif; ?>
      </a>
      <a href="dashboard.php?filter=pending"><span class="nav-icon">⏳</span> Pending Calls</a>
      <a href="dashboard.php?filter=accepted"><span class="nav-icon">🔵</span> My Active Cases</a>
      <a href="dashboard.php?filter=resolved"><span class="nav-icon">✅</span> Resolved</a>
    </div>
    <div class="sidebar-footer"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></div>
  </div>

  <div class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <h1>🚔 Police Emergency Dashboard</h1>
        <p><?=htmlspecialchars($station)?> · <?=date('D, d M Y, h:i A')?></p>
      </div>
      <?php if($pending > 0): ?>
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:10px 18px;display:flex;align-items:center;gap:10px;">
          <div class="pulse-dot"></div>
          <span style="color:var(--red);font-weight:700;font-size:.9rem;"><?=$pending?> Pending Alert<?=$pending>1?'s':''?></span>
        </div>
      <?php endif; ?>
    </div>

    <?php if($fm): ?>
      <div class="alert alert-<?=$ft?>" data-auto-dismiss="6000"><span class="alert-icon"><?=$ft==='success'?'✅':($ft==='warning'?'⚠️':'❌')?></span><span><?=$fm?></span></div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-icon-wrap">📋</div><div class="stat-num" data-target="<?=$total?>"><?=$total?></div><div class="stat-label">Total Police Calls</div></div>
      <div class="stat-card orange"><div class="stat-icon-wrap">⏳</div><div class="stat-num" data-target="<?=$pending?>"><?=$pending?></div><div class="stat-label">Pending</div></div>
      <div class="stat-card blue"><div class="stat-icon-wrap">🔵</div><div class="stat-num" data-target="<?=$accepted?>"><?=$accepted?></div><div class="stat-label">Accepted</div></div>
      <div class="stat-card green"><div class="stat-icon-wrap">✅</div><div class="stat-num" data-target="<?=$resolved?>"><?=$resolved?></div><div class="stat-label">Resolved</div></div>
      <div class="stat-card purple"><div class="stat-icon-wrap">👮</div><div class="stat-num" data-target="<?=$my_accepted?>"><?=$my_accepted?></div><div class="stat-label">My Cases</div></div>
    </div>

    <!-- FILTER TABS -->
    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
      <?php
      $tabs = [['all','All Requests'],['pending','⏳ Pending'],['accepted','🔵 My Active'],['resolved','✅ Resolved']];
      foreach($tabs as [$val,$label]):
        $active = ($filter === $val || ($val==='all' && $filter==='all'));
      ?>
        <a href="dashboard.php?filter=<?=$val?>" class="btn <?=$active?'btn-info':'btn-ghost'?> btn-sm"><?=$label?></a>
      <?php endforeach; ?>
    </div>

    <!-- REQUEST CARDS -->
    <?php $count = mysqli_num_rows($requests); ?>
    <?php if ($count === 0): ?>
      <div class="card"><div class="empty-state">
        <div class="empty-icon"><?=$filter==='pending'?'🎉':'📭'?></div>
        <div class="empty-title"><?=$filter==='pending'?'No pending calls — all clear!':'No requests found for this filter.'?></div>
        <div class="empty-desc">Check back shortly. This page refreshes every 30 seconds.</div>
      </div></div>
    <?php else: ?>
      <div style="margin-bottom:12px;font-size:.82rem;color:var(--text3);"><?=$count?> request<?=$count>1?'s':''?> shown</div>
      <?php while($req = mysqli_fetch_assoc($requests)):
        $sev = strtolower($req['severity']);
        $time_ago = human_time_diff($req['request_time']);
      ?>
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
          </div>
        </div>

        <div class="req-body">
          <div class="req-info-item">
            <div class="req-info-label">Request ID</div>
            <div class="req-info-value">#<?=str_pad($req['id'],4,'0',STR_PAD_LEFT)?></div>
          </div>
          <div class="req-info-item">
            <div class="req-info-label">Time</div>
            <div class="req-info-value"><?=htmlspecialchars($time_ago)?></div>
          </div>
          <?php if($req['description']): ?>
          <div class="req-info-item" style="grid-column:1/-1;">
            <div class="req-info-label">Description</div>
            <div class="req-info-value"><?=htmlspecialchars($req['description'])?></div>
          </div>
          <?php endif; ?>
          <div class="req-info-item req-location">
            <div class="req-info-label">📍 Location</div>
            <div class="req-info-value"><?=htmlspecialchars($req['location_text']??'Location not provided')?></div>
            <?php if($req['latitude']&&$req['longitude']): ?>
              <div style="margin-top:6px;">
                <a href="https://www.google.com/maps?q=<?=$req['latitude']?>,<?=$req['longitude']?>" target="_blank" class="btn btn-cyan btn-sm">🗺️ Open in Google Maps</a>
                <a href="https://www.google.com/maps/dir/current+location/<?=$req['latitude']?>,<?=$req['longitude']?>" target="_blank" class="btn btn-ghost btn-sm" style="margin-left:6px;">🧭 Get Directions</a>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="req-actions">
          <div class="req-time">
            <span>🕐</span> <?=date('d M Y, h:i A', strtotime($req['request_time']))?>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if($req['status']==='Pending'): ?>
              <a href="../actions/accept_request.php?id=<?=$req['id']?>"
                 class="btn btn-info"
                 onclick="return confirm('Accept this emergency call? You will be responsible for responding.')">
                ✔ Accept This Call
              </a>
            <?php elseif($req['status']==='Accepted' && $req['accepted_by_id']==$pid && $req['accepted_by_role']==='police'): ?>
              <span style="font-size:.8rem;color:var(--blue);">✅ Accepted by you at <?=date('h:i A',strtotime($req['accepted_at']))?></span>
              <a href="../actions/resolve_request.php?id=<?=$req['id']?>"
                 class="btn btn-success"
                 onclick="return confirm('Mark this case as fully resolved?')">
                🏁 Mark Resolved
              </a>
            <?php elseif($req['status']==='Accepted'): ?>
              <span style="font-size:.8rem;color:var(--text3);">Accepted by another officer</span>
            <?php else: ?>
              <span style="font-size:.82rem;color:var(--green);">✔ Resolved on <?=date('d M Y',strtotime($req['resolved_at']??$req['request_time']))?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>
</div>

<script src="../assets/js/app.js?v=2.1"></script>
<script>
// AJAX Auto-refresh every 5 seconds
setInterval(() => {
    // Don't refresh if a modal is open or user is interacting
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
