let currentUser = null;
let currentFilter = 'all';

async function init() {
    currentUser = await checkAuth();
    
    if (currentUser && currentUser.is_admin) {
        document.getElementById('admin-link').style.display = 'block';
        document.getElementById('admin-link').href = '/admin.php';
    }
    
    loadMarketplace();
}

async function loadMarketplace() {
    try {
        const response = await fetch(`/api/marketplace.php?action=list&type=${currentFilter}`);
        const data = await response.json();
        
        if (data.success) {
            const grid = document.getElementById('marketplace-grid');
            
            if (data.items.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                        <h3>قريباً...</h3>
                        <p>سيتم إضافة المزيد من الإضافات قريباً!</p>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = data.items.map(item => `
                <div class="marketplace-item">
                    <h3>${item.name}</h3>
                    <p>${item.description || 'لا يوجد وصف'}</p>
                    <div style="margin: 1rem 0;">
                        <span class="item-price">${item.price > 0 ? '$' + item.price : 'مجاني'}</span>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        📦 ${item.type} | ⬇️ ${item.downloads} downloads
                    </p>
                    <button onclick="downloadItem(${item.id})" class="btn-primary" style="width: 100%; margin-top: 1rem;">
                        ${item.price > 0 ? 'شراء' : 'تحميل'}
                    </button>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading marketplace:', error);
    }
}

function filterItems(type) {
    currentFilter = type;
    
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    event.target.classList.add('active');
    
    loadMarketplace();
}

async function downloadItem(itemId) {
    try {
        const response = await fetch('/api/marketplace.php?action=download', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({item_id: itemId})
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`تم تحميل ${data.name} بنجاح!`);
        } else {
            alert(data.message || 'فشل التحميل');
        }
    } catch (error) {
        alert('حدث خطأ');
    }
}

init();
