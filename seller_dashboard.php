<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. الاتصال بقاعدة البيانات
if (file_exists('db.php')) {
    require_once 'db.php';
} else {
    $conn = new mysqli("localhost", "root", "", "ajrni_db");
    if ($conn->connect_error) {
        die("خطأ في الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
}
$conn->set_charset("utf8mb4");

// 2. التحقق من تسجيل الدخول الحقيقي
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$seller_id = $_SESSION['user_id'];

// 3. جلب الاسم الحقيقي للمؤجر من عمود full_name في جدول users
$seller_name = "مؤجر معتمد";
$stmt_user = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
if ($stmt_user) {
    $stmt_user->bind_param("i", $seller_id);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    if ($row_user = $res_user->fetch_assoc()) {
        $seller_name = $row_user['full_name'];
    }
    $stmt_user->close();
}

$alert_text = "";
$alert_type = "";

// 4. معالجة حذف منتج خاص بالمؤجر الحالي فقط
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    $stmt_del = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("ii", $delete_id, $seller_id);
        if ($stmt_del->execute()) {
            $alert_text = "تم حذف السلعة بنجاح من قاعدة البيانات.";
            $alert_type = "success";
        }
        $stmt_del->close();
    }
}

// 5. معالجة إضافة سلعة جديدة للمؤجر الحالي
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn_add_product'])) {
    $item_name     = trim($_POST['item_name'] ?? '');
    $item_category = trim($_POST['item_category'] ?? '');
    $item_price    = floatval($_POST['item_price'] ?? 0);
    $item_deposit  = trim($_POST['item_deposit'] ?? '0');
    $item_terms    = trim($_POST['item_terms'] ?? '');
    $item_image    = "products.jpg";
    $is_featured   = 0;

    $desc_text = "مبلغ التأمين: " . $item_deposit . " ر.س | الشروط: " . $item_terms;

    if (!empty($item_name) && $item_price > 0) {
        $stmt = $conn->prepare("INSERT INTO products (seller_id, name, category, price, image, description, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issdssi", $seller_id, $item_name, $item_category, $item_price, $item_image, $desc_text, $is_featured);
            if ($stmt->execute()) {
                $alert_text = "تمت إضافة السلعة بنجاح وحفظها في قاعدة البيانات.";
                $alert_type = "success";
            }
            $stmt->close();
        }
    }
}

// 6. جلب منتجات هذا المؤجر فقط من جدول products
$stmt_list = $conn->prepare("SELECT id, name, category, price, description FROM products WHERE seller_id = ? ORDER BY id DESC");
$stmt_list->bind_param("i", $seller_id);
$stmt_list->execute();
$products_list = $stmt_list->get_result();

