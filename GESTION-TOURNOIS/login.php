<?php
// Simulation de variables pour l'affichage
$csrf_token = "simulated_csrf_token_12345"; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SportManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; }
    </style>
</head>
<body class="bg-dark">
    <div class="card login-card p-4">
        <div class="card-body">
            <h3 class="text-center mb-4 text-primary">SportManager</h3>
            <form action="dashboard.php" method="POST" class="needs-validation" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Veuillez entrer un email valide.</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Le mot de passe est requis.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>