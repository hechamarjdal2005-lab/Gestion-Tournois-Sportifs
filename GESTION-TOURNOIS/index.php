<?php
session_start();
$pageTitle = 'الصفحة الرئيسية - نظام إدارة البطولات';
include 'includes/templates/header.php';
include 'includes/templates/navigation.php';
?>

<div class="container">
    <!-- Hero Section -->
    <div class="hero">
        <h1>⚽ مرحباً بك في نظام إدارة البطولات</h1>
        <p>نظام متكامل ومتطور لإدارة الفرق والبطولات الرياضية والمباريات</p>
        
        <?php if(!isset($_SESSION['user'])): ?>
            <a href="/login.php" class="btn btn-lg btn-outline">
                <i class="fas fa-sign-in-alt"></i>
                ابدأ الآن
            </a>
        <?php else: ?>
            <a href="/dashboard.php" class="btn btn-lg btn-outline">
                <i class="fas fa-dashboard"></i>
                لوحة التحكم
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Features Section -->
    <div class="features">
        <div class="card feature-card">
            <i class="fas fa-users"></i>
            <h3>إدارة الفرق</h3>
            <p>أضف وعدل معلومات الفرق مع إمكانية رفع الشعارات والصور</p>
            <a href="/modules/equipes/" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-left"></i>
                عرض الفرق
            </a>
        </div>
        
        <div class="card feature-card">
            <i class="fas fa-trophy"></i>
            <h3>إدارة البطولات</h3>
            <p>نظم البطولات بأنواعها المختلفة (دوري، كأس، مجموعات)</p>
            <a href="/modules/tournois/" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-left"></i>
                عرض البطولات
            </a>
        </div>
        
        <div class="card feature-card">
            <i class="fas fa-futbol"></i>
            <h3>تتبع المباريات</h3>
            <p>سجل النتائج وتابع الترتيب والإحصائيات بسهولة</p>
            <a href="/modules/matches/" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-left"></i>
                عرض المباريات
            </a>
        </div>
    </div>
    
    <!-- Statistics Section (if logged in) -->
    <?php if(isset($_SESSION['user'])): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-chart-bar"></i>
                إحصائيات سريعة
            </h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <div class="stat-box text-center">
                        <i class="fas fa-users fa-3x" style="color: var(--primary);"></i>
                        <h3>25</h3>
                        <p>فريق</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-box text-center">
                        <i class="fas fa-trophy fa-3x" style="color: var(--warning);"></i>
                        <h3>8</h3>
                        <p>بطولة</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-box text-center">
                        <i class="fas fa-futbol fa-3x" style="color: var(--success);"></i>
                        <h3>120</h3>
                        <p>مباراة</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/templates/footer.php'; ?>