$realProductsCount = ($products_list) ? $products_list->num_rows : 0;
$realActiveRentals = 0;
$realEarnings      = 0.00;
$realDeposits      = 0.00;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة المؤجر | AJRNI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="seller.css">
</head>
<body>

    <header class="main-header">
        <a href="index.html" class="brand-area" style="text-decoration:none;">
            <div class="brand-text">
                <h1>AJRNI</h1>
                <span>RENT • BUY • REUSE</span>
            </div>
            <div class="brand-circle">
                <i class="fa-solid fa-box-open"></i>
            </div>
        </a>

        <nav class="nav-links">
            <a href="index.html">الرئيسية</a>
            <a href="prodcuts.html">المنتجات</a>
            <a href="seller_dashboard.php" class="active">بوابة المؤجر</a>
        </nav>

        <div class="header-tools">
            <a href="cart.html" class="icon-btn" title="السلة"><i class="fa-solid fa-cart-shopping"></i></a>
            <button type="button" onclick="logoutSeller()" class="icon-btn" title="تسجيل الخروج" style="background: transparent; border: none; cursor: pointer; color: #ef4444; font-size: 16px;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </button>
        </div>
    </header>

    <main class="dashboard-container">

        <?php if (!empty($alert_text)): ?>
            <div style="background: #dcfce7; color: #15803d; padding: 14px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: bold;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $alert_text; ?>
            </div>
        <?php endif; ?>

        <!-- بطاقة الترحيب (اسم المؤجر الحقيقي من full_name) -->
        <div class="welcome-card">
            <div>
                <h2>مرحباً بك، <span id="sellerDisplayName"><?php echo htmlspecialchars($seller_name); ?></span> 👋🏻</h2>
                <p>إدارة سلعك ومنتجاتك الخاصة ومتابعة طلباتك بكل سهولة وأمان.</p>
            </div>
            <div>
                <span class="deposit-tag" style="background: rgba(255,255,255,0.25); color: #ffffff; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                    <i class="fa-solid fa-circle-check"></i> حساب مؤجر موثّق
                </span>
            </div>
        </div>

        <!-- كروت الإحصائيات الأربعة كاملة -->
        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <span>المنتجات الحالية</span>
                    <strong><?php echo $realProductsCount; ?></strong>
                </div>
                <div class="stat-icon" style="background-color: #eff6ff; color: #1d4ed8;">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <span>العقود السارية</span>
                    <strong><?php echo $realActiveRentals; ?></strong>
                </div>
                <div class="stat-icon" style="background-color: #f0fdf4; color: #15803d;">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <span>إجمالي الأرباح</span>
                    <strong><?php echo number_format($realEarnings, 2); ?> ر.س</strong>
                </div>
                <div class="stat-icon" style="background-color: #fefce8; color: #ca8a04;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <span>التأمين المحتجز</span>
                    <strong style="color: #059669;"><?php echo number_format($realDeposits, 2); ?> ر.س</strong>
                </div>
                <div class="stat-icon" style="background-color: #ecfdf5; color: #059669;">
                    <i class="fa-solid fa-vault"></i>
                </div>
            </div>
        </div>

        <div class="tabs-nav">
            <button class="tab-btn active" onclick="showDashboardTab('tab-products', this)">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>المنتجات المسجلة</span>
            </button>
            <button class="tab-btn" onclick="showDashboardTab('tab-contracts', this)">
                <i class="fa-solid fa-file-signature"></i>
                <span>العقود والضمانات</span>
            </button>
            <button class="tab-btn" onclick="showDashboardTab('tab-add-item', this)">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إضافة سلعة جديدة</span>
            </button>
        </div>

        <!-- تبويب المنتجات المسجلة -->
        <div id="tab-products" class="tab-content active">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم المنتج</th>
                        <th>اسم السلعة</th>
                        <th>التصنيف</th>
                        <th>السعر اليومي</th>
                        <th>تفاصيل التأمين والشروط</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products_list && $products_list->num_rows > 0): ?>
                        <?php while ($item = $products_list->fetch_assoc()): ?>
                            <tr>
                                <td><span class="contract-id">#<?php echo htmlspecialchars($item['id']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><strong><?php echo htmlspecialchars($item['price']); ?> ر.س</strong></td>
                                <td style="font-size: 12px; color: #64748b;"><?php echo nl2br(htmlspecialchars($item['description'])); ?></td>
                                <td>
                                    <a href="seller_dashboard.php?action=delete&id=<?php echo $item['id']; ?>" 
                                       onclick="return confirm('هل أنتِ متأكدة من حذف هذه السلعة نهائياً؟');" 
                                       class="btn-action btn-claim" 
                                       style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #fee2e2; color: #dc2626; border-radius: 6px; font-weight: bold;">
                                        <i class="fa-solid fa-trash"></i>
                                        <span>حذف</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 25px;">
                                لا توجد منتجات مسجلة حالياً باسمك في قاعدة البيانات.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- تبويب العقود والضمانات -->
        <div id="tab-contracts" class="tab-content">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم العقد</th>
                        <th>اسم المستأجر</th>
                        <th>المنتج</th>
                        <th>المدة</th>
                        <th>قيمة التأمين</th>
                        <th>حالة العقد</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 25px;">لا توجد عقود سارية حالياً.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- تبويب إضافة سلعة -->
        <div id="tab-add-item" class="tab-content">
            <form action="seller_dashboard.php" method="POST">
                <input type="hidden" name="btn_add_product" value="1">

                <div class="form-row">
                    <div class="input-group">
                        <label>اسم السلعة / المنتج</label>
                        <input type="text" name="item_name" placeholder="مثلاً: كاميرا سوني احترافية" required>
                    </div>

                    <div class="input-group">
                        <label>التصنيف</label>
                        <select name="item_category" required>
                            <option value="إلكترونيات وتصوير">إلكترونيات وتصوير</option>
                            <option value="أزياء ومناسبات">أزياء ومناسبات</option>
                            <option value="حقائب وسفر">حقائب وسفر</option>
                            <option value="أجهزة ومعدات">أجهزة ومعدات</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label>السعر اليومي للإيجار (ر.س)</label>
                        <input type="number" step="any" name="item_price" placeholder="سعر الإيجار بالريال" required>
                    </div>

                    <div class="input-group">
                        <label>مبلغ التأمين المسترد (الضمان) - ر.س</label>
                        <input type="number" name="item_deposit" placeholder="مثلاً: 300 (تُحجز كوديعة)">
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 20px;">
                    <label>شروط المؤجر وبنود العقد</label>
                    <textarea name="item_terms" rows="3" placeholder="حدد شروط الاستخدام والسلامة الخاصة بالسلعة للمستأجر..."></textarea>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>حفظ ونشر السلعة في قاعدة البيانات</span>
                </button>
            </form>
        </div>

    </main>

    <script>
        function showDashboardTab(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(function(el) {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(function(el) {
                el.classList.remove('active');
            });

            const activeTab = document.getElementById(tabId);
            if (activeTab) activeTab.classList.add('active');
            if (btn) btn.classList.add('active');
        }

        function logoutSeller() {
            if (confirm("هل أنتِ متأكدة من تسجيل الخروج؟")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>