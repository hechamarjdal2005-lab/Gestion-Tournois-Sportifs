<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../includes/lib/auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Rediriger si déjà connecté
if ($auth->checkSession()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valider CSRF
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide";
    } else {
        $username = cleanInput($_POST['username'] ?? '');
        $email = cleanInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $nom_complet = cleanInput($_POST['nom_complet'] ?? '');
        
        // Validations
        if (empty($username) || empty($email) || empty($password)) {
            $error = "Veuillez remplir tous les champs obligatoires";
        } elseif (!isValidEmail($email)) {
            $error = "Format d'email invalide";
        } elseif (strlen($password) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères";
        } elseif ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            $error = "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre";
        } else {
            $result = $auth->register($username, $email, $password, $nom_complet);
            
            if ($result['success']) {
                $success = "Inscription réussie! Vous pouvez maintenant vous connecter.";
                header("refresh:2;url=login.php");
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); min-height: 100vh; }
        .register-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .register-header { background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: white; border-radius: 15px 15px 0 0; padding: 20px; }
        .btn-register { background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); border: none; color: white; padding: 12px; border-radius: 25px; font-weight: 600; }
        .btn-register:hover { opacity: 0.9; color: white; }
        .password-strength { height: 5px; transition: all 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="card register-card">
                    <div class="register-header text-center">
                        <h3><i class="bi bi-person-plus"></i> <?= APP_NAME ?></h3>
                        <p class="mb-0">Créez votre compte gratuitement</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                                <p class="mb-0 mt-2">Redirection vers la connexion...</p>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="registerForm">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Nom d'utilisateur *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               placeholder="johndoe" required>
                                    </div>
                                    <small class="text-muted">3-20 caractères, lettres et chiffres uniquement</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="nom_complet" class="form-label">Nom complet</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                        <input type="text" class="form-control" id="nom_complet" name="nom_complet" 
                                               placeholder="John Doe">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="john@example.com" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Mot de passe *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="••••••••" required>
                                    </div>
                                    <div class="password-strength mt-2" id="passwordStrength"></div>
                                    <small class="text-muted">Min 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" placeholder="••••••••" required>
                                    </div>
                                    <small class="text-muted" id="passwordMatch"></small>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="#" class="text-decoration-none">conditions d'utilisation</a> 
                                    et la <a href="#" class="text-decoration-none">politique de confidentialité</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-register w-100 mb-3">
                                <i class="bi bi-person-plus"></i> S'inscrire
                            </button>
                            
                            <div class="text-center">
                                <span class="text-muted">Déjà inscrit?</span>
                                <a href="login.php" class="text-decoration-none">Se connecter</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Vérification force mot de passe
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const colors = ['#dc3545', '#ffc107', '#28a745', '#28a745', '#28a745'];
            const widths = ['25%', '50%', '75%', '90%', '100%'];
            
            strengthBar.style.width = widths[strength - 1] || '0%';
            strengthBar.style.backgroundColor = colors[strength - 1] || '#ddd';
            strengthBar.style.height = '5px';
        });
        
        // Vérification confirmation mot de passe
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            const matchMsg = document.getElementById('passwordMatch');
            
            if (password === confirm) {
                matchMsg.innerHTML = '✓ Les mots de passe correspondent';
                matchMsg.style.color = 'green';
            } else {
                matchMsg.innerHTML = '✗ Les mots de passe ne correspondent pas';
                matchMsg.style.color = 'red';
            }
        });
    </script>
</body>
</html>