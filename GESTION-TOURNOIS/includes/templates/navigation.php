<nav class="navbar">
    <div class="container">
        <div class="nav-brand">
            <a href="/" class="logo">
                <i class="fas fa-trophy"></i>
                نظام البطولات
            </a>
            <button class="nav-toggle" id="navToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <ul class="nav-menu" id="navMenu">
            <li><a href="/" class="nav-link"><i class="fas fa-home"></i> الرئيسية</a></li>
            <li><a href="/modules/equipes/" class="nav-link"><i class="fas fa-users"></i> الفرق</a></li>
            <li><a href="/modules/tournois/" class="nav-link"><i class="fas fa-trophy"></i> البطولات</a></li>
            <li><a href="/modules/matches/" class="nav-link"><i class="fas fa-futbol"></i> المباريات</a></li>
            
            <?php if(isset($_SESSION['user'])): ?>
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['user']['nom'] ?? 'المستخدم', ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="/dashboard.php"><i class="fas fa-dashboard"></i> لوحة التحكم</a></li>
                        <li><a href="/modules/auth/profile.php"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="/modules/auth/logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="/login.php" class="nav-link btn-login"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<?php if(isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible">
        <button class="alert-close">&times;</button>
        <i class="fas fa-check-circle"></i>
        <?php 
            echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); 
            unset($_SESSION['success_message']);
        ?>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible">
        <button class="alert-close">&times;</button>
        <i class="fas fa-exclamation-circle"></i>
        <?php 
            echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); 
            unset($_SESSION['error_message']);
        ?>
    </div>
<?php endif; ?>