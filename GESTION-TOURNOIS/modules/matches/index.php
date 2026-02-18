<?php
/**
 * ==========================================
 * GESTION DES MATCHES - INDEX
 * ==========================================
 */

require_once '../../config.php';
require_once '../../includes/lib/auth.php';

$isAdmin = (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin'));

try {
    $db = getDB();
    $matches = $db->fetchAll("
        SELECT m.*, 
               e1.nom  AS equipe_domicile, 
               e2.nom  AS equipe_exterieur,
               t.nom   AS tournoi_nom,
               tr.nom  AS tour_nom,
               te.nom  AS terrain_nom,
               te.ville AS terrain_ville
        FROM `match` m
        LEFT JOIN equipe   e1 ON m.equipe_domicile_id  = e1.id
        LEFT JOIN equipe   e2 ON m.equipe_exterieur_id = e2.id
        LEFT JOIN tournoi  t  ON m.tournoi_id          = t.id
        LEFT JOIN tour     tr ON m.tour_id             = tr.id
        LEFT JOIN terrain  te ON m.terrain_id          = te.id
        ORDER BY m.date_match DESC
    ");
} catch (Exception $e) {
    $matches = [];
    $error = "Erreur lors du chargement des matches.";
}
?>

<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-futbol me-2 text-success"></i>Gestion des Matches</h2>
            <small class="text-muted">Total: <?= count($matches) ?> match(es)</small>
        </div>
        <?php if ($isAdmin): ?>
        <a href="create.php" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> Créer un Match
        </a>
        <?php endif; ?>
    </div>

    <!-- Message erreur -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Messages succès -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php if ($_GET['success'] == 'created'): ?>
                <i class="fas fa-check-circle me-2"></i>Match créé avec succès !
            <?php elseif ($_GET['success'] == 'updated'): ?>
                <i class="fas fa-check-circle me-2"></i>Match modifié avec succès !
            <?php elseif ($_GET['success'] == 'deleted'): ?>
                <i class="fas fa-check-circle me-2"></i>Match supprimé avec succès !
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
                            <th>Tournoi / Tour</th>
                            <th>Date</th>
                            <th class="text-end">Domicile</th>
                            <th class="text-center">Score</th>
                            <th>Extérieur</th>
                            <th>Terrain</th>
                            <th>Résultat</th>
                            <th>Statut</th>
                            <?php if ($isAdmin): ?>
                            <th class="text-center">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($matches)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-futbol fa-3x mb-3 d-block opacity-25"></i>
                                    Aucun match trouvé
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($matches as $match): ?>
                            <?php
                                // Statut badge
                                $statutBadge = match($match['statut']) {
                                    'termine'   => 'bg-secondary',
                                    'en_cours'  => 'bg-success',
                                    'planifie'  => 'bg-primary',
                                    'reporte'   => 'bg-warning text-dark',
                                    'annule'    => 'bg-danger',
                                    default     => 'bg-dark'
                                };

                                // Résultat
                                if ($match['statut'] !== 'termine') {
                                    $resultat = '-';
                                    $resBadge = 'bg-light text-dark';
                                } elseif ($match['est_nul']) {
                                    $resultat = 'Match nul';
                                    $resBadge = 'bg-warning text-dark';
                                } elseif ($match['score_domicile'] > $match['score_exterieur']) {
                                    $resultat = $match['equipe_domicile'] . ' gagne';
                                    $resBadge = 'bg-success';
                                } else {
                                    $resultat = $match['equipe_exterieur'] . ' gagne';
                                    $resBadge = 'bg-danger';
                                }

                                // Extra time / Penalties
                                $extra = '';
                                if ($match['termine_aux_tirs_au_but']) $extra = ' (TAB)';
                                elseif ($match['termine_ap_prolongation']) $extra = ' (AP)';
                            ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $match['id'] ?></td>
                                <td>
                                    <span class="badge bg-info text-dark d-block mb-1">
                                        <?= htmlspecialchars($match['tournoi_nom'] ?? '-') ?>
                                    </span>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($match['tour_nom'] ?? $match['nom_tour'] ?? '-') ?>
                                    </small>
                                </td>
                                <td>
                                    <i class="fas fa-calendar-alt text-muted me-1"></i>
                                    <?= $match['date_match'] ? date('d/m/Y H:i', strtotime($match['date_match'])) : '-' ?>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= htmlspecialchars($match['equipe_domicile'] ?? '-') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($match['statut'] == 'termine'): ?>
                                        <span class="badge bg-dark fs-6 px-3 py-2">
                                            <?= $match['score_domicile'] ?>
                                            <span class="text-muted mx-1">-</span>
                                            <?= $match['score_exterieur'] ?>
                                        </span>
                                        <?php if ($extra): ?>
                                            <br><small class="text-muted"><?= $extra ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">vs</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold">
                                    <?= htmlspecialchars($match['equipe_exterieur'] ?? '-') ?>
                                </td>
                                <td>
                                    <small>
                                        <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                        <?= htmlspecialchars($match['terrain_nom'] ?? '-') ?>
                                        <?php if ($match['terrain_ville']): ?>
                                            <br><span class="text-muted"><?= htmlspecialchars($match['terrain_ville']) ?></span>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge <?= $resBadge ?>">
                                        <?= $resultat ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $statutBadge ?>">
                                        <?= ucfirst(str_replace('_', ' ', $match['statut'])) ?>
                                    </span>
                                </td>
                                <?php if ($isAdmin): ?>
                                <td class="text-center">
                                    <a href="results.php?id=<?= $match['id'] ?>" 
                                       class="btn btn-sm btn-info text-white me-1" title="Résultats">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $match['id'] ?>" 
                                       class="btn btn-sm btn-warning text-white me-1" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $match['id'] ?>" 
                                       class="btn btn-sm btn-danger" title="Supprimer"
                                       onclick="return confirm('Voulez-vous vraiment supprimer ce match ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                                <?php endif; ?>
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