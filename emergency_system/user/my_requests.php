<?php
require_once '../db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php?msg=session_expired"); exit(); }
$uid = (int)$_SESSION['user_id']; $uname = $_SESSION['user_name'];

$filter_status  = $_GET['status']  ?? '';
$filter_service = $_GET['service'] ?? '';
$where = "WHERE user_id=$uid";
if ($filter_status)  $where .= " AND status='"  .mysqli_real_escape_string($conn,$filter_status)."'";
if ($filter_service) $where .= " AND service_type='".mysqli_real_escape_string($conn,$filter_service)."'";

$result = mysqli_query($conn, "SELECT * FROM emergency_requests $where ORDER BY request_time DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Requests — Smart Emergency Response and Accident Assistance System</title>
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
      <a href="send_request.php"><span class="nav-icon">🆘</span> Send Emergency</a>
      <a href="my_requests.php" class="active"><span class="nav-icon">📋</span> My Requests</a>
    </div>
    <div class="sidebar-footer"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></div>
  </div>
  <div class="main-content">
    <div class="page-header">
      <div class="page-header-left"><h1>📋 My Requests</h1><p>Full history of your emergency alerts</p></div>
      <a href="send_request.php" class="btn btn-primary">+ New Alert</a>
    </div>
    <!-- Filter -->
    <form method="GET" class="filter-bar">
      <select name="status">
        <option value="">All Status</option>
        <?php foreach(['Pending','Accepted','Resolved'] as $s): ?>
          <option value="<?=$s?>" <?=$filter_status===$s?'selected':''?>><?=$s?></option>
        <?php endforeach; ?>
      </select>
      <select name="service">
        <option value="">All Services</option>
        <?php foreach(['Police','Ambulance','Hospital'] as $s): ?>
          <option value="<?=$s?>" <?=$filter_service===$s?'selected':''?>><?=$s?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-info btn-sm">🔍 Filter</button>
      <a href="my_requests.php" class="btn btn-ghost btn-sm">Clear</a>
    </form>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Service</th><th>Severity</th><th>Location</th><th>GPS</th><th>Description</th><th>Time</th><th>Status</th></tr></thead>
          <tbody>
          <?php if(mysqli_num_rows($result)===0): ?>
            <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📭</div><div class="empty-title">No requests found</div></div></td></tr>
          <?php else: $i=1; while($req=mysqli_fetch_assoc($result)):
            $si=['Police'=>'🚔','Ambulance'=>'🚑','Hospital'=>'🏥'];
            $sc=['Police'=>'badge-police','Ambulance'=>'badge-ambulance','Hospital'=>'badge-hospital'];
            $sm=['Critical'=>'badge-critical','High'=>'badge-high','Medium'=>'badge-medium','Low'=>'badge-low'];
          ?>
            <tr>
              <td><?=$i++?></td>
              <td><span class="badge <?=$sc[$req['service_type']]??''?>"><?=($si[$req['service_type']]??'').' '.$req['service_type']?></span></td>
              <td><span class="badge <?=$sm[$req['severity']]??''?>"><?=$req['severity']?></span></td>
              <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;"><?=htmlspecialchars($req['location_text']??'—')?></td>
              <td><?php if($req['latitude']&&$req['longitude']): ?>
                <a href="https://www.google.com/maps?q=<?=$req['latitude']?>,<?=$req['longitude']?>" target="_blank" class="btn btn-cyan btn-xs">📍 Map</a>
              <?php else: ?>—<?php endif; ?></td>
              <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;"><?=htmlspecialchars($req['description']??'—')?></td>
              <td style="font-size:.78rem;white-space:nowrap;"><?=date('d M Y, h:i A',strtotime($req['request_time']))?></td>
              <td><span class="badge badge-<?=strtolower($req['status'])?>"><?=$req['status']?></span>
                <?php if($req['accepted_by_role']&&$req['status']!=='Pending'): ?>
                  <div style="font-size:.7rem;color:var(--text3);margin-top:3px;">by <?=ucfirst($req['accepted_by_role'])?></div>
                <?php endif; ?></td>
            </tr>
          <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/app.js?v=2.1"></script>
</body>
</html>
