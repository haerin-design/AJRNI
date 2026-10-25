<?php
session_start();
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

$email = $_SESSION['user_email'] ?? '';
$notifications = [];

if ($email !== '') {
    $stmt = $conn->prepare("SELECT id, subject, message, created_at FROM messages WHERE type = 'notification' AND email = ? ORDER BY id DESC LIMIT 10");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();
    }
}

echo json_encode(['success' => true, 'notifications' => $notifications]);
?>