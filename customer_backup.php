<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>منصة أجرني - تصفح وحجز المنتجات</title>
  
  <!-- استدعاء ملف الستايل الأساسي للموقع والخط والأيقونات -->
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <style>
    /* الألوان الاساسية المعتمدة للمشروع */
    :root {
      --navy: #062b52;
      --dark-navy: #041f3b;
      --light-blue: #eef5fb;
      --border: #e2e8ef;
      --text: #15283b;
      --primary-btn: #0b1c3f;
      --btn-hover: #1e3a8a;
      --success-green: #27ae60;
      --rent-orange: #e67e22;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Cairo", sans-serif; }
    body { background-color: #f8fafc; color: var(--text); }
    .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

    /* شريط التصفية والفرز */
    .filter-tabs { display: flex; gap: 12px; margin-bottom: 25px; }
    .filter-btn { padding: 8px 20px; border: 1px solid var(--border); background: #ffffff; color: var(--text); border-radius: 20px; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .filter-btn.active, .filter-btn:hover { background: var(--primary-btn); color: #ffffff; border-color: var(--primary-btn); }

    /* شبكة عرض المنتجات */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
    .product-card { background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; position: relative; }

    /* حاوية الصورة وعناصر التحكم العلوية */
    .product-img-container { position: relative; height: 210px; background: #f1f5f9; }
    .product-img-container img { width: 100%; height: 100%; object-fit: cover; }
    
    /* شارة التمييز بين الإيجار والبيع */
    .badge-status { position: absolute; top: 12px; right: 12px; color: #ffffff; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; z-index: 2; }
    .badge-rent { background: var(--rent-orange); }
    .badge-buy { background: var(--success-green); }

    /* زر قلب التفضيلات المطلوب من الدكتورة */
    .fav-btn { position: absolute; top: 12px; left: 12px; background: #ffffff; border: none; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: 0.3s; z-index: 2; }
    .fav-btn i { font-size: 16px; color: #64748b; transition: 0.3s; }
    .fav-btn.active i { color: #e74c3c; font-weight: 900; }

    .product-info { padding: 18px; }
    .product-category { font-size: 12px; color: #64748b; margin-bottom: 4px; }
    .product-name { font-size: 18px; font-weight: bold; color: var(--dark-navy); margin-bottom: 8px; }
    .product-price { font-size: 19px; font-weight: bold; color: var(--success-green); margin-bottom: 12px; }
    .product-price span { font-size: 13px; color: #64748b; font-weight: normal; }

    /*قسم تواريخ الإيجار والتأمين المسترد */
    .rental-box { background: var(--light-blue); padding: 12px; border-radius: 8px; margin-bottom: 12px; font-size: 13px; }
    .rental-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
    .date-group { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px; }
    .date-group label { font-size: 11px; font-weight: 600; margin-bottom: 2px; display: block; }
    .date-group input { width: 100%; padding: 6px; border: 1px solid var(--border); border-radius: 6px; font-size: 12px; }

    /* خيار تحديد الكمية */
    .quantity-control { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; padding: 6px 10px; background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; }
    .quantity-control input { width: 55px; padding: 4px 6px; text-align: center; border: 1px solid var(--border); border-radius: 4px; font-weight: bold; }

    /* أزرار الإجراءات */
    .btn-action { width: 100%; background: var(--primary-btn); color: #ffffff; border: none; padding: 11px; border-radius: 8px; font-size: 14px; font-weight: bold; cursor: pointer; transition: 0.3s; }
    .btn-action:hover { background: var(--btn-hover); }
    .btn-buy { background: var(--success-green); }
    .btn-buy:hover { background: #219150; }
  </style>
</head>
<body>

  <!--  التنقل العلوي (متوافق مع صفحات المشروع) -->
  <header class="header">
    <div class="logo-area">
      <div class="logo-circle"><i class="fa-solid fa-box-open"></i></div>
      <div class="logo-text">
        <a href="index.html"><h1>AJRNI</h1></a>
        <span>RENT • BUY • REUSE</span>
      </div>
    </div>
    <nav class="navbar">
      <a href="index.html">الرئيسية</a>
      <a href="customer_products.php" class="active">المنتجات</a>
      <a href="#offers">العروض</a>
      <a href="contacct.html">تواصل معنا</a>
    </nav>
  </header>

  <div class="container">
    <h2 style="margin-bottom: 20px; color: var(--dark-navy);">تصفح المنتجات المتاحة</h2>
    
    <!--  أزرار الصلاحيات  ماالمتاح-->
    <div class="filter-tabs">
      <button class="filter-btn active" onclick="filterItems('all')">عرض الكل</button>
      <button class="filter-btn" onclick="filterItems('rent')">متاح للإيجار</button>
      <button class="filter-btn" onclick="filterItems('buy')">متاح للبيع</button>
    </div>

    <!-- شبكة المنتجات -->
    <div class="products-grid">
      
      <!-- كرت 1: منتج إيجار (كاميرا) -->
      <div class="product-card" data-type="rent">
        <div class="product-img-container">
          <span class="badge-status badge-rent">متاح للإيجار</span>
          <!-- زر التفضيل -->
          <button class="fav-btn" onclick="toggleFavorite(this, 101)" title="إضافة للمفضلة">
            <i class="fa-regular fa-heart"></i>
          </button>
          <img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=500&q=80" alt="Canon Camera">
        </div>
        <div class="product-info">
          <div class="product-category">إلكترونيات / كاميرات</div>
          <div class="product-name">Canon EOS R7</div>
          <div class="product-price">120 ر.س <span>/ يومياً</span></div>

          <!-- تفاصيل الإيجار والتواريخ والتأمين -->
          <div class="rental-box">
            <div class="rental-row">
              <span>التأمين المسترد:</span>
              <strong>300 ر.س</strong>
            </div>
            <div class="date-group">
              <div>
                <label>من تاريخ:</label>
                <input type="date" required>
              </div>
              <div>
                <label>إلى تاريخ:</label>
                <input type="date" required>
              </div>
            </div>
          </div>

          <!-- اختيار الكمية المطلوبة للمستأجر -->
          <div class="quantity-control">
            <span>الكمية المتاحة: <strong>3 قطع</strong></span>
            <div>
              <label>المطلوب:</label>
              <input type="number" min="1" max="3" value="1">
            </div>
          </div>

          <button class="btn-action" onclick="bookProduct('Canon EOS R7', 'rent')">
            <i class="fa-solid fa-calendar-check"></i> احجز للإيجار
          </button>
        </div>
      </div>

      <!-- كرت 2: منتج بيع (نظارة شمسية) -->
      <div class="product-card" data-type="buy">
        <div class="product-img-container">
          <span class="badge-status badge-buy">متاح للبيع</span>
          <!-- زر التفضيل -->
          <button class="fav-btn" onclick="toggleFavorite(this, 102)" title="إضافة للمفضلة">
            <i class="fa-regular fa-heart"></i>
          </button>
          <img src="https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80" alt="Sunglasses">
        </div>
        <div class="product-info">
          <div class="product-category">إكسسوارات / نظارات</div>
          <div class="product-name">نظارة شمسية كلاسيك</div>
          <div class="product-price">250 ر.س <span>(سعر الشراء)</span></div>

          <div class="rental-box" style="background: #f8fafc;">
            <div class="rental-row">
              <span>الحالة:</span>
              <strong>شبه جديد - استخدام خفيف</strong>
            </div>
            <div class="rental-row">
              <span>طريقة الاستلام:</span>
              <strong>شحن سريع أو يد بيد</strong>
            </div>
          </div>

          <!-- اختيار الكمية للشراء -->
          <div class="quantity-control">
            <span>الكمية المتاحة: <strong>5 قطع</strong></span>
            <div>
              <label>المطلوب:</label>
              <input type="number" min="1" max="5" value="1">
            </div>
          </div>

          <button class="btn-action btn-buy" onclick="bookProduct('نظارة شمسية كلاسيك', 'buy')">
            <i class="fa-solid fa-cart-shopping"></i> شراء فوري
          </button>
        </div>
      </div>

      <!-- كرت 3: منتج إيجار (دراجة جبلية) -->
      <div class="product-card" data-type="rent">
        <div class="product-img-container">
          <span class="badge-status badge-rent">متاح للإيجار</span>
          <button class="fav-btn" onclick="toggleFavorite(this, 103)" title="إضافة للمفضلة">
            <i class="fa-regular fa-heart"></i>
          </button>
          <img src="https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=500&q=80" alt="Mountain Bike">
        </div>
        <div class="product-info">
          <div class="product-category">رياضة ومغامرات</div>
          <div class="product-name">Mountain Bike</div>
          <div class="product-price">75 ر.س <span>/ يومياً</span></div>

          <div class="rental-box">
            <div class="rental-row">
              <span>التأمين المسترد:</span>
              <strong>150 ر.س</strong>
            </div>
            <div class="date-group">
              <div>
                <label>من تاريخ:</label>
                <input type="date" required>
              </div>
              <div>
                <label>إلى تاريخ:</label>
                <input type="date" required>
              </div>
            </div>
          </div>

          <div class="quantity-control">
            <span>الكمية المتاحة: <strong>2 قطعة</strong></span>
            <div>
              <label>المطلوب:</label>
              <input type="number" min="1" max="2" value="1">
            </div>
          </div>

          <button class="btn-action" onclick="bookProduct('Mountain Bike', 'rent')">
            <i class="fa-solid fa-calendar-check"></i> احجز للإيجار
          </button>
        </div>
      </div>

    </div>
  </div>
  
<!-- دوال الجافاسكربت التفاعلية لصفحتي (المفضلة والفلترة والحجز) -->
  <script>
    // 1. دالة التفضيلات (Wishlist Function)    
    function toggleFavorite(btn, productId) {
      btn.classList.toggle('active');
      const icon = btn.querySelector('i');
      
      let favList = JSON.parse(localStorage.getItem('user_favorites')) || [];

      if (btn.classList.contains('active')) {
        icon.classList.remove('fa-regular');
        icon.classList.add('fa-solid');
        if (!favList.includes(productId)) favList.push(productId);
      } else {
        icon.classList.remove('fa-solid');
        icon.classList.add('fa-regular');
        favList = favList.filter(id => id !== productId);
      }

      localStorage.setItem('user_favorites', JSON.stringify(favList));
    }

    // 2. دالة فرز وتصفية المنتجات (الكل / إيجار / بيع)
    function filterItems(type) {
      const buttons = document.querySelectorAll('.filter-btn');
      buttons.forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');

      const cards = document.querySelectorAll('.product-card');
      cards.forEach(card => {
        if (type === 'all' || card.getAttribute('data-type') === type) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    }

    // 3. دالة إرسال الطلب وحفظه في السلة
    function bookProduct(name, actionType) {
      const typeText = actionType === 'rent' ? 'طلب استئجار' : 'طلب شراء';
      alert(`تم إضافة ${typeText} للمنتج (${name}) إلى سلتك بنجاح!`);
    }
  </script>

</body>
</html>