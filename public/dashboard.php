<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MineHub</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="nav-brand">
            <img src="/images/minehub-logo.png" alt="MineHub" class="nav-logo">
            MineHub
        </div>
        <div class="nav-menu">
            <a href="/dashboard.php" class="active">Dashboard</a>
            <a href="/servers.php">السيرفرات</a>
            <a href="/marketplace.php">Marketplace</a>
            <a href="/premium.php">Premium</a>
            <a href="/contact.php">تواصل معنا</a>
            <a href="#" id="admin-link" style="display:none;">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">🌙</div>
    </nav>
    
    <div class="container">
        <div class="welcome-section">
            <h1>مرحباً، <span id="username"></span>!</h1>
            <div id="premium-badge" style="display:none;" class="premium-badge">⭐ Premium Active</div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🖥️</div>
                <div class="stat-value" id="total-servers">0</div>
                <div class="stat-label">إجمالي السيرفرات</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value" id="running-servers">0</div>
                <div class="stat-label">سيرفرات نشطة</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value" id="total-players">0</div>
                <div class="stat-label">اللاعبين النشطين</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value">99%</div>
                <div class="stat-label">Uptime</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>إجراءات سريعة</h2>
            <div class="action-buttons">
                <button onclick="location.href='/servers.php'" class="btn-primary">إنشاء سيرفر جديد</button>
                <button onclick="location.href='/marketplace.php'" class="btn-secondary">تصفح Marketplace</button>
                <button onclick="location.href='/premium.php'" class="btn-accent">ترقية إلى Premium</button>
            </div>
        </div>
        
        <div class="recent-activity">
            <h2>النشاط الأخير</h2>
            <div id="activity-list" class="activity-list">
                <p>جاري التحميل...</p>
            </div>
        </div>
    </div>
    
    <script src="/js/dashboard.js"></script>
</body>
</html>
