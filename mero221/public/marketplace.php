<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - MineHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="nav-brand">
            <img src="public/images/minehub-logo.png" alt="MineHub" class="nav-logo">
            MineHub
        </div>
        <div class="nav-menu">
            <a href="public/dashboard.php">Dashboard</a>
            <a href="public/servers.php">السيرفرات</a>
            <a href="public/marketplace.php" class="active">Marketplace</a>
            <a href="public/contact.php">تواصل معنا</a>
            <a href="public/admin.php" id="admin-link" style="display:none;">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">Theme</div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Marketplace</h1>
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
    
    <script>
        const API_BASE = 'api';
        let currentUser = null;
        let allItems = [];
        let currentFilter = 'all';
        
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
        
        async function loadItems() {
            try {
                const response = await fetch(`${API_BASE}/marketplace.php?action=list&type=${currentFilter}`);
                const data = await response.json();
                if (data.success) {
                    allItems = data.items || [];
                    renderItems();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        function renderItems() {
            const grid = document.getElementById('marketplace-grid');
            if (allItems.length === 0) {
                grid.innerHTML = '<p style="text-align:center; margin-top:2rem;">لا توجد عناصر</p>';
                return;
            }
            
            grid.innerHTML = allItems.map(item => `
                <div class="marketplace-item">
                    <h3>${item.name}</h3>
                    <p class="item-type">${item.type}</p>
                    <p>${item.description || 'لا يوجد وصف'}</p>
                    <div class="item-footer">
                        <span class="item-price">${item.price > 0 ? '$' + item.price : 'مجاني'}</span>
                        <span>تحميلات: ${item.downloads || 0}</span>
                    </div>
                    <button onclick="downloadItem(${item.id})" class="btn-primary">تحميل</button>
                </div>
            `).join('');
        }
        
        function filterItems(type) {
            currentFilter = type;
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            loadItems();
        }
        
        async function downloadItem(id) {
            try {
                const response = await fetch(`${API_BASE}/marketplace.php?action=download`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({item_id: id})
                });
                const data = await response.json();
                if (data.success) {
                    alert('تم التحميل بنجاح!');
                    await loadItems();
                } else {
                    alert(data.message || 'فشل التحميل');
                }
            } catch (error) {
                alert('حدث خطأ');
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
                if (currentUser.is_admin) {
                    document.getElementById('admin-link').style.display = 'block';
                }
                await loadItems();
            }
        })();
    </script>
</body>
</html>
