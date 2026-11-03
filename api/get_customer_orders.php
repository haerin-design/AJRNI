<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

// تضمين ملف الاتصال بقاعدة البيانات حسب مساره المحتمل
if (file_exists('../db.php')) {
    require_once '../db.php';
} elseif (file_exists('db.php')) {
    require_once 'db.php';
} else {
    $conn = new mysqli("localhost", "root", "", "ajrni_db");
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed', 'orders' => []]);
    exit();
}

// جلب رقم المستخدم من الجلسة، مع إمكانية التراجع للرقم 22 للاختبار المؤقت إذا لزم
$userId = intval($_SESSION['user_id'] ?? $_SESSION['userId'] ?? 22);

if ($userId === 0) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized', 'orders' => []]);
    exit();
}

// الاستعلام المتوافق تماماً مع أعمدة جدول orders الفعلية (بدون product_id)
$sql = "SELECT o.*, COALESCE(u.full_name, 'مستأجر مسجل') AS customer_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.user_id = ? 
        ORDER BY o.id DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Query preparation failed', 'orders' => []]);
    exit();
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
$totalDeposit = 0;
$activeCount = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
        if (isset($row['status']) && $row['status'] === 'نشط') {
            $activeCount++;
            $totalDeposit += floatval($row['subtotal'] ?? 0);
        }
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'orders' => $orders,
    'activeCount' => $activeCount,
    'totalDeposit' => $totalDeposit
]);
exit();
?>