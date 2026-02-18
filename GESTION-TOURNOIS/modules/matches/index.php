<?php
// Simulation données matches
$matches = [
    ['id' => 1, 'equipe_a' => 'Lions', 'equipe_b' => 'Tigres', 'score_a' => 2, 'score_b' => 0, 'date' => '2023-10-20'],
    ['id' => 2, 'equipe_a' => 'FC Nord', 'equipe_b' => 'Sud United', 'score_a' => 1, 'score_b' => 1, 'date' => '2023-10-21'],
];
?>
<?php require_once '../../includes/templates/header.php'; ?>
<?php require_once '../../includes/templates/navigation.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Matches</h2>
    <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Créer un Match</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Équipe Domicile</th>
                    <th>Score</th>
                    <th>Équipe Extérieur</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?= htmlspecialchars($match['date']) ?></td>
                    <td><?= htmlspecialchars($match['equipe_a']) ?></td>
                    <td class="fw-bold text-center"><?= htmlspecialchars($match['score_a']) ?> - <?= htmlspecialchars($match['score_b']) ?></td>
                    <td><?= htmlspecialchars($match['equipe_b']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= htmlspecialchars($match['id']) ?>" class="btn btn-sm btn-warning text-white">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/templates/footer.php'; ?>