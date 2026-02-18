<?php
/**
 * ==========================================
 * DASHBOARD - STATISTIQUES RÉELLES
 * ==========================================
 */

require_once 'includes/config/database.php';

try {
    $db = getDB();

    // Stats cards
    $tournois_actifs    = $db->fetchColumn("SELECT COUNT(*) FROM tournoi WHERE statut = 'en_cours'");
    $equipes_inscrites  = $db->fetchColumn("SELECT COUNT(*) FROM equipe");
    $matches_joues      = $db->fetchColumn("SELECT COUNT(*) FROM `match` WHERE statut = 'termine'");
    $matches_en_attente = $db->fetchColumn("SELECT COUNT(*) FROM `match` WHERE statut = 'planifie'");

    // Derniers matches
    $derniers_matches = $db->fetchAll("
        SELECT m.*,
               e1.nom AS equipe_domicile,
               e2.nom AS equipe_exterieur,
               t.nom  AS tournoi_nom
        FROM `match` m
        LEFT JOIN equipe  e1 ON m.equipe_domicile_id  = e1.id
        LEFT JOIN equipe  e2 ON m.equipe_exterieur_id = e2.id
        LEFT JOIN tournoi t  ON m.tournoi_id          = t.id
        ORDER BY m.date_match DESC
        LIMIT 10
    ");

} catch (Exception $e) {
    $tournois_actifs    = 0;
    $equipes_inscrites  = 0;
    $matches_joues      = 0;
    $matches_en_attente = 0;
    $derniers_matches   = [];
    $error = "Erreur lors du chargement des données.";
}

// Stats array avec vraies données
$stats = [
    ['title' => 'Tournois Actifs',    'value' => $tournois_actifs,    'icon' => 'fa-flag',   'color' => 'text-warning'],
    ['title' => 'Équipes Inscrites',  'value' => $equipes_inscrites,  'icon' => 'fa-users',  'color' => 'text-info'],
    ['title' => 'Matches Joués',      'value' => $matches_joues,      'icon' => 'fa-futbol', 'color' => 'text-success'],
    ['title' => 'En attente',         'value' => $matches_en_attente, 'icon' => 'fa-clock',  'color' => 'text-danger'],
];
?>

<?php require_once 'includes/templates/header.php'; ?>
<?php require_once 'includes/templates/navigation.php'; ?>

<div class="container-fluid py-4">

    <!-- Message erreur -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <?php foreach ($stats as $stat): ?>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1"><?= htmlspecialchars($stat['title']) ?></h6>
                        <h3 class="fw-bold mb-0"><?= htmlspecialchars($stat['value']) ?></h3>
                    </div>
                    <div class="fs-1 <?= htmlspecialchars($stat['color']) ?>">
                        <i class="fas <?= htmlspecialchars($stat['icon']) ?>"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Derniers Matches -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-table me-2"></i>Derniers Matches</span>
                    <a href="modules/matches/index.php" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Tournoi</th>
                                    <th class="text-end">Domicile</th>
                                    <th class="text-center">Score</th>
                                    <th>Extérieur</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($derniers_matches)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-futbol fa-2x mb-2 d-block opacity-25"></i>
                                            Aucun match trouvé
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($derniers_matches as $match): ?>
                                    <?php
                                        $statutBadge = match($match['statut']) {
                                            'termine'   => 'bg-success',
                                            'en_cours'  => 'bg-warning text-dark',
                                            'planifie'  => 'bg-secondary',
                                            'reporte'   => 'bg-info text-dark',
                                            'annule'    => 'bg-danger',
                                            default     => 'bg-dark'
                                        };
                                        $statutLabel = match($match['statut']) {
                                            'termine'   => 'Terminé',
                                            'en_cours'  => 'En cours',
                                            'planifie'  => 'Planifié',
                                            'reporte'   => 'Reporté',
                                            'annule'    => 'Annulé',
                                            default     => $match['statut']
                                        };
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <i class="fas fa-calendar-alt text-muted me-1"></i>
                                            <?= $match['date_match'] ? date('d/m/Y H:i', strtotime($match['date_match'])) : '-' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?= htmlspecialchars($match['tournoi_nom'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">
                                            <?= htmlspecialchars($match['equipe_domicile'] ?? '-') ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($match['statut'] == 'termine'): ?>
                                                <span class="badge bg-dark px-3 py-2">
                                                    <?= $match['score_domicile'] ?> - <?= $match['score_exterieur'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">vs</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold">
                                            <?= htmlspecialchars($match['equipe_exterieur'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $statutBadge ?>">
                                                <?= $statutLabel ?>
                                            </span>
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
    </div>

</div>

<?php require_once 'includes/templates/footer.php'; ?>