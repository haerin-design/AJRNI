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
    echo json_encode(['success' => false, 'error' => 'تعذر الاتصال بقاعدة البيانات']);
    exit;
}

$conn->set_charset("utf8mb4");

$data = json_decode(file_get_contents('php://input'), true);
$orderId = intval($data['order_id'] ?? 0);
$action  = $data['action'] ?? '';

if (!$orderId || !in_array($action, ['release', 'damage'])) {
    echo json_encode(['success' => false, 'error' => 'بيانات الطلب غير صالحة']);
    exit;
}

$contractNo = "AGR-2026-" . str_pad($orderId, 3, '0', STR_PAD_LEFT);

if ($action === 'release') {
    $status  = 'مكتمل';
    $subject = "إفراج عن التأمين للعقد #$contractNo";
    $msgText = "عزيزي المستأجر، تم فحص المنتج المسترجع بنجاح وإرجاع مبلغ التأمين لحسابك.";
} else {
    $status  = 'نزاع - إثبات ضرر';
    $subject = "تنبيه ضرر للعقد #$contractNo";
    $msgText = "عزيزي المستأجر، تم تسجيل إثبات ضرر على السلعة للعقد #$contractNo واقتطاع القيمة من التأمين.";
}

// 1. تحديث حالة الطلب في جدول orders
$conn->query("UPDATE orders SET status = '$status' WHERE id = $orderId");

// 2. جلب بيانات المستأجر المرتبط بالطلب
$custName  = 'المستأجر';
$custEmail = 'customer@ajrni.com';

$orderRes = $conn->query("SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = $orderId LIMIT 1");
if ($orderRes && $row = $orderRes->fetch_assoc()) {
    if (!empty($row['full_name'])) $custName = $row['full_name'];
    if (!empty($row['email'])) $custEmail = $row['email'];
}

// 3. إدخال التنبيه في جدول messages بالأعمدة الموجودة فعلياً
$safeName    = $conn->real_escape_string($custName);
$safeEmail   = $conn->real_escape_string($custEmail);
$safeSubject = $conn->real_escape_string($subject);
$safeMsg     = $conn->real_escape_string($msgText);

$conn->query("INSERT INTO messages (name, email, subject, message, created_at) 
              VALUES ('$safeName', '$safeEmail', '$safeSubject', '$safeMsg', NOW())");

echo json_encode([
    'success' => true,
    'message' => 'تم تنفيذ الإجراء وتحديث العقد وإرسال التنبيه بنجاح!'
]);
?>