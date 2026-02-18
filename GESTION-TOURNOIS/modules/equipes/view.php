<?php
// Simulation récupération ID
$id = $_GET['id'] ?? 1;
$equipe = ['id' => $id, 'nom' => 'Les Lions', 'ville' => 'Paris', 'coach' => 'Jean Dupont', 'fonde' => '1990'];
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
                        <span><?= htmlspecialchars($equipe['ville']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between">
                        <span>Coach :</span>
                        <span><?= htmlspecialchars($equipe['coach']) ?></span>
                    </li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between">
                        <span>Année de fondation :</span>
                        <span><?= htmlspecialchars($equipe['fonde']) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/templates/footer.php'; ?>