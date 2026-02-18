<?php
/**
 * BRACKET - Arbre du tournoi
 * Génération et visualisation
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../includes/lib/auth.php';

$auth = new Auth();
if (!$auth->checkSession()) {
    redirect('modules/auth/login.php');
}

class BracketGenerator {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Générer bracket pour tournoi élimination directe
     */
    public function generateBracket($tournoiId) {
        // Récupérer les équipes inscrites
        $equipes = $this->db->fetchAll(
            "SELECT e.* FROM inscription_tournoi i 
             JOIN equipe e ON i.equipe_id = e.id 
             WHERE i.tournoi_id = ? AND i.statut = 'accepte'
             ORDER BY RAND()",
            [$tournoiId]
        );
        
        $nbEquipes = count($equipes);
        $tournoi = $this->db->fetchOne("SELECT * FROM tournoi WHERE id = ?", [$tournoiId]);
        
        if ($tournoi['type_tournoi'] !== 'elimination') {
            return ['error' => 'Ce type de tournoi ne supporte pas le bracket simple'];
        }
        
        // Calculer la puissance de 2 supérieure
        $puissance = 2;
        while ($puissance < $nbEquipes) {
            $puissance *= 2;
        }
        
        $nbByes = $puissance - $nbEquipes;
        
        // Créer les tours
        $this->createRounds($tournoiId, $puissance);
        
        // Générer les matchs vides pour les tours suivants (structure visuelle)
        $this->generatePlaceholderMatches($tournoiId);
        
        // Générer matchs premier tour
        return $this->generateFirstRound($tournoiId, $equipes, $nbByes);
    }
    
    /**
     * Créer les tours dans la base
     */
    private function createRounds($tournoiId, $nbEquipes) {
        $tours = [];
        $matchsPerRound = $nbEquipes / 2;
        $roundNum = 1;
        
        while ($matchsPerRound >= 1) {
            $nomTour = $this->getRoundName($matchsPerRound * 2);
            
            $this->db->execute(
                "INSERT INTO tour (tournoi_id, nom, ordre, type_tour, matchs_prevus, equipes_requises, statut) 
                 VALUES (?, ?, ?, 'elimination', ?, ?, 'a_venir')",
                [$tournoiId, $nomTour, $roundNum, $matchsPerRound, $matchsPerRound * 2]
            );
            
            $roundNum++;
            $matchsPerRound /= 2;
        }
    }
    
    private function getRoundName($nbEquipes) {
        $names = [
            2 => 'Finale',
            4 => 'Demi-finales',
            8 => 'Quarts de finale',
            16 => 'Huitièmes de finale',
            32 => 'Seizièmes de finale',
            64 => 'Trente-deuxièmes de finale',
            128 => 'Soixante-quatrièmes de finale'
        ];
        return $names[$nbEquipes] ?? "Tour $nbEquipes";
    }

    /**
     * Générer les matchs du premier tour
     */
    private function generateFirstRound($tournoiId, $equipes, $nbByes) {
        // Récupérer l'ID du premier tour
        $tourId = $this->db->fetchColumn(
            "SELECT id FROM tour WHERE tournoi_id = ? AND ordre = 1", 
            [$tournoiId]
        );
        
        if (!$tourId) {
            return ['error' => 'Impossible de trouver le premier tour'];
        }

        // Calculer le nombre d'équipes qui jouent au premier tour
        // Les équipes "Bye" sautent ce tour
        $nbJoueursT1 = count($equipes) - $nbByes;
        $equipesT1 = array_slice($equipes, 0, $nbJoueursT1);

        // Créer les matchs
        for ($i = 0; $i < count($equipesT1); $i += 2) {
            if (isset($equipesT1[$i+1])) {
                $sql = "INSERT INTO `match` (tournoi_id, tour_id, equipe_domicile_id, equipe_exterieur_id, date_match, statut) 
                        VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), 'a_venir')";
                $this->db->execute($sql, [$tournoiId, $tourId, $equipesT1[$i]['id'], $equipesT1[$i+1]['id']]);
            }
        }

        return ['success' => true];
    }

    /**
     * Générer les matchs vides pour les tours suivants (pour l'affichage complet de l'arbre)
     */
    private function generatePlaceholderMatches($tournoiId) {
        // Récupérer les tours > 1
        $tours = $this->db->fetchAll(
            "SELECT * FROM tour WHERE tournoi_id = ? AND ordre > 1 ORDER BY ordre ASC", 
            [$tournoiId]
        );

        foreach ($tours as $tour) {
            // Créer le nombre de matchs prévus (vides)
            for ($i = 0; $i < $tour['matchs_prevus']; $i++) {
                $this->db->execute(
                    "INSERT INTO `match` (tournoi_id, tour_id, date_match, statut) 
                     VALUES (?, ?, NULL, 'a_venir')",
                    [$tournoiId, $tour['id']]
                );
            }
        }
    }
}

