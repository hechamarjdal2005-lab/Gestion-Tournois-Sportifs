<?php
/**
 * ==========================================
 * DATABASE CONNECTION - PDO
 * ==========================================
 * Connexion sécurisée à la base de données
 * Protection contre SQL Injection
 * @author Étudiant 1 - Backend Database
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'tournoi_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;
    
    /**
     * Constructeur privé (Singleton Pattern)
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Erreur de connexion DB: " . $e->getMessage());
            die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
        }
    }
    
    /**
     * Obtenir l'instance unique de la classe (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtenir la connexion PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Prévenir le clonage
     */
    private function __clone() {}
    
    /**
     * Prévenir la désérialisation
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Exécuter une requête préparée sécurisée
     * @param string $sql Requête SQL avec placeholders
     * @param array $params Paramètres à binder
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erreur requête DB: " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }
    
    /**
     * Récupérer toutes les lignes
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer une seule ligne
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer une seule colonne
     */
    public function fetchColumn($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insertion sécurisée
     * @return int ID du dernier insert
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Update/Delete sécurisé
     * @return int Nombre de lignes affectées
     */
    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Démarrer une transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Annuler une transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Vérifier si la connexion est active
     */
    public function isConnected() {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Nettoyer et sécuriser une chaîne
     */
    public function sanitize($value) {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Fonction helper pour obtenir la connexion DB rapidement
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Fonction helper pour obtenir PDO directement
 */
function getPDO() {
    return Database::getInstance()->getConnection();
}