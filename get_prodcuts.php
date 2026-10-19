<?php
header('Content-Type: application/json; charset=utf-8');

if (file_exists('db.php')) {
    require_once 'db.php';
} else {
    $conn = new mysqli("localhost", "root", "", "ajrni_db");
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'فشل الاتصال بقاعدة البيانات']);
    exit;
}

$conn->set_charset("utf8mb4");

// جلب المنتجات المضافة في قاعدة البيانات
$sql = "SELECT id, name, category, price, image, description FROM products ORDER BY id DESC";
$result = $conn->query($sql);

$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
// جلب آخر التنبيهات مع استجابة المنتجات
$notifRes = $conn->query("SELECT * FROM messages ORDER BY id DESC LIMIT 5");
$notifications = [];
if ($notifRes) {
    while($n = $notifRes->fetch_assoc()) {
        $notifications[] = $n;
    }
}

// دمجها في الـ JSON النهائي
echo json_encode([
    'products' => $products, // مصفوفتك الحالية
    'notifications' => $notifications
]);
echo json_encode(['success' => true, 'products' => $products]);