<?php
/**
 * ==========================================
 * GESTION DES ÉQUIPES - INDEX
 * ==========================================
 */

require_once '../../includes/config/database.php';

// Récupérer toutes les équipes depuis la DB
try {
    $db = getDB();
    $equipes = $db->fetchAll("SELECT * FROM equipe ORDER BY id ASC");
} catch (Exception $e) {
    $equipes = [];
    $error = "Erreur lors du chargement des équipes.";
}
?>

<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Gestion des Équipes</h2>
            <small class="text-muted">Total: <?= count($equipes) ?> équipe(s)</small>
        </div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle Équipe
        </a>
    </div>

    <!-- Message d'erreur -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Message succès (après create/edit/delete) -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php if ($_GET['success'] == 'created'): ?>
                <i class="fas fa-check-circle me-2"></i>Équipe créée avec succès !
            <?php elseif ($_GET['success'] == 'updated'): ?>
                <i class="fas fa-check-circle me-2"></i>Équipe modifiée avec succès !
            <?php elseif ($_GET['success'] == 'deleted'): ?>
                <i class="fas fa-check-circle me-2"></i>Équipe supprimée avec succès !
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Nom de l'équipe</th>
                            <th>Ville</th>
                            <th>Coach</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($equipes)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    Aucune équipe trouvée
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($equipes as $equipe): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= htmlspecialchars($equipe['id']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($equipe['nom']) ?></td>
                                <td><i class="fas fa-map-marker-alt text-muted me-1"></i><?= htmlspecialchars($equipe['ville']) ?></td>
                                <td><i class="fas fa-user text-muted me-1"></i><?= htmlspecialchars($equipe['coach']) ?></td>
                                <td class="text-center">
                                    <a href="view.php?id=<?= $equipe['id'] ?>" 
                                       class="btn btn-sm btn-info text-white me-1" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $equipe['id'] ?>" 
                                       class="btn btn-sm btn-warning text-white me-1" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $equipe['id'] ?>" 
                                       class="btn btn-sm btn-danger" title="Supprimer"
                                       onclick="return confirm('Voulez-vous vraiment supprimer cette équipe ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/templates/footer.php'; ?>