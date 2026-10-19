<?php
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

if (file_exists('../db.php')) {
    require_once '../db.php';
} elseif (file_exists('db.php')) {
    require_once 'db.php';
} else {
    $conn = @new mysqli("localhost", "root", "", "ajrni_db");
}

if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'notifications' => []]);
    exit;
}

$conn->set_charset("utf8mb4");

// جلب آخر التنبيهات من جدول messages
$res = $conn->query("SELECT * FROM messages ORDER BY id DESC LIMIT 10");
$notifications = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $notifications[] = [
            'id'         => $row['id'],
            'subject'    => $row['subject'],
            'message'    => $row['message'],
            'created_at' => $row['created_at']
        ];
    }
}

echo json_encode([
    'success'       => true,
    'notifications' => $notifications
]);
?>