<?php
require_once '../db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }
$aname = $_SESSION['admin_name'];

function qc($conn,$sql){ return mysqli_fetch_assoc(mysqli_query($conn,$sql))['c']; }
$total_req   = qc($conn,"SELECT COUNT(*) c FROM emergency_requests");
$pending_req = qc($conn,"SELECT COUNT(*) c FROM emergency_requests WHERE status='Pending'");
$accept_req  = qc($conn,"SELECT COUNT(*) c FROM emergency_requests WHERE status='Accepted'");
$resolv_req  = qc($conn,"SELECT COUNT(*) c FROM emergency_requests WHERE status='Resolved'");
$total_users = qc($conn,"SELECT COUNT(*) c FROM users");
$total_pol   = qc($conn,"SELECT COUNT(*) c FROM police");
$total_amb   = qc($conn,"SELECT COUNT(*) c FROM ambulance");
$total_hosp  = qc($conn,"SELECT COUNT(*) c FROM hospital");

$by_svc = []; $sr=mysqli_query($conn,"SELECT service_type,COUNT(*) c FROM emergency_requests GROUP BY service_type");
while($r=mysqli_fetch_assoc($sr)) $by_svc[$r['service_type']]=$r['c'];

$recent = mysqli_query($conn,"SELECT * FROM emergency_requests ORDER BY request_time DESC LIMIT 12");

$flash='';
$fmap=['accepted'=>['success','Request accepted.'],'resolved'=>['success','Request resolved.'],
       'deleted'=>['success','Request deleted.'],'error'=>['error','Action failed.']];
if(isset($_GET['msg'])&&isset($fmap[$_GET['msg']])) [$ft,$fm]=$fmap[$_GET['msg']];
else $ft=$fm='';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard — Smart Emergency Response and Accident Assistance System</title>
<link rel="stylesheet" href="../assets/css/style.css?v=5.0">
</head>
<body class="login-bg-admin">
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
    <span style="color:var(--text3);font-size:.85rem;display:flex;align-items:center;gap:6px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <?=htmlspecialchars($aname)?>
    </span>
    <a href="../logout.php" class="btn btn-ghost btn-sm">Logout</a>
  </div>
