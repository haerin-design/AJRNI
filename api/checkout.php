<?php
header('Content-Type: application/json');
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

$userEmail = $data['userEmail'] ?? '';
$cart = $data['cart'] ?? [];
$shippingMethod = $data['shippingMethod'] ?? 'توصيل سريع';
$paymentMethod = $data['paymentMethod'] ?? 'بطاقة مدى / ائتمانية';
$subtotal = floatval($data['subtotal'] ?? 0);
$shippingFee = floatval($data['shippingFee'] ?? 35);
$total = $subtotal + $shippingFee;

if (empty($cart)) {
    echo json_encode(["status" => "error", "message" => "السلة فارغة."]);
    exit();
}

// البحث عن معرف المستخدم بالبريد الإلكتروني
$userId = 1; // الافتراضي للزوار غير المسجلين
if (!empty($userEmail)) {
    $userStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $userStmt->bind_param("s", $userEmail);
    $userStmt->execute();
    $res = $userStmt->get_result();
    if ($res->num_rows > 0) {
        $userId = $res->fetch_assoc()['id'];
    }
}

// إنشاء الطلب
$orderStmt = $conn->prepare("INSERT INTO orders (user_id, subtotal, shipping_fee, total_amount, shipping_method, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
$orderStmt->bind_param("idddss", $userId, $subtotal, $shippingFee, $total, $shippingMethod, $paymentMethod);

if ($orderStmt->execute()) {
    echo json_encode(["status" => "success", "message" => "تم إستلام طلبك بنجاح! شكراً لتسوقك عبر منصة أجرني."]);
} else {
    echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء حفظ الطلب."]);
}
?>