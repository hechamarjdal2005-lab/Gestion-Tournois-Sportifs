<?php
/**
 * ==========================================
 * CREATE TOURNOI - Module
 * ==========================================
 * Formulaire de création de tournoi
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
        $description = trim($_POST['description'] ?? '');
        $date_debut = $_POST['date_debut'] ?? null;
        $date_fin = $_POST['date_fin'] ?? null;
        $type_tournoi = $_POST['type_tournoi'] ?? 'elimination';
        $nombre_equipes = (int) ($_POST['nombre_equipes'] ?? 16);
        $statut = $_POST['statut'] ?? 'configuration';
        $avec_petite_finale = isset($_POST['avec_petite_finale']) ? 1 : 0;
        
        // Validations
        if (empty($nom)) {
            throw new Exception("Le nom du tournoi est obligatoire");
        }
        
        if ($nombre_equipes < 2 || $nombre_equipes > 128) {
            throw new Exception("Le nombre d'équipes doit être entre 2 et 128");
        }
        
        if ($date_debut && $date_fin && strtotime($date_fin) < strtotime($date_debut)) {
            throw new Exception("La date de fin doit être après la date de début");
        }
        
        // Vérifier si le nom existe déjà
        $checkSql = "SELECT COUNT(*) FROM tournoi WHERE nom = ?";
        $exists = $db->fetchColumn($checkSql, [$nom]);
        
        if ($exists > 0) {
            throw new Exception("Un tournoi avec ce nom existe déjà");
        }
        
        // Insertion
        $sql = "INSERT INTO tournoi (nom, description, date_debut, date_fin, type_tournoi, 
                nombre_equipes, statut, avec_petite_finale) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $nom,
            $description,
            $date_debut,
            $date_fin,
            $type_tournoi,
            $nombre_equipes,
            $statut,
            $avec_petite_finale
        ];
        
        $id = $db->insert($sql, $params);
        
        $message = "Tournoi créé avec succès! ID: $id";
        
        // Redirection
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
    <title>Créer un tournoi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-trophy"></i> Créer un nouveau tournoi
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="nom" class="form-label">Nom du tournoi *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           placeholder="Ex: Champions League 2025" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="nombre_equipes" class="form-label">Nombre d'équipes</label>
                                    <select class="form-select" id="nombre_equipes" name="nombre_equipes">
                                        <option value="2">2 équipes</option>
                                        <option value="4">4 équipes</option>
                                        <option value="8">8 équipes</option>
                                        <option value="16" selected>16 équipes</option>
                                        <option value="32">32 équipes</option>
                                        <option value="64">64 équipes</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="Description du tournoi..."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="type_tournoi" class="form-label">Type de tournoi</label>
                                    <select class="form-select" id="type_tournoi" name="type_tournoi">
                                        <option value="elimination" selected>Élimination directe</option>
                                        <option value="poules">Phases de poules</option>
                                        <option value="mixte">Mixte (Poules + Élimination)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="date_debut" class="form-label">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="date_fin" class="form-label">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="statut" class="form-label">Statut initial</label>
                                    <select class="form-select" id="statut" name="statut">
                                        <option value="configuration" selected>Configuration</option>
                                        <option value="inscription">Inscription ouverte</option>
                                        <option value="en_cours">En cours</option>
                                        <option value="termine">Terminé</option>
                                        <option value="annule">Annulé</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Généralement "Configuration" pour un nouveau tournoi
                                    </small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">Options</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="avec_petite_finale" 
                                               name="avec_petite_finale">
                                        <label class="form-check-label" for="avec_petite_finale">
                                            Inclure une petite finale (match pour la 3e place)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Info:</strong> Une fois le tournoi créé, vous pourrez :
                                <ul class="mb-0 mt-2">
                                    <li>Inscrire les équipes participantes</li>
                                    <li>Configurer les tours et phases</li>
                                    <li>Générer le bracket d'élimination</li>
                                    <li>Planifier les matchs</li>
                                </ul>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Retour
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-trophy"></i> Créer le tournoi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Guide rapide -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-lightbulb"></i> Guide rapide
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Élimination directe</h6>
                                <p class="small text-muted">
                                    Format classique à élimination. Chaque équipe éliminée sort du tournoi.
                                    Idéal pour 4, 8, 16, 32 équipes.
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h6>Phases de poules</h6>
                                <p class="small text-muted">
                                    Les équipes jouent dans des groupes. Les meilleures se qualifient.
                                    Format Coupe du Monde.
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h6>Mixte</h6>
                                <p class="small text-muted">
                                    Combinaison de poules suivies d'élimination directe.
                                    Format Champions League.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation des dates
        document.getElementById('date_debut').addEventListener('change', function() {
            document.getElementById('date_fin').min = this.value;
        });
    </script>
</body>
</html>