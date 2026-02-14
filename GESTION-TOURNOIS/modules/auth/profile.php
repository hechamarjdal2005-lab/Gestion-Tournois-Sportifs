<?php
/**
 * PROFIL UTILISATEUR
 * Affiche et modifie le profil de l'utilisateur connecté
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../includes/lib/auth.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if (!$auth->checkSession()) {
    $_SESSION['flash_message'] = "Veuillez vous connecter pour accéder à votre profil";
    $_SESSION['flash_type'] = "warning";
    redirect('modules/auth/login.php');
}

$db = Database::getInstance();
$error = '';
$success = '';

// Récupérer les infos de l'utilisateur
$user = $db->fetchOne(
    "SELECT * FROM utilisateur WHERE id = ?",
    [$_SESSION['user_id']]
);

// Récupérer les statistiques
$stats = [
    'tournois_organises' => $db->fetchColumn(
        "SELECT COUNT(*) FROM tournoi WHERE created_by = ?",
        [$user['id']]
    ),
    'matchs_arbitres' => $db->fetchColumn(
        "SELECT COUNT(*) FROM `match` WHERE arbitre_principal = ?",
        [$user['nom_complet']]
    ),
    'equipe_favorite' => $user['equipe_favorite_id'] ? $db->fetchOne(
        "SELECT nom FROM equipe WHERE id = ?",
        [$user['equipe_favorite_id']]
    )['nom'] : 'Aucune'
];

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            // Mise à jour des infos de base
            $nom_complet = cleanInput($_POST['nom_complet'] ?? '');
            $telephone = cleanInput($_POST['telephone'] ?? '');
            $langue = cleanInput($_POST['langue'] ?? 'fr');
            $equipe_favorite_id = !empty($_POST['equipe_favorite_id']) ? (int)$_POST['equipe_favorite_id'] : null;
            
            try {
                $db->execute(
                    "UPDATE utilisateur SET nom_complet = ?, telephone = ?, langue = ?, equipe_favorite_id = ? WHERE id = ?",
                    [$nom_complet, $telephone, $langue, $equipe_favorite_id, $user['id']]
                );
                $success = "Profil mis à jour avec succès";
                
                // Recharger les données
                $user = $db->fetchOne("SELECT * FROM utilisateur WHERE id = ?", [$user['id']]);
                
            } catch (Exception $e) {
                $error = "Erreur lors de la mise à jour";
            }
            
        } elseif ($action === 'change_password') {
            // Changement de mot de passe
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            
            // Vérifier mot de passe actuel
            if (!verifyPassword($current, $user['password_hash'])) {
                $error = "Mot de passe actuel incorrect";
            } elseif (strlen($new) < 8) {
                $error = "Le nouveau mot de passe doit contenir au moins 8 caractères";
            } elseif ($new !== $confirm) {
                $error = "Les nouveaux mots de passe ne correspondent pas";
            } else {
                $newHash = hashPassword($new);
                $db->execute(
                    "UPDATE utilisateur SET password_hash = ? WHERE id = ?",
                    [$newHash, $user['id']]
                );
                $success = "Mot de passe changé avec succès";
            }
        }
    }
}

// Récupérer les équipes pour le select
$equipes = $db->fetchAll("SELECT id, nom FROM equipe ORDER BY nom");

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .profile-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 0; margin-bottom: 30px; }
        .avatar { width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .stat-card { background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2rem; font-weight: bold; color: #667eea; }
        .nav-tabs .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/../../includes/templates/navigation.php'; ?>
    
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="avatar bg-white d-flex align-items-center justify-content-center mx-auto">
                        <i class="bi bi-person-circle" style="font-size: 80px; color: #667eea;"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <h2><?= htmlspecialchars($user['nom_complet'] ?: $user['username']) ?></h2>
                    <p><i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                    <p><i class="bi bi-person-badge"></i> Rôle: <?= ucfirst(str_replace('_', ' ', $user['role'])) ?></p>
                </div>
                <div class="col-md-4">
                    <span class="badge bg-light text-dark p-2">
                        <i class="bi bi-calendar"></i> Membre depuis: <?= date('d/m/Y', strtotime($user['date_inscription'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="container mb-4">
        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-trophy fs-1 text-warning"></i>
                    <div class="stat-number"><?= $stats['tournois_organises'] ?></div>
                    <div class="text-muted">Tournois organisés</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-stopwatch fs-1 text-success"></i>
                    <div class="stat-number"><?= $stats['matchs_arbitres'] ?></div>
                    <div class="text-muted">Matchs arbitrés</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-heart fs-1 text-danger"></i>
                    <div class="stat-number"><?= htmlspecialchars($stats['equipe_favorite']) ?></div>
                    <div class="text-muted">Équipe favorite</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container mb-5">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                    <i class="bi bi-person"></i> Informations personnelles
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
                    <i class="bi bi-shield-lock"></i> Sécurité
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">
                    <i class="bi bi-clock-history"></i> Activité récente
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Informations personnelles -->
            <div class="tab-pane fade show active" id="info">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-pencil"></i> Modifier mes informations</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Nom d'utilisateur</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?= htmlspecialchars($user['username']) ?>" readonly disabled>
                                    <small class="text-muted">Le nom d'utilisateur ne peut pas être modifié</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom_complet" class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="nom_complet" name="nom_complet" 
                                           value="<?= htmlspecialchars($user['nom_complet'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" 
                                           value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="langue" class="form-label">Langue préférée</label>
                                    <select class="form-select" id="langue" name="langue">
                                        <option value="fr" <?= $user['langue'] == 'fr' ? 'selected' : '' ?>>Français</option>
                                        <option value="ar" <?= $user['langue'] == 'ar' ? 'selected' : '' ?>>Arabe</option>
                                        <option value="en" <?= $user['langue'] == 'en' ? 'selected' : '' ?>>Anglais</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="equipe_favorite_id" class="form-label">Équipe favorite</label>
                                    <select class="form-select" id="equipe_favorite_id" name="equipe_favorite_id">
                                        <option value="">-- Aucune --</option>
                                        <?php foreach ($equipes as $equipe): ?>
                                            <option value="<?= $equipe['id'] ?>" 
                                                <?= $user['equipe_favorite_id'] == $equipe['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($equipe['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sécurité -->
            <div class="tab-pane fade" id="security">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-key"></i> Changer mon mot de passe</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" required>
                                    <small class="text-muted">Min 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key"></i> Changer le mot de passe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Activité récente -->
            <div class="tab-pane fade" id="activity">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-clock"></i> Dernières connexions</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Récupérer les logs de connexion (si table existe)
                        $logs = $db->fetchAll(
                            "SELECT * FROM log_activite WHERE utilisateur_id = ? ORDER BY created_at DESC LIMIT 10",
                            [$user['id']]
                        );
                        ?>
                        
                        <?php if (empty($logs)): ?>
                            <p class="text-muted text-center">Aucune activité récente</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($logs as $log): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <span><i class="bi bi-<?= $log['action'] == 'LOGIN' ? 'box-arrow-in-right' : 'clock' ?>"></i> 
                                                <?= htmlspecialchars($log['action']) ?></span>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                                        </div>
                                        <?php if ($log['details']): ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($log['details']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-muted">
                        Dernière connexion: <?= $user['derniere_connexion'] ? date('d/m/Y H:i', strtotime($user['derniere_connexion'])) : 'Jamais' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/../../includes/templates/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmation avant certaines actions
        document.querySelectorAll('form[action*="password"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Voulez-vous vraiment changer votre mot de passe ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>