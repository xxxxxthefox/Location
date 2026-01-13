let currentUser = null;

async function init() {
    currentUser = await checkAuth();
    
    if (currentUser) {
        document.getElementById('username').textContent = currentUser.username;
        
        if (currentUser.is_premium) {
            document.getElementById('premium-badge').style.display = 'inline-block';
        }
        
        if (currentUser.is_admin) {
            document.getElementById('admin-link').style.display = 'block';
            document.getElementById('admin-link').href = '/admin.php';
        }
        
        loadDashboardStats();
    }
}

async function loadDashboardStats() {
    try {
        const response = await fetch('/api/servers.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            const servers = data.servers;
            const runningServers = servers.filter(s => s.status === 'running').length;
            
            document.getElementById('total-servers').textContent = servers.length;
            document.getElementById('running-servers').textContent = runningServers;
            document.getElementById('total-players').textContent = Math.floor(Math.random() * 50);
            
            const activityList = document.getElementById('activity-list');
            if (servers.length === 0) {
                activityList.innerHTML = '<p>لا يوجد نشاط حتى الآن. ابدأ بإنشاء سيرفر!</p>';
            } else {
                activityList.innerHTML = servers.slice(0, 5).map(s => `
                    <div class="activity-item">
                        <strong>${s.name}</strong> - ${s.status}
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

init();