// --- LOGIQUE DE LA PAGE ---

$db = Database::getInstance();
$tournoiId = $_GET['id'] ?? null;
$error = '';
$success = '';

if (!$tournoiId) redirect('modules/tournois/index.php');

$tournoi = $db->fetchOne("SELECT * FROM tournoi WHERE id = ?", [$tournoiId]);
if (!$tournoi) redirect('modules/tournois/index.php');

// Traitement de la génération
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $generator = new BracketGenerator();
    
    // Vérifier s'il y a déjà des matchs
    $matchCount = $db->fetchColumn("SELECT COUNT(*) FROM `match` WHERE tournoi_id = ?", [$tournoiId]);
    
    if ($matchCount > 0) {
        $error = "Le bracket a déjà été généré.";
    } else {
        $result = $generator->generateBracket($tournoiId);
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            $success = "Arbre du tournoi généré avec succès !";
            // Mettre à jour le statut du tournoi
            $db->execute("UPDATE tournoi SET statut = 'en_cours' WHERE id = ?", [$tournoiId]);
            $tournoi['statut'] = 'en_cours';
        }
    }
}

// Récupérer les données pour l'affichage
$tours = $db->fetchAll("SELECT * FROM tour WHERE tournoi_id = ? ORDER BY ordre ASC", [$tournoiId]);
$matchs = $db->fetchAll(
    "SELECT m.*, 
     ed.nom as equipe1, ed.logo_url as logo1,
     ee.nom as equipe2, ee.logo_url as logo2
     FROM `match` m
     LEFT JOIN equipe ed ON m.equipe_domicile_id = ed.id
     LEFT JOIN equipe ee ON m.equipe_exterieur_id = ee.id
     WHERE m.tournoi_id = ?
     ORDER BY m.id ASC", 
    [$tournoiId]
);
?>
<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-sitemap me-2"></i>Arbre : <?= htmlspecialchars($tournoi['nom']) ?></h2>
            <span class="badge bg-<?= $tournoi['statut'] == 'en_cours' ? 'success' : 'secondary' ?>">
                <?= htmlspecialchars($tournoi['statut']) ?>
            </span>
        </div>
        
        <?php if (empty($tours) && ($tournoi['statut'] == 'inscription' || $tournoi['statut'] == 'configuration')): ?>
            <form method="POST" onsubmit="return confirm('Générer le bracket ? Cela créera les matchs automatiquement.');">
                <button type="submit" name="generate" class="btn btn-primary">
                    <i class="fas fa-magic me-2"></i>Générer l'arbre
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (empty($tours)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Le tournoi n'a pas encore commencé ou l'arbre n'a pas été généré.
        </div>
    <?php else: ?>
        <div class="row flex-nowrap overflow-auto pb-4">
            <?php foreach ($tours as $tour): ?>
                <div class="col-md-3" style="min-width: 300px;">
                    <div class="card shadow-sm h-100 bg-dark border-secondary">
                        <div class="card-header text-center bg-secondary text-white">
                            <h5 class="mb-0"><?= htmlspecialchars($tour['nom']) ?></h5>
                        </div>
                        <div class="card-body p-2">
                            <?php 
                            $tourMatchs = array_filter($matchs, fn($m) => $m['tour_id'] == $tour['id']);
                            foreach ($tourMatchs as $m): 
                            ?>
                                <div class="card mb-3 bg-light text-dark">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold"><?= htmlspecialchars($m['equipe1'] ?? 'À déterminer') ?></span>
                                            <span class="badge bg-primary"><?= $m['score_domicile'] !== null ? $m['score_domicile'] : '-' ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold"><?= htmlspecialchars($m['equipe2'] ?? 'À déterminer') ?></span>
                                            <span class="badge bg-primary"><?= $m['score_exterieur'] !== null ? $m['score_exterieur'] : '-' ?></span>
                                        </div>
                                        <div class="text-center mt-1">
                                            <small class="text-muted"><?= date('d/m H:i', strtotime($m['date_match'])) ?></small>
                                            <?php if($m['statut'] == 'termine'): ?>
                                                <i class="fas fa-check-circle text-success ms-1"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/templates/footer.php'; ?>