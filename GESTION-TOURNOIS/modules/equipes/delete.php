<?php
/**
 * ==========================================
 * DELETE ÉQUIPE - Module
 * ==========================================
 * Suppression d'une équipe avec sécurité
 * @author Étudiant 1 - Backend Database
 */

require_once __DIR__ . '/../../config.php';

$message = '';
$error = '';
$equipe = null;
$canDelete = true;
$dependencies = [];

// Récupérer l'ID
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit();
}

$db = Database::getInstance();

// Récupérer les données de l'équipe
try {
    $sql = "SELECT * FROM equipe WHERE id = ?";
    $equipe = $db->fetchOne($sql, [$id]);
    
    if (!$equipe) {
        throw new Exception("Équipe introuvable");
    }
    
    // Vérifier les dépendances
    
    // 1. Joueurs
    $joueursCount = $db->fetchColumn("SELECT COUNT(*) FROM joueur WHERE equipe_id = ?", [$id]);
    if ($joueursCount > 0) {
        $canDelete = false;
        $dependencies[] = "$joueursCount joueur(s)";
    }
    
    // 2. Matchs
    $matchsCount = $db->fetchColumn(
        "SELECT COUNT(*) FROM `match` WHERE equipe_domicile_id = ? OR equipe_exterieur_id = ?", 
        [$id, $id]
    );
    if ($matchsCount > 0) {
        $canDelete = false;
        $dependencies[] = "$matchsCount match(s)";
    }
    
    // 3. Inscriptions tournois
    $inscriptionsCount = $db->fetchColumn("SELECT COUNT(*) FROM inscription_tournoi WHERE equipe_id = ?", [$id]);
    if ($inscriptionsCount > 0) {
        $dependencies[] = "$inscriptionsCount inscription(s) à des tournois";
    }
    
    // 4. Coachs
    $coachsCount = $db->fetchColumn("SELECT COUNT(*) FROM coach WHERE equipe_id = ?", [$id]);
    if ($coachsCount > 0) {
        $dependencies[] = "$coachsCount coach(s)";
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $equipe && $canDelete) {
    try {
        // Vérifier le token CSRF (simple)
        if (!isset($_POST['token']) || $_POST['token'] !== md5($id . 'delete')) {
            throw new Exception("Token de sécurité invalide");
        }
        
        $db->beginTransaction();
        
        try {
            // Supprimer les inscriptions aux tournois (si autorisé)
            if ($inscriptionsCount > 0) {
                $db->execute("DELETE FROM inscription_tournoi WHERE equipe_id = ?", [$id]);
            }
            
            // Mettre à jour les coachs (set NULL)
            if ($coachsCount > 0) {
                $db->execute("UPDATE coach SET equipe_id = NULL WHERE equipe_id = ?", [$id]);
            }
            
            // Supprimer le logo s'il existe
            if ($equipe['logo_url'] && file_exists(__DIR__ . '/../../' . $equipe['logo_url'])) {
                unlink(__DIR__ . '/../../' . $equipe['logo_url']);
            }
            
            // Supprimer l'équipe
            $db->execute("DELETE FROM equipe WHERE id = ?", [$id]);
            
            $db->commit();
            
            $message = "Équipe supprimée avec succès!";
            
            // Redirection après 2 secondes
            header("refresh:2;url=index.php");
            $equipe = null; // Empêcher l'affichage du formulaire
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer l'équipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-trash"></i> Supprimer l'équipe
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($message) ?>
                                <p class="mb-0 mt-2">Redirection en cours...</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($equipe): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Attention!</strong> Cette action est irréversible.
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <?php if ($equipe['logo_url']): ?>
                                        <div class="col-md-3 text-center">
                                            <img src="../../<?= htmlspecialchars($equipe['logo_url']) ?>" 
                                                 alt="Logo" 
                                                 class="img-thumbnail" 
                                                 style="max-height: 100px;">
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="col-md-<?= $equipe['logo_url'] ? '9' : '12' ?>">
                                            <h5><?= htmlspecialchars($equipe['nom']) ?></h5>
                                            <p class="mb-1">
                                                <strong>Abréviation:</strong> <?= htmlspecialchars($equipe['abrege'] ?? 'N/A') ?>
                                            </p>
                                            <p class="mb-1">
                                                <strong>Ville:</strong> <?= htmlspecialchars($equipe['ville'] ?? 'N/A') ?>, 
                                                <?= htmlspecialchars($equipe['pays'] ?? 'N/A') ?>
                                            </p>
                                            <?php if ($equipe['budget']): ?>
                                            <p class="mb-1">
                                                <strong>Budget:</strong> <?= number_format($equipe['budget'], 2, ',', ' ') ?> €
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($dependencies)): ?>
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Dépendances détectées:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($dependencies as $dep): ?>
                                        <li><?= htmlspecialchars($dep) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if (!$canDelete): ?>
                                    <hr>
                                    <p class="mb-0 text-danger">
                                        <strong>⚠️ Suppression impossible:</strong> 
                                        L'équipe a des joueurs ou des matchs associés. 
                                        Veuillez d'abord les supprimer ou les réassigner.
                                    </p>
                                <?php else: ?>
                                    <hr>
                                    <p class="mb-0 text-success">
                                        Ces dépendances seront automatiquement gérées lors de la suppression.
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($canDelete): ?>
                            <form method="POST" onsubmit="return confirm('Êtes-vous vraiment sûr de vouloir supprimer cette équipe ?');">
                                <input type="hidden" name="id" value="<?= $equipe['id'] ?>">
                                <input type="hidden" name="token" value="<?= md5($equipe['id'] . 'delete') ?>">
                                <input type="hidden" name="confirm_delete" value="1">
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirm" required>
                                    <label class="form-check-label" for="confirm">
                                        Je confirme vouloir supprimer définitivement l'équipe 
                                        <strong><?= htmlspecialchars($equipe['nom']) ?></strong>
                                    </label>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Annuler
                                    </a>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash"></i> Supprimer définitivement
                                    </button>
                                </div>
                            </form>
                            <?php else: ?>
                                <div class="d-flex justify-content-center">
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Retour à la liste
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>