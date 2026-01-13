<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium - MineHub</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="nav-brand">
            <img src="/images/minehub-logo.png" alt="MineHub" class="nav-logo">
            MineHub
        </div>
        <div class="nav-menu">
            <a href="/dashboard.php">Dashboard</a>
            <a href="/servers.php">السيرفرات</a>
            <a href="/marketplace.php">Marketplace</a>
            <a href="/premium.php" class="active">Premium</a>
            <a href="/contact.php">تواصل معنا</a>
            <a href="#" id="admin-link" style="display:none;">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">🌙</div>
    </nav>
    
    <div class="container">
        <div class="premium-hero">
            <h1>⭐ MineHub Premium</h1>
            <p class="hero-subtitle">احصل على أقصى استفادة من سيرفراتك</p>
        </div>
        
        <div class="pricing-card">
            <div class="price-tag">$9.99 <span>/شهر</span></div>
            <div class="trial-banner">🎉 أول شهر مجاني للمستخدمين الجدد!</div>
            
            <div class="features-list">
                <div class="feature">✅ حتى 10 سيرفرات</div>
                <div class="feature">✅ RAM حتى 8GB لكل سيرفر</div>
                <div class="feature">✅ أولوية في الدعم الفني</div>
                <div class="feature">✅ Auto-Restart & Auto-Shutdown</div>
                <div class="feature">✅ Marketplace مجاني</div>
                <div class="feature">✅ أدوات إدارة متقدمة</div>
                <div class="feature">✅ نسخ احتياطية تلقائية</div>
                <div class="feature">✅ لا إعلانات</div>
            </div>
            
            <div class="payment-methods">
                <h3>اختر طريقة الدفع</h3>
                <button onclick="payWithStripe()" class="payment-btn stripe-btn">
                    💳 الدفع باستخدام Stripe
                </button>
                <button onclick="payWithPayPal()" class="payment-btn paypal-btn">
                    🅿️ الدفع باستخدام PayPal
                </button>
            </div>
        </div>
        
        <div class="comparison-table">
            <h2>مقارنة الخطط</h2>
            <table>
                <tr>
                    <th>الميزة</th>
                    <th>Free</th>
                    <th>Premium</th>
                </tr>
                <tr>
                    <td>عدد السيرفرات</td>
                    <td>1</td>
                    <td>10</td>
                </tr>
                <tr>
                    <td>RAM</td>
                    <td>1GB</td>
                    <td>8GB</td>
                </tr>
                <tr>
                    <td>Marketplace</td>
                    <td>محدود</td>
                    <td>كامل مجاني</td>
                </tr>
                <tr>
                    <td>الدعم</td>
                    <td>عادي</td>
                    <td>أولوية</td>
                </tr>
            </table>
        </div>
    </div>
    
    <script src="/js/premium.js"></script>
</body>
</html>
