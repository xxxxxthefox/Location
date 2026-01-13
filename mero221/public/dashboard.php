<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MineHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="nav-brand">
            <img src="public/images/minehub-logo.png" alt="MineHub" class="nav-logo">
            MineHub
        </div>
        <div class="nav-menu">
            <a href="public/dashboard.php" class="active">Dashboard</a>
            <a href="public/servers.php">السيرفرات</a>
            <a href="public/marketplace.php">Marketplace</a>
            <a href="public/contact.php">تواصل معنا</a>
            <a href="public/admin.php" id="admin-link" style="display:none;">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">Theme</div>
    </nav>
    
    <div class="container">
        <div class="welcome-section">
            <h1>مرحباً، <span id="username"></span></h1>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">S</div>
                <div class="stat-value" id="total-servers">0</div>
                <div class="stat-label">إجمالي السيرفرات</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">+</div>
                <div class="stat-value" id="running-servers">0</div>
                <div class="stat-label">سيرفرات نشطة</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">P</div>
                <div class="stat-value" id="total-players">0</div>
                <div class="stat-label">اللاعبين النشطين</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">%</div>
                <div class="stat-value">99%</div>
                <div class="stat-label">Uptime</div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>إجراءات سريعة</h2>
            <div class="action-buttons">
                <button onclick="location.href='public/servers.php'" class="btn-primary">إدارة السيرفرات</button>
                <button onclick="location.href='public/marketplace.php'" class="btn-secondary">تصفح Marketplace</button>
            </div>
        </div>
        
        <div class="recent-activity">
            <h2>النشاط الأخير</h2>
            <div id="activity-list" class="activity-list">
                <p>لا يوجد نشاط</p>
            </div>
        </div>
    </div>
    
    <script>
        const API_BASE = 'api';
        let currentUser = null;
        
        async function checkAuth() {
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=check`);
                const data = await response.json();
                
                if (!data.success) {
                    window.location.href = '';
                    return null;
                }
                
                return data.user;
            } catch (error) {
                window.location.href = '';
                return null;
            }
        }
        
        async function loadStats() {
            try {
                const response = await fetch(`${API_BASE}/servers.php?action=list`);
                const data = await response.json();
                
                if (data.success) {
                    const servers = data.servers || [];
                    document.getElementById('total-servers').textContent = servers.length;
                    document.getElementById('running-servers').textContent = servers.filter(s => s.status === 'running').length;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        async function logout() {
            try {
                await fetch(`${API_BASE}/auth.php?action=logout`, {method: 'POST'});
            } catch (error) {}
            localStorage.removeItem('user');
            window.location.href = '';
        }
        
        function toggleTheme() {
            document.body.classList.toggle('light-theme');
            document.body.classList.toggle('dark-theme');
        }
        
        (async function init() {
            currentUser = await checkAuth();
            if (currentUser) {
                document.getElementById('username').textContent = currentUser.username;
                if (currentUser.is_admin) {
                    document.getElementById('admin-link').style.display = 'block';
                }
                await loadStats();
            }
        })();
    </script>
</body>
</html>
