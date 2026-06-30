<?php
require_once '../db.php';
global $conn;
if (!isset($_SESSION['hospital_id'])) { header("Location: login.php"); exit(); }

$hid      = (int)$_SESSION['hospital_id'];
$hname    = $_SESSION['hospital_name'];
$hospname = $_SESSION['hospital_hosp'] ?? 'Hospital';

function human_time_diff(string $dt) {
    $d = time() - strtotime($dt);
    if ($d < 60)   return $d.'s ago';
    if ($d < 3600) return floor($d/60).'m ago';
    if ($d < 86400)return floor($d/3600).'h ago';
    return floor($d/86400).'d ago';
}
function hc(mysqli $conn, string $w){ return mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM emergency_requests WHERE service_type='Hospital' AND $w"))['c']; }

$total    = hc($conn,"1=1");
$pending  = hc($conn,"status='Pending'");
$accepted = hc($conn,"status='Accepted'");
$resolved = hc($conn,"status='Resolved'");
$my_cases = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM emergency_requests WHERE service_type='Hospital' AND accepted_by_id=$hid AND accepted_by_role='hospital'"))['c'];

$filter  = $_GET['filter'] ?? 'all';
$where   = "service_type='Hospital'";
if ($filter==='pending')  $where .= " AND status='Pending'";
if ($filter==='accepted') $where .= " AND status='Accepted' AND accepted_by_id=$hid AND accepted_by_role='hospital'";
if ($filter==='resolved') $where .= " AND status='Resolved'";

$requests = mysqli_query($conn,"SELECT * FROM emergency_requests WHERE $where ORDER BY
    CASE status WHEN 'Pending' THEN 1 WHEN 'Accepted' THEN 2 ELSE 3 END,
    CASE severity WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Medium' THEN 3 ELSE 4 END,
    request_time DESC");

$fmap = [
    'accepted' => ['success','✅ ER Alerted! Prepare trauma bay for incoming patient.'],
    'resolved' => ['success','✅ Case marked as resolved.'],
    'error'    => ['error',  '❌ Action failed. Please try again.'],
];
[$ft,$fm] = isset($_GET['msg']) && isset($fmap[$_GET['msg']]) ? $fmap[$_GET['msg']] : ['',''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Hospital Dashboard — Smart Emergency Response and Accident Assistance System</title>
<link rel="stylesheet" href="../assets/css/style.css?v=5.0">
</head>
<body class="login-bg-hospital">
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
    <span style="color:var(--text3);font-size:.85rem;margin-left:12px;">🏥 <?=htmlspecialchars($hname)?></span>
    <a href="../logout.php" class="btn btn-ghost btn-sm">Logout</a>
  </div>
</nav>

<div class="layout">
  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-avatar" style="background:linear-gradient(135deg,var(--green),#15803d);">🏥</div>
      <div class="sidebar-name"><?=htmlspecialchars($hname)?></div>
      <div class="sidebar-role"><?=htmlspecialchars($hospname)?></div>
    </div>
    <div class="sidebar-nav">
      <div class="nav-section-label">Navigation</div>
      <a href="dashboard.php" class="active"><span class="nav-icon">📊</span> Dashboard
        <?php if($pending>0): ?><span class="nav-badge"><?=$pending?></span><?php endif; ?>
      </a>
      <a href="dashboard.php?filter=pending"><span class="nav-icon">⏳</span> Incoming Patients</a>
      <a href="dashboard.php?filter=accepted"><span class="nav-icon">🔵</span> ER Active</a>
      <a href="dashboard.php?filter=resolved"><span class="nav-icon">✅</span> Discharged</a>
    </div>
    <div class="sidebar-footer">
      <a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a>
    </div>
  </div>

  <div class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <h1>🏥 Hospital Emergency Dashboard</h1>
        <p><?=htmlspecialchars($hospname)?> · <?=date('D, d M Y, h:i A')?></p>
      </div>
      <?php if($pending > 0): ?>
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);
                    border-radius:10px;padding:10px 18px;display:flex;align-items:center;gap:10px;">
          <div class="pulse-dot"></div>
          <span style="color:var(--red);font-weight:700;font-size:.9rem;">
            <?=$pending?> Incoming Patient<?=$pending>1?'s':''?> Awaiting ER Prep
          </span>
        </div>
      <?php endif; ?>
    </div>

    <?php if($fm): ?>
      <div class="alert alert-<?=$ft?>" data-auto-dismiss="6000">
        <span class="alert-icon"><?=$ft==='success'?'✅':'❌'?></span>
        <span><?=$fm?></span>
      </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrap">📋</div>
        <div class="stat-num" data-target="<?=$total?>"><?=$total?></div>
        <div class="stat-label">Total ER Alerts</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon-wrap">⏳</div>
        <div class="stat-num" data-target="<?=$pending?>"><?=$pending?></div>
        <div class="stat-label">Incoming</div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon-wrap">🛏️</div>
        <div class="stat-num" data-target="<?=$accepted?>"><?=$accepted?></div>
        <div class="stat-label">ER Active</div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon-wrap">✅</div>
        <div class="stat-num" data-target="<?=$resolved?>"><?=$resolved?></div>
        <div class="stat-label">Discharged</div>
      </div>
      <div class="stat-card purple">
        <div class="stat-icon-wrap">🏅</div>
        <div class="stat-num" data-target="<?=$my_cases?>"><?=$my_cases?></div>
        <div class="stat-label">My ER Cases</div>
      </div>
    </div>

    <!-- FILTER TABS -->
    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
      <?php
      $tabs = [['all','All'],['pending','⏳ Incoming'],['accepted','🔵 ER Active'],['resolved','✅ Discharged']];
      foreach($tabs as [$v,$l]): ?>
        <a href="dashboard.php?filter=<?=$v?>"
           class="btn <?=$filter===$v?'btn-success':'btn-ghost'?> btn-sm"><?=$l?></a>
      <?php endforeach; ?>
    </div>

    <!-- REQUEST CARDS -->
    <?php if(mysqli_num_rows($requests) === 0): ?>
      <div class="card"><div class="empty-state">
        <div class="empty-icon"><?=$filter==='pending'?'🏥':'📭'?></div>
        <div class="empty-title">
          <?=$filter==='pending' ? 'No incoming patients — ER is clear.' : 'No records found for this filter.'?>
        </div>
        <div class="empty-desc">This page refreshes every 30 seconds.</div>
      </div></div>
    <?php else: while($req = mysqli_fetch_assoc($requests)):
      $sev = strtolower($req['severity']); ?>

      <div class="req-card <?=$sev?>">
        <!-- HEADER -->
        <div class="req-card-header">
          <div class="req-user">
            <div class="req-avatar">👤</div>
            <div>
              <div class="req-user-name"><?=htmlspecialchars($req['user_name'])?></div>
              <div class="req-user-phone">📞 <?=htmlspecialchars($req['user_phone'] ?? 'N/A')?></div>
            </div>
          </div>
          <div class="req-badges">
            <span class="badge badge-<?=strtolower($req['status'])?>"><?=$req['status']?></span>
            <span class="badge badge-<?=$sev?>"><?=$req['severity']?></span>
            <span style="font-size:.75rem;color:var(--text3);"><?=human_time_diff($req['request_time'])?></span>
          </div>
        </div>

        <!-- BODY INFO GRID -->
        <div class="req-body">
          <div class="req-info-item">
            <div class="req-info-label">Case ID</div>
            <div class="req-info-value">#<?=str_pad($req['id'],4,'0',STR_PAD_LEFT)?></div>
          </div>
          <div class="req-info-item">
            <div class="req-info-label">Alert Time</div>
            <div class="req-info-value"><?=date('h:i A, d M',strtotime($req['request_time']))?></div>
          </div>

          <?php if($req['description']): ?>
          <div class="req-info-item" style="grid-column:1/-1;">
            <div class="req-info-label">🩺 Patient Condition / Description</div>
            <div class="req-info-value"><?=htmlspecialchars($req['description'])?></div>
          </div>
          <?php endif; ?>

          <div class="req-info-item req-location">
            <div class="req-info-label">📍 Patient Location</div>
            <div class="req-info-value"><?=htmlspecialchars($req['location_text'] ?? 'Location not provided')?></div>
            <?php if($req['latitude'] && $req['longitude']): ?>
              <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <a href="https://www.google.com/maps?q=<?=$req['latitude']?>,<?=$req['longitude']?>"
                   target="_blank" class="btn btn-cyan btn-sm">🗺️ Open Map</a>
                <a href="https://www.google.com/maps/dir/current+location/<?=$req['latitude']?>,<?=$req['longitude']?>"
                   target="_blank" class="btn btn-ghost btn-sm">🧭 Directions</a>
                <span style="font-family:monospace;font-size:.75rem;color:var(--cyan);
                             background:var(--bg3);border:1px solid var(--border);
                             padding:4px 10px;border-radius:6px;">
                  <?=$req['latitude']?>, <?=$req['longitude']?>
                </span>
              </div>
            <?php endif; ?>
          </div>

          <!-- ER PREP CHECKLIST (shown when pending) -->
          <?php if($req['status']==='Pending' || ($req['status']==='Accepted' && $req['accepted_by_id']==$hid)): ?>
          <div class="req-info-item" style="grid-column:1/-1;background:rgba(34,197,94,.05);border-color:rgba(34,197,94,.2);">
            <div class="req-info-label">🏥 ER Preparation Checklist</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
              <?php
              $sev_upper = strtoupper($req['severity']);
              $checklists = [
                'CRITICAL' => ['🚨 Alert trauma team','🛏️ Clear trauma bay','🩸 Type & crossmatch ready','💉 IV access prepared','🫀 Crash cart on standby','🔬 Lab on alert'],
                'HIGH'     => ['🛏️ ER bed prepared','🩺 Assign ER physician','💉 IV tray ready','🔬 Labs on standby'],
                'MEDIUM'   => ['🛏️ ER bed available','🩺 Triage nurse notified','📋 Intake forms ready'],
                'LOW'      => ['📋 Registration desk notified','🛏️ Waiting area prep'],
              ];
              $items = $checklists[$sev_upper] ?? $checklists['MEDIUM'];
              foreach($items as $item): ?>
                <span style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);
                             color:#4ade80;padding:4px 12px;border-radius:99px;font-size:.78rem;font-weight:600;">
                  <?=$item?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- RESOLVED NOTE -->
          <?php if($req['status']==='Resolved' && $req['responder_note']): ?>
          <div class="req-info-item" style="grid-column:1/-1;">
            <div class="req-info-label">📝 Discharge Note</div>
            <div class="req-info-value"><?=htmlspecialchars($req['responder_note'])?></div>
          </div>
          <?php endif; ?>
        </div>

        <!-- ACTIONS -->
        <div class="req-actions">
          <div class="req-time">🕐 <?=date('d M Y, h:i A',strtotime($req['request_time']))?></div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <?php if($req['status']==='Pending'): ?>
              <a href="../actions/accept_request.php?id=<?=$req['id']?>"
                 class="btn btn-success"
                 onclick="return confirm('Alert ER and accept this incoming patient?')">
                🏥 Alert ER &amp; Accept
              </a>

            <?php elseif($req['status']==='Accepted' && $req['accepted_by_id']==$hid && $req['accepted_by_role']==='hospital'): ?>
              <span style="font-size:.8rem;color:var(--green);">
                ✅ ER Alerted at <?=date('h:i A',strtotime($req['accepted_at']))?>
              </span>

              <!-- Resolve with optional discharge note -->
              <button type="button" class="btn btn-primary"
                      onclick="document.getElementById('resolve-modal-<?=$req['id']?>').classList.add('show')">
                🏁 Discharge / Resolve
              </button>

              <!-- Resolve Modal -->
              <div class="modal-overlay" id="resolve-modal-<?=$req['id']?>">
                <div class="modal">
                  <div class="modal-header">
                    <div class="modal-title">🏁 Resolve Case #<?=str_pad($req['id'],4,'0',STR_PAD_LEFT)?></div>
                    <button class="modal-close"
                            onclick="document.getElementById('resolve-modal-<?=$req['id']?>').classList.remove('show')">×</button>
                  </div>
                  <form method="POST" action="../actions/resolve_request.php?id=<?=$req['id']?>">
                    <?=csrf_field()?>
                    <div class="form-group">
                      <label>Discharge / Resolution Note <small style="color:var(--text3)">(optional)</small></label>
                      <textarea name="note" rows="3"
                        placeholder="e.g. Patient treated for fractures, stable condition, discharged after 4 hrs..."></textarea>
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
                      <button type="button" class="btn btn-ghost"
                              onclick="document.getElementById('resolve-modal-<?=$req['id']?>').classList.remove('show')">Cancel</button>
                      <button type="submit" class="btn btn-success">✔ Confirm Discharge</button>
                    </div>
                  </form>
                </div>
              </div>

            <?php elseif($req['status']==='Accepted'): ?>
              <span style="font-size:.8rem;color:var(--text3);">Another hospital unit is handling this</span>

            <?php else: ?>
              <span style="font-size:.82rem;color:var(--green);">
                ✔ Discharged on <?=date('d M Y',strtotime($req['resolved_at'] ?? $req['request_time']))?>
              </span>
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
    // Don't refresh if a modal is open to prevent losing typed notes
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
