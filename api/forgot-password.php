<?php
header('Content-Type: application/json');
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

$phone = trim($data['phone'] ?? '');

if (empty($phone)) {
    echo json_encode(["status" => "error", "message" => "يرجى كتابة رقم الهاتف."]);
    exit();
}

// التحقق من وجود رقم الهاتف في قاعدة البيانات
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "message" => "تم العثور على الحساب! تم إرسال رمز استعادة كلمة المرور إلى رقم جوالك."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "عذراً، رقم الهاتف غير مسجل لدينا."
    ]);
}
$stmt->close();
?>