  <?php
  $conn = new mysqli("localhost", "root", "", "ajrni_db");

  if ($conn->connect_error) {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(["status" => "error", "message" => "فشل الاتصال بقاعدة البيانات"]);
      exit();
  }

  $conn->set_charset("utf8mb4");
  ?>