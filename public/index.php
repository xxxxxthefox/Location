<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MineHub - Professional Minecraft Server Hosting</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dark-theme">
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <img src="/images/minehub-logo.png" alt="MineHub Logo" class="logo-image">
                <h1>MineHub</h1>
                <p>Professional Minecraft Server Hosting</p>
            </div>
            
            <div id="login-form" class="form-container">
                <h2>تسجيل الدخول</h2>
                <input type="text" id="login-username" placeholder="اسم المستخدم" autocomplete="username">
                <input type="password" id="login-password" placeholder="كلمة المرور" autocomplete="current-password">
                <button onclick="login()">دخول</button>
                <p class="switch-form">ليس لديك حساب؟ <a href="#" onclick="showRegister()">إنشاء حساب</a></p>
            </div>
            
            <div id="register-form" class="form-container" style="display:none;">
                <h2>إنشاء حساب جديد</h2>
                <input type="text" id="register-username" placeholder="اسم المستخدم" autocomplete="username">
                <input type="password" id="register-password" placeholder="كلمة المرور" autocomplete="new-password">
                <button onclick="register()">إنشاء حساب</button>
                <p class="switch-form">لديك حساب؟ <a href="#" onclick="showLogin()">تسجيل الدخول</a></p>
                <div class="trial-notice">🎉 احصل على شهر مجاني كامل من Premium عند التسجيل!</div>
            </div>
            
            <div class="contact-section">
                <p>تواصل مع المطور</p>
                <a href="https://t.me/QP4RM" target="_blank" class="contact-link">
                    📱 Telegram: @QP4RM
                </a>
            </div>
        </div>
    </div>
    
    <script src="/js/auth.js"></script>
</body>
</html>
