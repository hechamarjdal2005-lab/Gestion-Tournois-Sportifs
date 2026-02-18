<?php
// Simulation de données équipes
$equipes = [
    ['id' => 1, 'nom' => 'Les Lions', 'ville' => 'Paris', 'coach' => 'Jean Dupont'],
    ['id' => 2, 'nom' => 'Les Tigres', 'ville' => 'Lyon', 'coach' => 'Marc Martin'],
    ['id' => 3, 'nom' => 'FC Nord', 'ville' => 'Lille', 'coach' => 'Paul Paul'],
];
?>
<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Équipes</h2>
    <button class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Équipe</button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom de l'équipe</th>
                        <th>Ville</th>
                        <th>Coach</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipes as $equipe): ?>
                    <tr>
                        <td><?= htmlspecialchars($equipe['id']) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($equipe['nom']) ?></td>
                        <td><?= htmlspecialchars($equipe['ville']) ?></td>
                        <td><?= htmlspecialchars($equipe['coach']) ?></td>
                        <td>
                            <a href="view.php?id=<?= htmlspecialchars($equipe['id']) ?>" class="btn btn-sm btn-info text-white me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-warning text-white">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/templates/footer.php'; ?>