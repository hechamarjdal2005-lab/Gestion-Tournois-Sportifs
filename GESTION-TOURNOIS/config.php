<?php
// config.php - Configuration centrale
require_once __DIR__ . '/includes/config/database.php';

define('APP_NAME', 'Gestion Tournois Sportifs');
define('APP_VERSION', '1.0');
define('BASE_URL', 'http://localhost/pfe/gestion-tournois/');
define('ENVIRONMENT', 'development'); // ou 'production'

// Session
session_name('TOURNOI_SESSION');
session_start();
date_default_timezone_set('Africa/Casablanca');

// Fonction de debug
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit;
}

// Redirection
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

// Fonctions globales
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}
?>