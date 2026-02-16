<?php
session_start();
require_once '../../includes/config/database.php';

$pageTitle = 'قائمة الفرق';
include '../../includes/templates/header.php';
include '../../includes/templates/navigation.php';

// Fetch teams from database
try {
    $stmt = $pdo->query("SELECT * FROM equipes ORDER BY nom ASC");
    $equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $equipes = [];
    $_SESSION['error_message'] = "خطأ في جلب البيانات: " . $e->getMessage();
}
?>

<div class="container">
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">
                <i class="fas fa-users"></i>
                قائمة الفرق
            </h2>
            
            <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/modules/equipes/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    إضافة فريق جديد
                </a>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <?php if(empty($equipes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    لا توجد فرق حالياً. قم بإضافة فريق جديد للبدء.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الشعار</th>
                                <th>الاسم</th>
                                <th>المدينة</th>
                                <th>تاريخ التأسيس</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($equipes as $index => $equipe): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <?php if(!empty($equipe['logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($equipe['logo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                             alt="Logo" 
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <i class="fas fa-shield-alt fa-2x" style="color: var(--secondary);"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($equipe['nom'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($equipe['ville'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($equipe['date_creation'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="/modules/equipes/view.php?id=<?php echo $equipe['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                                        <a href="/modules/equipes/edit.php?id=<?php echo $equipe['id']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="/modules/equipes/delete.php?id=<?php echo $equipe['id']; ?>" 
                                           class="btn btn-sm btn-danger btn-delete"
                                           data-confirm="هل أنت متأكد من حذف هذا الفريق؟">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/templates/footer.php'; ?>