<?php
session_start();
require_once '../../includes/config/database.php';

// Get team ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if(!$id) {
    $_SESSION['error_message'] = "معرّف الفريق غير صحيح";
    header('Location: /modules/equipes/');
    exit;
}

// Fetch team details
try {
    $stmt = $pdo->prepare("SELECT * FROM equipes WHERE id = ?");
    $stmt->execute([$id]);
    $equipe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$equipe) {
        $_SESSION['error_message'] = "الفريق غير موجود";
        header('Location: /modules/equipes/');
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "خطأ في جلب البيانات";
    header('Location: /modules/equipes/');
    exit;
}

$pageTitle = htmlspecialchars($equipe['nom'], ENT_QUOTES, 'UTF-8');
include '../../includes/templates/header.php';
include '../../includes/templates/navigation.php';
?>

<div class="container">
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">
                <i class="fas fa-shield-alt"></i>
                <?php echo htmlspecialchars($equipe['nom'], ENT_QUOTES, 'UTF-8'); ?>
            </h2>
            
            <div>
                <a href="/modules/equipes/" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i>
                    رجوع
                </a>
                
                <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="/modules/equipes/edit.php?id=<?php echo $equipe['id']; ?>" 
                       class="btn btn-warning">
                        <i class="fas fa-edit"></i>
                        تعديل
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row">
                <!-- Logo -->
                <div class="col-3">
                    <div class="text-center">
                        <?php if(!empty($equipe['logo'])): ?>
                            <img src="<?php echo htmlspecialchars($equipe['logo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="Logo" 
                                 style="max-width: 200px; border-radius: var(--radius); box-shadow: 0 4px 12px var(--shadow);">
                        <?php else: ?>
                            <i class="fas fa-shield-alt" style="font-size: 150px; color: var(--secondary);"></i>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Info -->
                <div class="col-3">
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 1rem; font-weight: bold;">
                                <i class="fas fa-signature"></i> الاسم:
                            </td>
                            <td style="padding: 1rem;">
                                <?php echo htmlspecialchars($equipe['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 1rem; font-weight: bold;">
                                <i class="fas fa-city"></i> المدينة:
                            </td>
                            <td style="padding: 1rem;">
                                <?php echo htmlspecialchars($equipe['ville'] ?? 'غير محدد', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 1rem; font-weight: bold;">
                                <i class="fas fa-calendar"></i> تاريخ التأسيس:
                            </td>
                            <td style="padding: 1rem;">
                                <?php echo htmlspecialchars($equipe['date_creation'] ?? 'غير محدد', ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 1rem; font-weight: bold;">
                                <i class="fas fa-clock"></i> تاريخ الإضافة:
                            </td>
                            <td style="padding: 1rem;">
                                <?php echo date('Y-m-d H:i', strtotime($equipe['created_at'] ?? 'now')); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Description -->
            <?php if(!empty($equipe['description'])): ?>
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border);">
                <h3><i class="fas fa-info-circle"></i> نبذة عن الفريق:</h3>
                <p style="line-height: 1.8; margin-top: 1rem;">
                    <?php echo nl2br(htmlspecialchars($equipe['description'], ENT_QUOTES, 'UTF-8')); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <!-- Statistics Section -->
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border);">
                <h3><i class="fas fa-chart-bar"></i> الإحصائيات:</h3>
                <div class="row" style="margin-top: 1rem;">
                    <div class="col-4 text-center">
                        <div style="padding: 1.5rem; background: var(--light); border-radius: var(--radius);">
                            <i class="fas fa-trophy fa-2x" style="color: var(--warning);"></i>
                            <h3>5</h3>
                            <p>البطولات</p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div style="padding: 1.5rem; background: var(--light); border-radius: var(--radius);">
                            <i class="fas fa-futbol fa-2x" style="color: var(--success);"></i>
                            <h3>45</h3>
                            <p>المباريات</p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div style="padding: 1.5rem; background: var(--light); border-radius: var(--radius);">
                            <i class="fas fa-medal fa-2x" style="color: var(--primary);"></i>
                            <h3>30</h3>
                            <p>الانتصارات</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/templates/footer.php'; ?>