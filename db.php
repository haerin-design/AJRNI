<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ajrni_db";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "فشل الاتصال بقاعدة البيانات: " . $conn->connect_error]);
    exit();
}
?>