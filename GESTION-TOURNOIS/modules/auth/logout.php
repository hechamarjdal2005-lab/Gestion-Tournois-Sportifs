<?php
/**
 * LOGOUT - Déconnexion
 * Détruit la session et redirige vers la page d'accueil
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../includes/lib/auth.php';

$auth = new Auth();
$auth->logout();

// Redirection avec message
$_SESSION['flash_message'] = "Vous avez été déconnecté avec succès";
$_SESSION['flash_type'] = "success";

redirect('modules/auth/login.php');
?>