<?php
// Simulation données tournois
$tournois = [
    ['id' => 1, 'nom' => 'Coupe de France 2023', 'date_debut' => '2023-09-01', 'statut' => 'En cours'],
    ['id' => 2, 'nom' => 'Championnat Régional', 'date_debut' => '2023-11-15', 'statut' => 'À venir'],
];
?>
<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<h2 class="mb-4">Tournois</h2>

<div class="card">
    <div class="card-body">
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Nom du Tournoi</th>
                    <th>Date de début</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tournois as $tournoi): ?>
                <tr>
                    <td><?= htmlspecialchars($tournoi['nom']) ?></td>
                    <td><?= htmlspecialchars($tournoi['date_debut']) ?></td>
                    <td>
                        <span class="badge <?= $tournoi['statut'] == 'En cours' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= htmlspecialchars($tournoi['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary">Gérer</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/templates/footer.php'; ?>