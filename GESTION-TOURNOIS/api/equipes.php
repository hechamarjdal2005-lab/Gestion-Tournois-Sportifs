<?php
/**
 * ==========================================
 * API ÉQUIPES - CRUD Operations
 * ==========================================
 * Gestion des équipes via API REST
 * @author Étudiant 1 - Backend Database
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../includes/config/database.php';

class EquipeAPI {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtenir toutes les équipes
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT * FROM equipe WHERE 1=1";
            $params = [];
            
            // Filtres optionnels
            if (!empty($filters['pays'])) {
                $sql .= " AND pays = ?";
                $params[] = $filters['pays'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (nom LIKE ? OR ville LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY nom ASC";
            
            // Pagination
            if (!empty($filters['limit'])) {
                $limit = (int) $filters['limit'];
                $offset = !empty($filters['offset']) ? (int) $filters['offset'] : 0;
                $sql .= " LIMIT $limit OFFSET $offset";
            }
            
            $equipes = $this->db->fetchAll($sql, $params);
            
            return $this->response(200, [
                'success' => true,
                'count' => count($equipes),
                'data' => $equipes
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la récupération des équipes'
            ]);
        }
    }
    
    /**
     * Obtenir une équipe par ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT e.*, 
                    (SELECT COUNT(*) FROM joueur WHERE equipe_id = e.id) as total_joueurs,
                    (SELECT COUNT(*) FROM inscription_tournoi WHERE equipe_id = e.id) as total_tournois
                    FROM equipe e 
                    WHERE e.id = ?";
            
            $equipe = $this->db->fetchOne($sql, [$id]);
            
            if (!$equipe) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Équipe introuvable'
                ]);
            }
            
            // Récupérer les joueurs de l'équipe
            $sqlJoueurs = "SELECT * FROM joueur WHERE equipe_id = ? ORDER BY numero_maillot";
            $equipe['joueurs'] = $this->db->fetchAll($sqlJoueurs, [$id]);
            
            return $this->response(200, [
                'success' => true,
                'data' => $equipe
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'équipe'
            ]);
        }
    }
    
    /**
     * Créer une nouvelle équipe
     */
    public function create($data) {
        try {
            // Validation
            $errors = $this->validate($data);
            if (!empty($errors)) {
                return $this->response(400, [
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $errors
                ]);
            }
            
            // Vérifier si le nom existe déjà
            $checkSql = "SELECT COUNT(*) FROM equipe WHERE nom = ?";
            $exists = $this->db->fetchColumn($checkSql, [$data['nom']]);
            
            if ($exists > 0) {
                return $this->response(409, [
                    'success' => false,
                    'message' => 'Une équipe avec ce nom existe déjà'
                ]);
            }
            
            // Insertion
            $sql = "INSERT INTO equipe (nom, abrege, date_creation, ville, pays, couleur_maillot, logo_url, budget, classement_national) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['nom'],
                $data['abrege'] ?? null,
                $data['date_creation'] ?? null,
                $data['ville'] ?? null,
                $data['pays'] ?? null,
                $data['couleur_maillot'] ?? null,
                $data['logo_url'] ?? null,
                $data['budget'] ?? null,
                $data['classement_national'] ?? null
            ];
            
            $id = $this->db->insert($sql, $params);
            
            return $this->response(201, [
                'success' => true,
                'message' => 'Équipe créée avec succès',
                'data' => ['id' => $id]
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'équipe'
            ]);
        }
    }
    
    /**
     * Mettre à jour une équipe
     */
    public function update($id, $data) {
        try {
            // Vérifier si l'équipe existe
            $checkSql = "SELECT COUNT(*) FROM equipe WHERE id = ?";
            $exists = $this->db->fetchColumn($checkSql, [$id]);
            
            if ($exists == 0) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Équipe introuvable'
                ]);
            }
            
            // Construire la requête dynamiquement
            $fields = [];
            $params = [];
            
            $allowedFields = ['nom', 'abrege', 'date_creation', 'ville', 'pays', 
                             'couleur_maillot', 'logo_url', 'budget', 'classement_national'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return $this->response(400, [
                    'success' => false,
                    'message' => 'Aucune donnée à mettre à jour'
                ]);
            }
            
            $params[] = $id;
            $sql = "UPDATE equipe SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $this->db->execute($sql, $params);
            
            return $this->response(200, [
                'success' => true,
                'message' => 'Équipe mise à jour avec succès'
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'équipe'
            ]);
        }
    }
    
    /**
     * Supprimer une équipe
     */
    public function delete($id) {
        try {
            // Vérifier si l'équipe existe
            $checkSql = "SELECT COUNT(*) FROM equipe WHERE id = ?";
            $exists = $this->db->fetchColumn($checkSql, [$id]);
            
            if ($exists == 0) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Équipe introuvable'
                ]);
            }
            
            // Vérifier les dépendances (matchs, inscriptions)
            $checkMatchs = "SELECT COUNT(*) FROM `match` 
                           WHERE equipe_domicile_id = ? OR equipe_exterieur_id = ?";
            $hasMatchs = $this->db->fetchColumn($checkMatchs, [$id, $id]);
            
            if ($hasMatchs > 0) {
                return $this->response(409, [
                    'success' => false,
                    'message' => 'Impossible de supprimer: l\'équipe a des matchs associés'
                ]);
            }
            
            // Suppression
            $sql = "DELETE FROM equipe WHERE id = ?";
            $this->db->execute($sql, [$id]);
            
            return $this->response(200, [
                'success' => true,
                'message' => 'Équipe supprimée avec succès'
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'équipe'
            ]);
        }
    }
    
    /**
     * Upload logo
     */
    public function uploadLogo($id, $file) {
        try {
            // Vérifier si l'équipe existe
            $equipe = $this->db->fetchOne("SELECT * FROM equipe WHERE id = ?", [$id]);
            if (!$equipe) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Équipe introuvable'
                ]);
            }
            
            // Validation du fichier
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowedTypes)) {
                return $this->response(400, [
                    'success' => false,
                    'message' => 'Format de fichier non autorisé'
                ]);
            }
            
            if ($file['size'] > $maxSize) {
                return $this->response(400, [
                    'success' => false,
                    'message' => 'Fichier trop volumineux (max 5MB)'
                ]);
            }
            
            // Créer le dossier s'il n'existe pas
            $uploadDir = __DIR__ . '/../uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Générer un nom unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . $id . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            // Déplacer le fichier
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Mettre à jour la DB
                $logoUrl = 'uploads/logos/' . $filename;
                $sql = "UPDATE equipe SET logo_url = ? WHERE id = ?";
                $this->db->execute($sql, [$logoUrl, $id]);
                
                return $this->response(200, [
                    'success' => true,
                    'message' => 'Logo uploadé avec succès',
                    'data' => ['logo_url' => $logoUrl]
                ]);
            } else {
                return $this->response(500, [
                    'success' => false,
                    'message' => 'Erreur lors de l\'upload'
                ]);
            }
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de l\'upload du logo'
            ]);
        }
    }
    
    /**
     * Validation des données
     */
    private function validate($data) {
        $errors = [];
        
        if (empty($data['nom'])) {
            $errors['nom'] = 'Le nom est obligatoire';
        }
        
        if (!empty($data['budget']) && !is_numeric($data['budget'])) {
            $errors['budget'] = 'Le budget doit être un nombre';
        }
        
        if (!empty($data['classement_national']) && !is_numeric($data['classement_national'])) {
            $errors['classement_national'] = 'Le classement doit être un nombre';
        }
        
        return $errors;
    }
    
    /**
     * Envoyer une réponse JSON
     */
    private function response($code, $data) {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
}

