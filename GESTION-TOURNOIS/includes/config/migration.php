<?php
/**
 * ==========================================
 * MIGRATION SYSTEM
 * ==========================================
 * Système de migration pour gérer les versions de la DB
 * @author Étudiant 1 - Backend Database
 */

require_once __DIR__ . '/database.php';

class Migration {
    private $db;
    private $migrationsTable = 'migrations';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->createMigrationsTable();
    }
    
    /**
     * Créer la table des migrations si elle n'existe pas
     */
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            execution_time DECIMAL(10,3),
            status ENUM('success', 'failed') DEFAULT 'success'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log("Erreur création table migrations: " . $e->getMessage());
        }
    }
    
    /**
     * Exécuter le schema initial
     */
    public function runInitialSchema() {
        $schemaFile = __DIR__ . '/../../database/schema.sql';
        
        if (!file_exists($schemaFile)) {
            throw new Exception("Fichier schema.sql introuvable");
        }
        
        $sql = file_get_contents($schemaFile);
        
        // Vérifier si déjà exécuté
        if ($this->isMigrationExecuted('initial_schema')) {
            return ['status' => 'already_executed', 'message' => 'Schema déjà créé'];
        }
        
        try {
            $startTime = microtime(true);
            
            // Exécuter le schema
            $pdo = $this->db->getConnection();
            $pdo->exec($sql);
            
            $executionTime = microtime(true) - $startTime;
            
            // Enregistrer la migration
            $this->recordMigration('initial_schema', 'Création du schema de base', $executionTime);
            
            return [
                'status' => 'success',
                'message' => 'Schema créé avec succès',
                'execution_time' => round($executionTime, 3) . 's'
            ];
            
        } catch (Exception $e) {
            $this->recordMigration('initial_schema', 'Création du schema de base', 0, 'failed');
            throw new Exception("Erreur lors de la création du schema: " . $e->getMessage());
        }
    }
    
    /**
     * Exécuter les données de test
     */
    public function runTestData() {
        $dataFile = __DIR__ . '/../../database/data.sql';
        
        if (!file_exists($dataFile)) {
            throw new Exception("Fichier data.sql introuvable");
        }
        
        if ($this->isMigrationExecuted('test_data')) {
            return ['status' => 'already_executed', 'message' => 'Données test déjà insérées'];
        }
        
        $sql = file_get_contents($dataFile);
        
        try {
            $startTime = microtime(true);
            
            $pdo = $this->db->getConnection();
            $pdo->exec($sql);
            
            $executionTime = microtime(true) - $startTime;
            
            $this->recordMigration('test_data', 'Insertion des données de test', $executionTime);
            
            return [
                'status' => 'success',
                'message' => 'Données test insérées avec succès',
                'execution_time' => round($executionTime, 3) . 's'
            ];
            
        } catch (Exception $e) {
            $this->recordMigration('test_data', 'Insertion des données de test', 0, 'failed');
            throw new Exception("Erreur lors de l'insertion des données: " . $e->getMessage());
        }
    }
    
    /**
     * Vérifier si une migration a été exécutée
     */
    private function isMigrationExecuted($version) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->migrationsTable} WHERE version = ? AND status = 'success'";
            $count = $this->db->fetchColumn($sql, [$version]);
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Enregistrer une migration
     */
    private function recordMigration($version, $description, $executionTime, $status = 'success') {
        $sql = "INSERT INTO {$this->migrationsTable} (version, description, execution_time, status) 
                VALUES (?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [$version, $description, $executionTime, $status]);
        } catch (Exception $e) {
            error_log("Erreur enregistrement migration: " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir l'historique des migrations
     */
    public function getMigrationHistory() {
        $sql = "SELECT * FROM {$this->migrationsTable} ORDER BY executed_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Réinitialiser la base de données (DANGER!)
     */
    public function resetDatabase() {
        try {
            $pdo = $this->db->getConnection();
            
            // Désactiver les contraintes de clés étrangères
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Obtenir toutes les tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            // Supprimer toutes les tables
            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS `$table`");
            }
            
            // Réactiver les contraintes
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            return ['status' => 'success', 'message' => 'Base de données réinitialisée'];
            
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la réinitialisation: " . $e->getMessage());
        }
    }
    
    /**
     * Vérifier l'état de la base de données
     */
    public function checkDatabaseStatus() {
        try {
            $pdo = $this->db->getConnection();
            
            // Compter les tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $tableCount = count($tables);
            
            // Vérifier si les tables principales existent
            $requiredTables = ['equipe', 'tournoi', 'joueur', 'match', 'utilisateur'];
            $existingTables = array_intersect($requiredTables, $tables);
            
            $isComplete = count($existingTables) === count($requiredTables);
            
            return [
                'total_tables' => $tableCount,
                'required_tables' => count($requiredTables),
                'existing_tables' => count($existingTables),
                'is_complete' => $isComplete,
                'tables_list' => $tables
            ];
            
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}

/**
 * Fonction helper pour obtenir l'instance de Migration
 */
function getMigration() {
    return new Migration();
}