<?php
header('Content-Type: application/json');
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(["status" => "error", "message" => "يرجى تعبئة جميع الحقول."]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $subject, $message);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "تم إرسال رسالتك بنجاح! سنترد عليك قريباً."]);
} else {
    echo json_encode(["status" => "error", "message" => "فشل إرسال الرسالة."]);
}
$stmt->close();
?>