<?php
/**
 * RESULTS - Gestion des résultats de matchs
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../includes/lib/auth.php';

$auth = new Auth();
if (!$auth->checkSession()) {
    redirect('modules/auth/login.php');
}

$db = Database::getInstance();
$error = '';
$success = '';
$match_id = $_GET['id'] ?? null;

// Récupérer la liste des matchs à venir/terminés
$matchs = $db->fetchAll(
    "SELECT m.*, 
     t.nom as tournoi_nom,
     ed.nom as equipe_domicile_nom, ed.logo_url as domicile_logo,
     ee.nom as equipe_exterieur_nom, ee.logo_url as exterieur_logo
     FROM `match` m
     JOIN tournoi t ON m.tournoi_id = t.id
     JOIN equipe ed ON m.equipe_domicile_id = ed.id
     JOIN equipe ee ON m.equipe_exterieur_id = ee.id
     ORDER BY m.date_match DESC"
);

// Traitement formulaire saisie résultat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_result'])) {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide";
    } else {
        $id = (int)$_POST['match_id'];
        $score_domicile = (int)$_POST['score_domicile'];
        $score_exterieur = (int)$_POST['score_exterieur'];
        $prolongation = isset($_POST['prolongation']) ? 1 : 0;
        $tirs_au_but = isset($_POST['tirs_au_but']) ? 1 : 0;
        
        if ($prolongation) {
            $score_domicile = (int)$_POST['score_domicile_ap'];
            $score_exterieur = (int)$_POST['score_exterieur_ap'];
        }
        
        if ($tirs_au_but) {
            $tab_domicile = (int)$_POST['tab_domicile'];
            $tab_exterieur = (int)$_POST['tab_exterieur'];
        }
        
        // Déterminer vainqueur
        $vainqueur_id = null;
        $perdant_id = null;
        $est_nul = false;
        
        if ($tirs_au_but) {
            $vainqueur_id = $tab_domicile > $tab_exterieur ? 
                $_POST['equipe_domicile_id'] : $_POST['equipe_exterieur_id'];
            $perdant_id = $vainqueur_id == $_POST['equipe_domicile_id'] ? 
                $_POST['equipe_exterieur_id'] : $_POST['equipe_domicile_id'];
        } elseif ($score_domicile > $score_exterieur) {
            $vainqueur_id = $_POST['equipe_domicile_id'];
            $perdant_id = $_POST['equipe_exterieur_id'];
        } elseif ($score_exterieur > $score_domicile) {
            $vainqueur_id = $_POST['equipe_exterieur_id'];
            $perdant_id = $_POST['equipe_domicile_id'];
        } else {
            $est_nul = true;
        }
        
        try {
            $db->beginTransaction();
            
            // Mettre à jour le match
            $sql = "UPDATE `match` SET 
                    score_domicile = ?,
                    score_exterieur = ?,
                    vainqueur_id = ?,
                    perdant_id = ?,
                    est_nul = ?,
                    termine_ap_prolongation = ?,
                    termine_aux_tirs_au_but = ?,
                    statut = 'termine'
                    WHERE id = ?";
            
            $db->execute($sql, [
                $score_domicile,
                $score_exterieur,
                $vainqueur_id,
                $perdant_id,
                $est_nul,
                $prolongation,
                $tirs_au_but,
                $id
            ]);
            
            // Mettre à jour les statistiques des équipes
            $match = $db->fetchOne("SELECT * FROM `match` WHERE id = ?", [$id]);
            
            // Mise à jour inscription_tournoi
            if (!$est_nul && $vainqueur_id) {
                // Victoire pour le vainqueur
                $db->execute(
                    "UPDATE inscription_tournoi SET 
                     victoires = victoires + 1,
                     points = points + 3,
                     buts_pour = buts_pour + ?,
                     buts_contre = buts_contre + ?
                     WHERE tournoi_id = ? AND equipe_id = ?",
                    [$score_domicile, $score_exterieur, $match['tournoi_id'], $vainqueur_id]
                );
                
                // Défaite pour le perdant
                $db->execute(
                    "UPDATE inscription_tournoi SET 
                     defaites = defaites + 1,
                     buts_pour = buts_pour + ?,
                     buts_contre = buts_contre + ?
                     WHERE tournoi_id = ? AND equipe_id = ?",
                    [$score_exterieur, $score_domicile, $match['tournoi_id'], $perdant_id]
                );
            } else {
                // Match nul
                foreach ([$_POST['equipe_domicile_id'], $_POST['equipe_exterieur_id']] as $eq_id) {
                    $db->execute(
                        "UPDATE inscription_tournoi SET 
                         nuls = nuls + 1,
                         points = points + 1,
                         buts_pour = buts_pour + ?,
                         buts_contre = buts_contre + ?
                         WHERE tournoi_id = ? AND equipe_id = ?",
                        [$eq_id == $_POST['equipe_domicile_id'] ? $score_domicile : $score_exterieur,
                         $eq_id == $_POST['equipe_domicile_id'] ? $score_exterieur : $score_domicile,
                         $match['tournoi_id'], $eq_id]
                    );
                }
            }
            
            // Mettre à jour matchs_joués
            $db->execute(
                "UPDATE inscription_tournoi SET matchs_joues = matchs_joues + 1 
                 WHERE tournoi_id = ? AND equipe_id IN (?, ?)",
                [$match['tournoi_id'], $_POST['equipe_domicile_id'], $_POST['equipe_exterieur_id']]
            );
            
            $db->commit();
            $success = "Résultat enregistré avec succès!";
            
        } catch (Exception $e) {
            $db->rollback();
            $error = "Erreur: " . $e->getMessage();
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>
    
    <div class="container-fluid py-4">
        <h2><i class="bi bi-trophy"></i> Résultats des matchs</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <!-- Liste des matchs -->
        <div class="row">
            <?php foreach ($matchs as $match): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-<?= $match['statut'] == 'termine' ? 'success' : 'warning' ?> text-white">
                            <?= htmlspecialchars($match['tournoi_nom']) ?> - <?= $match['nom_tour'] ?>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-4 text-center">
                                    <?php if ($match['domicile_logo']): ?>
                                        <img src="<?= BASE_URL . $match['domicile_logo'] ?>" style="height: 50px;">
                                    <?php endif; ?>
                                    <h5><?= htmlspecialchars($match['equipe_domicile_nom']) ?></h5>
                                </div>
                                
                                <div class="col-4 text-center">
                                    <?php if ($match['statut'] == 'termine'): ?>
                                        <h3><?= $match['score_domicile'] ?> - <?= $match['score_exterieur'] ?></h3>
                                        <?php if ($match['termine_aux_tirs_au_but']): ?>
                                            <small class="text-muted">(TAB)</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <h4>VS</h4>
                                        <small><?= date('d/m/Y H:i', strtotime($match['date_match'])) ?></small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-4 text-center">
                                    <?php if ($match['exterieur_logo']): ?>
                                        <img src="<?= BASE_URL . $match['exterieur_logo'] ?>" style="height: 50px;">
                                    <?php endif; ?>
                                    <h5><?= htmlspecialchars($match['equipe_exterieur_nom']) ?></h5>
                                </div>
                            </div>
                            
                            <?php if ($match['statut'] != 'termine'): ?>
                                <button class="btn btn-primary w-100 mt-3" 
                                        onclick="showResultModal(<?= htmlspecialchars(json_encode($match)) ?>)">
                                    <i class="bi bi-pencil"></i> Saisir résultat
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Modal saisie résultat -->
    <div class="modal fade" id="resultModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="save_result" value="1">
                    <input type="hidden" name="match_id" id="match_id">
                    <input type="hidden" name="equipe_domicile_id" id="equipe_domicile_id">
                    <input type="hidden" name="equipe_exterieur_id" id="equipe_exterieur_id">
                    
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Saisir le résultat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="row text-center mb-3" id="modalEquipes"></div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Score domicile</label>
                                <input type="number" class="form-control" name="score_domicile" id="score_domicile" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Score extérieur</label>
                                <input type="number" class="form-control" name="score_exterieur" id="score_exterieur" min="0">
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="prolongation" name="prolongation">
                                <label class="form-check-label">Prolongation</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tirs_au_but" name="tirs_au_but">
                                <label class="form-check-label">Tirs au but</label>
                            </div>
                        </div>
                        
                        <div id="prolongation_fields" style="display:none;" class="mt-3">
                            <h6>Scores après prolongation</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="number" class="form-control" name="score_domicile_ap" placeholder="Domicile">
                                </div>
                                <div class="col-md-6">
                                    <input type="number" class="form-control" name="score_exterieur_ap" placeholder="Extérieur">
                                </div>
                            </div>
                        </div>
                        
                        <div id="tab_fields" style="display:none;" class="mt-3">
                            <h6>Tirs au but</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="number" class="form-control" name="tab_domicile" placeholder="Domicile">
                                </div>
                                <div class="col-md-6">
                                    <input type="number" class="form-control" name="tab_exterieur" placeholder="Extérieur">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    function showResultModal(match) {
        document.getElementById('match_id').value = match.id;
        document.getElementById('equipe_domicile_id').value = match.equipe_domicile_id;
        document.getElementById('equipe_exterieur_id').value = match.equipe_exterieur_id;
        
        document.getElementById('modalEquipes').innerHTML = `
            <div class="col-5"><strong>${match.equipe_domicile_nom}</strong></div>
            <div class="col-2">VS</div>
            <div class="col-5"><strong>${match.equipe_exterieur_nom}</strong></div>
        `;
        
        new bootstrap.Modal(document.getElementById('resultModal')).show();
    }
    
    document.getElementById('prolongation').addEventListener('change', function() {
        document.getElementById('prolongation_fields').style.display = this.checked ? 'block' : 'none';
    });
    
    document.getElementById('tirs_au_but').addEventListener('change', function() {
        document.getElementById('tab_fields').style.display = this.checked ? 'block' : 'none';
    });
    </script>
    
<?php require_once '../../includes/templates/footer.php'; ?>