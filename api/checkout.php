<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';
mysqli_report(MYSQLI_REPORT_OFF);

try {
    $conn->set_charset("utf8mb4");

    // السيرفر يعرف المستخدم من الجلسة، مو من المتصفح
    $userId = intval($_SESSION['user_id'] ?? 0);
    if ($userId === 0) {
        echo json_encode(["status" => "error", "code" => "auth", "message" => "لازم تسجّل دخول أو تنشئ حساب أولاً."]);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $cart = $data['cart'] ?? [];

    if (empty($cart) || !is_array($cart)) {
        echo json_encode(["status" => "error", "message" => "السلة فارغة."]);
        exit();
    }

    $shippingMethod = trim($data['shippingMethod'] ?? 'توصيل سريع');
    $paymentMethod  = trim($data['paymentMethod'] ?? 'بطاقة مدى / ائتمانية');
    $shippingFee    = 35; // ثابتة من السيرفر

    // نحسب المبالغ من السلة نفسها (ما نصدق المجموع الجاي من المتصفح)
    $subtotal = 0;
    $discountBase = 0;
    foreach ($cart as $item) {
        $price   = max(0, floatval($item['price'] ?? 0));
        $deposit = max(0, floatval($item['deposit'] ?? 0));
        $qty     = max(1, intval($item['quantity'] ?? 1));
        $subtotal     += $price * $qty;
        $discountBase += max(0, $price - $deposit) * $qty; // الخصم بدون التأمين المسترد
    }

    $discountCode    = strtoupper(trim($data['discountCode'] ?? ''));
    $validCodes      = ["AJRNI15" => 15];
    $discountPercent = $validCodes[$discountCode] ?? 0;
    $discountAmount  = round($discountBase * $discountPercent / 100, 2);
    $total           = $subtotal - $discountAmount + $shippingFee;

    $status = 'نشط';
    $orderStmt = $conn->prepare("INSERT INTO orders (user_id, subtotal, shipping_fee, total_amount, shipping_method, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$orderStmt) {
        echo json_encode(["status" => "error", "message" => "خطأ في الاستعلام: " . $conn->error]);
        exit();
    }
    $orderStmt->bind_param("idddsss", $userId, $subtotal, $shippingFee, $total, $shippingMethod, $paymentMethod, $status);

    if ($orderStmt->execute()) {
        $contractId = $conn->insert_id;
        echo json_encode(["status" => "success", "message" => "تم إستلام طلبك بنجاح! رقم العقد: #" . $contractId]);
    } else {
        echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء حفظ الطلب: " . $orderStmt->error]);
    }
} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => "خطأ: " . $e->getMessage()]);
}
?>