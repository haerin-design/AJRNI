<?php
header('Content-Type: application/json; charset=utf-8');

if (file_exists('db.php')) {
    require_once 'db.php';
} else {
    $conn = new mysqli("localhost", "root", "", "ajrni_db");
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed']);
    exit;
}

$conn->set_charset("utf8mb4");

$sql = "SELECT o.*, COALESCE(u.full_name, 'مستأجر مسجل') AS customer_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.id DESC";
$result = $conn->query($sql);

$orders = [];
$totalDeposit = 0;
$activeCount = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
        $totalDeposit += floatval($row['subtotal']);
        $activeCount++;
    }
}

echo json_encode([
    'success' => true,
    'orders' => $orders,
    'activeCount' => $activeCount,
    'totalDeposit' => $totalDeposit
]);