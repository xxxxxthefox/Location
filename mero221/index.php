<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MineHub - استضافة سيرفرات Minecraft</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-theme">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="public/images/minehub-logo.png" alt="MineHub">
            </div>
            <h1>MineHub</h1>
            <p class="auth-subtitle">منصة استضافة سيرفرات Minecraft مجانية</p>
            
            <div class="auth-tabs">
                <button class="auth-tab active" onclick="showAuthTab('login')">تسجيل دخول</button>
                <button class="auth-tab" onclick="showAuthTab('register')">حساب جديد</button>
            </div>
            
            <div id="login-form" class="auth-form">
                <input type="text" id="login-username" placeholder="اسم المستخدم" autocomplete="username">
                <input type="password" id="login-password" placeholder="كلمة المرور" autocomplete="current-password">
                <button onclick="login()" class="btn-primary">دخول</button>
            </div>
            
            <div id="register-form" class="auth-form" style="display:none;">
                <input type="text" id="register-username" placeholder="اسم المستخدم (3 أحرف على الأقل)" autocomplete="username">
                <input type="password" id="register-password" placeholder="كلمة المرور (6 أحرف على الأقل)" autocomplete="new-password">
                <button onclick="register()" class="btn-primary">إنشاء حساب</button>
            </div>
            
            <div class="auth-footer">
                <p>مجاني تماماً - سيرفر واحد لكل مستخدم</p>
                <a href="public/contact.php" class="contact-link">تواصل معنا</a>
            </div>
        </div>
    </div>
    
    <script>
        const API_BASE = 'api';
        
        function showAuthTab(tab) {
            document.querySelectorAll('.auth-tab').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            if (tab === 'login') {
                document.getElementById('login-form').style.display = 'block';
                document.getElementById('register-form').style.display = 'none';
            } else {
                document.getElementById('login-form').style.display = 'none';
                document.getElementById('register-form').style.display = 'block';
            }
        }
        
        async function login() {
            const username = document.getElementById('login-username').value;
            const password = document.getElementById('login-password').value;
            
            if (!username || !password) {
                alert('الرجاء إدخال اسم المستخدم وكلمة المرور');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=login`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username, password})
                });
                
                const data = await response.json();
                
                if (data.success) {
                    localStorage.setItem('user', JSON.stringify(data.user));
                    window.location.href = 'public/dashboard.php';
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
                alert('الرجاء إدخال جميع الحقول');
                return;
            }
            
            if (username.length < 3) {
                alert('اسم المستخدم يجب أن يكون 3 أحرف على الأقل');
                return;
            }
            
            if (password.length < 6) {
                alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=register`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username, password})
                });
                
                const data = await response.json();
                
                if (data.success) {
                    localStorage.setItem('user', JSON.stringify(data.user));
                    window.location.href = 'public/dashboard.php';
                } else {
                    alert(data.message || 'فشل إنشاء الحساب');
                }
            } catch (error) {
                alert('حدث خطأ في الاتصال');
            }
        }
        
        document.getElementById('login-password').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') login();
        });
        
        document.getElementById('register-password').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') register();
        });
        
        (async function checkAuth() {
            try {
                const response = await fetch(`${API_BASE}/auth.php?action=check`);
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'public/dashboard.php';
                }
            } catch (error) {
                console.log('Not authenticated');
            }
        })();
    </script>
</body>
</html>
