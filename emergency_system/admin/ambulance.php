<?php
require_once '../db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }
$error=$success='';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_record'])) {
    verify_csrf();
    $n=trim($_POST['name']??''); $e=trim($_POST['email']??''); $ph=trim($_POST['phone']??''); $pw=$_POST['password']??'';
    $f1=trim($_POST['field1']??''); $f2=trim($_POST['field2']??''); $f3=trim($_POST['field3']??'');
    if(!$n||!$e||!$ph||!$pw) { $error="All required fields must be filled."; }
    else {
        $chk=mysqli_prepare($conn,"SELECT id FROM ambulance WHERE email=?");
        mysqli_stmt_bind_param($chk,'s',$e); mysqli_stmt_execute($chk); mysqli_stmt_store_result($chk);
        if(mysqli_stmt_num_rows($chk)>0) { $error="Email already exists."; }
        else {
            $h=password_hash($pw,PASSWORD_DEFAULT);
            $ins=mysqli_prepare($conn,"INSERT INTO ambulance(name,email,phone,vehicle_number,driver_license,base_location,password) VALUES(?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($ins,'sssssss',$n,$e,$ph,$f1,$f2,$f3,$h);
            mysqli_stmt_execute($ins) ? $success="Record added." : $error="Insert failed.";
        }
    }
}
if(isset($_GET['toggle'])&&is_numeric($_GET['toggle'])){
    $tid=(int)$_GET['toggle'];
    $cur=mysqli_fetch_assoc(mysqli_query($conn,"SELECT status FROM ambulance WHERE id=$tid"))['status'];
    mysqli_query($conn,"UPDATE ambulance SET status='".($cur==='active'?'inactive':'active')."' WHERE id=$tid");
    header("Location: ambulance.php"); exit();
}
if(isset($_GET['delete'])&&is_numeric($_GET['delete'])){
    $st=mysqli_prepare($conn,"DELETE FROM ambulance WHERE id=?"); $d=(int)$_GET['delete'];
    mysqli_stmt_bind_param($st,'i',$d); mysqli_stmt_execute($st);
    header("Location: ambulance.php"); exit();
}
$records=mysqli_query($conn,"SELECT * FROM ambulance ORDER BY created_at DESC");
$labels=explode(',','Vehicle No.,Driver License,Base Location');
$fields=explode(',','vehicle_number,driver_license,base_location');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Ambulance Units — Admin</title><link rel="stylesheet" href="../assets/css/style.css?v=5.0"></head>
<body class="login-bg-admin">
<div class="login-bg-overlay"></div>
<nav class="navbar"><div class="nav-brand"><svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M21 12h1"/><path d="M18.5 4.5 18 5"/><path d="M2 12h1"/><path d="M12 2v1"/><path d="m4.929 4.929.707.707"/><path d="M12 12v6"/></svg> SERAAS</div>
<div class="nav-links">
    <button id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Toggle Theme" style="padding:6px;margin-right:8px;">
      <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
      <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
    </button><a href="../logout.php" class="btn btn-ghost btn-sm">Logout</a></div></nav>
<div class="layout">
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-avatar" style="background:linear-gradient(135deg,var(--purple),#7c3aed);">🛡️</div>
      <div class="sidebar-name"><?=htmlspecialchars($_SESSION['admin_name'])?></div>
      <div class="sidebar-role">System Administrator</div>
    </div>
    <div class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <a href="dashboard.php"><span class="nav-icon">📊</span> Dashboard</a>
      <a href="requests.php"><span class="nav-icon">🚨</span> All Requests</a>
      <div class="nav-section-label" style="margin-top:8px;">Management</div>
      <a href="users.php"><span class="nav-icon">👥</span> Users</a>
      <a href="police.php"><span class="nav-icon">🚔</span> Police</a>
      <a href="ambulance.php" class="active"><span class="nav-icon">🚑</span> Ambulance</a>
      <a href="hospital.php"><span class="nav-icon">🏥</span> Hospital</a>
    </div>
    <div class="sidebar-footer"><a href="../logout.php"><span class="nav-icon">🚪</span> Logout</a></div>
  </div>
  <div class="main-content">
    <div class="page-header">
      <div class="page-header-left"><h1>🚑 Manage Ambulance Units</h1><p><?=mysqli_num_rows($records)?> records</p></div>
    </div>
    <?php if($error): ?><div class="alert alert-error" data-auto-dismiss="5000"><span class="alert-icon">❌</span><span><?=htmlspecialchars($error)?></span></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success" data-auto-dismiss="5000"><span class="alert-icon">✅</span><span><?=htmlspecialchars($success)?></span></div><?php endif; ?>

    <div class="card" style="margin-bottom:20px;">
      <div class="card-header"><div class="card-title">➕ Add New Record</div></div>
      <form method="POST"><?=csrf_field()?>
        <div class="form-grid-2">
          <div class="form-group" style="margin:0"><label>Name *</label><input type="text" name="name" placeholder="Full name" required></div>
          <div class="form-group" style="margin:0"><label>Email *</label><input type="email" name="email" placeholder="Email" required></div>
          <div class="form-group" style="margin:0"><label>Phone *</label><input type="text" name="phone" placeholder="Phone" required></div>
          <div class="form-group" style="margin:0"><label><?=$labels[0]?></label><input type="text" name="field1" placeholder="<?=$labels[0]?>"></div>
          <div class="form-group" style="margin:0"><label><?=$labels[1]?></label><input type="text" name="field2" placeholder="<?=$labels[1]?>"></div>
          <div class="form-group" style="margin:0"><label><?=$labels[2]?></label><input type="text" name="field3" placeholder="<?=$labels[2]?>"></div>
          <div class="form-group" style="margin:0"><label>Password *</label><input type="password" name="password" placeholder="Password" required></div>
        </div>
        <button type="submit" name="add_record" class="btn btn-primary" style="margin-top:12px;">➕ Add Record</button>
      </form>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th>
            <?php foreach($labels as $lbl): ?><th><?=$lbl?></th><?php endforeach; ?>
            <th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php $i=1; while($r=mysqli_fetch_assoc($records)): ?>
          <tr>
            <td><?=$i++?></td>
            <td><strong><?=htmlspecialchars($r['name'])?></strong></td>
            <td style="font-size:.85rem;"><?=htmlspecialchars($r['email'])?></td>
            <td><?=htmlspecialchars($r['phone'])?></td>
            <?php foreach($fields as $f): ?><td style="font-size:.83rem;"><?=htmlspecialchars($r[$f]??'—')?></td><?php endforeach; ?>
            <td><span class="badge badge-<?=$r['status']?>"><?=ucfirst($r['status'])?></span></td>
            <td style="white-space:nowrap;">
              <a href="ambulance.php?toggle=<?=$r['id']?>" class="btn btn-warning btn-xs"><?=$r['status']==='active'?'🔒 Disable':'🔓 Enable'?></a>
              <a href="ambulance.php?delete=<?=$r['id']?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this record?')">🗑</a>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/app.js?v=2.1"></script>
</body></html>
