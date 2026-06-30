<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'] ?? '';
    $value = trim($_POST['value'] ?? '');

    if (empty($field) || empty($value)) {
        echo json_encode(['exists' => false]);
        exit;
    }

    // Determine table based on who's checking, default to users table
    // For registration, we only care about 'users' table.

    $allowed_fields = ['email', 'phone'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['exists' => false]);
        exit;
    }

    $query = "SELECT id FROM users WHERE $field = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $value);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['exists' => false]);
}
