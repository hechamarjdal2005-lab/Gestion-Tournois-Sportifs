<?php
// Simulation données existantes pour édition
$match = ['id' => 1, 'equipe_a' => 'Les Lions', 'equipe_b' => 'Les Tigres', 'score_a' => 2, 'score_b' => 0, 'date' => '2023-10-20T14:00'];
$csrf_token = "simulated_csrf_token_edit";
?>
<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Modifier le match #<?= htmlspecialchars($match['id']) ?>
            </div>
            <div class="card-body">
                <form action="index.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($match['id']) ?>">

                    <div class="row mb-3">
                        <div class="col">
                            <label for="equipe_a" class="form-label">Équipe Domicile</label>
                            <input type="text" class="form-control" id="equipe_a" name="equipe_a" value="<?= htmlspecialchars($match['equipe_a']) ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="equipe_b" class="form-label">Équipe Extérieur</label>
                            <input type="text" class="form-control" id="equipe_b" name="equipe_b" value="<?= htmlspecialchars($match['equipe_b']) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="score_a" class="form-label">Score Domicile</label>
                            <input type="number" class="form-control" id="score_a" name="score_a" value="<?= htmlspecialchars($match['score_a']) ?>" required>
                        </div>
                        <div class="col">
                            <label for="score_b" class="form-label">Score Extérieur</label>
                            <input type="number" class="form-control" id="score_b" name="score_b" value="<?= htmlspecialchars($match['score_b']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="date_match" class="form-label">Date du Match</label>
                        <input type="datetime-local" class="form-control" id="date_match" name="date_match" value="<?= htmlspecialchars($match['date']) ?>" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning text-white">Mettre à jour</button>
                        <a href="index.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/templates/footer.php'; ?>