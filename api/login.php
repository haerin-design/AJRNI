<?php
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

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "يرجى كتابة البريد الإلكتروني وكلمة المرور."]);
    exit();
}

// استعلام التحقق من المستخدم في جدول users (حسب هيكل قاعدة البيانات الفعلية)
$stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE email = ? AND password = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "خطأ في تجهيز الاستعلام: " . $conn->error]);
    exit();
}

$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // حفظ بيانات المستخدم في الجلسة (Session) لترتبط بصفحة المؤجر تلقائياً
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        "status" => "success",
        "message" => "تم تسجيل الدخول بنجاح!",
        "user" => $user
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "البريد الإلكتروني أو كلمة المرور غير صحيحة."]);
}

$stmt->close();
$conn->close();
?>