<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$products = [];
$result = $conn->query("SELECT id, name, category, price, image, description FROM products ORDER BY id DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode(['success' => true, 'products' => $products], JSON_UNESCAPED_UNICODE);