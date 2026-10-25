<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

mysqli_report(MYSQLI_REPORT_OFF); // نعالج الأخطاء بأنفسنا ونرجعها JSON

try {
    $conn->set_charset("utf8mb4"); // مهم للنص العربي

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
    // لازم يكون المستخدم مسجل
    if (empty($userEmail)) {
        echo json_encode(["status" => "error", "message" => "لازم تسجّل دخول أو تنشئ حساب أولاً."]);
        exit();
    }

    $userStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $userStmt->bind_param("s", $userEmail);
    $userStmt->execute();
    $res = $userStmt->get_result();

    if (!$res || $res->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "الحساب غير موجود، سجّل حساب جديد أولاً."]);
        exit();
    }
    $userId = $res->fetch_assoc()['id'];

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