<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Servers - MineHub</title>
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
            <a href="public/servers.php" class="active">السيرفرات</a>
            <a href="public/marketplace.php">Marketplace</a>
            <a href="public/contact.php">تواصل معنا</a>
            <a href="public/admin.php" id="admin-link" style="display:none;">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">Theme</div>
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
            <input type="text" id="server-name" placeholder="My Server">
            
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
            <input type="number" id="server-ram" value="1024" min="512" max="2048" step="512">
            
            <button onclick="createServer()" class="btn-primary">إنشاء السيرفر</button>
        </div>
    </div>
    
    <div id="console-modal" class="modal">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeConsoleModal()">&times;</span>
            <h2>Console - Server #<span id="console-server-id"></span></h2>
            
            <div class="console-box" id="console-output"></div>
            
            <div class="console-input">
                <input type="text" id="console-command" placeholder="أدخل الأمر...">
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
    
    <script>
        const API_BASE = 'api';
        let currentUser = null;
        let currentServerId = null;
        let servers = [];
        
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
        
        async function loadServers() {
            try {
                const response = await fetch(`${API_BASE}/servers.php?action=list`);
                const data = await response.json();
                if (data.success) {
                    servers = data.servers || [];
                    renderServers();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        function renderServers() {
            const list = document.getElementById('servers-list');
            if (servers.length === 0) {
                list.innerHTML = '<p style="text-align:center; margin-top:2rem;">لا توجد سيرفرات. قم بإنشاء سيرفر جديد!</p>';
                return;
            }
            
            list.innerHTML = servers.map(server => `
                <div class="server-card">
                    <div class="server-header">
                        <h3>${server.name}</h3>
                        <span class="server-status status-${server.status}">${server.status === 'running' ? 'نشط' : 'متوقف'}</span>
                    </div>
                    <div class="server-info">
                        <p><strong>النوع:</strong> ${server.type} ${server.version}</p>
                        <p><strong>Port:</strong> ${server.port}</p>
                        <p><strong>RAM:</strong> ${server.ram}MB</p>
                    </div>
                    <div class="server-actions">
                        <button onclick="startServer(${server.id})" class="btn-start" ${server.status === 'running' ? 'disabled' : ''}>تشغيل</button>
                        <button onclick="stopServer(${server.id})" class="btn-stop" ${server.status === 'stopped' ? 'disabled' : ''}>إيقاف</button>
                        <button onclick="restartServer(${server.id})" class="btn-secondary">إعادة تشغيل</button>
                        <button onclick="deleteServer(${server.id})" class="btn-danger">حذف</button>
                    </div>
                </div>
            `).join('');
        }
        
        function showCreateModal() {
            document.getElementById('create-modal').style.display = 'block';
        }
        
        function closeCreateModal() {
            document.getElementById('create-modal').style.display = 'none';
        }
        
        async function createServer() {
            const name = document.getElementById('server-name').value;
            const version = document.getElementById('server-version').value;
            const type = document.getElementById('server-type').value;
            const ram = parseInt(document.getElementById('server-ram').value);
            
            if (!name) {
                alert('الرجاء إدخال اسم السيرفر');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/servers.php?action=create`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({name, version, type, ram})
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('تم إنشاء السيرفر بنجاح!');
                    closeCreateModal();
                    await loadServers();
                } else {
                    alert(data.message || 'فشل إنشاء السيرفر');
                }
            } catch (error) {
                alert('حدث خطأ');
            }
        }
        
        async function startServer(id) {
            try {
                const response = await fetch(`${API_BASE}/servers.php?action=start`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({server_id: id})
                });
                const data = await response.json();
                alert(data.message || 'تم تشغيل السيرفر');
                await loadServers();
            } catch (error) {
                alert('حدث خطأ');
            }
        }
        
        async function stopServer(id) {
            try {
                const response = await fetch(`${API_BASE}/servers.php?action=stop`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({server_id: id})
                });
                const data = await response.json();
                alert(data.message || 'تم إيقاف السيرفر');
                await loadServers();
            } catch (error) {
                alert('حدث خطأ');
            }
        }
        
        async function restartServer(id) {
            try {
                const response = await fetch(`${API_BASE}/servers.php?action=restart`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({server_id: id})
                });
                const data = await response.json();
                alert(data.message || 'تم إعادة تشغيل السيرفر');
                await loadServers();
            } catch (error) {
                alert('حدث خطأ');
            }
        }
        
        async function deleteServer(id) {
            if (!confirm('هل أنت متأكد من حذف هذا السيرفر؟')) return;
            
            try {
                const response = await fetch(`${API_BASE}/servers.php?action=delete&server_id=${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                alert(data.message || 'تم حذف السيرفر');
                await loadServers();
            } catch (error) {
                alert('حدث خطأ');
            }
        }
        
        function closeConsoleModal() {
            document.getElementById('console-modal').style.display = 'none';
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
                await loadServers();
                setInterval(loadServers, 5000);
            }
        })();
    </script>
</body>
</html>
