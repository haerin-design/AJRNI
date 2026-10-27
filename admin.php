<?php
// الاتصال بقاعدة البيانات ajrni_db
$conn = new mysqli("localhost", "root", "", "ajrni_db");

// التأكد من نجاح الاتصال
if ($conn->connect_error) {
   die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
// معالجة تحديث حالة منتجات المؤجرين
if (isset($_POST['update_product_status'])) {
    $product_id = $_POST['product_id'];
    $new_status = $_POST['status']; 
    $stmt = $conn->prepare("UPDATE products SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $product_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// معالجة حذف المستخدم
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    $delete_query = "DELETE FROM users WHERE id = $user_id";
    mysqli_query($conn, $delete_query);
    header("Location: admin.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | AJRNI</title>
    
    
    <link rel="stylesheet" href="style.css">

    <!-- خط وتنسيقات الأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        
        body {
            background-color: var(--light-blue);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background-color: var(--dark-navy);
            color: var(--white);
            padding: 20px 15px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar .brand {
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar-menu a {
            color: var(--light-blue);
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--navy);
            color: var(--white);
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--white);
            padding: 15px 25px;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid var(--border);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: var(--light-blue);
            color: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .section-box {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-add {
            background-color: var(--navy);
            color: var(--white);
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
        }

        th {
            background-color: var(--light-blue);
            color: var(--navy);
            font-size: 14px;
            font-weight: 700;
        }

        .empty-row {
            text-align: center;
            color: var(--gray);
            padding: 30px;
            font-size: 14px;
        }

        table th, table td {
    text-align: center !important;
    vertical-align: middle;
}
    </style>
</head>
<body>

    <!-- القائمة الجانبية -->
    <aside class="sidebar">
    <div class="brand">
    <i class="fa-solid fa-box-open"></i>
    <span>AJRNI</span>
</div>
        <ul class="sidebar-menu">
            <li><a href="#dashboard" class="active"><i class="fa-solid fa-chart-line"></i> لوحة التحكم</a></li>
            <li><a href="#products"><i class="fa-solid fa-box-open"></i> إدارة المنتجات</a></li>
            <li><a href="#orders"><i class="fa-solid fa-file-invoice"></i> الطلبات</a></li>
            <li><a href="index.html"><i class="fa-solid fa-store"></i> العودة للمتجر</a></li>
        </ul>
    </aside>

    <!-- المحتوى الرئيسي -->
    <main class="main-content">
        
        <!-- الهيدر العلوي -->
        <div class="header-bar">
            <div>
                <h2>مرحباً، <span id="admin-name"></span> </h2>
                <span style="color: var(--gray); font-size: 13px;">نظام إدارة متجر أجرني</span>
            </div>
            <a href="login.html" style="color: #e74c3c; text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 6px; background-color: #fdf2f2;">
                <i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج
            </a>
        </div>

        <!-- كروت الإحصائيات -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
                <div>
                    <h3 style="color: var(--navy);">0 ر.س</h3>
                    <p style="color: var(--gray); font-size: 13px;">إجمالي الأرباح</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-shopping-bag"></i></div>
                <div>
                    <h3 style="color: var(--navy);">0</h3>
                    <p style="color: var(--gray); font-size: 13px;">الطلبات الجديدة</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div>
                    <h3 style="color: var(--navy);">0</h3>
                    <p style="color: var(--gray); font-size: 13px;">المنتجات المتاحة</p>
                </div>
            </div>
        </div>

        <!-- جدول إدارة المنتجات -->
        <div class="section-box" id="products">
            <div class="section-header">
                <h3 style="color: var(--navy);">قائمة المنتجات والعروض</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>اسم المنتج</th>
                        <th>التصنيف</th>
                        <th>السعر</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . (isset($row['name']) ? $row['name'] : '-') . "</td>";
            echo "<td>" . (isset($row['price']) ? $row['price'] : '0') . " ريال</td>";
            echo "<td>" . (isset($row['status']) ? $row['status'] : 'معلق') . "</td>";
            
            // أزرار الموافقة والرفض
            echo "<td>
                    <form method='POST' style='display:inline-block;'>
                        <input type='hidden' name='product_id' value='" . $row['id'] . "'>
                        <input type='hidden' name='status' value='مقبول'>
                        <button type='submit' name='update_product_status' style='background-color: #28a745; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 5px;'>موافقة</button>
                    </form>
                    
                    <form method='POST' style='display:inline-block;'>
                        <input type='hidden' name='product_id' value='" . $row['id'] . "'>
                        <input type='hidden' name='status' value='مرفوض'>
                        <button type='submit' name='update_product_status' style='background-color: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;'>رفض</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='empty-row' style='text-align:center;'>لا توجد منتجات مضافة حالياً.</td></tr>";
    }
    ?>
</tbody>
            </table>
        </div>


        <!-- جدول الطلبات -->
        <div class="section-box" id="orders">
            <div class="section-header">
                <h3 style="color: var(--navy);">أحدث الطلبات</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>العميل</th>
                        <th>المنتج</th>
                        <th>المبلغ</th>
                        <th>حالة الطلب</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
$sql = "SELECT * FROM orders ORDER BY id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . (isset($row['customer_name']) ? $row['customer_name'] : 'عميل') . "</td>";
        echo "<td>" . (isset($row['product_name']) ? $row['product_name'] : '-') . "</td>";
        echo "<td>" . (isset($row['total_price']) ? $row['total_price'] : (isset($row['price']) ? $row['price'] : '0')) . " ريال</td>";
        echo "<td>" . (isset($row['status']) ? $row['status'] : 'معلق') . "</td>";
       echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5' class='empty-row' style='text-align:center;'>لا توجد طلبات جديدة حتى الآن.</td></tr>";
}
?>
                </tbody>
            </table>
        </div>

        <!-- جدول إدارة المستخدمين -->
<div class="section-box" id="users">
    <div class="section-header">
        <h3 style="color: var(--navy);">إدارة حسابات المستخدمين والبائعين</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th># ID</th>
                <th>الاسم الكامل</th>
                <th>البريد الإلكتروني</th>
                <th>الجوال</th>
                <th>نوع الحساب (Role)</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            
            $users_sql = "SELECT * FROM users ORDER BY id DESC";
            $users_result = $conn->query($users_sql);

            if ($users_result && $users_result->num_rows > 0) {
                while($user = $users_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $user['id'] . "</td>";
                    echo "<td>" . $user['full_name'] . "</td>";
                    echo "<td>" . $user['email'] . "</td>";
                    echo "<td>" . $user['phone'] . "</td>";
                    echo "<td>" . $user['role'] . "</td>";
                    // زر الحذف للحسابات المخالفة حسب المتطلبات
                    echo "<td><a href='admin.php?delete_user=" . $user['id'] . "' class='btn btn-danger' onclick='return confirm(\"هل أنت متأكد من حذف هذا المستخدم؟\");'>حذف</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>لا يوجد مستخدمين مسجلين حالياً</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="section-box">
    <div class="section-header">
        <h3 style="color: var(--navy);">إدارة البلاغات والشكاوى</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th># ID</th>
                <th>المبلغ</th>
                <th>نوع البلاغ</th>
                <th>تفاصيل الشكوى</th>
                <th>تاريخ البلاغ</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #888;">
                    لا توجد بلاغات أو شكاوى مسجلة حالياً في النظام.
                </td>
            </tr>
        </tbody>
    </table>
</div>


    </main>

</body>
</html>