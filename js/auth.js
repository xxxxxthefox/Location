function showRegister() {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('register-form').style.display = 'block';
}

function showLogin() {
    document.getElementById('register-form').style.display = 'none';
    document.getElementById('login-form').style.display = 'block';
}

async function login() {
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;
    
    if (!username || !password) {
        alert('الرجاء إدخال اسم المستخدم وكلمة المرور');
        return;
    }
    
    try {
        const response = await fetch('/api/auth.php?action=login', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, password})
        });
        
        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('user', JSON.stringify(data.user));
            window.location.href = '/dashboard.php';
        } else {
            alert(data.message || 'فشل تسجيل الدخول');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
}

async function register() {
    const username = document.getElementById('register-username').value;
    const password = document.getElementById('register-password').value;
    
    if (!username || !password) {
        alert('الرجاء إدخال اسم المستخدم وكلمة المرور');
        return;
    }
    
    try {
        const response = await fetch('/api/auth.php?action=register', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, password})
        });
        
        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('user', JSON.stringify(data.user));
            alert('تم إنشاء الحساب بنجاح! 🎉 لديك شهر مجاني من Premium');
            window.location.href = '/dashboard.php';
        } else {
            alert(data.message || 'فشل إنشاء الحساب');
        }
    } catch (error) {
        alert('حدث خطأ في الاتصال');
    }
}

async function checkAuth() {
    try {
        const response = await fetch('/api/auth.php?action=check');
        const data = await response.json();
        
        if (!data.success) {
            window.location.href = '/';
        } else {
            localStorage.setItem('user', JSON.stringify(data.user));
            return data.user;
        }
    } catch (error) {
        window.location.href = '/';
    }
}

async function logout() {
    await fetch('/api/auth.php?action=logout', {method: 'POST'});
    localStorage.removeItem('user');
    window.location.href = '/';
}

function toggleTheme() {
    document.body.classList.toggle('light-theme');
    document.body.classList.toggle('dark-theme');
    const theme = document.body.classList.contains('light-theme') ? 'light' : 'dark';
    localStorage.setItem('theme', theme);
    document.querySelector('.theme-toggle').textContent = theme === 'dark' ? '🌙' : '☀️';
}

if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-theme');
    document.body.classList.remove('dark-theme');
}
