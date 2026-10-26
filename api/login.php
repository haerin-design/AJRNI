<?php
<<<<<<< HEAD
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';
=======
// بدء الجلسة لتخزين بيانات المستخدم المسجل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// الاتصال بقاعدة البيانات (مع مراعاة المسار الصحيح للملف)
if (file_exists('../db.php')) {
    require_once '../db.php';
} elseif (file_exists('db.php')) {
    require_once 'db.php';
} else {
    echo json_encode(["status" => "error", "message" => "خطأ: ملف الاتصال بقاعدة البيانات غير موجود."]);
    exit();
}
>>>>>>> 0af3c53fec2d35f915cf69abd52cf6495b5c6bf9

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "يرجى كتابة البريد الإلكتروني وكلمة المرور."]);
    exit();
}

<<<<<<< HEAD
$stmt = $conn->prepare("SELECT id, full_name, email, role, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
=======
// استعلام التحقق من المستخدم في جدول users (حسب هيكل قاعدة البيانات الفعلية)
$stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE email = ? AND password = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "خطأ في تجهيز الاستعلام: " . $conn->error]);
    exit();
}

$stmt->bind_param("ss", $email, $password);
>>>>>>> 0af3c53fec2d35f915cf69abd52cf6495b5c6bf9
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$ok = false;

if ($row) {
    $stored = $row['password'];

    if (password_verify($password, $stored)) {
        // كلمة مرور مشفّرة وصحيحة
        $ok = true;
    } elseif (hash_equals($stored, $password)) {
        // حساب قديم بكلمة مرور غير مشفّرة: ندخله ونشفّر كلمته الحين
        $ok = true;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->bind_param("si", $newHash, $row['id']);
        $upd->execute();
        $upd->close();
    }
}

if ($ok) {
    unset($row['password']); // ما نرسل الباسورد للمتصفح

    session_regenerate_id(true);
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['user_email'] = $row['email'];
    $_SESSION['user_role'] = $row['role'];
    $_SESSION['user_name'] = $row['full_name'];

<<<<<<< HEAD
=======
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // حفظ بيانات المستخدم في الجلسة (Session) لترتبط بصفحة المؤجر تلقائياً
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];

>>>>>>> 0af3c53fec2d35f915cf69abd52cf6495b5c6bf9
    echo json_encode([
        "status" => "success",
        "message" => "تم تسجيل الدخول بنجاح!",
        "user" => $row
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "البريد الإلكتروني أو كلمة المرور غير صحيحة."]);
}
<<<<<<< HEAD
=======

$stmt->close();
$conn->close();
>>>>>>> 0af3c53fec2d35f915cf69abd52cf6495b5c6bf9
?>