<?php
header('Content-Type: application/json; charset=utf-8');

// استقبال الإيميل أو الـ ID أياً كان القادم من المتصفح
$userEmail = $_GET['email'] ?? '';
$userId = $_GET['user_id'] ?? '';

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

$orders = [];
$activeCount = 0;
$totalDeposit = 0;
$realUserId = 0;

// 1. لو وصلنا الإيميل، نطلع الـ ID حق المستخدم من القاعدة
if (!empty($userEmail)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $realUserId = intval($row['id']);
    }
} 
// 2. لو وصل الـ ID مباشرة، نعتمدة
elseif (!empty($userId)) {
    $realUserId = intval($userId);
}

// 3. لو عرفنا الـ ID، نجيب طلباته هو وبس!
if ($realUserId > 0) {
    $sql = "SELECT id, subtotal, total_amount, payment_method, status, created_at 
            FROM orders 
            WHERE user_id = ? 
            ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $realUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
            if ($row['status'] === 'نشط') {
                $activeCount++;
            }
            $totalDeposit += floatval($row['subtotal']);
        }
    }
}

echo json_encode([
    'success' => true,
    'activeCount' => $activeCount,
    'totalDeposit' => $totalDeposit,
    'orders' => $orders
]);
exit;
?>