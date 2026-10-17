<?php
header('Content-Type: application/json');
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "يرجى كتابة البريد الإلكتروني وكلمة المرور."]);
    exit();
}

$stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "message" => "تم تسجيل الدخول بنجاح!",
        "user" => $user
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "البريد الإلكتروني أو كلمة المرور غير صحيحة."]);
}
$stmt->close();
?>