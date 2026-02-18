<?php
$csrf_token = "simulated_csrf_token_create";
// Simulation liste équipes pour le select
$equipes_list = ['Les Lions', 'Les Tigres', 'FC Nord', 'Sud United'];
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
                <form action="index.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="row mb-3">
                        <div class="col">
                            <label for="equipe_a" class="form-label">Équipe Domicile</label>
                            <select class="form-select" id="equipe_a" name="equipe_a" required>
                                <option value="" selected disabled>Choisir...</option>
                                <?php foreach($equipes_list as $eq): ?>
                                    <option value="<?= htmlspecialchars($eq) ?>"><?= htmlspecialchars($eq) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label for="equipe_b" class="form-label">Équipe Extérieur</label>
                            <select class="form-select" id="equipe_b" name="equipe_b" required>
                                <option value="" selected disabled>Choisir...</option>
                                <?php foreach($equipes_list as $eq): ?>
                                    <option value="<?= htmlspecialchars($eq) ?>"><?= htmlspecialchars($eq) ?></option>
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