<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

require_once '../../includes/config/database.php';

// Get match ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if(!$id) {
    $_SESSION['error_message'] = "معرّف المباراة غير صحيح";
    header('Location: /modules/matches/');
    exit;
}

// Fetch match details
try {
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
    $stmt->execute([$id]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$match) {
        $_SESSION['error_message'] = "المباراة غير موجودة";
        header('Location: /modules/matches/');
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "خطأ في جلب البيانات";
    header('Location: /modules/matches/');
    exit;
}

// Fetch teams and tournaments
try {
    $stmt = $pdo->query("SELECT id, nom FROM equipes ORDER BY nom ASC");
    $equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id, nom FROM tournois ORDER BY nom ASC");
    $tournois = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $equipes = [];
    $tournois = [];
}

$pageTitle = 'تعديل المباراة';
include '../../includes/templates/header.php';
include '../../includes/templates/navigation.php';
?>

<div class="container">
    <div class="card" style="margin-top: 2rem; max-width: 800px; margin-left: auto; margin-right: auto;">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-edit"></i>
                تعديل المباراة
            </h2>
        </div>
        
        <div class="card-body">
            <form method="POST" action="/api/matches.php" data-validate>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $match['id']; ?>">
                
                <div class="form-group">
                    <label for="tournoi_id">
                        <i class="fas fa-trophy"></i>
                        البطولة *
                    </label>
                    <select name="tournoi_id" id="tournoi_id" class="form-control" required>
                        <option value="">-- اختر البطولة --</option>
                        <?php foreach($tournois as $tournoi): ?>
                            <option value="<?php echo $tournoi['id']; ?>"
                                <?php echo ($match['tournoi_id'] == $tournoi['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tournoi['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-2">
                        <div class="form-group">
                            <label for="equipe1_id">
                                <i class="fas fa-shield-alt"></i>
                                الفريق الأول *
                            </label>
                            <select name="equipe1_id" id="equipe1_id" class="form-control" required>
                                <option value="">-- اختر الفريق --</option>
                                <?php foreach($equipes as $equipe): ?>
                                    <option value="<?php echo $equipe['id']; ?>"
                                        <?php echo ($match['equipe1_id'] == $equipe['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($equipe['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-2">
                        <div class="form-group">
                            <label for="equipe2_id">
                                <i class="fas fa-shield-alt"></i>
                                الفريق الثاني *
                            </label>
                            <select name="equipe2_id" id="equipe2_id" class="form-control" required>
                                <option value="">-- اختر الفريق --</option>
                                <?php foreach($equipes as $equipe): ?>
                                    <option value="<?php echo $equipe['id']; ?>"
                                        <?php echo ($match['equipe2_id'] == $equipe['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($equipe['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="date_match">
                        <i class="fas fa-calendar"></i>
                        تاريخ ووقت المباراة *
                    </label>
                    <input 
                        type="datetime-local" 
                        name="date_match" 
                        id="date_match" 
                        class="form-control"
                        value="<?php echo date('Y-m-d\TH:i', strtotime($match['date_match'])); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="lieu">
                        <i class="fas fa-map-marker-alt"></i>
                        مكان المباراة
                    </label>
                    <input 
                        type="text" 
                        name="lieu" 
                        id="lieu" 
                        class="form-control"
                        value="<?php echo htmlspecialchars($match['lieu'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="مثال: ملعب محمد الخامس"
                    >
                </div>
                
                <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="/modules/matches/" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/templates/footer.php'; ?>