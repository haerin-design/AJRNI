<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "يرجى كتابة البريد الإلكتروني وكلمة المرور."]);
    exit();
}

$stmt = $conn->prepare("SELECT id, full_name, email, role, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
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

    echo json_encode([
        "status" => "success",
        "message" => "تم تسجيل الدخول بنجاح!",
        "user" => $row
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "البريد الإلكتروني أو كلمة المرور غير صحيحة."]);
}
?>