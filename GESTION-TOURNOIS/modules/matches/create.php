<?php
require_once '../../config.php';
require_once '../../includes/lib/auth.php'; // Assuming Auth class exists for CSRF

$db = Database::getInstance();
$message = '';
$error = '';

// Fetch real teams from DB
try {
    $equipes_list = $db->fetchAll("SELECT * FROM equipe ORDER BY nom ASC");
} catch (Exception $e) {
    $error = "Erreur de chargement des équipes";
    $equipes_list = [];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO `match` (equipe_domicile_id, equipe_exterieur_id, score_domicile, score_exterieur, date_match) 
                VALUES (?, ?, ?, ?, ?)";
        $params = [
            $_POST['equipe_a'],
            $_POST['equipe_b'],
            $_POST['score_a'],
            $_POST['score_b'],
            $_POST['date_match']
        ];
        $db->insert($sql, $params);
        header("Location: index.php?success=Match créé");
        exit();
    } catch (Exception $e) {
        $error = "Erreur lors de la création: " . $e->getMessage();
    }
}
?>
<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Créer un nouveau match
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="needs-validation" novalidate>
                    <!-- <input type="hidden" name="csrf_token" value="..."> Add CSRF here if Auth available -->

                    <div class="row mb-3">
                        <div class="col">
                            <label for="equipe_a" class="form-label">Équipe Domicile</label>
                            <select class="form-select" id="equipe_a" name="equipe_a" required>
                                <option value="" selected disabled>Choisir...</option>
                                <?php foreach($equipes_list as $eq): ?>
                                    <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label for="equipe_b" class="form-label">Équipe Extérieur</label>
                            <select class="form-select" id="equipe_b" name="equipe_b" required>
                                <option value="" selected disabled>Choisir...</option>
                                <?php foreach($equipes_list as $eq): ?>
                                    <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="score_a" class="form-label">Score Domicile</label>
                            <input type="number" class="form-control" id="score_a" name="score_a" min="0" required>
                        </div>
                        <div class="col">
                            <label for="score_b" class="form-label">Score Extérieur</label>
                            <input type="number" class="form-control" id="score_b" name="score_b" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="date_match" class="form-label">Date du Match</label>
                        <input type="datetime-local" class="form-control" id="date_match" name="date_match" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Enregistrer le Match</button>
                        <a href="index.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/templates/footer.php'; ?>