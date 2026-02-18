<?php
// Simulation de données pour les stats
$stats = [
    ['title' => 'Tournois Actifs', 'value' => 12, 'icon' => 'fa-flag', 'color' => 'text-warning'],
    ['title' => 'Équipes Inscrites', 'value' => 48, 'icon' => 'fa-users', 'color' => 'text-info'],
    ['title' => 'Matches Joués', 'value' => 156, 'icon' => 'fa-futbol', 'color' => 'text-success'],
    ['title' => 'En attente', 'value' => 5, 'icon' => 'fa-clock', 'color' => 'text-danger'],
];
?>
<?php require_once 'includes/templates/header.php'; ?>
<?php require_once 'includes/templates/navigation.php'; ?>

<div class="row g-3 mb-4">
    <?php foreach ($stats as $stat): ?>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1"><?= htmlspecialchars($stat['title']) ?></h6>
                    <h3 class="fw-bold mb-0"><?= htmlspecialchars($stat['value']) ?></h3>
                </div>
                <div class="fs-1 <?= htmlspecialchars($stat['color']) ?>">
                    <i class="fas <?= htmlspecialchars($stat['icon']) ?>"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table me-2"></i>Derniers Matches
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Équipe A</th>
                                <th>Score</th>
                                <th>Équipe B</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Données simulées -->
                            <tr>
                                <td>2023-10-25</td>
                                <td>Les Lions</td>
                                <td>2 - 1</td>
                                <td>Les Tigres</td>
                                <td><span class="badge bg-success">Terminé</span></td>
                            </tr>
                            <tr>
                                <td>2023-10-26</td>
                                <td>FC Paris</td>
                                <td>-</td>
                                <td>OM Marseille</td>
                                <td><span class="badge bg-warning text-dark">À venir</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/templates/footer.php'; ?>