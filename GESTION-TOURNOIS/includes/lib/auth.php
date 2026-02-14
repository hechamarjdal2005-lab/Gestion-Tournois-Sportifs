<?php
// includes/lib/auth.php - Système d'authentification

class Auth {
    private $db;
    private $maxAttempts = 5;
    private $lockoutTime = 900; // 15 minutes
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Inscription utilisateur
     */
    public function register($username, $email, $password, $nom_complet = '') {
        try {
            // Validation
            if (!isValidEmail($email)) {
                return ['success' => false, 'message' => 'Email invalide'];
            }
            
            if (strlen($password) < 8) {
                return ['success' => false, 'message' => 'Mot de passe trop court (min 8 caractères)'];
            }
            
            // Vérifier si email existe
            $exists = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM utilisateur WHERE email = ?", 
                [$email]
            );
            
            if ($exists > 0) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
            }
            
            // Vérifier si username existe
            $exists = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM utilisateur WHERE username = ?", 
                [$username]
            );
            
            if ($exists > 0) {
                return ['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà pris'];
            }
            
            // Hasher le mot de passe
            $passwordHash = hashPassword($password);
            
            // Insérer
            $sql = "INSERT INTO utilisateur (username, email, password_hash, nom_complet, role, est_actif) 
                    VALUES (?, ?, ?, ?, 'spectateur', 1)";
            
            $id = $this->db->insert($sql, [$username, $email, $passwordHash, $nom_complet]);
            
            logSecurity('REGISTER', "Nouvel utilisateur: $email");
            
            return ['success' => true, 'message' => 'Inscription réussie', 'user_id' => $id];
            
        } catch (Exception $e) {
            logSecurity('REGISTER_ERROR', $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        }
    }
    
    /**
     * Connexion utilisateur
     */
    public function login($email, $password) {
        try {
            $email = cleanInput($email);
            
            // Vérifier si compte bloqué
            $attempts = $this->checkLoginAttempts($email);
            
            if ($attempts['locked']) {
                return [
                    'success' => false, 
                    'message' => "Compte temporairement bloqué. Réessayez dans " . ceil($attempts['remaining'] / 60) . " minutes"
                ];
            }
            
            // Récupérer utilisateur
            $user = $this->db->fetchOne(
                "SELECT * FROM utilisateur WHERE email = ? AND est_actif = 1",
                [$email]
            );
            
            if (!$user || !verifyPassword($password, $user['password_hash'])) {
                $this->recordFailedAttempt($email);
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            }
            
            // Connexion réussie
            $this->clearFailedAttempts($email);
            
            // Créer session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            
            // Mettre à jour dernière connexion
            $this->db->execute(
                "UPDATE utilisateur SET derniere_connexion = NOW() WHERE id = ?",
                [$user['id']]
            );
            
            logSecurity('LOGIN', "Connexion réussie: $email");
            
            return ['success' => true, 'message' => 'Connexion réussie', 'user' => $user];
            
        } catch (Exception $e) {
            logSecurity('LOGIN_ERROR', $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la connexion'];
        }
    }
    
    /**
     * Vérifier les tentatives de connexion
     */
    private function checkLoginAttempts($email) {
        $sql = "SELECT attempt_count, last_attempt FROM login_attempts WHERE email = ?";
        $attempt = $this->db->fetchOne($sql, [$email]);
        
        if (!$attempt) {
            return ['locked' => false];
        }
        
        if ($attempt['attempt_count'] >= $this->maxAttempts) {
            $timeDiff = time() - strtotime($attempt['last_attempt']);
            if ($timeDiff < $this->lockoutTime) {
                return [
                    'locked' => true,
                    'remaining' => $this->lockoutTime - $timeDiff
                ];
            }
        }
        
        return ['locked' => false];
    }
    
    /**
     * Enregistrer tentative échouée
     */
    private function recordFailedAttempt($email) {
        $sql = "INSERT INTO login_attempts (email, attempt_count, last_attempt) 
                VALUES (?, 1, NOW()) 
                ON DUPLICATE KEY UPDATE 
                attempt_count = attempt_count + 1, 
                last_attempt = NOW()";
        
        $this->db->execute($sql, [$email]);
    }
    
    /**
     * Effacer tentatives échouées
     */
    private function clearFailedAttempts($email) {
        $this->db->execute("DELETE FROM login_attempts WHERE email = ?", [$email]);
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        logSecurity('LOGOUT', "Utilisateur: " . ($_SESSION['username'] ?? 'unknown'));
        session_destroy();
        return ['success' => true];
    }
    
    /**
     * Vérifier si utilisateur est connecté
     */
    public function checkSession() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_ip']) || !isset($_SESSION['user_agent'])) {
            return false;
        }
        
        // Vérifier vol de session
        if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || 
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->logout();
            return false;
        }
        
        // Vérifier timeout (8 heures)
        if (time() - $_SESSION['login_time'] > 28800) {
            $this->logout();
            return false;
        }
        
        return true;
    }
    
    /**
     * Vérifier rôle
     */
    public function hasRole($role) {
        if (!$this->checkSession()) return false;
        return $_SESSION['role'] === $role;
    }
    
    /**
     * Vérifier permission (au moins admin_tournoi)
     */
    public function isAdmin() {
        return $this->hasRole('super_admin') || $this->hasRole('admin_tournoi');
    }
}

// Créer table login_attempts si elle n'existe pas
$db = Database::getInstance();
$db->getConnection()->exec("
    CREATE TABLE IF NOT EXISTS login_attempts (
        email VARCHAR(100) PRIMARY KEY,
        attempt_count INT DEFAULT 0,
        last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");
?>