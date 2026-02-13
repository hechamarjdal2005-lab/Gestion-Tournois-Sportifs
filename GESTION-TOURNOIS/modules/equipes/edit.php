<?php
/**
 * ==========================================
 * EDIT ÉQUIPE - Module
 * ==========================================
 * Formulaire de modification d'équipe
 * @author Étudiant 1 - Backend Database
 */

require_once __DIR__ . '/../../includes/config/database.php';

$message = '';
$error = '';
$equipe = null;

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
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $equipe) {
    try {
        // Validation
        $nom = trim($_POST['nom'] ?? '');
        
        if (empty($nom)) {
            throw new Exception("Le nom de l'équipe est obligatoire");
        }
        
        // Vérifier si le nom existe déjà (sauf pour cette équipe)
        $checkSql = "SELECT COUNT(*) FROM equipe WHERE nom = ? AND id != ?";
        $exists = $db->fetchColumn($checkSql, [$nom, $id]);
        
        if ($exists > 0) {
            throw new Exception("Une autre équipe avec ce nom existe déjà");
        }
        
        // Gestion de l'upload du logo
        $logo_url = $equipe['logo_url'];
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024;
            
            if (!in_array($_FILES['logo']['type'], $allowedTypes)) {
                throw new Exception("Format de logo non autorisé");
            }
            
            if ($_FILES['logo']['size'] > $maxSize) {
                throw new Exception("Logo trop volumineux (max 5MB)");
            }
            
            $uploadDir = __DIR__ . '/../../uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . $id . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $filepath)) {
                // Supprimer l'ancien logo
                if ($equipe['logo_url'] && file_exists(__DIR__ . '/../../' . $equipe['logo_url'])) {
                    unlink(__DIR__ . '/../../' . $equipe['logo_url']);
                }
                $logo_url = 'uploads/logos/' . $filename;
            }
        }
        
        // Mise à jour
        $sql = "UPDATE equipe SET 
                nom = ?, 
                abrege = ?, 
                date_creation = ?, 
                ville = ?, 
                pays = ?, 
                couleur_maillot = ?, 
                logo_url = ?, 
                budget = ?, 
                classement_national = ?
                WHERE id = ?";
        
        $params = [
            $nom,
            trim($_POST['abrege'] ?? ''),
            $_POST['date_creation'] ?? null,
            trim($_POST['ville'] ?? ''),
            trim($_POST['pays'] ?? ''),
            trim($_POST['couleur_maillot'] ?? ''),
            $logo_url,
            $_POST['budget'] ?? null,
            $_POST['classement_national'] ?? null,
            $id
        ];
        
        $db->execute($sql, $params);
        
        $message = "Équipe mise à jour avec succès!";
        
        // Recharger les données
        $equipe = $db->fetchOne("SELECT * FROM equipe WHERE id = ?", [$id]);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'équipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Modifier l'équipe: <?= htmlspecialchars($equipe['nom'] ?? '') ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($equipe): ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $equipe['id'] ?>">
                            
                            <?php if ($equipe['logo_url']): ?>
                            <div class="mb-3 text-center">
                                <img src="../../<?= htmlspecialchars($equipe['logo_url']) ?>" 
                                     alt="Logo actuel" 
                                     class="img-thumbnail" 
                                     style="max-height: 100px;">
                                <p class="text-muted small">Logo actuel</p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom de l'équipe *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?= htmlspecialchars($equipe['nom']) ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="abrege" class="form-label">Abréviation</label>
                                    <input type="text" class="form-control" id="abrege" name="abrege" 
                                           value="<?= htmlspecialchars($equipe['abrege'] ?? '') ?>" maxlength="10">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ville" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="ville" name="ville" 
                                           value="<?= htmlspecialchars($equipe['ville'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="pays" class="form-label">Pays</label>
                                    <input type="text" class="form-control" id="pays" name="pays" 
                                           value="<?= htmlspecialchars($equipe['pays'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="couleur_maillot" class="form-label">Couleur maillot</label>
                                    <input type="text" class="form-control" id="couleur_maillot" name="couleur_maillot" 
                                           value="<?= htmlspecialchars($equipe['couleur_maillot'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="date_creation" class="form-label">Date de création</label>
                                    <input type="date" class="form-control" id="date_creation" name="date_creation" 
                                           value="<?= htmlspecialchars($equipe['date_creation'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="classement_national" class="form-label">Classement</label>
                                    <input type="number" class="form-control" id="classement_national" name="classement_national" 
                                           value="<?= htmlspecialchars($equipe['classement_national'] ?? '') ?>" min="1">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="budget" class="form-label">Budget (€)</label>
                                <input type="number" class="form-control" id="budget" name="budget" 
                                       value="<?= htmlspecialchars($equipe['budget'] ?? '') ?>" step="0.01" min="0">
                            </div>
                            
                            <div class="mb-3">
                                <label for="logo" class="form-label">Changer le logo</label>
                                <input type="file" class="form-control" id="logo" name="logo" 
                                       accept="image/jpeg,image/png,image/gif">
                                <small class="form-text text-muted">Laissez vide pour garder le logo actuel</small>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">Retour</a>
                                <button type="submit" class="btn btn-warning">Mettre à jour</button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>