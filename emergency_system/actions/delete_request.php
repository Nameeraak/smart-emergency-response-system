<?php
require_once '../db.php';
global $conn; // Added to resolve IDE "Undefined variable" warnings
if (!isset($_SESSION['admin_id'])) { header("Location: ../login.php"); exit(); }
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $st = mysqli_prepare($conn,"DELETE FROM emergency_requests WHERE id=?");
    mysqli_stmt_bind_param($st,'i',$id);
    mysqli_stmt_execute($st);
}
header("Location: ../admin/requests.php?msg=deleted"); exit();
