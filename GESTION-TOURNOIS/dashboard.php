<?php
/**
 * ==========================================
 * DASHBOARD - STATISTIQUES RÉELLES
 * ==========================================
 */

require_once 'config.php';
require_once 'includes/lib/auth.php';

$auth = new Auth();
$auth->checkSession(); // Vérifie si connecté
$role = $_SESSION['role'] ?? 'spectateur';
$userId = $_SESSION['user_id'] ?? 0;

try {
    $db = getDB();

    // --- LOGIQUE ADMIN ---
    if ($role === 'admin' || $role === 'super_admin') {
        $stats = [
            ['title' => 'Tournois Actifs',    'value' => $db->fetchColumn("SELECT COUNT(*) FROM tournoi WHERE statut = 'en_cours'"), 'icon' => 'fa-flag',   'color' => 'text-warning'],
            ['title' => 'Équipes',            'value' => $db->fetchColumn("SELECT COUNT(*) FROM equipe"),                            'icon' => 'fa-users',  'color' => 'text-info'],
            ['title' => 'Matches Joués',      'value' => $db->fetchColumn("SELECT COUNT(*) FROM `match` WHERE statut = 'termine'"),  'icon' => 'fa-futbol', 'color' => 'text-success'],
            ['title' => 'Utilisateurs',       'value' => $db->fetchColumn("SELECT COUNT(*) FROM utilisateur"),                       'icon' => 'fa-user-shield', 'color' => 'text-danger'],
        ];
        
        $derniers_matches = $db->fetchAll("
            SELECT m.*, e1.nom AS equipe_domicile, e2.nom AS equipe_exterieur, t.nom AS tournoi_nom
            FROM `match` m
            LEFT JOIN equipe e1 ON m.equipe_domicile_id = e1.id
            LEFT JOIN equipe e2 ON m.equipe_exterieur_id = e2.id
            LEFT JOIN tournoi t ON m.tournoi_id = t.id
            ORDER BY m.created_at DESC LIMIT 5
        ");
    }
    
    // --- LOGIQUE COACH ---
    elseif ($role === 'coach') {
        // On suppose que l'équipe favorite est l'équipe coachée pour l'instant
        $user = $db->fetchOne("SELECT equipe_favorite_id FROM utilisateur WHERE id = ?", [$userId]);
        $myTeamId = $user['equipe_favorite_id'];
        
        if ($myTeamId) {
            $myTeam = $db->fetchOne("SELECT * FROM equipe WHERE id = ?", [$myTeamId]);
            $nextMatch = $db->fetchOne("
                SELECT m.*, e1.nom as eq1, e2.nom as eq2 
                FROM `match` m 
                JOIN equipe e1 ON m.equipe_domicile_id = e1.id
                JOIN equipe e2 ON m.equipe_exterieur_id = e2.id
                WHERE (m.equipe_domicile_id = ? OR m.equipe_exterieur_id = ?) 
                AND m.statut = 'planifie' 
                ORDER BY m.date_match ASC LIMIT 1", 
                [$myTeamId, $myTeamId]
            );
            $nbJoueurs = $db->fetchColumn("SELECT COUNT(*) FROM joueur WHERE equipe_id = ?", [$myTeamId]);
        } else {
            $myTeam = null;
        }
    }
    
    // --- LOGIQUE SPECTATEUR ---
    else {
        $matchs_avenir = $db->fetchAll("
            SELECT m.*, e1.nom as eq1, e2.nom as eq2, t.nom as tournoi
            FROM `match` m
            JOIN equipe e1 ON m.equipe_domicile_id = e1.id
            JOIN equipe e2 ON m.equipe_exterieur_id = e2.id
            LEFT JOIN tournoi t ON m.tournoi_id = t.id
            WHERE m.statut = 'planifie' AND m.date_match >= NOW()
            ORDER BY m.date_match ASC LIMIT 6
        ");
        
        // Équipe favorite
        $user = $db->fetchOne("SELECT equipe_favorite_id FROM utilisateur WHERE id = ?", [$userId]);
        $favTeam = null;
        if ($user['equipe_favorite_id']) {
            $favTeam = $db->fetchOne("SELECT * FROM equipe WHERE id = ?", [$user['equipe_favorite_id']]);
        }
    }

} catch (Exception $e) {
    $error = "Erreur de chargement: " . $e->getMessage();
}
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

    <!-- ==================== VUE ADMIN ==================== -->
    <?php if ($role === 'admin' || $role === 'super_admin'): ?>
    <h4 class="mb-3">Vue Administrateur</h4>
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

    <!-- Activité récente -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history me-2"></i>Derniers ajouts</span>
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
    <?php endif; ?>

    <!-- ==================== VUE COACH ==================== -->
    <?php if ($role === 'coach'): ?>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card bg-primary text-white shadow">
                <div class="card-body p-4">
                    <h2><i class="fas fa-whistle me-2"></i>Espace Coach</h2>
                    <p class="mb-0">Gérez votre effectif et préparez vos prochains matchs.</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($myTeam) && $myTeam): ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Mon Équipe : <?= htmlspecialchars($myTeam['nom']) ?></h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if($myTeam['logo_url']): ?>
                            <img src="<?= htmlspecialchars($myTeam['logo_url']) ?>" class="img-fluid mb-3" style="max-height:100px;">
                        <?php endif; ?>
                        <div class="row mt-3">
                            <div class="col-6 border-end">
                                <h3><?= $nbJoueurs ?></h3>
                                <small class="text-muted">Joueurs</small>
                            </div>
                            <div class="col-6">
                                <h3><?= htmlspecialchars($myTeam['classement_national'] ?? '-') ?></h3>
                                <small class="text-muted">Classement</small>
                            </div>
                        </div>
                        <a href="modules/equipes/view.php?id=<?= $myTeam['id'] ?>" class="btn btn-outline-primary mt-3 w-100">Gérer l'effectif</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Prochain Match</h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <?php if (isset($nextMatch) && $nextMatch): ?>
                            <h4 class="text-center mb-3">
                                <?= htmlspecialchars($nextMatch['eq1']) ?> <span class="text-muted">vs</span> <?= htmlspecialchars($nextMatch['eq2']) ?>
                            </h4>
                            <p class="lead text-success fw-bold">
                                <?= date('d/m/Y à H:i', strtotime($nextMatch['date_match'])) ?>
                            </p>
                            <span class="badge bg-warning text-dark">À venir</span>
                        <?php else: ?>
                            <p class="text-muted">Aucun match planifié pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle me-2"></i>
            Vous n'êtes associé à aucune équipe. Veuillez configurer votre "Équipe favorite" dans votre profil pour simuler votre équipe coachée.
            <a href="modules/auth/profile.php" class="alert-link">Configurer mon profil</a>.
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ==================== VUE SPECTATEUR ==================== -->
    <?php if ($role === 'spectateur'): ?>
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-3"><i class="fas fa-calendar-alt me-2 text-primary"></i>Matchs à venir</h4>
            <div class="row g-3">
                <?php if(empty($matchs_avenir)): ?>
                    <div class="col-12"><p class="text-muted">Aucun match programmé.</p></div>
                <?php else: ?>
                    <?php foreach($matchs_avenir as $m): ?>
                    <div class="col-md-6">
                        <div class="card h-100 border-start border-4 border-primary shadow-sm">
                            <div class="card-body">
                                <small class="text-muted"><?= date('d/m H:i', strtotime($m['date_match'])) ?> • <?= htmlspecialchars($m['tournoi'] ?? 'Amical') ?></small>
                                <h6 class="mt-2 mb-0">
                                    <?= htmlspecialchars($m['eq1']) ?> <span class="text-danger">vs</span> <?= htmlspecialchars($m['eq2']) ?>
                                </h6>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-4">
            <h4 class="mb-3"><i class="fas fa-heart me-2 text-danger"></i>Mon Équipe</h4>
            <?php if(isset($favTeam) && $favTeam): ?>
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($favTeam['nom']) ?></h3>
                        <a href="modules/equipes/view.php?id=<?= $favTeam['id'] ?>" class="btn btn-sm btn-outline-secondary mt-2">Voir détails</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-light">Aucune équipe favorite sélectionnée.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/templates/footer.php'; ?>