<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$pageTitle = 'لوحة التحكم';
include 'includes/templates/header.php';
include 'includes/templates/navigation.php';

// Get user info
$user = $_SESSION['user'];
$userName = htmlspecialchars($user['nom'] ?? 'المستخدم', ENT_QUOTES, 'UTF-8');
$userRole = htmlspecialchars($user['role'] ?? 'user', ENT_QUOTES, 'UTF-8');
?>

<div class="container">
    <!-- Welcome Section -->
    <div class="card" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; margin-top: 2rem;">
        <h1><i class="fas fa-dashboard"></i> مرحباً، <?php echo $userName; ?>!</h1>
        <p>الدور: <strong><?php echo $userRole === 'admin' ? 'مدير' : 'مستخدم'; ?></strong></p>
        <p>آخر دخول: <?php echo date('Y-m-d H:i'); ?></p>
    </div>
    
    <!-- Quick Stats -->
    <div class="row">
        <div class="col-4">
            <div class="card text-center" style="border-right: 4px solid var(--primary);">
                <i class="fas fa-users fa-3x" style="color: var(--primary);"></i>
                <h2 style="margin: 1rem 0;">25</h2>
                <p>إجمالي الفرق</p>
                <a href="/modules/equipes/" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i> عرض
                </a>
            </div>
        </div>
        
        <div class="col-4">
            <div class="card text-center" style="border-right: 4px solid var(--warning);">
                <i class="fas fa-trophy fa-3x" style="color: var(--warning);"></i>
                <h2 style="margin: 1rem 0;">8</h2>
                <p>البطولات النشطة</p>
                <a href="/modules/tournois/" class="btn btn-warning btn-sm">
                    <i class="fas fa-eye"></i> عرض
                </a>
            </div>
        </div>
        
        <div class="col-4">
            <div class="card text-center" style="border-right: 4px solid var(--success);">
                <i class="fas fa-futbol fa-3x" style="color: var(--success);"></i>
                <h2 style="margin: 1rem 0;">120</h2>
                <p>إجمالي المباريات</p>
                <a href="/modules/matches/" class="btn btn-success btn-sm">
                    <i class="fas fa-eye"></i> عرض
                </a>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-bolt"></i>
                إجراءات سريعة
            </h2>
        </div>
        <div class="card-body">
            <div class="row">
                <?php if($userRole === 'admin'): ?>
                <div class="col-3">
                    <a href="/modules/equipes/create.php" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus"></i>
                        إضافة فريق جديد
                    </a>
                </div>
                
                <div class="col-3">
                    <a href="/modules/tournois/create.php" class="btn btn-warning" style="width: 100%;">
                        <i class="fas fa-plus"></i>
                        إنشاء بطولة
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="col-3">
                    <a href="/modules/matches/create.php" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-plus"></i>
                        إضافة مباراة
                    </a>
                </div>
                
                <div class="col-3">
                    <a href="/modules/auth/profile.php" class="btn btn-secondary" style="width: 100%;">
                        <i class="fas fa-user"></i>
                        الملف الشخصي
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="row">
        <!-- Latest Teams -->
        <div class="col-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i>
                        آخر الفرق المضافة
                    </h3>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <div class="list-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>الرجاء الرياضي</span>
                        </div>
                        <div class="list-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>الوداد البيضاوي</span>
                        </div>
                        <div class="list-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>حسنية أكادير</span>
                        </div>
                    </div>
                    <a href="/modules/equipes/" class="btn btn-outline btn-sm" style="margin-top: 1rem;">
                        عرض الكل
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Matches -->
        <div class="col-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar"></i>
                        المباريات القادمة
                    </h3>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <div class="list-item">
                            <i class="fas fa-futbol"></i>
                            <span>الرجاء vs الوداد - غداً</span>
                        </div>
                        <div class="list-item">
                            <i class="fas fa-futbol"></i>
                            <span>حسنية vs المغرب الفاسي - 3 أيام</span>
                        </div>
                        <div class="list-item">
                            <i class="fas fa-futbol"></i>
                            <span>الجيش الملكي vs أولمبيك آسفي - 5 أيام</span>
                        </div>
                    </div>
                    <a href="/modules/matches/" class="btn btn-outline btn-sm" style="margin-top: 1rem;">
                        عرض الكل
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.list-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.list-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--light);
    border-radius: var(--radius);
}

.list-item i {
    color: var(--primary);
}
</style>

<?php include 'includes/templates/footer.php'; ?>