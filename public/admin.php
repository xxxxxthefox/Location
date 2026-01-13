<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن - MineHub</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="nav-brand">
            <img src="/images/minehub-logo.png" alt="MineHub" class="nav-logo">
            MineHub Admin
        </div>
        <div class="nav-menu">
            <a href="/dashboard.php">Dashboard</a>
            <a href="/servers.php">السيرفرات</a>
            <a href="/marketplace.php">Marketplace</a>
            <a href="/premium.php">Premium</a>
            <a href="/contact.php">تواصل معنا</a>
            <a href="/admin.php" class="active">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">🌙</div>
    </nav>
    
    <div class="container">
        <div class="admin-header">
            <h1>🔧 لوحة التحكم المتقدمة</h1>
            <div class="admin-actions">
                <button onclick="showTab('overview')" class="tab-btn active" id="tab-overview">نظرة عامة</button>
                <button onclick="showTab('users')" class="tab-btn" id="tab-users">المستخدمين</button>
                <button onclick="showTab('servers')" class="tab-btn" id="tab-servers">السيرفرات</button>
                <button onclick="showTab('marketplace')" class="tab-btn" id="tab-marketplace">Marketplace</button>
                <button onclick="showTab('payments')" class="tab-btn" id="tab-payments">المدفوعات</button>
                <button onclick="showTab('logs')" class="tab-btn" id="tab-logs">السجلات</button>
                <button onclick="showTab('settings')" class="tab-btn" id="tab-settings">الإعدادات</button>
            </div>
        </div>
        
        <!-- نظرة عامة -->
        <div id="content-overview" class="tab-content active">
            <div class="stats-grid">
                <div class="stat-card clickable" onclick="showTab('users')">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value" id="total-users">0</div>
                    <div class="stat-label">إجمالي المستخدمين</div>
                    <div class="stat-trend" id="users-trend">+0 هذا الأسبوع</div>
                </div>
                <div class="stat-card clickable" onclick="showTab('servers')">
                    <div class="stat-icon">🖥️</div>
                    <div class="stat-value" id="total-servers-admin">0</div>
                    <div class="stat-label">إجمالي السيرفرات</div>
                    <div class="stat-trend" id="servers-trend">+0 هذا الأسبوع</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value" id="running-servers-admin">0</div>
                    <div class="stat-label">سيرفرات نشطة</div>
                </div>
                <div class="stat-card clickable" onclick="showTab('users')">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-value" id="premium-users">0</div>
                    <div class="stat-label">مستخدمين Premium</div>
                </div>
                <div class="stat-card clickable" onclick="showTab('payments')">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value" id="total-payments">$0</div>
                    <div class="stat-label">إجمالي المدفوعات</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value" id="marketplace-items">0</div>
                    <div class="stat-label">عناصر Marketplace</div>
                </div>
            </div>
            
            <div class="charts-section">
                <div class="chart-card">
                    <h3>📈 نشاط المستخدمين (آخر 7 أيام)</h3>
                    <div id="user-activity-chart" class="chart"></div>
                </div>
                <div class="chart-card">
                    <h3>🖥️ نشاط السيرفرات</h3>
                    <div id="server-activity-chart" class="chart"></div>
                </div>
            </div>
            
            <div class="quick-stats">
                <h3>إحصائيات سريعة</h3>
                <div id="quick-stats-list"></div>
            </div>
        </div>
        
        <!-- إدارة المستخدمين -->
        <div id="content-users" class="tab-content">
            <div class="section-header">
                <h2>إدارة المستخدمين</h2>
                <div class="section-actions">
                    <input type="text" id="search-users" placeholder="بحث عن مستخدم..." class="search-input">
                    <button onclick="createUser()" class="btn-primary">+ إضافة مستخدم</button>
                    <button onclick="exportUsers()" class="btn-secondary">📥 تصدير البيانات</button>
                </div>
            </div>
            <div id="users-list"></div>
        </div>
        
        <!-- إدارة السيرفرات -->
        <div id="content-servers" class="tab-content">
            <div class="section-header">
                <h2>جميع السيرفرات</h2>
                <div class="section-actions">
                    <input type="text" id="search-servers" placeholder="بحث عن سيرفر..." class="search-input">
                    <select id="filter-status" onchange="filterServers()">
                        <option value="all">جميع الحالات</option>
                        <option value="running">نشط</option>
                        <option value="stopped">متوقف</option>
                    </select>
                    <button onclick="refreshServers()" class="btn-secondary">🔄 تحديث</button>
                </div>
            </div>
            <div id="all-servers-list" class="servers-grid"></div>
        </div>
        
        <!-- إدارة Marketplace -->
        <div id="content-marketplace" class="tab-content">
            <div class="section-header">
                <h2>إدارة Marketplace</h2>
                <div class="section-actions">
                    <button onclick="showAddItemModal()" class="btn-primary">+ إضافة عنصر جديد</button>
                </div>
            </div>
            <div id="marketplace-items-list" class="marketplace-grid"></div>
        </div>
        
        <!-- إدارة المدفوعات -->
        <div id="content-payments" class="tab-content">
            <div class="section-header">
                <h2>سجل المدفوعات</h2>
                <div class="section-actions">
                    <select id="filter-payment-status">
                        <option value="all">جميع الحالات</option>
                        <option value="completed">مكتمل</option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="failed">فشل</option>
                    </select>
                    <button onclick="exportPayments()" class="btn-secondary">📥 تصدير</button>
                </div>
            </div>
            <div id="payments-list"></div>
        </div>
        
        <!-- السجلات -->
        <div id="content-logs" class="tab-content">
            <div class="section-header">
                <h2>سجلات النظام</h2>
                <div class="section-actions">
                    <select id="log-type">
                        <option value="all">جميع السجلات</option>
                        <option value="user">المستخدمين</option>
                        <option value="server">السيرفرات</option>
                        <option value="payment">المدفوعات</option>
                        <option value="admin">الأدمن</option>
                    </select>
                    <button onclick="refreshLogs()" class="btn-secondary">🔄 تحديث</button>
                    <button onclick="clearLogs()" class="btn-secondary">🗑️ مسح القديم</button>
                </div>
            </div>
            <div id="logs-list" class="logs-container"></div>
        </div>
        
        <!-- الإعدادات -->
        <div id="content-settings" class="tab-content">
            <div class="settings-grid">
                <div class="settings-card">
                    <h3>⚙️ إعدادات النظام</h3>
                    <div class="setting-item">
                        <label>السماح بالتسجيل الجديد</label>
                        <input type="checkbox" id="allow-registration" checked>
                    </div>
                    <div class="setting-item">
                        <label>أيام التجربة المجانية</label>
                        <input type="number" id="trial-days" value="30">
                    </div>
                    <div class="setting-item">
                        <label>الحد الأقصى للسيرفرات (عادي)</label>
                        <input type="number" id="max-servers-free" value="1">
                    </div>
                    <div class="setting-item">
                        <label>الحد الأقصى للسيرفرات (Premium)</label>
                        <input type="number" id="max-servers-premium" value="10">
                    </div>
                    <button onclick="saveSettings()" class="btn-primary">💾 حفظ الإعدادات</button>
                </div>
                
                <div class="settings-card">
                    <h3>💾 النسخ الاحتياطي</h3>
                    <button onclick="createBackup()" class="btn-primary full-width">📦 إنشاء نسخة احتياطية</button>
                    <button onclick="downloadDatabase()" class="btn-secondary full-width">💾 تحميل قاعدة البيانات</button>
                    <button onclick="showBackupHistory()" class="btn-secondary full-width">📋 سجل النسخ</button>
                </div>
                
                <div class="settings-card">
                    <h3>🔧 صيانة النظام</h3>
                    <button onclick="optimizeDatabase()" class="btn-secondary full-width">🔧 تحسين قاعدة البيانات</button>
                    <button onclick="clearCache()" class="btn-secondary full-width">🗑️ مسح الذاكرة المؤقتة</button>
                    <button onclick="showSystemInfo()" class="btn-secondary full-width">ℹ️ معلومات النظام</button>
                </div>
                
                <div class="settings-card danger-zone">
                    <h3>⚠️ منطقة الخطر</h3>
                    <button onclick="resetAllServers()" class="btn-danger full-width">🔄 إعادة تعيين جميع السيرفرات</button>
                    <button onclick="deleteInactiveUsers()" class="btn-danger full-width">🗑️ حذف المستخدمين غير النشطين</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
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
    
    <script src="/js/auth.js"></script>
    <script src="/js/admin.js"></script>
</body>
</html>
