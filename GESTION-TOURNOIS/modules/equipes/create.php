<?php
/**
 * ==========================================
 * CREATE ÉQUIPE - Module
 * ==========================================
 * Formulaire de création d'équipe
 * @author Étudiant 1 - Backend Database
 */

require_once __DIR__ . '/../../includes/config/database.php';

$message = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();
        
        // Validation
        $nom = trim($_POST['nom'] ?? '');
        $abrege = trim($_POST['abrege'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $pays = trim($_POST['pays'] ?? '');
        $couleur_maillot = trim($_POST['couleur_maillot'] ?? '');
        $budget = $_POST['budget'] ?? null;
        $classement_national = $_POST['classement_national'] ?? null;
        
        if (empty($nom)) {
            throw new Exception("Le nom de l'équipe est obligatoire");
        }
        
        // Vérifier si le nom existe déjà
        $checkSql = "SELECT COUNT(*) FROM equipe WHERE nom = ?";
        $exists = $db->fetchColumn($checkSql, [$nom]);
        
        if ($exists > 0) {
            throw new Exception("Une équipe avec ce nom existe déjà");
        }
        
        // Gestion de l'upload du logo
        $logo_url = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['logo']['type'], $allowedTypes)) {
                throw new Exception("Format de logo non autorisé (JPG, PNG, GIF uniquement)");
            }
            
            if ($_FILES['logo']['size'] > $maxSize) {
                throw new Exception("Logo trop volumineux (max 5MB)");
            }
            
            // Créer le dossier s'il n'existe pas
            $uploadDir = __DIR__ . '/../../uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Générer un nom sécurisé
            $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $filepath)) {
                $logo_url = 'uploads/logos/' . $filename;
            }
        }
        
        // Insertion dans la base de données
        $sql = "INSERT INTO equipe (nom, abrege, date_creation, ville, pays, couleur_maillot, logo_url, budget, classement_national) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $nom,
            $abrege,
            $_POST['date_creation'] ?? null,
            $ville,
            $pays,
            $couleur_maillot,
            $logo_url,
            $budget,
            $classement_national
        ];
        
        $id = $db->insert($sql, $params);
        
        $message = "Équipe créée avec succès! ID: $id";
        
        // Redirection après 2 secondes
        header("refresh:2;url=index.php");
        
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
    <title>Créer une équipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Créer une nouvelle équipe</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom de l'équipe *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="abrege" class="form-label">Abréviation</label>
                                    <input type="text" class="form-control" id="abrege" name="abrege" maxlength="10" placeholder="Ex: RMA">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ville" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="ville" name="ville">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="pays" class="form-label">Pays</label>
                                    <input type="text" class="form-control" id="pays" name="pays">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="couleur_maillot" class="form-label">Couleur maillot</label>
                                    <input type="text" class="form-control" id="couleur_maillot" name="couleur_maillot">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="date_creation" class="form-label">Date de création</label>
                                    <input type="date" class="form-control" id="date_creation" name="date_creation">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="classement_national" class="form-label">Classement national</label>
                                    <input type="number" class="form-control" id="classement_national" name="classement_national" min="1">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="budget" class="form-label">Budget (€)</label>
                                <input type="number" class="form-control" id="budget" name="budget" step="0.01" min="0">
                            </div>
                            
                            <div class="mb-3">
                                <label for="logo" class="form-label">Logo de l'équipe</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/jpeg,image/png,image/gif">
                                <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Retour
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Créer l'équipe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>