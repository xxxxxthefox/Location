<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تواصل معنا - MineHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark-theme">
    <nav class="navbar">
        <div class="nav-brand">
            <img src="public/images/minehub-logo.png" alt="MineHub" class="nav-logo">
            MineHub
        </div>
        <div class="nav-menu">
            <a href="">الرئيسية</a>
            <a href="public/contact.php" class="active">تواصل معنا</a>
        </div>
        <div class="theme-toggle" onclick="toggleTheme()">Theme</div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>تواصل معنا</h1>
        </div>
        
        <div class="contact-box">
            <div class="contact-info">
                <h2>للتواصل والدعم الفني</h2>
                <p>يمكنك التواصل معنا عبر Telegram</p>
                <div class="contact-methods">
                    <a href="https://t.me/QP4RM" target="_blank" class="contact-method">
                        <div class="contact-icon">T</div>
                        <div>
                            <h3>Telegram</h3>
                            <p>@QP4RM</p>
                        </div>
                    </a>
                </div>
                
                <div class="contact-note">
                    <h3>خدمات متوفرة:</h3>
                    <ul>
                        <li>استضافة سيرفرات Minecraft مجانية</li>
                        <li>سيرفر واحد لكل مستخدم</li>
                        <li>دعم فني مجاني</li>
                        <li>تحديثات دورية</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleTheme() {
            document.body.classList.toggle('light-theme');
            document.body.classList.toggle('dark-theme');
        }
    </script>
</body>
</html>
