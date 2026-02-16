<?php
session_start();

// Redirect if already logged in
if(isset($_SESSION['user'])) {
    header('Location: /dashboard.php');
    exit;
}

$pageTitle = 'تسجيل الدخول';
include 'includes/templates/header.php';
include 'includes/templates/navigation.php';
?>

<div class="container">
    <div class="row" style="margin-top: 3rem;">
        <!-- Login Form -->
        <div class="col-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-sign-in-alt"></i>
                        تسجيل الدخول
                    </h2>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="/modules/auth/login.php" data-validate>
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                البريد الإلكتروني
                            </label>
                            <input 
                                type="email" 
                                id="email"
                                name="email" 
                                class="form-control" 
                                placeholder="example@email.com"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i>
                                كلمة المرور
                            </label>
                            <input 
                                type="password" 
                                id="password"
                                name="password" 
                                class="form-control" 
                                placeholder="••••••••"
                                required
                                minlength="6"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="remember"> تذكرني
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-sign-in-alt"></i>
                            دخول
                        </button>
                    </form>
                </div>
                
                <div class="card-footer text-center">
                    <p>ليس لديك حساب؟ <a href="/modules/auth/register.php">سجل الآن</a></p>
                </div>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="col-2">
            <div class="card" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white;">
                <h2><i class="fas fa-info-circle"></i> مرحباً بعودتك!</h2>
                <p>سجل دخولك للوصول إلى:</p>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin: 1rem 0;">
                        <i class="fas fa-check-circle"></i>
                        إدارة الفرق والبطولات
                    </li>
                    <li style="margin: 1rem 0;">
                        <i class="fas fa-check-circle"></i>
                        تسجيل نتائج المباريات
                    </li>
                    <li style="margin: 1rem 0;">
                        <i class="fas fa-check-circle"></i>
                        متابعة الإحصائيات
                    </li>
                    <li style="margin: 1rem 0;">
                        <i class="fas fa-check-circle"></i>
                        إنشاء البطولات
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>