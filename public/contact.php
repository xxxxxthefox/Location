<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تواصل معنا - MineHub</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="nav-brand">
            <img src="/images/minehub-logo.png" alt="MineHub" class="nav-logo">
            MineHub
        </div>
        <div class="nav-menu">
            <a href="/dashboard.php">Dashboard</a>
            <a href="/servers.php">السيرفرات</a>
            <a href="/marketplace.php">Marketplace</a>
            <a href="/premium.php">Premium</a>
            <a href="/contact.php" class="active">تواصل معنا</a>
            <a href="#" id="admin-link" style="display:none;">Admin</a>
            <a href="#" onclick="logout()">تسجيل خروج</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">🌙</div>
    </nav>
    
    <div class="container">
        <div class="contact-page">
            <div class="contact-hero">
                <h1>📞 تواصل مع المطور</h1>
                <p class="hero-subtitle">نحن هنا لمساعدتك في أي وقت</p>
            </div>
            
            <div class="contact-card">
                <div class="contact-info">
                    <div class="info-item">
                        <div class="info-icon">📱</div>
                        <div class="info-content">
                            <h3>Telegram</h3>
                            <a href="https://t.me/QP4RM" target="_blank" class="contact-link-large">
                                @QP4RM
                            </a>
                            <p>تواصل معنا مباشرة عبر تيليجرام للحصول على دعم سريع</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">💬</div>
                        <div class="info-content">
                            <h3>الدعم الفني</h3>
                            <p>متوفر 24/7 للإجابة على استفساراتك ومساعدتك في إدارة سيرفراتك</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">⚡</div>
                        <div class="info-content">
                            <h3>استجابة سريعة</h3>
                            <p>نسعى للرد على جميع الاستفسارات في أقل من 24 ساعة</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">🎯</div>
                        <div class="info-content">
                            <h3>مساعدة متخصصة</h3>
                            <p>فريقنا لديه خبرة واسعة في إدارة وتشغيل سيرفرات Minecraft</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-footer">
                    <h3>✨ اقتراحات وملاحظات</h3>
                    <p>نرحب بجميع الاقتراحات والملاحظات لتحسين الخدمة</p>
                    <a href="https://t.me/QP4RM" target="_blank" class="btn-primary">
                        تواصل الآن
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/js/dashboard.js"></script>
</body>
</html>
