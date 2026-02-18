<?php
/**
 * STANDINGS - Classement des tournois
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../includes/lib/auth.php';

$db = Database::getInstance();
$tournoi_id = $_GET['tournoi_id'] ?? null;

if (!$tournoi_id) {
    redirect('index.php');
}

// Infos du tournoi
$tournoi = $db->fetchOne("SELECT * FROM tournoi WHERE id = ?", [$tournoi_id]);

// Récupérer le classement
$classement = $db->fetchAll(
    "SELECT e.id, e.nom, e.logo_url,
     i.matchs_joues, i.victoires, i.nuls, i.defaites,
     i.buts_pour, i.buts_contre,
     (i.buts_pour - i.buts_contre) as difference_buts,
     i.points,
     (i.victoires * 3 + i.nuls) as points_calcules,
     RANK() OVER (ORDER BY (i.victoires * 3 + i.nuls) DESC, 
                  (i.buts_pour - i.buts_contre) DESC, 
                  i.buts_pour DESC) as position
     FROM inscription_tournoi i
     JOIN equipe e ON i.equipe_id = e.id
     WHERE i.tournoi_id = ?
     ORDER BY points_calcules DESC, difference_buts DESC, i.buts_pour DESC",
    [$tournoi_id]
);

// Statistiques supplémentaires
$stats = [
    'total_matchs' => $db->fetchColumn("SELECT COUNT(*) FROM `match` WHERE tournoi_id = ?", [$tournoi_id]),
    'matchs_joues' => $db->fetchColumn("SELECT COUNT(*) FROM `match` WHERE tournoi_id = ? AND statut = 'termine'", [$tournoi_id]),
    'total_buts' => $db->fetchColumn(
        "SELECT SUM(score_domicile + score_exterieur) FROM `match` WHERE tournoi_id = ? AND statut = 'termine'",
        [$tournoi_id]
    ),
    'meilleure_attaque' => $db->fetchOne(
        "SELECT e.nom, SUM(i.buts_pour) as buts 
         FROM inscription_tournoi i 
         JOIN equipe e ON i.equipe_id = e.id 
         WHERE i.tournoi_id = ? 
         GROUP BY i.equipe_id 
         ORDER BY buts DESC LIMIT 1",
        [$tournoi_id]
    ),
    'meilleure_defense' => $db->fetchOne(
        "SELECT e.nom, SUM(i.buts_contre) as buts 
         FROM inscription_tournoi i 
         JOIN equipe e ON i.equipe_id = e.id 
         WHERE i.tournoi_id = ? 
         GROUP BY i.equipe_id 
         ORDER BY buts ASC LIMIT 1",
        [$tournoi_id]
    )
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Classement - <?= htmlspecialchars($tournoi['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../../includes/templates/navigation.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h2><i class="bi bi-trophy"></i> <?= htmlspecialchars($tournoi['nom']) ?></h2>
                <p class="text-muted"><?= date('d/m/Y', strtotime($tournoi['date_debut'])) ?> - <?= date('d/m/Y', strtotime($tournoi['date_fin'])) ?></p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-success" onclick="exportPDF()">
                    <i class="bi bi-file-pdf"></i> PDF
                </button>
                <button class="btn btn-primary" onclick="exportExcel()">
                    <i class="bi bi-file-excel"></i> Excel
                </button>
            </div>
        </div>
        
        <!-- Stats rapides -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="text-muted">Matchs joués</h5>
                        <h3><?= $stats['matchs_joues'] ?> / <?= $stats['total_matchs'] ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="text-muted">Total buts</h5>
                        <h3><?= $stats['total_buts'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="text-muted">Meilleure attaque</h5>
                        <h6><?= htmlspecialchars($stats['meilleure_attaque']['nom'] ?? '-') ?></h6>
                        <small><?= $stats['meilleure_attaque']['buts'] ?? 0 ?> buts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="text-muted">Meilleure défense</h5>
                        <h6><?= htmlspecialchars($stats['meilleure_defense']['nom'] ?? '-') ?></h6>
                        <small><?= $stats['meilleure_defense']['buts'] ?? 0 ?> encaissés</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Classement -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Classement général</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Équipe</th>
                            <th>J</th>
                            <th>G</th>
                            <th>N</th>
                            <th>P</th>
                            <th>BP</th>
                            <th>BC</th>
                            <th>+/-</th>
                            <th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classement as $index => $equipe): ?>
                            <tr class="<?= $index < 3 ? 'table-warning' : '' ?>">
                                <td><strong><?= $equipe['position'] ?></strong></td>
                                <td>
                                    <?php if ($equipe['logo_url']): ?>
                                        <img src="<?= BASE_URL . $equipe['logo_url'] ?>" style="height: 30px;" class="me-2">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($equipe['nom']) ?>
                                </td>
                                <td><?= $equipe['matchs_joues'] ?></td>
                                <td><?= $equipe['victoires'] ?></td>
                                <td><?= $equipe['nuls'] ?></td>
                                <td><?= $equipe['defaites'] ?></td>
                                <td><?= $equipe['buts_pour'] ?></td>
                                <td><?= $equipe['buts_contre'] ?></td>
                                <td class="<?= $equipe['difference_buts'] > 0 ? 'text-success' : ($equipe['difference_buts'] < 0 ? 'text-danger' : '') ?>">
                                    <?= $equipe['difference_buts'] ?>
                                </td>
                                <td><strong><?= $equipe['points_calcules'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Historique des matchs -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Derniers résultats</h5>
            </div>
            <div class="card-body">
                <?php
                $derniers_matchs = $db->fetchAll(
                    "SELECT m.*, 
                     ed.nom as domicile_nom, ee.nom as exterieur_nom
                     FROM `match` m
                     JOIN equipe ed ON m.equipe_domicile_id = ed.id
                     JOIN equipe ee ON m.equipe_exterieur_id = ee.id
                     WHERE m.tournoi_id = ? AND m.statut = 'termine'
                     ORDER BY m.date_match DESC LIMIT 5",
                    [$tournoi_id]
                );
                ?>
                
                <?php if (empty($derniers_matchs)): ?>
                    <p class="text-muted text-center">Aucun match joué</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($derniers_matchs as $match): ?>
                            <div class="list-group-item">
                                <div class="row">
                                    <div class="col-5 text-end">
                                        <strong><?= htmlspecialchars($match['domicile_nom']) ?></strong>
                                    </div>
                                    <div class="col-2 text-center">
                                        <span class="badge bg-primary">
                                            <?= $match['score_domicile'] ?> - <?= $match['score_exterieur'] ?>
                                        </span>
                                    </div>
                                    <div class="col-5">
                                        <strong><?= htmlspecialchars($match['exterieur_nom']) ?></strong>
                                    </div>
                                    <div class="col-12 text-center text-muted small">
                                        <?= date('d/m/Y', strtotime($match['date_match'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    function exportPDF() {
        window.location.href = 'export.php?type=pdf&tournoi_id=<?= $tournoi_id ?>';
    }
    
    function exportExcel() {
        window.location.href = 'export.php?type=excel&tournoi_id=<?= $tournoi_id ?>';
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>