<?php
require_once '../db.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

$fs = $_GET['status']  ?? '';
$fv = $_GET['service'] ?? '';
$fd = $_GET['date']    ?? '';

$where = "1=1";
if ($fs) $where .= " AND status='"  . mysqli_real_escape_string($conn, $fs) . "'";
if ($fv) $where .= " AND service_type='" . mysqli_real_escape_string($conn, $fv) . "'";
if ($fd) $where .= " AND DATE(request_time)='" . mysqli_real_escape_string($conn, $fd) . "'";

$result  = mysqli_query($conn, "SELECT * FROM emergency_requests WHERE $where ORDER BY request_time DESC");
$total   = mysqli_num_rows($result);

$fmap = [
  'accepted' => ['success', '✅ Request accepted.'],
  'resolved' => ['success', '✅ Request resolved.'],
  'deleted' => ['success', '✅ Deleted.'],
  'error' => ['error', '❌ Action failed.']
];
[$ft, $fm] = isset($_GET['msg']) && isset($fmap[$_GET['msg']]) ? $fmap[$_GET['msg']] : ['', ''];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>All Requests — Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=5.0">
</head>

<body class="login-bg-admin">
<div class="login-bg-overlay"></div>
  <nav class="navbar">
    <div class="nav-brand"><svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M21 12h1"/><path d="M18.5 4.5 18 5"/><path d="M2 12h1"/><path d="M12 2v1"/><path d="m4.929 4.929.707.707"/><path d="M12 12v6"/></svg> SERAAS</div>
    <div class="nav-links">
    <button id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Toggle Theme" style="padding:6px;margin-right:8px;">
      <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
      <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
    </button><a href="../logout.php" class="btn btn-ghost btn-sm">Logout</a></div>
  </nav>
  <div class="layout">
    <div class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-avatar" style="background:linear-gradient(135deg,var(--purple),#7c3aed);">🛡️</div>
        <div class="sidebar-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
        <div class="sidebar-role">System Administrator</div>
      </div>
      <div class="sidebar-nav">
        <div class="nav-section-label">Overview</div>
        <a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
        <a href="requests.php" class="active"><span class="nav-icon">🚨</span> All Requests</a>
        <div class="nav-section-label" style="margin-top:8px;">Management</div>
        <a href="users.php"><span class="nav-icon">👥</span> Users</a>
        <a href="police.php"><span class="nav-icon">🚔</span> Police</a>
        <a href="ambulance.php"><span class="nav-icon">🚑</span> Ambulance</a>
        <a href="hospital.php"><span class="nav-icon">🏥</span> Hospital</a>
      </div>
      <div class="sidebar-footer"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></div>
    </div>
    <div class="main-content">
      <div class="page-header">
        <div class="page-header-left">
          <h1>🚨 All Emergency Requests</h1>
          <p><?= $total ?> total records found</p>
        </div>
      </div>

      <?php if ($fm): ?><div class="alert alert-<?= $ft ?>" data-auto-dismiss="5000"><span class="alert-icon"><?= $ft === 'success' ? '✅' : '❌' ?></span><span><?= $fm ?></span></div><?php endif; ?>

      <!-- FILTER BAR -->
      <form method="GET" class="filter-bar">
        <select name="status">
          <option value="">All Status</option>
          <?php foreach (['Pending', 'Accepted', 'Resolved'] as $s): ?><option value="<?= $s ?>" <?= $fs === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
        </select>
        <select name="service">
          <option value="">All Services</option>
          <?php foreach (['Police', 'Ambulance', 'Hospital'] as $s): ?><option value="<?= $s ?>" <?= $fv === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
        </select>
        <input type="date" name="date" value="<?= htmlspecialchars($fd) ?>" style="max-width:160px;">
        <button type="submit" class="btn btn-info btn-sm">🔍 Filter</button>
        <a href="requests.php" class="btn btn-ghost btn-sm">Clear</a>
      </form>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Phone</th>
                <th>Service</th>
                <th>Severity</th>
                <th>Location</th>
                <th>GPS</th>
                <th>Description</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1;
              if (mysqli_num_rows($result) === 0): ?>
                <tr>
                  <td colspan="11">
                    <div class="empty-state">
                      <div class="empty-icon">📭</div>
                      <div class="empty-title">No requests found</div>
                    </div>
                  </td>
                </tr>
                <?php else: while ($req = mysqli_fetch_assoc($result)):
                  $si = ['Police' => '🚔', 'Ambulance' => '🚑', 'Hospital' => '🏥'];
                  $sc = ['Police' => 'badge-police', 'Ambulance' => 'badge-ambulance', 'Hospital' => 'badge-hospital'];
                  $sm = ['Critical' => 'badge-critical', 'High' => 'badge-high', 'Medium' => 'badge-medium', 'Low' => 'badge-low'];
                ?>
                  <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($req['user_name']) ?></strong></td>
                    <td style="font-size:.82rem;"><?= htmlspecialchars($req['user_phone'] ?? '—') ?></td>
                    <td><span class="badge <?= $sc[$req['service_type']] ?? '' ?>"><?= ($si[$req['service_type']] ?? '') . ' ' . $req['service_type'] ?></span></td>
                    <td><span class="badge <?= $sm[$req['severity']] ?? '' ?>"><?= $req['severity'] ?></span></td>
                    <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.8rem;"><?= htmlspecialchars($req['location_text'] ?? '—') ?></td>
                    <td><?php if ($req['latitude'] && $req['longitude']): ?>
                        <a href="https://www.google.com/maps?q=<?= $req['latitude'] ?>,<?= $req['longitude'] ?>" target="_blank" class="btn btn-cyan btn-xs">📍 Map</a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.8rem;"><?= htmlspecialchars($req['description'] ?? '—') ?></td>
                    <td style="font-size:.78rem;white-space:nowrap;"><?= date('d M Y, h:i A', strtotime($req['request_time'])) ?></td>
                    <td><span class="badge badge-<?= strtolower($req['status']) ?>"><?= $req['status'] ?></span></td>
                    <td style="white-space:nowrap;">
                      <?php if ($req['status'] === 'Pending'): ?>
                        <a href="../actions/accept_request.php?id=<?= $req['id'] ?>" class="btn btn-info btn-xs">✔ Accept</a>
                      <?php elseif ($req['status'] === 'Accepted'): ?>
                        <a href="../actions/resolve_request.php?id=<?= $req['id'] ?>" class="btn btn-success btn-xs">✔ Resolve</a>
                      <?php else: ?>
                        <span style="color:var(--green);font-size:.8rem;">Done</span>
                      <?php endif; ?>
                      <a href="../actions/delete_request.php?id=<?= $req['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this request permanently?')">🗑</a>
                    </td>
                  </tr>
              <?php endwhile;
              endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/js/app.js?v=2.1"></script>
</body>

</html>
