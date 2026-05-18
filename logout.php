<?php
/* OMNESEVENT - DÉCONNEXION */

require_once "includes/functions.php";

// On détruit toutes les variables de session
$_SESSION = [];

// On efface le cookie de session côté navigateur
// (en lui donnant une date d'expiration dans le passé)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// On détruit la session côté serveur
session_destroy();

// Message flash (qui sera perdu car la session est détruite,
// mais on va en recréer une pour ce message)
session_start();
ajouter_message('succes', "Tu as bien été déconnectée. À bientôt !");

// Redirection vers la page d'accueil
rediriger("index.php");
?>