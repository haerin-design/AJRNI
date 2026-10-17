<?php
header('Content-Type: application/json');
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "بيانات غير صالحة"]);
    exit();
}

$fullName = trim($data['fullName'] ?? '');
$phone = trim($data['phone'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';
$role = $data['role'] ?? 'customer';

if (empty($fullName) || empty($phone) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "يرجى تعبئة جميع الحقول المطلوبة."]);
    exit();
}

if ($password !== $confirmPassword) {
    echo json_encode(["status" => "error", "message" => "كلمة المرور وتأكيدها غير متطابقين."]);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(["status" => "error", "message" => "كلمة المرور يجب أن تكون 6 خانات على الأقل."]);
    exit();
}

// التحقق من وجود البريد مسبقاً
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "البريد الإلكتروني مسجل مسبقاً."]);
    exit();
}
$checkStmt->close();

// حفظ المستخدم الجديد
$insertStmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
$insertStmt->bind_param("sssss", $fullName, $email, $phone, $password, $role);

if ($insertStmt->execute()) {
    echo json_encode(["status" => "success", "message" => "تم إنشاء الحساب بنجاح!"]);
} else {
    echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء حفظ البيانات."]);
}
$insertStmt->close();
?>