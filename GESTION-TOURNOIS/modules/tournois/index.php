<?php
session_start();
require_once '../../includes/config/database.php';

$pageTitle = 'قائمة البطولات';
include '../../includes/templates/header.php';
include '../../includes/templates/navigation.php';

// Fetch tournaments
try {
    $stmt = $pdo->query("SELECT * FROM tournois ORDER BY date_debut DESC");
    $tournois = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $tournois = [];
    $_SESSION['error_message'] = "خطأ في جلب البيانات";
}
?>

<div class="container">
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">
                <i class="fas fa-trophy"></i>
                قائمة البطولات
            </h2>
            
            <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/modules/tournois/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    إنشاء بطولة جديدة
                </a>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <?php if(empty($tournois)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    لا توجد بطولات حالياً. قم بإنشاء بطولة جديدة للبدء.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach($tournois as $tournoi): ?>
                    <div class="col-3">
                        <div class="card">
                            <div style="text-align: center; padding: 2rem; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; border-radius: var(--radius) var(--radius) 0 0;">
                                <i class="fas fa-trophy fa-3x"></i>
                            </div>
                            
                            <div style="padding: 1.5rem;">
                                <h3><?php echo htmlspecialchars($tournoi['nom'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                
                                <p style="margin: 1rem 0;">
                                    <i class="fas fa-tag"></i>
                                    <strong>النوع:</strong>
                                    <?php 
                                    $types = [
                                        'league' => 'دوري',
                                        'cup' => 'كأس',
                                        'groups' => 'مجموعات'
                                    ];
                                    echo $types[$tournoi['type']] ?? $tournoi['type'];
                                    ?>
                                </p>
                                
                                <p style="margin: 1rem 0;">
                                    <i class="fas fa-calendar"></i>
                                    <strong>البداية:</strong>
                                    <?php echo htmlspecialchars($tournoi['date_debut'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                
                                <p style="margin: 1rem 0;">
                                    <i class="fas fa-users"></i>
                                    <strong>الفرق:</strong>
                                    <?php echo $tournoi['nombre_equipes'] ?? 0; ?>
                                </p>
                                
                                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                                    <a href="/modules/tournois/standings.php?id=<?php echo $tournoi['id']; ?>" 
                                       class="btn btn-sm btn-primary" style="flex: 1;">
                                        <i class="fas fa-list"></i> الترتيب
                                    </a>
                                    
                                    <a href="/modules/tournois/bracket.php?id=<?php echo $tournoi['id']; ?>" 
                                       class="btn btn-sm btn-success" style="flex: 1;">
                                        <i class="fas fa-sitemap"></i> الجدول
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/templates/footer.php'; ?>