</nav>
<div class="layout">
  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-avatar" style="background:linear-gradient(135deg,var(--purple),#7c3aed);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
      </div>
      <div class="sidebar-name"><?=htmlspecialchars($aname)?></div>
      <div class="sidebar-role">System Administrator</div>
    </div>
    <div class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <a href="dashboard.php" class="active"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg></span> Dashboard</a>
      <a href="requests.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span> All Requests
        <?php if($pending_req>0): ?><span class="nav-badge"><?=$pending_req?></span><?php endif; ?>
      </a>
      <div class="nav-section-label" style="margin-top:8px;">Management</div>
      <a href="users.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span> Users</a>
      <a href="police.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span> Police</a>
      <a href="ambulance.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span> Ambulance</a>
      <a href="hospital.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></span> Hospital</a>
    </div>
    <div class="sidebar-footer"><a href="../logout.php"><span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg></span> Logout</a></div>
  </div>

  <div class="main-content">
    <div class="page-header">
      <div class="page-header-left">
        <h1 style="display:flex;align-items:center;gap:10px;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
          Admin Dashboard
        </h1>
        <p>Welcome back, <?=htmlspecialchars($aname)?> · <?=date('D, d M Y')?></p>
      </div>
      <a href="requests.php" class="btn btn-primary">
        View All Requests
      </a>
    </div>

    <?php if($fm): ?>
      <div class="alert alert-<?=$ft?>" data-auto-dismiss="5000">
        <span class="alert-icon">
          <?php if($ft==='success'): ?><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg><?php else: ?><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg><?php endif; ?>
        </span>
        <span><?=$fm?></span>
      </div>
    <?php endif; ?>

    <!-- REQUEST STATS -->
    <p style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,0.85);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:10px;">EMERGENCY REQUESTS</p>
    <div class="stats-grid" style="margin-bottom:28px;">
      <div class="stat-card">
        <div class="stat-icon-wrap" style="color:var(--text);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg></div>
        <div class="stat-num" data-target="<?=$total_req?>"><?=$total_req?></div><div class="stat-label">Total</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon-wrap" style="color:var(--orange);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="stat-num" data-target="<?=$pending_req?>"><?=$pending_req?></div><div class="stat-label">Pending</div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon-wrap" style="color:var(--blue);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg></div>
        <div class="stat-num" data-target="<?=$accept_req?>"><?=$accept_req?></div><div class="stat-label">Accepted</div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon-wrap" style="color:var(--green);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg></div>
        <div class="stat-num" data-target="<?=$resolv_req?>"><?=$resolv_req?></div><div class="stat-label">Resolved</div>
      </div>
    </div>

    <!-- BY SERVICE -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:28px;">
      <?php
      $svc_conf=[
        'Police'   =>['<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>','blue',  $by_svc['Police']??0],
        'Ambulance'=>['<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>','',     $by_svc['Ambulance']??0],
        'Hospital' =>['<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>','green', $by_svc['Hospital']??0],
      ];
      foreach($svc_conf as $sn=>[$icon,$cls,$cnt]):
      ?>
      <div class="stat-card <?=$cls?>">
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <div><div class="stat-num" data-target="<?=$cnt?>"><?=$cnt?></div><div class="stat-label"><?=$sn?> Requests</div></div>
          <div style="opacity:.5;color:var(--<?=$cls? $cls : 'red'?>);"><?=$icon?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- RESPONDER STATS -->
    <p style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,0.85);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:10px;">REGISTERED ACCOUNTS</p>
    <div class="stats-grid" style="margin-bottom:28px;">
      <div class="stat-card">
        <div class="stat-icon-wrap" style="color:var(--text);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div class="stat-num" data-target="<?=$total_users?>"><?=$total_users?></div><div class="stat-label">Users</div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon-wrap" style="color:var(--blue);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <div class="stat-num" data-target="<?=$total_pol?>"><?=$total_pol?></div><div class="stat-label">Police</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon-wrap" style="color:var(--orange);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
        <div class="stat-num" data-target="<?=$total_amb?>"><?=$total_amb?></div><div class="stat-label">Ambulance</div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon-wrap" style="color:var(--green);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
        <div class="stat-num" data-target="<?=$total_hosp?>"><?=$total_hosp?></div><div class="stat-label">Hospital</div>
      </div>
    </div>

        

    <!-- RECENT REQUESTS TABLE -->
    <div class="card">
      <div class="card-header">
        <div class="card-title" style="display:flex;align-items:center;gap:8px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          Recent Emergency Requests
        </div>
        <a href="requests.php" class="btn btn-ghost btn-sm">View All →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>User</th><th>Phone</th><th>Service</th><th>Severity</th><th>Location</th><th>GPS</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php $i=1;
          if(mysqli_num_rows($recent)===0): ?>
            <tr><td colspan="10"><div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="3" x2="21" y1="9" y2="9"/><path d="m9 16 3-3 3 3"/></svg></div><div class="empty-title">No requests yet</div></div></td></tr>
          <?php else: while($req=mysqli_fetch_assoc($recent)):
            $sc=['Police'=>'badge-police','Ambulance'=>'badge-ambulance','Hospital'=>'badge-hospital'];
            $sm=['Critical'=>'badge-critical','High'=>'badge-high','Medium'=>'badge-medium','Low'=>'badge-low'];
          ?>
            <tr>
              <td><?=$i++?></td>
              <td><strong><?=htmlspecialchars($req['user_name'])?></strong></td>
              <td style="font-size:.82rem;"><?=htmlspecialchars($req['user_phone']??'—')?></td>
              <td><span class="badge <?=$sc[$req['service_type']]??''?>"><?=$req['service_type']?></span></td>
              <td><span class="badge <?=$sm[$req['severity']]??''?>"><?=$req['severity']?></span></td>
              <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.8rem;"><?=htmlspecialchars($req['location_text']??'—')?></td>
              <td><?php if($req['latitude']&&$req['longitude']): ?>
                <a href="https://www.google.com/maps?q=<?=$req['latitude']?>,<?=$req['longitude']?>" target="_blank" class="btn btn-cyan btn-xs"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></a>
              <?php else: ?>—<?php endif; ?></td>
              <td style="font-size:.78rem;white-space:nowrap;"><?=date('d M, h:i A',strtotime($req['request_time']))?></td>
              <td><span class="badge badge-<?=strtolower($req['status'])?>"><?=$req['status']?></span></td>
              <td style="white-space:nowrap;display:flex;gap:4px;">
                <?php if($req['status']==='Pending'): ?>
                  <a href="../actions/accept_request.php?id=<?=$req['id']?>" class="btn btn-info btn-xs">Accept</a>
                <?php elseif($req['status']==='Accepted'): ?>
                  <a href="../actions/resolve_request.php?id=<?=$req['id']?>" class="btn btn-success btn-xs">Resolve</a>
                <?php else: ?>
                  <span style="color:var(--green);font-size:.8rem;padding:0 4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg></span>
                <?php endif; ?>
                <a href="../actions/delete_request.php?id=<?=$req['id']?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this request?')"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></a>
              </td>
            </tr>
          <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- QUICK LINKS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;">
      <?php $ql=[
        ['users.php','<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--text3)" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>','Manage Users',$total_users,'users'],
        ['police.php','<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>','Police Officers',$total_pol,'police'],
        ['ambulance.php','<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>','Ambulance Units',$total_amb,'amb'],
        ['hospital.php','<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>','Hospitals',$total_hosp,'hosp']
      ];
      foreach($ql as [$url,$icon,$label,$count,$cls]): ?>
      <a href="<?=$url?>" class="card" style="text-decoration:none;text-align:center;padding:20px;cursor:pointer;" onmouseover="this.style.borderColor='rgba(239,68,68,.4)'" onmouseout="this.style.borderColor='var(--border)'">
        <div style="margin-bottom:8px;"><?=$icon?></div>
        <div style="font-weight:700;color:var(--text);"><?=$label?></div>
        <div style="font-size:.78rem;color:var(--text3);margin-top:4px;"><?=$count?> records</div>
      </a>
      <?php endforeach; ?>
    </div>

  </div>
</div>
<script src="../assets/js/app.js?v=2.1"></script>

</body>
</html>
