<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';
mysqli_report(MYSQLI_REPORT_OFF);

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

// نقبل نوعين فقط (ما نثق بالقيمة اللي جاية من المتصفح)
$role = (($data['role'] ?? '') === 'seller') ? 'seller' : 'customer';

if (empty($fullName) || empty($phone) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "يرجى تعبئة جميع الحقول المطلوبة."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "البريد الإلكتروني غير صحيح."]);
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

// هل الإيميل مسجل قبل؟
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "البريد الإلكتروني مسجل مسبقاً."]);
    exit();
}
$checkStmt->close();

// نشفّر كلمة المرور قبل الحفظ
$hash = password_hash($password, PASSWORD_DEFAULT);

$insertStmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
$insertStmt->bind_param("sssss", $fullName, $email, $phone, $hash, $role);

if ($insertStmt->execute()) {
    $newId = $conn->insert_id;

    // نسجّل دخوله مباشرة
    session_regenerate_id(true);
    $_SESSION['user_id'] = $newId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_name'] = $fullName;

    echo json_encode([
        "status" => "success",
        "message" => "تم إنشاء الحساب بنجاح!",
        "user" => ["id" => $newId, "full_name" => $fullName, "email" => $email, "role" => $role]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء حفظ البيانات."]);
}
$insertStmt->close();
?>