// ==========================================
// ROUTING
// ==========================================
$api = new EquipeAPI();
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Parse URI
$path = parse_url($uri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Obtenir l'ID si présent
$id = null;
if (isset($pathParts[count($pathParts) - 1]) && is_numeric($pathParts[count($pathParts) - 1])) {
    $id = (int) $pathParts[count($pathParts) - 1];
}

// Routing selon la méthode HTTP
switch ($method) {
    case 'GET':
        if ($id) {
            $api->getById($id);
        } else {
            $filters = [
                'pays' => $_GET['pays'] ?? null,
                'search' => $_GET['search'] ?? null,
                'limit' => $_GET['limit'] ?? null,
                'offset' => $_GET['offset'] ?? null
            ];
            $api->getAll($filters);
        }
        break;
        
    case 'POST':
        if (isset($_FILES['logo'])) {
            // Upload logo
            $api->uploadLogo($id, $_FILES['logo']);
        } else {
            // Créer équipe
            $data = json_decode(file_get_contents('php://input'), true);
            $api->create($data);
        }
        break;
        
    case 'PUT':
        if ($id) {
            $data = json_decode(file_get_contents('php://input'), true);
            $api->update($id, $data);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
        }
        break;
        
    case 'DELETE':
        if ($id) {
            $api->delete($id);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requis']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}