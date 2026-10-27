<?php
// منع أي مخرجات نصية غير مرغوب فيها
error_reporting(0);
@ini_set('display_errors', 0);
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

// بيانات الاتصال بقاعدة البيانات الصحيحة
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ajrni_db';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "فشل الاتصال بقاعدة البيانات"]);
    exit();
}
$conn->set_charset("utf8mb4");

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $userEmail = trim($data['userEmail'] ?? '');
    $cart = $data['cart'] ?? [];
    $shippingMethod = trim($data['shippingMethod'] ?? 'توصيل سريع');
    $paymentMethod = trim($data['paymentMethod'] ?? 'بطاقة مدى / ائتمانية');
    $subtotal = floatval($data['subtotal'] ?? 0);
    $shippingFee = floatval($data['shippingFee'] ?? 35);
    $discountCode = strtoupper(trim($data['discountCode'] ?? ''));

    if (empty($cart)) {
        echo json_encode(["status" => "error", "message" => "السلة فارغة."]);
        exit();
    }

    if (empty($userEmail)) {
        echo json_encode(["status" => "error", "message" => "لازم تسجّل دخول أو تنشئ حساب أولاً."]);
        exit();
    }

    // البحث عن المستخدم في جدول users للحصول على ID الحقيقي
    $userId = 1; // قيمة احتياطية
    $userStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($userStmt) {
        $userStmt->bind_param("s", $userEmail);
        $userStmt->execute();
        $res = $userStmt->get_result();
        if ($res && $res->num_rows > 0) {
            $userId = intval($res->fetch_assoc()['id']);
        }
    }

    // حساب الخصم لو وجد (رمز AJRNI15 يعطي 15% خصم على السلع بدون التأمين)
    $validCodes = ["AJRNI15" => 15];
    $discountPercent = $validCodes[$discountCode] ?? 0;
    
    $discountBase = 0;
    foreach ($cart as $item) {
        $itemPrice = floatval($item['price'] ?? 0);
        $itemDeposit = floatval($item['deposit'] ?? 0);
        $itemQty = intval($item['quantity'] ?? 1);
        $discountBase += max(0, $itemPrice - $itemDeposit) * $itemQty;
    }
    
    $discountAmount = round($discountBase * $discountPercent / 100, 2);
    $total = $subtotal - $discountAmount + $shippingFee;

    $status = 'نشط';
    $orderStmt = $conn->prepare("INSERT INTO orders (user_id, subtotal, shipping_fee, total_amount, shipping_method, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$orderStmt) {
        echo json_encode(["status" => "error", "message" => "خطأ في الاستعلام: " . $conn->error]);
        exit();
    }

    $orderStmt->bind_param("idddsss", $userId, $subtotal, $shippingFee, $total, $shippingMethod, $paymentMethod, $status);

    if ($orderStmt->execute()) {
        $orderId = $conn->insert_id;
        echo json_encode(["status" => "success", "message" => "تم إستلام طلبك بنجاح! رقم الطلب: #" . $orderId]);
    } else {
        echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء حفظ الطلب: " . $orderStmt->error]);
    }
} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => "خطأ في السيرفر: " . $e->getMessage()]);
}
exit();
?>