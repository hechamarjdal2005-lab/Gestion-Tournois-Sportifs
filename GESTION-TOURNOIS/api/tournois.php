<?php
/**
 * ==========================================
 * API TOURNOIS - CRUD Operations
 * ==========================================
 * Gestion des tournois via API REST
 * @author Étudiant 1 - Backend Database
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../includes/config/database.php';

class TournoiAPI {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtenir tous les tournois
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT t.*, 
                    (SELECT COUNT(*) FROM inscription_tournoi WHERE tournoi_id = t.id) as nombre_inscriptions
                    FROM tournoi t 
                    WHERE 1=1";
            $params = [];
            
            // Filtres
            if (!empty($filters['statut'])) {
                $sql .= " AND statut = ?";
                $params[] = $filters['statut'];
            }
            
            if (!empty($filters['type_tournoi'])) {
                $sql .= " AND type_tournoi = ?";
                $params[] = $filters['type_tournoi'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND nom LIKE ?";
                $params[] = '%' . $filters['search'] . '%';
            }
            
            $sql .= " ORDER BY date_debut DESC";
            
            // Pagination
            if (!empty($filters['limit'])) {
                $limit = (int) $filters['limit'];
                $offset = !empty($filters['offset']) ? (int) $filters['offset'] : 0;
                $sql .= " LIMIT $limit OFFSET $offset";
            }
            
            $tournois = $this->db->fetchAll($sql, $params);
            
            return $this->response(200, [
                'success' => true,
                'count' => count($tournois),
                'data' => $tournois
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la récupération des tournois'
            ]);
        }
    }
    
    /**
     * Obtenir un tournoi par ID avec détails
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM tournoi WHERE id = ?";
            $tournoi = $this->db->fetchOne($sql, [$id]);
            
            if (!$tournoi) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Tournoi introuvable'
                ]);
            }
            
            // Récupérer les équipes inscrites
            $sqlEquipes = "SELECT e.*, i.statut as statut_inscription, i.date_inscription,
                          i.victoires, i.nuls, i.defaites, i.points, i.buts_pour, i.buts_contre
                          FROM inscription_tournoi i
                          INNER JOIN equipe e ON i.equipe_id = e.id
                          WHERE i.tournoi_id = ?
                          ORDER BY i.points DESC, (i.buts_pour - i.buts_contre) DESC";
            
            $tournoi['equipes_inscrites'] = $this->db->fetchAll($sqlEquipes, [$id]);
            
            // Récupérer les matchs
            $sqlMatchs = "SELECT m.*, 
                         ed.nom as equipe_domicile_nom, 
                         ee.nom as equipe_exterieur_nom
                         FROM `match` m
                         LEFT JOIN equipe ed ON m.equipe_domicile_id = ed.id
                         LEFT JOIN equipe ee ON m.equipe_exterieur_id = ee.id
                         WHERE m.tournoi_id = ?
                         ORDER BY m.date_match";
            
            $tournoi['matchs'] = $this->db->fetchAll($sqlMatchs, [$id]);
            
            return $this->response(200, [
                'success' => true,
                'data' => $tournoi
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la récupération du tournoi'
            ]);
        }
    }
    
    /**
     * Créer un nouveau tournoi
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
            
            // Insertion
            $sql = "INSERT INTO tournoi (nom, description, date_debut, date_fin, type_tournoi, 
                    nombre_equipes, statut, avec_petite_finale) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['nom'],
                $data['description'] ?? null,
                $data['date_debut'] ?? null,
                $data['date_fin'] ?? null,
                $data['type_tournoi'] ?? 'elimination',
                $data['nombre_equipes'] ?? 16,
                $data['statut'] ?? 'configuration',
                $data['avec_petite_finale'] ?? false
            ];
            
            $id = $this->db->insert($sql, $params);
            
            return $this->response(201, [
                'success' => true,
                'message' => 'Tournoi créé avec succès',
                'data' => ['id' => $id]
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la création du tournoi'
            ]);
        }
    }
    
    /**
     * Mettre à jour un tournoi
     */
    public function update($id, $data) {
        try {
            // Vérifier si le tournoi existe
            $checkSql = "SELECT COUNT(*) FROM tournoi WHERE id = ?";
            $exists = $this->db->fetchColumn($checkSql, [$id]);
            
            if ($exists == 0) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Tournoi introuvable'
                ]);
            }
            
            // Construire la requête
            $fields = [];
            $params = [];
            
            $allowedFields = ['nom', 'description', 'date_debut', 'date_fin', 'type_tournoi', 
                             'nombre_equipes', 'statut', 'avec_petite_finale'];
            
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
            $sql = "UPDATE tournoi SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $this->db->execute($sql, $params);
            
            return $this->response(200, [
                'success' => true,
                'message' => 'Tournoi mis à jour avec succès'
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du tournoi'
            ]);
        }
    }
    
    /**
     * Supprimer un tournoi
     */
    public function delete($id) {
        try {
            // Vérifier si le tournoi existe
            $checkSql = "SELECT COUNT(*) FROM tournoi WHERE id = ?";
            $exists = $this->db->fetchColumn($checkSql, [$id]);
            
            if ($exists == 0) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Tournoi introuvable'
                ]);
            }
            
            // Suppression (CASCADE supprimera inscriptions et matchs)
            $sql = "DELETE FROM tournoi WHERE id = ?";
            $this->db->execute($sql, [$id]);
            
            return $this->response(200, [
                'success' => true,
                'message' => 'Tournoi supprimé avec succès'
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la suppression du tournoi'
            ]);
        }
    }
    
    /**
     * Inscrire une équipe à un tournoi
     */
    public function inscrireEquipe($tournoiId, $equipeId) {
        try {
            // Vérifier si le tournoi existe et accepte les inscriptions
            $tournoi = $this->db->fetchOne("SELECT * FROM tournoi WHERE id = ?", [$tournoiId]);
            if (!$tournoi) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Tournoi introuvable'
                ]);
            }
            
            if ($tournoi['statut'] !== 'inscription' && $tournoi['statut'] !== 'configuration') {
                return $this->response(400, [
                    'success' => false,
                    'message' => 'Le tournoi n\'accepte plus d\'inscriptions'
                ]);
            }
            
            // Vérifier si l'équipe existe
            $equipe = $this->db->fetchOne("SELECT * FROM equipe WHERE id = ?", [$equipeId]);
            if (!$equipe) {
                return $this->response(404, [
                    'success' => false,
                    'message' => 'Équipe introuvable'
                ]);
            }
            
            // Vérifier si déjà inscrite
            $checkSql = "SELECT COUNT(*) FROM inscription_tournoi WHERE tournoi_id = ? AND equipe_id = ?";
            $exists = $this->db->fetchColumn($checkSql, [$tournoiId, $equipeId]);
            
            if ($exists > 0) {
                return $this->response(409, [
                    'success' => false,
                    'message' => 'Équipe déjà inscrite à ce tournoi'
                ]);
            }
            
            // Vérifier le nombre max d'équipes
            $countSql = "SELECT COUNT(*) FROM inscription_tournoi WHERE tournoi_id = ?";
            $currentCount = $this->db->fetchColumn($countSql, [$tournoiId]);
            
            if ($currentCount >= $tournoi['nombre_equipes']) {
                return $this->response(400, [
                    'success' => false,
                    'message' => 'Nombre maximum d\'équipes atteint'
                ]);
            }
            
            // Inscription
            $sql = "INSERT INTO inscription_tournoi (tournoi_id, equipe_id, statut) VALUES (?, ?, 'en_attente')";
            $this->db->insert($sql, [$tournoiId, $equipeId]);
            
            return $this->response(201, [
                'success' => true,
                'message' => 'Équipe inscrite avec succès'
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de l\'inscription'
            ]);
        }
    }
    
    /**
     * Obtenir le classement d'un tournoi
     */
    public function getClassement($id) {
        try {
            $sql = "SELECT e.nom, e.logo_url, i.victoires, i.nuls, i.defaites, i.points, 
                    i.buts_pour, i.buts_contre, (i.buts_pour - i.buts_contre) as difference_buts
                    FROM inscription_tournoi i
                    INNER JOIN equipe e ON i.equipe_id = e.id
                    WHERE i.tournoi_id = ?
                    ORDER BY i.points DESC, difference_buts DESC, i.buts_pour DESC";
            
            $classement = $this->db->fetchAll($sql, [$id]);
            
            return $this->response(200, [
                'success' => true,
                'data' => $classement
            ]);
            
        } catch (Exception $e) {
            return $this->response(500, [
                'success' => false,
                'message' => 'Erreur lors de la récupération du classement'
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
        
        if (!empty($data['type_tournoi']) && !in_array($data['type_tournoi'], ['elimination', 'poules', 'mixte'])) {
            $errors['type_tournoi'] = 'Type de tournoi invalide';
        }
        
        if (!empty($data['nombre_equipes']) && (!is_numeric($data['nombre_equipes']) || $data['nombre_equipes'] < 2)) {
            $errors['nombre_equipes'] = 'Nombre d\'équipes invalide (minimum 2)';
        }
        
        return $errors;
    }
    
    /**
     * Réponse JSON
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
$api = new TournoiAPI();
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$path = parse_url($uri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Extraire l'ID et l'action
$id = null;
$action = null;

if (isset($pathParts[count($pathParts) - 1]) && is_numeric($pathParts[count($pathParts) - 1])) {
    $id = (int) $pathParts[count($pathParts) - 1];
}

if (isset($pathParts[count($pathParts) - 2]) && is_numeric($pathParts[count($pathParts) - 2])) {
    $id = (int) $pathParts[count($pathParts) - 2];
    $action = $pathParts[count($pathParts) - 1];
}

switch ($method) {
    case 'GET':
        if ($action === 'classement') {
            $api->getClassement($id);
        } elseif ($id) {
            $api->getById($id);
        } else {
            $filters = [
                'statut' => $_GET['statut'] ?? null,
                'type_tournoi' => $_GET['type_tournoi'] ?? null,
                'search' => $_GET['search'] ?? null,
                'limit' => $_GET['limit'] ?? null,
                'offset' => $_GET['offset'] ?? null
            ];
            $api->getAll($filters);
        }
        break;
        
    case 'POST':
        if ($action === 'inscrire') {
            $data = json_decode(file_get_contents('php://input'), true);
            $api->inscrireEquipe($id, $data['equipe_id']);
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            $api->create($data);
        }
        break;
        
    case 'PUT':
        if ($id) {
            $data = json_decode(file_get_contents('php://input'), true);
            $api->update($id, $data);
        }
        break;
        
    case 'DELETE':
        if ($id) {
            $api->delete($id);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}