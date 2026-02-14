<?php
// functions.php - Fonctions de sécurité et utilitaires

/**
 * Nettoyer les inputs
 */
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Générer token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valider token CSRF
 */
function validateCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    if (time() - $_SESSION['csrf_token_time'] > 1800) { // 30 minutes
        unset($_SESSION['csrf_token']);
        return false;
    }
    return true;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Vérifier password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Vérifier email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Logger sécurité
 */
function logSecurity($action, $details = '') {
    $logFile = __DIR__ . '/logs/security.log';
    $date = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['user_id'] ?? 'guest';
    
    $message = "[$date] [$ip] [User:$user] [$action] $details\n";
    file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
}
?>