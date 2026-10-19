<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. استخدام ملف الاتصال المركزي المعتمد بالمشروع
if (file_exists('db.php')) {
    require_once 'db.php';
} else {
    $conn = new mysqli("localhost", "root", "", "ajrni_db");
    if ($conn->connect_error) {
        die("خطأ في الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
}
// 1. إجمالي السلع المسجلة
$resP = $conn->query("SELECT COUNT(*) AS total FROM products");
$realProductsCount = ($resP) ? $resP->fetch_assoc()['total'] : 0;

// 2. العقود السارية (من جدول orders)
$resR = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'قيد الانتظار' OR status = 'active'");
$realActiveRentals = ($resR) ? $resR->fetch_assoc()['total'] : 0;

// 3. إجمالي الأرباح والمبيعات
$resProf = $conn->query("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders");
$realEarnings = ($resProf) ? $resProf->fetch_assoc()['total'] : 0;

// 4. التأمين المحتجز
$resDep = $conn->query("SELECT COALESCE(SUM(subtotal), 0) AS total FROM orders WHERE status = 'قيد الانتظار'");
$realDeposits = ($resDep) ? $resDep->fetch_assoc()['total'] : 0;

$conn->set_charset("utf8mb4");

// 2. تعيين اسم المؤجر
$seller_name = $_SESSION['user_name'] ?? 'المؤجر';

$alert_text = "";
$alert_type = "";

// ==========================================
// 1. معالجة حذف المنتج
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    $stmt_del = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("i", $delete_id);
        if ($stmt_del->execute()) {
            $alert_text = "تم حذف السلعة بنجاح من قاعدة البيانات.";
            $alert_type = "success";
        } else {
            $alert_text = "حدث خطأ أثناء محاولة الحذف: " . $stmt_del->error;
            $alert_type = "error";
        }
        $stmt_del->close();
    }
}

// ==========================================
// 2. معالجة نموذج إضافة سلعة جديدة
// ==========================================
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
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, image, description, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssdssi", $item_name, $item_category, $item_price, $item_image, $desc_text, $is_featured);
            if ($stmt->execute()) {
                $alert_text = "تمت إضافة السلعة بنجاح وحفظها في قاعدة البيانات.";
                $alert_type = "success";
            } else {
                $alert_text = "خطأ أثناء حفظ السلعة: " . $stmt->error;
                $alert_type = "error";
            }
            $stmt->close();
        }
    }
}

// 3. جلب قائمة المنتجات الحالية من القاعدة
$query_products = "SELECT id, name, category, price, description FROM products ORDER BY id DESC";
$products_list = $conn->query($query_products);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة المؤجر | AJRNI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="seller.css">

    <style>
        .alert-box {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-box.success { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .alert-box.error   { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .contract-id {
            background-color: #f1f5f9;
            color: #0b1c3f;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            font-weight: 700;
        }

        .deposit-tag {
            background-color: #ecfdf5;
            color: #059669;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-action {
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-release { background-color: #dcfce7; color: #16a34a; }
        .btn-claim   { background-color: #fee2e2; color: #dc2626; }

        .policy-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 22px;
            margin-top: 20px;
            line-height: 1.9;
            font-size: 13px;
            color: #475569;
        }
    </style>
</head>
<body>

    <header class="main-header">
        <a href="index.html" class="brand-area">
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
            <div class="alert-box <?php echo $alert_type; ?>">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo $alert_text; ?></span>
            </div>
        <?php endif; ?>

        <!-- بطاقة الترحيب المخصصة للمؤجر مع شارة التوثيق -->
        <div class="welcome-card">
            <div>
                <h2>مرحباً بك، <span id="sellerDisplayName"><?php echo htmlspecialchars($seller_name); ?></span> 👋🏻</h2>
                <p>يسعدنا وجودك في منصة أجرني • إدارة سلعك، متابعة العقود الرقمية، ومحفظة التأمين والضمان.</p>
            </div>
            <div>
                <span class="deposit-tag" style="background: rgba(255,255,255,0.25); color: #ffffff;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>حساب مؤجر موثّق</span>
                </span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <span>المنتجات الحالية</span>
                    <strong><?php echo ($products_list) ? $products_list->num_rows : 0; ?></strong>
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

        <!-- تبويب المنتجات من قاعدة البيانات -->
        <div id="tab-products" class="tab-content active">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم المنتج</th>
                        <th>اسم السلعة</th>
                        <th>التصنيف</th>
                        <th>السعر اليومي</th>
                        <th>تفاصيل التأمين والشروط</th>
                        <th>الحالة</th>
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
                                <td><span class="tag tag-ok">متاح</span></td>
                                <td>
                                    <a href="seller_dashboard.php?action=delete&id=<?php echo $item['id']; ?>" 
                                       onclick="return confirm('هل أنتِ متأكدة من حذف هذه السلعة نهائياً من قاعدة البيانات؟');" 
                                       class="btn-action btn-claim" 
                                       style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;">
                                        <i class="fa-solid fa-trash"></i>
                                        <span>حذف</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8; padding: 25px;">
                                لا توجد منتجات مسجلة حالياً في قاعدة البيانات.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- تبويب العقود والضمان -->
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
                        <th>إجراء التأمين عند الإرجاع</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="contract-id">AGR-2026-081</span></td>
                        <td>سارة العتيبي</td>
                        <td><strong>كاميرا كانون EOS R6</strong></td>
                        <td>3 أيام</td>
                        <td>
                            <span class="deposit-tag">
                                <i class="fa-solid fa-lock"></i>
                                <span>500 ر.س بالضمان</span>
                            </span>
                        </td>
                        <td><span class="tag tag-rent">ساري</span></td>
                        <td>
                            <button type="button" class="btn-action btn-release" onclick="alert('تم فحص السلعة وإرجاع التأمين للمستأجر بنجاح.')">
                                <i class="fa-solid fa-check"></i> إفراج التأمين
                            </button>
                            <button type="button" class="btn-action btn-claim" onclick="alert('تم تسجيل إثبات ضرر واقتطاع القيمة من التأمين.')">
                                <i class="fa-solid fa-triangle-exclamation"></i> إثبات ضرر
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="policy-card">
                <strong><i class="fa-solid fa-scale-balanced"></i> سياسة الضمان والعقود المعتمدة في منصة أجرني:</strong>
                <p>
                    يتم حجز قيمة التأمين مباشرة من حساب المستأجر كوديعة ضمان آمنة. لا يتم تحويل المبلغ للمستأجر إلا بعد تأكيد فحص السلعة وسلامتها من قبل المؤجر عند الاسترجاع لضمان حقوق الطرفين.
                </p>
            </div>
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
        // التحقق من الصلاحيات وتحديث الاسم المسجل فوراً
        document.addEventListener("DOMContentLoaded", function () {
            const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
            const userRole = (localStorage.getItem("userRole") || "").toLowerCase();
            const userName = localStorage.getItem("userName");

            // حماية البوابة: إذا لم يكن مسجلاً، يُعاد إلى صفحة الدخول
            if (!isLoggedIn) {
                window.location.href = "login.html";
                return;
            }

            // عرض اسم المؤجر الفعلي القادم من المتصفح
            if (userName) {
                const nameDisplay = document.getElementById("sellerDisplayName");
                if (nameDisplay) nameDisplay.textContent = userName;
            }
        });

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
            localStorage.clear();
            window.location.href = "login.html";
        }
    </script>
</body>
</html>