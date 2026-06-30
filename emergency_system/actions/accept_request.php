<?php
require_once '../db.php';
global $conn; // Added to resolve IDE "Undefined variable" warnings
$role = $role_id = '';
if (isset($_SESSION['admin_id']))         { $role='admin';     $role_id=$_SESSION['admin_id']; }
elseif (isset($_SESSION['police_id']))    { $role='police';    $role_id=$_SESSION['police_id']; }
elseif (isset($_SESSION['ambulance_id'])){ $role='ambulance'; $role_id=$_SESSION['ambulance_id']; }
elseif (isset($_SESSION['hospital_id'])) { $role='hospital';  $role_id=$_SESSION['hospital_id']; }
else { header("Location: ../login.php"); exit(); }

$backs = ['admin'=>'../admin/requests.php','police'=>'../police/dashboard.php',
          'ambulance'=>'../ambulance/dashboard.php','hospital'=>'../hospital/dashboard.php'];
$back = $backs[$role];

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: $back?msg=error"); exit(); }

$stmt = mysqli_prepare($conn,
    "UPDATE emergency_requests SET status='Accepted',accepted_by_id=?,accepted_by_role=?,accepted_at=NOW()
     WHERE id=? AND status='Pending'");
mysqli_stmt_bind_param($stmt,'isi',$role_id,$role,$id);
mysqli_stmt_execute($stmt);
header("Location: $back?msg=" . (mysqli_stmt_affected_rows($stmt)>0 ? 'accepted' : 'error'));
exit();
