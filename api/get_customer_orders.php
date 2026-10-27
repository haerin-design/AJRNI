<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

<<<<<<< HEAD
$userId = intval($_SESSION['user_id'] ?? 0);
$role   = strtolower($_SESSION['user_role'] ?? '');
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$seller_id = $_SESSION['user_id'];

if (file_exists('db.php')) {
    require_once 'db.php';
} else {
    $conn = new mysqli("localhost", "root", "", "ajrni_db");
}
>>>>>>> 0af3c53fec2d35f915cf69abd52cf6495b5c6bf9

if ($userId === 0) {
    echo json_encode(['success' => false, 'error' => 'غير مسجل دخول']);
    exit;
}

$isStaff = in_array($role, ['seller', 'مؤجر', 'admin', 'أدمن', 'ادمن']);

<<<<<<< HEAD
$base = "SELECT o.*, COALESCE(u.full_name, 'مستأجر مسجل') AS customer_name
         FROM orders o LEFT JOIN users u ON o.user_id = u.id";

if ($isStaff) {
    $stmt = $conn->prepare($base . " ORDER BY o.id DESC");
} else {
    $stmt = $conn->prepare($base . " WHERE o.user_id = ? ORDER BY o.id DESC");
    $stmt->bind_param("i", $userId);
}

=======
$sql = "SELECT o.*, COALESCE(u.full_name, 'مستأجر مسجل') AS customer_name, p.name AS product_name 
        FROM orders o 
        JOIN products p ON o.product_id = p.id
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE p.seller_id = ?
        ORDER BY o.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
>>>>>>> 0af3c53fec2d35f915cf69abd52cf6495b5c6bf9
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
<<<<<<< HEAD
$totalDeposit = 0;
$activeCount = 0;

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
    if ($row['status'] === 'نشط') {
        $activeCount++;
        $totalDeposit += floatval($row['subtotal']);
=======
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
>>>>>>> 0af3c53fec2d35f915cf69abd52cf6495b5c6bf9
    }
}

echo json_encode([
    'success' => true,
<<<<<<< HEAD
    'orders' => $orders,
    'activeCount' => $activeCount,
    'totalDeposit' => $totalDeposit
]);
=======
    'orders' => $orders
]);
exit;
>>>>>>> 0af3c53fec2d35f915cf69abd52cf6495b5c6bf9
?>