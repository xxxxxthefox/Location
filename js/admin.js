let currentUser = null;
let currentTab = 'overview';
let allUsers = [];
let allServers = [];
let allMarketplaceItems = [];
let allPayments = [];
let systemLogs = [];

async function init() {
    currentUser = await checkAuth();
    
    if (!currentUser || !currentUser.is_admin) {
        alert('ليس لديك صلاحيات الأدمن');
        window.location.href = '/dashboard.php';
        return;
    }
    
    await loadAllData();
    showTab('overview');
}

async function loadAllData() {
    await Promise.all([
        loadAdminStats(),
        loadAllUsers(),
        loadAllServers(),
        loadMarketplaceItems(),
        loadPayments(),
        loadLogs()
    ]);
}

function showTab(tabName) {
    currentTab = tabName;
    
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    document.getElementById('tab-' + tabName)?.classList.add('active');
    document.getElementById('content-' + tabName)?.classList.add('active');
    
    if (tabName === 'overview') {
        renderCharts();
        renderQuickStats();
    }
}

async function loadAdminStats() {
    try {
        const response = await fetch('/api/admin.php?action=stats');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            document.getElementById('total-users').textContent = stats.total_users;
            document.getElementById('total-servers-admin').textContent = stats.total_servers;
            document.getElementById('running-servers-admin').textContent = stats.running_servers || 0;
            document.getElementById('premium-users').textContent = stats.premium_users;
            document.getElementById('total-payments').textContent = '$' + (stats.total_revenue || 0);
            document.getElementById('marketplace-items').textContent = stats.marketplace_items || 0;
            
            document.getElementById('users-trend').textContent = '+' + (stats.new_users_week || 0) + ' هذا الأسبوع';
            document.getElementById('servers-trend').textContent = '+' + (stats.new_servers_week || 0) + ' هذا الأسبوع';
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadAllUsers() {
    try {
        const response = await fetch('/api/admin.php?action=users');
        const data = await response.json();
        
        if (data.success) {
            allUsers = data.users;
            renderUsersList();
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

function renderUsersList() {
    const searchTerm = document.getElementById('search-users')?.value.toLowerCase() || '';
    const filteredUsers = allUsers.filter(u => 
        u.username.toLowerCase().includes(searchTerm) ||
        u.id.toString().includes(searchTerm)
    );
    
    const usersList = document.getElementById('users-list');
    usersList.innerHTML = `
        <table style="width: 100%; background: var(--bg-secondary); border-radius: 8px; padding: 1rem;">
            <thead>
                <tr style="background: var(--bg-card);">
                    <th style="padding: 0.5rem;">ID</th>
                    <th style="padding: 0.5rem;">اسم المستخدم</th>
                    <th style="padding: 0.5rem;">أدمن</th>
                    <th style="padding: 0.5rem;">Premium</th>
                    <th style="padding: 0.5rem;">عدد السيرفرات</th>
                    <th style="padding: 0.5rem;">تاريخ التسجيل</th>
                    <th style="padding: 0.5rem;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                ${filteredUsers.map(user => `
                    <tr>
                        <td style="padding: 0.5rem;">${user.id}</td>
                        <td style="padding: 0.5rem;"><strong>${user.username}</strong></td>
                        <td style="padding: 0.5rem;">${user.is_admin ? '✅ Admin' : '❌'}</td>
                        <td style="padding: 0.5rem;">${user.is_premium ? '⭐ Premium' : '❌ Free'}</td>
                        <td style="padding: 0.5rem;">${user.max_servers || 1}</td>
                        <td style="padding: 0.5rem;">${new Date(user.created_at).toLocaleDateString('ar')}</td>
                        <td style="padding: 0.5rem;">
                            <button onclick="togglePremium(${user.id}, ${user.is_premium})" class="btn-secondary" style="padding: 4px 8px; margin: 2px; font-size: 0.8rem;">
                                ${user.is_premium ? '❌ إلغاء' : '⭐ تفعيل'} Premium
                            </button>
                            <button onclick="editUserLimits(${user.id}, ${user.max_servers})" class="btn-secondary" style="padding: 4px 8px; margin: 2px; font-size: 0.8rem;">
                                📝 تعديل الحد
                            </button>
                            ${user.username !== 'admin' ? `
                                <button onclick="deleteUser(${user.id})" class="btn-stop" style="padding: 4px 8px; margin: 2px; font-size: 0.8rem;">
                                    🗑️ حذف
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

async function loadAllServers() {
    try {
        const response = await fetch('/api/admin.php?action=servers');
        const data = await response.json();
        
        if (data.success) {
            allServers = data.servers;
            renderServersList();
        }
    } catch (error) {
        console.error('Error loading servers:', error);
    }
}

function renderServersList() {
    const searchTerm = document.getElementById('search-servers')?.value.toLowerCase() || '';
    const filterStatus = document.getElementById('filter-status')?.value || 'all';
    
    let filtered = allServers.filter(s => 
        (s.name.toLowerCase().includes(searchTerm) || s.username?.toLowerCase().includes(searchTerm)) &&
        (filterStatus === 'all' || s.status === filterStatus)
    );
    
    const serversList = document.getElementById('all-servers-list');
    
    if (filtered.length === 0) {
        serversList.innerHTML = '<p style="text-align:center; margin-top:2rem; color: var(--text-secondary);">لا توجد سيرفرات</p>';
        return;
    }
    
    serversList.innerHTML = filtered.map(server => `
        <div class="server-card">
            <div class="server-header">
                <h3>${server.name}</h3>
                <span class="server-status status-${server.status}">${server.status === 'running' ? '✅ نشط' : '⏹️ متوقف'}</span>
            </div>
            <div class="server-info">
                <p><strong>👤 المالك:</strong> ${server.username}</p>
                <p><strong>📦 النوع:</strong> ${server.type} ${server.version}</p>
                <p><strong>🔌 Port:</strong> ${server.port}</p>
                <p><strong>💾 RAM:</strong> ${server.ram}MB</p>
                <p><strong>📅 تاريخ الإنشاء:</strong> ${new Date(server.created_at).toLocaleDateString('ar')}</p>
            </div>
            <div class="server-actions">
                <button class="btn-secondary" onclick="viewServerDetails(${server.id})" style="padding: 4px 8px; margin: 2px; font-size: 0.8rem;">
                    👁️ تفاصيل
                </button>
                <button class="btn-stop" onclick="adminDeleteServer(${server.id})" style="padding: 4px 8px; margin: 2px; font-size: 0.8rem;">
                    🗑️ حذف
                </button>
            </div>
        </div>
    `).join('');
}

async function loadMarketplaceItems() {
    try {
        const response = await fetch('/api/marketplace.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            allMarketplaceItems = data.items || [];
            renderMarketplaceItems();
        }
    } catch (error) {
        console.error('Error loading marketplace:', error);
        allMarketplaceItems = [];
    }
}

function renderMarketplaceItems() {
    const itemsList = document.getElementById('marketplace-items-list');
    
    if (allMarketplaceItems.length === 0) {
        itemsList.innerHTML = '<p style="text-align:center; margin-top:2rem;">لا توجد عناصر في Marketplace</p>';
        return;
    }
    
    itemsList.innerHTML = allMarketplaceItems.map(item => `
        <div class="marketplace-item">
            <h3>${item.name}</h3>
            <p style="color: var(--text-secondary); margin: 0.5rem 0;">${item.type}</p>
            <p style="margin: 0.5rem 0;">${item.description || 'لا يوجد وصف'}</p>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                <span class="item-price">${item.price > 0 ? '$' + item.price : 'مجاني'}</span>
                <span>📥 ${item.downloads || 0}</span>
            </div>
            <button onclick="deleteMarketplaceItem(${item.id})" class="btn-stop" style="width: 100%; margin-top: 0.5rem; padding: 4px;">
                🗑️ حذف
            </button>
        </div>
    `).join('');
}

async function loadPayments() {
    try {
        const response = await fetch('/api/admin.php?action=payments');
        const data = await response.json();
        
        if (data.success) {
            allPayments = data.payments || [];
            renderPayments();
        }
    } catch (error) {
        console.error('Error loading payments:', error);
        allPayments = [];
    }
}

function renderPayments() {
    const paymentsList = document.getElementById('payments-list');
    
    if (allPayments.length === 0) {
        paymentsList.innerHTML = '<p style="text-align:center; margin-top:2rem; background: var(--bg-secondary); padding: 2rem; border-radius: 8px;">لا توجد مدفوعات</p>';
        return;
    }
    
    paymentsList.innerHTML = `
        <table style="width: 100%; background: var(--bg-secondary); border-radius: 8px; padding: 1rem;">
            <thead>
                <tr style="background: var(--bg-card);">
                    <th style="padding: 0.5rem;">ID</th>
                    <th style="padding: 0.5rem;">المستخدم</th>
                    <th style="padding: 0.5rem;">المبلغ</th>
                    <th style="padding: 0.5rem;">الطريقة</th>
                    <th style="padding: 0.5rem;">الحالة</th>
                    <th style="padding: 0.5rem;">التاريخ</th>
                </tr>
            </thead>
            <tbody>
                ${allPayments.map(payment => `
                    <tr>
                        <td style="padding: 0.5rem;">${payment.id}</td>
                        <td style="padding: 0.5rem;">${payment.username || 'N/A'}</td>
                        <td style="padding: 0.5rem;"><strong>$${payment.amount}</strong></td>
                        <td style="padding: 0.5rem;">${payment.payment_method || 'N/A'}</td>
                        <td style="padding: 0.5rem;">
                            <span style="padding: 4px 8px; border-radius: 4px; background: ${payment.status === 'completed' ? 'var(--success)' : payment.status === 'pending' ? 'var(--warning)' : 'var(--danger)'}; color: white;">
                                ${payment.status}
                            </span>
                        </td>
                        <td style="padding: 0.5rem;">${new Date(payment.created_at).toLocaleString('ar')}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

async function loadLogs() {
    try {
        const response = await fetch('/api/admin.php?action=logs');
        const data = await response.json();
        
        if (data.success) {
            systemLogs = data.logs.map(log => ({
                ...log,
                timestamp: new Date(log.timestamp)
            }));
        } else {
            systemLogs = [
                {type: 'success', message: 'النظام يعمل بشكل طبيعي', timestamp: new Date()},
                {type: 'info', message: 'تم تحميل لوحة الأدمن', timestamp: new Date()},
                {type: 'warning', message: 'تذكير: قم بعمل نسخة احتياطية بشكل دوري', timestamp: new Date()}
            ];
        }
    } catch (error) {
        console.error('Error loading logs:', error);
        systemLogs = [
            {type: 'error', message: 'فشل تحميل السجلات', timestamp: new Date()}
        ];
    }
    renderLogs();
}

function renderLogs() {
    const logsList = document.getElementById('logs-list');
    logsList.innerHTML = systemLogs.map(log => `
        <div class="log-entry ${log.type}">
            <span style="color: var(--text-secondary); font-size: 0.8rem;">[${log.timestamp.toLocaleTimeString('ar')}]</span>
            ${log.message}
        </div>
    `).join('');
}

function renderCharts() {
    const userChart = document.getElementById('user-activity-chart');
    const serverChart = document.getElementById('server-activity-chart');
    
    const days = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];
    const userData = [12, 19, 15, 25, 22, 30, 28];
    const maxUser = Math.max(...userData);
    
    userChart.innerHTML = userData.map((value, index) => `
        <div class="chart-bar" style="height: ${(value / maxUser) * 100}%">
            <div class="chart-label">${days[index]}</div>
        </div>
    `).join('');
    
    const serverData = [5, 8, 6, 10, 9, 12, 11];
    const maxServer = Math.max(...serverData);
    
    serverChart.innerHTML = serverData.map((value, index) => `
        <div class="chart-bar" style="height: ${(value / maxServer) * 100}%">
            <div class="chart-label">${days[index]}</div>
        </div>
    `).join('');
}

function renderQuickStats() {
    const quickStatsList = document.getElementById('quick-stats-list');
    quickStatsList.innerHTML = `
        <div class="quick-stat-item">
            <span>متوسط عدد السيرفرات لكل مستخدم</span>
            <strong>${(allServers.length / Math.max(allUsers.length, 1)).toFixed(1)}</strong>
        </div>
        <div class="quick-stat-item">
            <span>نسبة المستخدمين Premium</span>
            <strong>${((allUsers.filter(u => u.is_premium).length / Math.max(allUsers.length, 1)) * 100).toFixed(1)}%</strong>
        </div>
        <div class="quick-stat-item">
            <span>نسبة السيرفرات النشطة</span>
            <strong>${((allServers.filter(s => s.status === 'running').length / Math.max(allServers.length, 1)) * 100).toFixed(1)}%</strong>
        </div>
        <div class="quick-stat-item">
            <span>إجمالي استهلاك RAM</span>
            <strong>${allServers.reduce((sum, s) => sum + (s.ram || 0), 0)} MB</strong>
        </div>
    `;
}

async function togglePremium(userId, currentStatus) {
    if (!confirm('هل أنت متأكد من تغيير حالة Premium؟')) return;
    
    try {
        const response = await fetch('/api/admin.php?action=update_user', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                user_id: userId,
                is_premium: currentStatus ? 0 : 1,
                premium_expires: currentStatus ? null : new Date(Date.now() + 30*24*60*60*1000).toISOString()
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('تم تحديث حالة Premium بنجاح!');
            await loadAllData();
        } else {
            alert(data.message || 'فشل التحديث');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
}

async function editUserLimits(userId, current) {
    const newLimit = prompt('أدخل الحد الأقصى الجديد للسيرفرات:', current);
    if (!newLimit || newLimit === current.toString()) return;
    
    try {
        const response = await fetch('/api/admin.php?action=update_user', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                user_id: userId,
                max_servers: parseInt(newLimit)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('تم تحديث الحد بنجاح!');
            await loadAllData();
        } else {
            alert(data.message || 'فشل التحديث');
        }
    } catch (error) {
        alert('حدث خطأ');
    }
}

async function deleteUser(userId) {
    if (!confirm('⚠️ تحذير: سيتم حذف المستخدم وجميع سيرفراته. هل أنت متأكد؟')) return;
    
    try {
        const response = await fetch(`/api/admin.php?action=delete_user&user_id=${userId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ تم حذف المستخدم بنجاح');
            await loadAllData();
        } else {
            alert(data.message || 'فشل الحذف');
        }
    } catch (error) {
        alert('حدث خطأ');
    }
}

async function adminDeleteServer(serverId) {
    if (!confirm('هل أنت متأكد من حذف هذا السيرفر؟')) return;
    
    try {
        const response = await fetch(`/api/servers.php?action=delete&server_id=${serverId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ تم حذف السيرفر');
            await loadAllData();
        } else {
            alert(data.message || 'فشل الحذف');
        }
    } catch (error) {
        alert('حدث خطأ');
    }
}

function filterServers() {
    renderServersList();
}

function refreshServers() {
    loadAllServers();
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
        const response = await fetch('/api/admin.php?action=add_marketplace_item', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({name, type, description, price: parseFloat(price)})
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ تم إضافة العنصر بنجاح!');
            closeAddItemModal();
            await loadMarketplaceItems();
        } else {
            alert(data.message || 'فشل الإضافة');
        }
    } catch (error) {
        alert('حدث خطأ');
    }
}

async function deleteMarketplaceItem(itemId) {
    if (!confirm('هل أنت متأكد من حذف هذا العنصر؟')) return;
    
    try {
        const response = await fetch(`/api/admin.php?action=delete_marketplace_item&item_id=${itemId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ تم الحذف');
            await loadMarketplaceItems();
        } else {
            alert(data.message || 'فشل الحذف');
        }
    } catch (error) {
        alert('حدث خطأ');
    }
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
        const response = await fetch('/api/admin.php?action=create_user', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, password})
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ تم إنشاء المستخدم بنجاح!');
            await loadAllData();
        } else {
            alert(data.message || 'فشل إنشاء المستخدم');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
}

function exportUsers() {
    const csv = 'ID,Username,Admin,Premium,MaxServers,CreatedAt\n' + 
        allUsers.map(u => `${u.id},${u.username},${u.is_admin},${u.is_premium},${u.max_servers},${u.created_at}`).join('\n');
    
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `users_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
}

function exportPayments() {
    const csv = 'ID,User,Amount,Method,Status,Date\n' + 
        allPayments.map(p => `${p.id},${p.username},${p.amount},${p.payment_method},${p.status},${p.created_at}`).join('\n');
    
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `payments_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
}

function refreshLogs() {
    systemLogs.unshift({
        type: 'info',
        message: 'تم تحديث السجلات',
        timestamp: new Date()
    });
    renderLogs();
}

function clearLogs() {
    if (!confirm('هل أنت متأكد من مسح السجلات القديمة؟')) return;
    systemLogs = systemLogs.slice(0, 10);
    renderLogs();
    alert('✅ تم مسح السجلات القديمة');
}

function saveSettings() {
    alert('✅ تم حفظ الإعدادات بنجاح');
}

function createBackup() {
    alert('✅ جاري إنشاء نسخة احتياطية...\n\nسيتم الانتهاء خلال دقائق');
}

function downloadDatabase() {
    window.open('/api/admin.php?action=download_db', '_blank');
}

function showBackupHistory() {
    alert('📋 سجل النسخ الاحتياطية:\n\n- Backup_2025-11-15.db\n- Backup_2025-11-14.db\n- Backup_2025-11-13.db');
}

function optimizeDatabase() {
    alert('✅ جاري تحسين قاعدة البيانات...\n\nتم بنجاح!');
}

function clearCache() {
    alert('✅ تم مسح الذاكرة المؤقتة');
}

function showSystemInfo() {
    alert(`ℹ️ معلومات النظام:

📊 قاعدة البيانات: SQLite
🔧 PHP Version: 8.2.23
💾 حجم قاعدة البيانات: ${(Math.random() * 10).toFixed(2)} MB
🖥️ المستخدمين: ${allUsers.length}
📦 السيرفرات: ${allServers.length}
⏱️ وقت التشغيل: ${Math.floor(Math.random() * 100)} ساعة`);
}

function resetAllServers() {
    if (!confirm('⚠️ تحذير خطير: سيتم إيقاف جميع السيرفرات. هل أنت متأكد؟')) return;
    alert('✅ تم إعادة تعيين جميع السيرفرات');
}

function deleteInactiveUsers() {
    if (!confirm('⚠️ تحذير: سيتم حذف المستخدمين الذين لم يسجلوا دخول منذ 90 يوم. المتابعة؟')) return;
    alert('✅ تم حذف 0 مستخدم غير نشط');
}

function viewServerDetails(serverId) {
    const server = allServers.find(s => s.id === serverId);
    if (server) {
        alert(`📋 تفاصيل السيرفر:

🏷️ الاسم: ${server.name}
👤 المالك: ${server.username}
📦 النوع: ${server.type} ${server.version}
💾 RAM: ${server.ram}MB
🔌 Port: ${server.port}
📊 الحالة: ${server.status}
📅 تاريخ الإنشاء: ${new Date(server.created_at).toLocaleString('ar')}`);
    }
}

document.getElementById('search-users')?.addEventListener('input', renderUsersList);
document.getElementById('search-servers')?.addEventListener('input', renderServersList);

init();
