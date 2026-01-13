<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - MineHub</title>
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
            <a href="/marketplace.php" class="active">Marketplace</a>
            <a href="/premium.php">Premium</a>
            <a href="/contact.php">تواصل معنا</a>
            <a href="#" id="admin-link" style="display:none;">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">🌙</div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>🛒 Marketplace</h1>
        </div>
        
        <div class="marketplace-filters">
            <button onclick="filterItems('all')" class="filter-btn active">الكل</button>
            <button onclick="filterItems('plugin')">Plugins</button>
            <button onclick="filterItems('mod')">Mods</button>
            <button onclick="filterItems('map')">Maps</button>
            <button onclick="filterItems('resource')">Resource Packs</button>
        </div>
        
        <div id="marketplace-grid" class="marketplace-grid"></div>
    </div>
    
    <script src="/js/marketplace.js"></script>
</body>
</html>
