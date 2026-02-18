<?php
/**
 * ==========================================
 * GESTION DES TOURNOIS - INDEX
 * ==========================================
 */

require_once '../../includes/config/database.php';

// Récupérer tous les tournois depuis la DB
try {
    $db = getDB();
    $tournois = $db->fetchAll("SELECT * FROM tournoi ORDER BY date_debut DESC");
} catch (Exception $e) {
    $tournois = [];
    $error = "Erreur lors du chargement des tournois.";
}
?>

<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Gestion des Tournois</h2>
            <small class="text-muted">Total: <?= count($tournois) ?> tournoi(s)</small>
        </div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouveau Tournoi
        </a>
    </div>

    <!-- Message erreur -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Messages succès -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php if ($_GET['success'] == 'created'): ?>
                <i class="fas fa-check-circle me-2"></i>Tournoi créé avec succès !
            <?php elseif ($_GET['success'] == 'updated'): ?>
                <i class="fas fa-check-circle me-2"></i>Tournoi modifié avec succès !
            <?php elseif ($_GET['success'] == 'deleted'): ?>
                <i class="fas fa-check-circle me-2"></i>Tournoi supprimé avec succès !
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Nom du Tournoi</th>
                            <th>Date de début</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tournois)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-trophy fa-3x mb-3 d-block opacity-25"></i>
                                    Aucun tournoi trouvé
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tournois as $tournoi): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= htmlspecialchars($tournoi['id']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($tournoi['nom']) ?></td>
                                <td>
                                    <i class="fas fa-calendar-alt text-muted me-1"></i>
                                    <?= htmlspecialchars($tournoi['date_debut']) ?>
                                </td>
                                <td>
                                    <?php
                                        $statut = $tournoi['statut'];
                                        $badge = match($statut) {
                                            'En cours'  => 'bg-success',
                                            'À venir'   => 'bg-primary',
                                            'Terminé'   => 'bg-secondary',
                                            default     => 'bg-dark'
                                        };
                                    ?>
                                    <span class="badge <?= $badge ?>">
                                        <?= htmlspecialchars($statut) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="bracket.php?id=<?= $tournoi['id'] ?>" 
                                       class="btn btn-sm btn-primary me-1" title="Gérer">
                                        <i class="fas fa-sitemap"></i>
                                    </a>
                                    <a href="standings.php?id=<?= $tournoi['id'] ?>" 
                                       class="btn btn-sm btn-info text-white me-1" title="Classement">
                                        <i class="fas fa-list-ol"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $tournoi['id'] ?>" 
                                       class="btn btn-sm btn-warning text-white me-1" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $tournoi['id'] ?>" 
                                       class="btn btn-sm btn-danger" title="Supprimer"
                                       onclick="return confirm('Voulez-vous vraiment supprimer ce tournoi ?')">
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