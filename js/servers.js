let currentUser = null;
let currentServerId = null;

async function init() {
    currentUser = await checkAuth();
    
    if (currentUser && currentUser.is_admin) {
        document.getElementById('admin-link').style.display = 'block';
        document.getElementById('admin-link').href = '/admin.php';
    }
    
    loadServers();
    setInterval(loadServers, 5000);
}

async function loadServers() {
    try {
        const response = await fetch('/api/servers.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            const serversList = document.getElementById('servers-list');
            
            if (data.servers.length === 0) {
                serversList.innerHTML = '<p style="text-align:center; margin-top:2rem;">لا توجد سيرفرات. ابدأ بإنشاء سيرفر جديد!</p>';
                return;
            }
            
            serversList.innerHTML = data.servers.map(server => `
                <div class="server-card">
                    <div class="server-header">
                        <h3>${server.name}</h3>
                        <span class="server-status status-${server.status}">${server.status}</span>
                    </div>
                    <div class="server-info">
                        <p>📦 ${server.type} ${server.version}</p>
                        <p>🔌 Port: ${server.port}</p>
                        <p>💾 RAM: ${server.ram}MB</p>
                        ${server.username ? `<p>👤 Owner: ${server.username}</p>` : ''}
                    </div>
                    <div class="server-actions">
                        <button class="btn-start" onclick="startServer(${server.id})" ${server.status === 'running' ? 'disabled' : ''}>▶️ Start</button>
                        <button class="btn-stop" onclick="stopServer(${server.id})" ${server.status === 'stopped' ? 'disabled' : ''}>⏹️ Stop</button>
                        <button class="btn-console" onclick="openConsole(${server.id})">🖥️ Console</button>
                        <button class="btn-stop" onclick="deleteServer(${server.id})">🗑️ Delete</button>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading servers:', error);
    }
}

function showCreateModal() {
    document.getElementById('create-modal').style.display = 'block';
}

function closeCreateModal() {
    document.getElementById('create-modal').style.display = 'none';
}

async function createServer() {
    const name = document.getElementById('server-name').value || 'My Server';
    const version = document.getElementById('server-version').value;
    const type = document.getElementById('server-type').value;
    const ram = parseInt(document.getElementById('server-ram').value);
    
    try {
        const response = await fetch('/api/servers.php?action=create', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({name, version, type, ram})
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('تم إنشاء السيرفر بنجاح!');
            closeCreateModal();
            loadServers();
        } else {
            alert(data.message || 'فشل إنشاء السيرفر');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
}

async function startServer(serverId) {
    try {
        const response = await fetch('/api/servers.php?action=start', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({server_id: serverId})
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadServers();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('حدث خطأ');
    }
}

async function stopServer(serverId) {
    try {
        const response = await fetch('/api/servers.php?action=stop', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({server_id: serverId})
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadServers();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('حدث خطأ');
    }
}

async function deleteServer(serverId) {
    if (!confirm('هل أنت متأكد من حذف السيرفر؟')) return;
    
    try {
        const response = await fetch(`/api/servers.php?action=delete&server_id=${serverId}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('تم حذف السيرفر');
            loadServers();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('حدث خطأ');
    }
}

function openConsole(serverId) {
    currentServerId = serverId;
    document.getElementById('console-server-id').textContent = serverId;
    document.getElementById('console-modal').style.display = 'block';
}

function closeConsoleModal() {
    document.getElementById('console-modal').style.display = 'none';
    currentServerId = null;
}

async function sendCommand() {
    const command = document.getElementById('console-command').value;
    
    if (!command || !currentServerId) return;
    
    try {
        const response = await fetch('/api/servers.php?action=execute', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({server_id: currentServerId, command})
        });
        
        const data = await response.json();
        
        if (data.success) {
            const consoleOutput = document.getElementById('console-output');
            consoleOutput.innerHTML += `> ${command}\n`;
            document.getElementById('console-command').value = '';
        }
    } catch (error) {
        console.error('Error sending command:', error);
    }
}

async function kickPlayer() {
    const playerName = document.getElementById('player-name').value;
    if (!playerName) return;
    
    await fetch('/api/players.php?action=kick', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({server_id: currentServerId, player: playerName})
    });
    
    alert('تم طرد اللاعب');
}

async function banPlayer() {
    const playerName = document.getElementById('player-name').value;
    if (!playerName) return;
    
    await fetch('/api/players.php?action=ban', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({server_id: currentServerId, player: playerName})
    });
    
    alert('تم حظر اللاعب');
}

async function opPlayer() {
    const playerName = document.getElementById('player-name').value;
    if (!playerName) return;
    
    await fetch('/api/players.php?action=op', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({server_id: currentServerId, player: playerName})
    });
    
    alert('تم إعطاء صلاحيات للاعب');
}

async function whitelistPlayer() {
    const playerName = document.getElementById('player-name').value;
    if (!playerName) return;
    
    await fetch('/api/players.php?action=whitelist', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({server_id: currentServerId, player: playerName})
    });
    
    alert('تم إضافة اللاعب للقائمة البيضاء');
}

init();
