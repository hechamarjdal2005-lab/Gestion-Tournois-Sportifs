<?php
require_once '../../config.php';

$db = Database::getInstance();
$id = $_GET['id'] ?? null;

if (!$id) {
    redirect('modules/equipes/index.php');
}

$equipe = $db->fetchOne("SELECT * FROM equipe WHERE id = ?", [$id]);

if (!$equipe) {
    die("Équipe introuvable");
}

// Récupérer le coach si existe (optionnel, selon schema)
$coach = $db->fetchOne("SELECT * FROM coach WHERE equipe_id = ?", [$id]);
?>
<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="mb-4">
    <a href="index.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Retour</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Détails de l'équipe</div>
            <div class="card-body">
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between">
                        <span>Nom :</span>
                        <span class="fw-bold"><?= htmlspecialchars($equipe['nom']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between">
                        <span>Ville :</span>
                        <span><?= htmlspecialchars($equipe['ville'] ?? 'N/A') ?></span>
                    </li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between">
                        <span>Pays :</span>
                        <span><?= htmlspecialchars($equipe['pays'] ?? 'N/A') ?></span>
                    </li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between">
                        <span>Coach :</span>
                        <span><?= htmlspecialchars(($coach['prenom'] ?? '') . ' ' . ($coach['nom'] ?? 'Non assigné')) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between">
                        <span>Date de création :</span>
                        <span><?= htmlspecialchars($equipe['date_creation'] ?? 'N/A') ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/templates/footer.php'; ?>