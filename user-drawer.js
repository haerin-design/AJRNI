(function () {
    // الصفحة اللي فيها القائمة جاهزة (مثل prodcuts.html) ما تحتاج شي
    if (document.getElementById("userDrawer")) return;

    function esc(s) {
        return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
        });
    }

    var DRAWER_HTML = `
<div id="sidebarOverlay" onclick="toggleUserSidebar()" style="position: fixed; inset: 0; background: rgba(11, 28, 63, 0.45); backdrop-filter: blur(2px); z-index: 9998; display: none; opacity: 0; transition: opacity 0.3s ease;"></div>

<aside id="userDrawer" style="position: fixed; top: 0; left: -380px; width: 360px; max-width: 90vw; height: 100vh; background: #ffffff; z-index: 9999; box-shadow: 4px 0 25px rgba(11, 28, 63, 0.15); transition: left 0.35s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; overflow-y: auto;">

    <div style="padding: 22px 20px; background: #0b1c3f; color: #ffffff; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1e3a8a;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #eef5fb; color: #0b1c3f; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 2px solid #93c5fd;">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div style="font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 6px;">
                    أهلاً بك، <span id="sideUserName">عميلنا العزيز</span>
                    <i class="fa-solid fa-circle-check" style="color: #60a5fa; font-size: 13px;"></i>
                </div>
                <div id="sideUserRoleBadge" style="font-size: 11px; color: #cbd5e1; margin-top: 2px;"></div>
            </div>
        </div>
        <button type="button" onclick="toggleUserSidebar()" style="background: rgba(255,255,255,0.1); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div id="sellerReturnBox" style="display: none; padding: 12px 15px; background: #eff6ff; border-bottom: 1px solid #bfdbfe;">
        <a href="seller_dashboard.php" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px; background: #1e3a8a; color: #ffffff; border-radius: 8px; font-weight: bold; font-size: 13px; text-decoration: none;">
            <i class="fa-solid fa-store"></i>
            <span>العودة إلى لوحة تحكم المؤجر</span>
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>

    <div id="customerSection">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <div style="background: #ffffff; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: right;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; margin-bottom: 4px;">
                    <i class="fa-solid fa-box-open" style="color: #0b1c3f;"></i>
                    <span>إيجارات نشطة</span>
                </div>
                <strong id="drawerActiveCount" style="font-size: 18px; color: #0b1c3f;">0</strong>
            </div>
            <div style="background: #ffffff; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; text-align: right;">
                <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; margin-bottom: 4px;">
                    <i class="fa-solid fa-shield-halved" style="color: #16a34a;"></i>
                    <span>تأمين بالضمان</span>
                </div>
                <strong id="drawerDeposit" style="font-size: 18px; color: #16a34a;">0 <span style="font-size: 12px; font-weight: normal;">ر.س</span></strong>
            </div>
        </div>

        <div style="display: flex; border-bottom: 1px solid #e2e8f0; background: #ffffff;">
            <button type="button" class="side-tab-btn" onclick="switchSideTab('rentals', this)" style="flex: 1; padding: 12px 5px; border: none; background: transparent; font-weight: 700; font-size: 13px; color: #0b1c3f; border-bottom: 2px solid #0b1c3f; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="fa-solid fa-clock-rotate-left"></i> إيجاراتي
            </button>
            <button type="button" class="side-tab-btn" onclick="switchSideTab('orders', this)" style="flex: 1; padding: 12px 5px; border: none; background: transparent; font-size: 13px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="fa-solid fa-bag-shopping"></i> مشترياتي
            </button>
            <button type="button" class="side-tab-btn" onclick="switchSideTab('alerts', this)" style="flex: 1; padding: 12px 5px; border: none; background: transparent; font-size: 13px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="fa-regular fa-bell"></i> التنبيهات
            </button>
        </div>

        <div style="padding: 15px; flex: 1; overflow-y: auto;">
            <div id="sideTab-rentals" class="side-panel-content">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-contract" style="color: #0b1c3f;"></i>
                    عقود الإيجار والطلبات المسجلة:
                </div>
                <div id="realRentalsContainer" style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="text-align: center; color: #64748b; font-size: 12px; padding: 15px;">جاري تحميل العقود...</div>
                </div>
            </div>

            <div id="sideTab-orders" class="side-panel-content" style="display: none;">
                <div style="font-size: 12px; color: #64748b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-receipt" style="color: #0b1c3f;"></i>
                    المنتجات المشتراة ملكية دائمة:
                </div>
                <div style="text-align: center; color: #94a3b8; font-size: 12px; padding: 20px;">لا توجد مشتريات حالياً</div>
            </div>

            <div id="sideTab-alerts" class="side-panel-content" style="display: none;">
                <div id="alertsContainer" style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="text-align: center; color: #888; font-size: 12px; padding: 15px;">جاري تحميل التنبيهات...</div>
                </div>
            </div>
        </div>
    </div>

    <div id="sellerSection" style="display: none; padding: 20px; text-align: center; flex: 1;">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-top: 15px;">
            <i class="fa-solid fa-boxes-stacked" style="font-size: 32px; color: #1e3a8a; margin-bottom: 12px;"></i>
            <h3 style="font-size: 15px; color: #0b1c3f; margin-bottom: 6px;">حساب المؤجر ومقدم الخدمة</h3>
            <p style="font-size: 12px; color: #64748b; line-height: 1.6; margin-bottom: 15px;">
                تتم إدارة سلعك المعروضة، متابعة العقود الرقمية، ومحفظة التأمين والضمان عبر لوحة التحكم الخاصة بك.
            </p>
            <a href="seller_dashboard.php" style="display: inline-block; padding: 9px 18px; background: #0b1c3f; color: white; border-radius: 8px; font-size: 12px; font-weight: bold; text-decoration: none;">
                فتح لوحة التحكم كاملة <i class="fa-solid fa-arrow-left" style="margin-right: 5px;"></i>
            </a>
        </div>
    </div>

    <div style="padding: 15px; border-top: 1px solid #e2e8f0; background: #f8fafc; margin-top: auto;">
        <button type="button" onclick="logoutUser()" style="width: 100%; padding: 10px; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 6px; font-weight: bold; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            تسجيل الخروج
        </button>
    </div>
</aside>`;

    /* ---------- دوال تُستدعى من الأزرار ---------- */
    window.toggleUserSidebar = function () {
        var drawer = document.getElementById("userDrawer");
        var overlay = document.getElementById("sidebarOverlay");
        if (!drawer || !overlay) return;

        if (drawer.style.left === "0px") {
            drawer.style.left = "-380px";
            overlay.style.opacity = "0";
            setTimeout(function () { overlay.style.display = "none"; }, 300);
        } else {
            overlay.style.display = "block";
            setTimeout(function () { overlay.style.opacity = "1"; }, 10);
            drawer.style.left = "0px";
        }
    };

    window.switchSideTab = function (tabName, btn) {
        document.querySelectorAll("#userDrawer .side-panel-content").forEach(function (p) {
            p.style.display = "none";
        });
        document.querySelectorAll("#userDrawer .side-tab-btn").forEach(function (b) {
            b.style.color = "#64748b";
            b.style.fontWeight = "normal";
            b.style.borderBottom = "none";
        });
        var panel = document.getElementById("sideTab-" + tabName);
        if (panel) panel.style.display = "block";
        if (btn) {
            btn.style.color = "#0b1c3f";
            btn.style.fontWeight = "700";
            btn.style.borderBottom = "2px solid #0b1c3f";
        }
    };
    window.logoutUser = function () {
        fetch("api/logout.php").finally(function () {
            ["isLoggedIn", "userName", "userRole", "userEmail", "userId", "user", "loggedInEmail"]
                .forEach(function (k) { localStorage.removeItem(k); });
            window.location.href = "login.html";
        });
    };

    /* ---------- أيقونة المستخدم في الشريط ---------- */
    function getHeaderSlot() {
        var slot = document.getElementById("userHeaderAction");
        if (slot) return slot;

        var actions = document.querySelector(".header-actions");
        if (!actions) return null;

        slot = document.createElement("div");
        slot.id = "userHeaderAction";

        var oldLink = actions.querySelector('a[href="signin.html"], a[href="login.html"]');
        if (oldLink) {
            oldLink.replaceWith(slot);
        } else {
            actions.appendChild(slot);
        }
        return slot;
    }

    /* ---------- جلب العقود ---------- */
    function loadOrders() {
        fetch("get_customer_orders.php")
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var box = document.getElementById("realRentalsContainer");
                if (!box) return;

                if (!data.success || !data.orders || data.orders.length === 0) {
                    box.innerHTML = '<div style="text-align:center; color:#94a3b8; font-size:12px; padding:20px;">لا توجد عقود أو طلبات حالياً</div>';
                    return;
                }

                var count = document.getElementById("drawerActiveCount");
                if (count) count.textContent = data.activeCount;
                var dep = document.getElementById("drawerDeposit");
                if (dep) dep.innerHTML = Number(data.totalDeposit).toLocaleString() + ' <span style="font-size:12px; font-weight:normal;">ر.س</span>';

                box.innerHTML = data.orders.map(function (o) {
                    return '<div style="border:1px solid #e2e8f0; border-radius:10px; padding:12px; background:#fff;">' +
                        '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">' +
                        '<strong style="font-size:13px; color:#0b1c3f;">عقد طلب #' + esc(o.id) + '</strong>' +
                        '<span style="font-size:10px; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:12px; font-weight:bold;">' + esc(o.status) + '</span>' +
                        '</div>' +
                        '<div style="font-size:11px; color:#64748b; line-height:1.8;">' +
                        '<div><i class="fa-regular fa-calendar-days" style="width:14px;"></i> تاريخ التسجيل: ' + esc(o.created_at) + '</div>' +
                        '<div style="color:#16a34a; font-weight:600;"><i class="fa-solid fa-shield-halved" style="width:14px;"></i> التأمين / الضمان: ' + Number(o.subtotal).toLocaleString() + ' ر.س</div>' +
                        '<div style="color:#0b1c3f; font-weight:600;"><i class="fa-solid fa-receipt" style="width:14px;"></i> الإجمالي: ' + Number(o.total_amount).toLocaleString() + ' ر.س (' + esc(o.payment_method) + ')</div>' +
                        '</div></div>';
                }).join("");
            })
            .catch(function () {
                var box = document.getElementById("realRentalsContainer");
                if (box) box.innerHTML = '<div style="text-align:center; color:#94a3b8; font-size:12px; padding:20px;">لا توجد عقود أو طلبات حالياً</div>';
            });
    }

    /* ---------- جلب التنبيهات ---------- */
    function loadNotifications() {
        fetch("api/get_user_notifications.php")
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var box = document.getElementById("alertsContainer");
                if (!box) return;

                if (data.notifications && data.notifications.length > 0) {
                    box.innerHTML = data.notifications.map(function (n) {
                        return '<div style="background:#f0fdf4; border:1px solid #dcfce7; color:#166534; padding:12px; border-radius:10px; font-size:12px; text-align:right;">' +
                            '<strong style="display:block; font-size:13px; margin-bottom:4px;">🔔 ' + esc(n.subject) + '</strong>' +
                            '<div style="color:#374151; margin-bottom:6px;">' + esc(n.message) + '</div>' +
                            '<small style="color:#9ca3af; font-size:10px;">' + esc(n.created_at) + '</small></div>';
                    }).join("");
                } else {
                    box.innerHTML = '<div style="text-align:center; color:#888; font-size:12px; padding:15px;">لا توجد تنبيهات جديدة</div>';
                }
            })
            .catch(function () {
                var box = document.getElementById("alertsContainer");
                if (box) box.innerHTML = '<div style="text-align:center; color:#888; font-size:12px; padding:15px;">لا توجد تنبيهات حالياً</div>';
            });
    }

    /* ---------- التشغيل ---------- */
    function start() {
        var slot = getHeaderSlot();
        var isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
        var userName = localStorage.getItem("userName");
        var userRole = (localStorage.getItem("userRole") || "").toLowerCase();

        // زائر: أيقونة تودي لتسجيل الدخول
        if (!isLoggedIn || !userName) {
            if (slot) {
                slot.innerHTML = '<a href="login.html" title="تسجيل الدخول" style="font-size:18px; padding:5px;"><i class="fa-regular fa-user"></i></a>';
            }
            return;
        }

        // مسجل دخول: أيقونة خضراء + القائمة
        document.body.insertAdjacentHTML("beforeend", DRAWER_HTML);

        if (slot) {
            slot.innerHTML = '<button type="button" onclick="toggleUserSidebar()" title="حسابي" style="background:transparent; border:none; font-size:18px; cursor:pointer; padding:5px;"><i class="fa-solid fa-user-check" style="color:#16a34a;"></i></button>';
        }

        document.getElementById("sideUserName").textContent = userName;

        var isSeller = (userRole === "seller" || userRole === "مؤجر");
        var badge = document.getElementById("sideUserRoleBadge");

        if (isSeller) {
            badge.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#60a5fa;"></i> حساب مؤجر موثّق (إدارة السلع)';
            document.getElementById("sellerReturnBox").style.display = "block";
            document.getElementById("customerSection").style.display = "none";
            document.getElementById("sellerSection").style.display = "block";

            var navbar = document.querySelector(".navbar");
            if (navbar && !navbar.querySelector('a[href="seller_dashboard.php"]')) {
                var link = document.createElement("a");
                link.href = "seller_dashboard.php";
                link.innerHTML = '<i class="fa-solid fa-store" style="font-size:12px;"></i> بوابة المؤجر';
                link.style.color = "#2563eb";
                link.style.fontWeight = "bold";
                navbar.appendChild(link);
            }
        } else {
            badge.innerHTML = '<i class="fa-solid fa-shield-halved" style="font-size:10px;"></i> حساب عميل موثّق (مستأجر / مشتري)';
            loadOrders();
            loadNotifications();
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", start);
    } else {
        start();
    }
})();