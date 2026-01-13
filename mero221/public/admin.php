<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة أدمن - MineHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="nav-brand">
            <img src="public/images/minehub-logo.png" alt="MineHub" class="nav-logo">
            MineHub Admin
        </div>
        <div class="nav-menu">
            <a href="public/dashboard.php">Dashboard</a>
            <a href="public/servers.php">السيرفرات</a>
            <a href="public/marketplace.php">Marketplace</a>
            <a href="public/contact.php">تواصل معنا</a>
            <a href="public/admin.php" class="active">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">Theme</div>
    </nav>
    
    <div class="container">
        <div class="admin-header">
            <h1>لوحة التحكم</h1>
            <div class="admin-actions">
                <button onclick="showTab('overview')" class="tab-btn active" id="tab-overview">نظرة عامة</button>
                <button onclick="showTab('users')" class="tab-btn" id="tab-users">المستخدمين</button>
                <button onclick="showTab('servers')" class="tab-btn" id="tab-servers">السيرفرات</button>
                <button onclick="showTab('marketplace')" class="tab-btn" id="tab-marketplace">Marketplace</button>
            </div>
        </div>
        
        <div id="content-overview" class="tab-content active">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="total-users">0</div>
                    <div class="stat-label">إجمالي المستخدمين</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="total-servers-admin">0</div>
                    <div class="stat-label">إجمالي السيرفرات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="running-servers-admin">0</div>
                    <div class="stat-label">سيرفرات نشطة</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="marketplace-items">0</div>
                    <div class="stat-label">عناصر Marketplace</div>
                </div>
            </div>
        </div>
        
        <div id="content-users" class="tab-content">
            <div class="section-header">
                <h2>إدارة المستخدمين</h2>
                <div class="section-actions">
                    <button onclick="createUser()" class="btn-primary">+ إضافة مستخدم</button>
                </div>
            </div>
            <div id="users-list"></div>
        </div>
        
        <div id="content-servers" class="tab-content">
            <div class="section-header">
                <h2>جميع السيرفرات</h2>
            </div>
            <div id="all-servers-list"></div>
        </div>
        
        <div id="content-marketplace" class="tab-content">
            <div class="section-header">
                <h2>إدارة Marketplace</h2>
                <div class="section-actions">
                    <button onclick="showAddItemModal()" class="btn-primary">+ إضافة عنصر جديد</button>
                </div>
            </div>
            <div id="marketplace-items-list"></div>
        </div>
    </div>
    
    <div id="add-item-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddItemModal()">&times;</span>
            <h2>إضافة عنصر جديد للـ Marketplace</h2>
            <label>اسم العنصر</label>
            <input type="text" id="item-name" placeholder="مثال: EssentialsX">
            <label>النوع</label>
            <select id="item-type">
                <option value="plugin">Plugin</option>
                <option value="mod">Mod</option>
                <option value="map">Map</option>
                <option value="resource">Resource Pack</option>
            </select>
            <label>الوصف</label>
            <textarea id="item-description" rows="4"></textarea>
            <label>السعر (0 = مجاني)</label>
            <input type="number" id="item-price" value="0" step="0.01">
            <button onclick="addMarketplaceItem()" class="btn-primary">إضافة</button>
        </div>
    </div>
    
    <script>
        const API_BASE = 'api';
        let currentUser = null;
        let currentTab = 'overview';
        
        async function checkAuth() {
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=check`);
                const data = await response.json();
                if (!data.success || !data.user.is_admin) {
                    alert('ليس لديك صلاحيات الأدمن');
                    window.location.href = 'public/dashboard.php';
                    return null;
                }
                return data.user;
            } catch (error) {
                window.location.href = '';
                return null;
            }
        }
        
        function showTab(tabName) {
            currentTab = tabName;
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById('tab-' + tabName)?.classList.add('active');
            document.getElementById('content-' + tabName)?.classList.add('active');
        }
        
        async function loadAdminStats() {
            try {
                const response = await fetch(`${API_BASE}/admin.php?action=stats`);
                const data = await response.json();
                if (data.success) {
                    const stats = data.stats;
                    document.getElementById('total-users').textContent = stats.total_users;
                    document.getElementById('total-servers-admin').textContent = stats.total_servers;
                    document.getElementById('running-servers-admin').textContent = stats.running_servers || 0;
                    document.getElementById('marketplace-items').textContent = stats.marketplace_items || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        async function loadAllUsers() {
            try {
                const response = await fetch(`${API_BASE}/admin.php?action=users`);
                const data = await response.json();
                if (data.success) {
                    renderUsersList(data.users);
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }
        
        function renderUsersList(users) {
            const usersList = document.getElementById('users-list');
            usersList.innerHTML = `
                <table style="width: 100%; background: var(--bg-secondary); border-radius: 8px; padding: 1rem;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>اسم المستخدم</th>
                            <th>أدمن</th>
                            <th>عدد السيرفرات</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${users.map(user => `
                            <tr>
                                <td>${user.id}</td>
                                <td><strong>${user.username}</strong></td>
                                <td>${user.is_admin ? 'Admin' : '-'}</td>
                                <td>${user.max_servers || 1}</td>
                                <td>${new Date(user.created_at).toLocaleDateString('ar')}</td>
                                <td>
                                    <button onclick="editUserLimits(${user.id}, ${user.max_servers})" class="btn-secondary">تعديل الحد</button>
                                    ${user.username !== 'admin' ? `<button onclick="deleteUser(${user.id})" class="btn-danger">حذف</button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        async function createUser() {
            const username = prompt('أدخل اسم المستخدم:');
            if (!username) return;
            const password = prompt('أدخل كلمة المرور (6 أحرف على الأقل):');
            if (!password || password.length < 6) {
                alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/admin.php?action=create_user`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username, password})
                });
                const data = await response.json();
                if (data.success) {
                    alert('تم إنشاء المستخدم بنجاح!');
                    await loadAllUsers();
                } else {
                    alert(data.message || 'فشل إنشاء المستخدم');
                }
            } catch (error) {
                alert('حدث خطأ');
            }
        }
        
        async function editUserLimits(userId, current) {
            const newLimit = prompt('أدخل الحد الأقصى الجديد للسيرفرات:', current);
            if (!newLimit || newLimit === current.toString()) return;
            
            try {
                const response = await fetch(`${API_BASE}/admin.php?action=update_user`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({user_id: userId, max_servers: parseInt(newLimit)})
                });
                const data = await response.json();
                if (data.success) {
                    alert('تم تحديث الحد بنجاح!');
                    await loadAllUsers();
                } else {
                    alert(data.message || 'فشل التحديث');
                }
            } catch (error) {
                alert('حدث خطأ');
            }
        }
        
        async function deleteUser(userId) {
            if (!confirm('هل أنت متأكد من حذف هذا المستخدم؟')) return;
            
            try {
                const response = await fetch(`${API_BASE}/admin.php?action=delete_user&user_id=${userId}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                if (data.success) {
                    alert('تم حذف المستخدم بنجاح');
                    await loadAllUsers();
                } else {
                    alert(data.message || 'فشل الحذف');
                }
            } catch (error) {
                alert('حدث خطأ');
            }
        }
        
        function showAddItemModal() {
            document.getElementById('add-item-modal').style.display = 'block';
        }
        
        function closeAddItemModal() {
            document.getElementById('add-item-modal').style.display = 'none';
        }
        
        async function addMarketplaceItem() {
            const name = document.getElementById('item-name').value;
            const type = document.getElementById('item-type').value;
            const description = document.getElementById('item-description').value;
            const price = document.getElementById('item-price').value;
            
            if (!name) {
                alert('الرجاء إدخال اسم العنصر');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/admin.php?action=add_marketplace_item`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({name, type, description, price: parseFloat(price)})
                });
                const data = await response.json();
                if (data.success) {
                    alert('تم إضافة العنصر بنجاح!');
                    closeAddItemModal();
                } else {
                    alert(data.message || 'فشل الإضافة');
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
                await loadAdminStats();
                await loadAllUsers();
            }
        })();
    </script>
</body>
</html>
