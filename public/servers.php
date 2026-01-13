<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Servers - MineHub</title>
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
            <a href="/servers.php" class="active">السيرفرات</a>
            <a href="/marketplace.php">Marketplace</a>
            <a href="/premium.php">Premium</a>
            <a href="/contact.php">تواصل معنا</a>
            <a href="#" id="admin-link" style="display:none;">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">🌙</div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>سيرفراتي</h1>
            <button onclick="showCreateModal()" class="btn-primary">+ إنشاء سيرفر جديد</button>
        </div>
        
        <div id="servers-list" class="servers-grid"></div>
    </div>
    
    <div id="create-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <h2>إنشاء سيرفر جديد</h2>
            
            <label>اسم السيرفر</label>
            <input type="text" id="server-name" placeholder="My Awesome Server">
            
            <label>إصدار Minecraft (Java Edition)</label>
            <select id="server-version">
                <option value="1.21.3">1.21.3 (Latest)</option>
                <option value="1.21.1">1.21.1</option>
                <option value="1.20.4">1.20.4</option>
                <option value="1.20.1" selected>1.20.1</option>
                <option value="1.16.5">1.16.5</option>
                <option value="1.12.2">1.12.2</option>
                <option value="1.8.8">1.8.8</option>
            </select>
            
            <label>نوع السيرفر</label>
            <select id="server-type">
                <option value="vanilla">Vanilla</option>
                <option value="spigot">Spigot</option>
                <option value="paper" selected>Paper</option>
                <option value="purpur">Purpur</option>
                <option value="forge">Forge</option>
                <option value="fabric">Fabric</option>
            </select>
            
            <label>RAM (MB)</label>
            <input type="number" id="server-ram" value="1024" min="512" max="8192" step="512">
            
            <button onclick="createServer()" class="btn-primary">إنشاء السيرفر</button>
        </div>
    </div>
    
    <div id="console-modal" class="modal">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeConsoleModal()">&times;</span>
            <h2>Console - Server #<span id="console-server-id"></span></h2>
            
            <div class="console-box" id="console-output"></div>
            
            <div class="console-input">
                <input type="text" id="console-command" placeholder="أدخل الأمر هنا...">
                <button onclick="sendCommand()" class="btn-primary">إرسال</button>
            </div>
            
            <div class="player-management">
                <h3>إدارة اللاعبين</h3>
                <input type="text" id="player-name" placeholder="اسم اللاعب">
                <div class="player-actions">
                    <button onclick="kickPlayer()">Kick</button>
                    <button onclick="banPlayer()">Ban</button>
                    <button onclick="opPlayer()">OP</button>
                    <button onclick="whitelistPlayer()">Whitelist</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/servers.js"></script>
</body>
</html>
