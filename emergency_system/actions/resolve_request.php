<?php
require_once '../db.php';
global $conn; // Added to resolve IDE "Undefined variable" warnings
$role = '';
if (isset($_SESSION['admin_id']))         $role='admin';
elseif (isset($_SESSION['police_id']))    $role='police';
elseif (isset($_SESSION['ambulance_id'])) $role='ambulance';
elseif (isset($_SESSION['hospital_id']))  $role='hospital';
else { header("Location: ../login.php"); exit(); }

$backs = ['admin'=>'../admin/requests.php','police'=>'../police/dashboard.php',
          'ambulance'=>'../ambulance/dashboard.php','hospital'=>'../hospital/dashboard.php'];
$back = $backs[$role];

$id   = intval($_GET['id'] ?? 0);
$note = trim($_POST['note'] ?? '');
if (!$id) { header("Location: $back?msg=error"); exit(); }

$stmt = mysqli_prepare($conn,
    "UPDATE emergency_requests SET status='Resolved',resolved_at=NOW(),responder_note=?
     WHERE id=? AND status='Accepted'");
mysqli_stmt_bind_param($stmt,'si',$note,$id);
mysqli_stmt_execute($stmt);
header("Location: $back?msg=" . (mysqli_stmt_affected_rows($stmt)>0 ? 'resolved' : 'error'));
exit();
