<?php
require_once '../../config.php';

$db = Database::getInstance();
$id = $_GET['id'] ?? null;
$error = '';

if (!$id) {
    header("Location: index.php");
    exit();
}

// Fetch match details with team names
$sql = "SELECT m.*, ed.nom as equipe_a_nom, ee.nom as equipe_b_nom 
        FROM `match` m
        JOIN equipe ed ON m.equipe_domicile_id = ed.id
        JOIN equipe ee ON m.equipe_exterieur_id = ee.id
        WHERE m.id = ?";
$match = $db->fetchOne($sql, [$id]);

if (!$match) {
    die("Match introuvable");
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateSql = "UPDATE `match` SET score_domicile = ?, score_exterieur = ?, date_match = ? WHERE id = ?";
        $db->execute($updateSql, [
            $_POST['score_a'], 
            $_POST['score_b'], 
            $_POST['date_match'], 
            $id
        ]);
        header("Location: index.php?success=Match mis à jour");
        exit();
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
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
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($match['id']) ?>">

                    <div class="row mb-3">
                        <div class="col">
                            <label for="equipe_a" class="form-label">Équipe Domicile</label>
                            <input type="text" class="form-control" id="equipe_a" value="<?= htmlspecialchars($match['equipe_a_nom']) ?>" readonly>
                        </div>
                        <div class="col">
                            <label for="equipe_b" class="form-label">Équipe Extérieur</label>
                            <input type="text" class="form-control" id="equipe_b" value="<?= htmlspecialchars($match['equipe_b_nom']) ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="score_a" class="form-label">Score Domicile</label>
                            <input type="number" class="form-control" id="score_a" name="score_a" value="<?= htmlspecialchars($match['score_domicile']) ?>" required>
                        </div>
                        <div class="col">
                            <label for="score_b" class="form-label">Score Extérieur</label>
                            <input type="number" class="form-control" id="score_b" name="score_b" value="<?= htmlspecialchars($match['score_exterieur']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="date_match" class="form-label">Date du Match</label>
                        <input type="datetime-local" class="form-control" id="date_match" name="date_match" value="<?= date('Y-m-d\TH:i', strtotime($match['date_match'])) ?>" required>
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