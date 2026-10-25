<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$userId = intval($_SESSION['user_id'] ?? 0);
$role   = strtolower($_SESSION['user_role'] ?? '');

if ($userId === 0) {
    echo json_encode(['success' => false, 'error' => 'غير مسجل دخول']);
    exit;
}

$isStaff = in_array($role, ['seller', 'مؤجر', 'admin', 'أدمن', 'ادمن']);

$base = "SELECT o.*, COALESCE(u.full_name, 'مستأجر مسجل') AS customer_name
         FROM orders o LEFT JOIN users u ON o.user_id = u.id";

if ($isStaff) {
    $stmt = $conn->prepare($base . " ORDER BY o.id DESC");
} else {
    $stmt = $conn->prepare($base . " WHERE o.user_id = ? ORDER BY o.id DESC");
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$result = $stmt->get_result();

$orders = [];
$totalDeposit = 0;
$activeCount = 0;

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
    if ($row['status'] === 'نشط') {
        $activeCount++;
        $totalDeposit += floatval($row['subtotal']);
    }
}

echo json_encode([
    'success' => true,
    'orders' => $orders,
    'activeCount' => $activeCount,
    'totalDeposit' => $totalDeposit
]);
?>