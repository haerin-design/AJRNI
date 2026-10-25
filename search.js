(function () {
    var names = [];

    function addName(n) {
        n = (n || "").trim();
        if (n && names.indexOf(n) === -1) names.push(n);
    }

    function loadProductNames() {
        // 1) المنتجات الموجودة في صفحة المنتجات نفسها
        fetch("prodcuts.html")
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, "text/html");
                doc.querySelectorAll(".product-card").forEach(function (card) {
                    var el = card.querySelector(".product-name");
                    addName(el ? el.textContent : card.dataset.name);
                });
            })
            .catch(function () {});

        // 2) المنتجات المضافة من قاعدة البيانات
        fetch("get_prodcuts.php")
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.products) {
                    d.products.forEach(function (p) { addName(p.name); });
                }
            })
            .catch(function () {});
    }

    function setupSearch() {
        var input = document.getElementById("searchInput");
        var btn = document.getElementById("searchButton");
        if (!input || !btn) return;

        loadProductNames();

        var box = input.parentElement;
        box.style.position = "relative";
        var list = document.createElement("div");
        list.style.cssText =
            "position:absolute;top:100%;right:0;left:0;background:#fff;" +
            "border:1px solid #e2e8f0;border-radius:8px;margin-top:4px;" +
            "box-shadow:0 8px 20px rgba(0,0,0,0.12);z-index:99999;display:none;" +
            "max-height:260px;overflow-y:auto;text-align:right;";
        box.appendChild(list);

        function goToSearch(text) {
            text = (text || input.value).trim();
            if (text === "") return;
            window.location.href = "prodcuts.html?search=" + encodeURIComponent(text);
        }

        function showSuggestions() {
            var q = input.value.trim().toLowerCase();
            list.innerHTML = "";
            if (q === "") { list.style.display = "none"; return; }

            var matches = names.filter(function (n) {
                return n.toLowerCase().indexOf(q) !== -1;
            }).slice(0, 6);

            if (matches.length === 0) {
                var empty = document.createElement("div");
                empty.textContent = "لا توجد نتائج مطابقة";
                empty.style.cssText = "padding:10px 14px;font-size:13px;color:#94a3b8;";
                list.appendChild(empty);
            } else {
                matches.forEach(function (n) {
                    var item = document.createElement("div");
                    item.textContent = n;
                    item.style.cssText =
                        "padding:10px 14px;font-size:13px;color:#0b1c3f;cursor:pointer;" +
                        "border-bottom:1px solid #f1f5f9;";
                    item.onmouseenter = function () { item.style.background = "#f0f7ff"; };
                    item.onmouseleave = function () { item.style.background = "#fff"; };
                    item.onclick = function () { goToSearch(n); };
                    list.appendChild(item);
                });
            }
            list.style.display = "block";
        }

        input.addEventListener("input", showSuggestions);
        input.addEventListener("focus", showSuggestions);

        input.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.keyCode === 13) {
                e.preventDefault();
                goToSearch();
            }
        });

        btn.addEventListener("click", function () { goToSearch(); });

        document.addEventListener("click", function (e) {
            if (!box.contains(e.target)) list.style.display = "none";
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", setupSearch);
    } else {
        setupSearch();
    }
})();