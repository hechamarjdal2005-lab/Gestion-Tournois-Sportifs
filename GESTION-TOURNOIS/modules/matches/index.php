<?php
session_start();
require_once '../../includes/config/database.php';

$pageTitle = 'قائمة المباريات';
include '../../includes/templates/header.php';
include '../../includes/templates/navigation.php';

// Fetch matches
try {
    $stmt = $pdo->query("
        SELECT m.*, 
               e1.nom as equipe1_nom, e1.logo as equipe1_logo,
               e2.nom as equipe2_nom, e2.logo as equipe2_logo,
               t.nom as tournoi_nom
        FROM matches m
        LEFT JOIN equipes e1 ON m.equipe1_id = e1.id
        LEFT JOIN equipes e2 ON m.equipe2_id = e2.id
        LEFT JOIN tournois t ON m.tournoi_id = t.id
        ORDER BY m.date_match DESC
    ");
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $matches = [];
    $_SESSION['error_message'] = "خطأ في جلب البيانات";
}
?>

<div class="container">
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">
                <i class="fas fa-futbol"></i>
                قائمة المباريات
            </h2>
            
            <?php if(isset($_SESSION['user'])): ?>
                <a href="/modules/matches/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    إضافة مباراة جديدة
                </a>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <?php if(empty($matches)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    لا توجد مباريات حالياً.
                </div>
            <?php else: ?>
                <?php foreach($matches as $match): ?>
                <div class="card" style="margin-bottom: 1rem; border: 2px solid var(--border);">
                    <div style="padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <!-- Team 1 -->
                            <div style="flex: 1; text-align: center;">
                                <?php if(!empty($match['equipe1_logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($match['equipe1_logo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                         style="width: 60px; height: 60px; object-fit: contain;">
                                <?php else: ?>
                                    <i class="fas fa-shield-alt fa-3x" style="color: var(--secondary);"></i>
                                <?php endif; ?>
                                <h3 style="margin-top: 0.5rem;">
                                    <?php echo htmlspecialchars($match['equipe1_nom'], ENT_QUOTES, 'UTF-8'); ?>
                                </h3>
                            </div>
                            
                            <!-- Score -->
                            <div style="flex: 1; text-align: center;">
                                <?php if($match['score_equipe1'] !== null && $match['score_equipe2'] !== null): ?>
                                    <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary);">
                                        <?php echo $match['score_equipe1']; ?> - <?php echo $match['score_equipe2']; ?>
                                    </div>
                                    <span style="color: var(--success);">
                                        <i class="fas fa-check-circle"></i> انتهت
                                    </span>
                                <?php else: ?>
                                    <div style="font-size: 2rem; color: var(--secondary);">
                                        VS
                                    </div>
                                    <span style="color: var(--warning);">
                                        <i class="fas fa-clock"></i> لم تبدأ
                                    </span>
                                <?php endif; ?>
                                
                                <div style="margin-top: 1rem;">
                                    <small style="color: var(--secondary);">
                                        <i class="fas fa-trophy"></i>
                                        <?php echo htmlspecialchars($match['tournoi_nom'] ?? 'غير محدد', ENT_QUOTES, 'UTF-8'); ?>
                                    </small>
                                </div>
                                
                                <div style="margin-top: 0.5rem;">
                                    <small style="color: var(--secondary);">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('Y-m-d H:i', strtotime($match['date_match'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Team 2 -->
                            <div style="flex: 1; text-align: center;">
                                <?php if(!empty($match['equipe2_logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($match['equipe2_logo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                         style="width: 60px; height: 60px; object-fit: contain;">
                                <?php else: ?>
                                    <i class="fas fa-shield-alt fa-3x" style="color: var(--secondary);"></i>
                                <?php endif; ?>
                                <h3 style="margin-top: 0.5rem;">
                                    <?php echo htmlspecialchars($match['equipe2_nom'], ENT_QUOTES, 'UTF-8'); ?>
                                </h3>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <?php if(isset($_SESSION['user'])): ?>
                        <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid var(--border); padding-top: 1rem;">
                            <?php if($match['score_equipe1'] === null): ?>
                                <a href="/modules/matches/results.php?id=<?php echo $match['id']; ?>" 
                                   class="btn btn-sm btn-success">
                                    <i class="fas fa-edit"></i> إضافة النتيجة
                                </a>
                            <?php endif; ?>
                            
                            <a href="/modules/matches/edit.php?id=<?php echo $match['id']; ?>" 
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> تعديل
                            </a>
                            
                            <?php if($_SESSION['user']['role'] === 'admin'): ?>
                                <a href="/modules/matches/delete.php?id=<?php echo $match['id']; ?>" 
                                   class="btn btn-sm btn-danger btn-delete"
                                   data-confirm="هل أنت متأكد من حذف هذه المباراة؟">
                                    <i class="fas fa-trash"></i> حذف
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/templates/footer.php'; ?>