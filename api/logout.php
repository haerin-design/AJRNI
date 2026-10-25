<?php
session_start();
$_SESSION = [];
session_destroy();
header('Content-Type: application/json; charset=utf-8');
echo json_encode(["status" => "success"]);
?>