<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: json/application; charset=utf-8'); // أو application/json
header('Content-Type: application/json; charset=utf-8');
require_once '../db.php';

$sql = "SELECT * FROM products";
$result = $conn->query($sql);

$products = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $products], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["status" => "error", "message" => "لا توجد منتجات متاحة حالياً."], JSON_UNESCAPED_UNICODE);
}
$conn->close();
?>