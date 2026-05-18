<?php
/* ============================================ */
/*  OMNESEVENT - FONCTIONS UTILITAIRES          */
/* ============================================ */

/*
   Ce fichier contient des fonctions PHP réutilisables
   dans toute l'application :
   - gestion de session
   - nettoyage des données utilisateur
   - vérification du rôle
   - redirection

   Il est inclus via require_once dans les pages qui en ont besoin.
*/


/* ============================================ */
/*  1. DÉMARRAGE DE LA SESSION                  */
/* ============================================ */

/*
   On démarre la session UNE SEULE FOIS au début du fichier.
   La fonction session_status() vérifie si une session est déjà active,
   ce qui évite les erreurs "session already started" si plusieurs
   fichiers démarrent la session.
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ============================================ */
/*  2. NETTOYAGE DES DONNÉES REÇUES             */
/* ============================================ */

/*
   nettoyer($valeur) :
   Supprime les espaces inutiles autour d'une chaîne reçue
   d'un formulaire (via $_POST ou $_GET).

   IMPORTANT : cette fonction NE protège PAS contre les injections SQL.
   Pour ça, on utilisera les requêtes préparées PDO.
   Cette fonction sert juste à nettoyer (espaces, retours à la ligne).
*/
function nettoyer($valeur) {
    // Si la valeur n'existe pas, on renvoie une chaîne vide
    if (!isset($valeur)) {
        return "";
    }
    // trim() supprime les espaces, tabulations et retours à la ligne
    return trim($valeur);
}


/* ============================================ */
/*  3. VÉRIFICATION DE CONNEXION                */
/* ============================================ */

/*
   est_connecte() :
   Retourne true si un utilisateur est actuellement connecté.
   On vérifie la présence de $_SESSION['id_user'].
*/
function est_connecte() {
    return isset($_SESSION['id_user']);
}


/*
   a_le_role($role) :
   Retourne true si l'utilisateur connecté a le rôle demandé.
   Exemple : a_le_role('admin') retourne true si l'utilisateur est admin.
*/
function a_le_role($role) {
    return est_connecte() && $_SESSION['role'] === $role;
}


/* ============================================ */
/*  4. REDIRECTION                              */
/* ============================================ */

/*
   rediriger($url) :
   Envoie un en-tête HTTP qui redirige le navigateur vers une autre page.
   Le die() arrête le script tout de suite pour s'assurer
   qu'aucun autre code ne s'exécute après.
*/
function rediriger($url) {
    header("Location: $url");
    die(); // équivalent à exit() : arrête le script
}


/* ============================================ */
/*  5. PROTECTION DES PAGES                     */
/* ============================================ */

/*
   exiger_connexion() :
   À appeler en haut d'une page qui demande d'être connecté.
   Si l'utilisateur n'est pas connecté, il est redirigé vers login.php.
*/
function exiger_connexion() {
    if (!est_connecte()) {
        rediriger("login.php");
    }
}


/*
   exiger_role($role) :
   À appeler en haut d'une page réservée à un rôle particulier.
   Si l'utilisateur n'a pas le bon rôle, il est redirigé vers l'accueil.

   Exemple :
   exiger_role('admin');  -> seul un admin peut accéder à la page
*/
function exiger_role($role) {
    exiger_connexion(); // d'abord vérifier qu'on est connecté

    if (!a_le_role($role)) {
        rediriger("index.php"); // pas le bon rôle, on dégage
    }
}


/* ============================================ */
/*  6. MESSAGES FLASH (succès / erreur)         */
/* ============================================ */

/*
   Les "messages flash" sont des notifications affichées UNE SEULE FOIS
   après une action (ex : "Inscription réussie", "Mot de passe incorrect").
   On les stocke dans la session puis on les supprime après affichage.

   ajouter_message($type, $texte) :
   $type peut être 'succes' ou 'erreur'
   $texte est le contenu du message
*/
function ajouter_message($type, $texte) {
    // On stocke le message dans la session
    $_SESSION['messages'][] = [
        'type'  => $type,
        'texte' => $texte
    ];
}


/*
   afficher_messages() :
   Affiche tous les messages stockés puis les supprime.
   À appeler dans header.php ou menu.php pour qu'ils apparaissent
   en haut de chaque page.
*/
function afficher_messages() {
    // Si pas de message en attente, on ne fait rien
    if (empty($_SESSION['messages'])) {
        return;
    }

    // Sinon, on les affiche tous
    foreach ($_SESSION['messages'] as $message) {
        $type  = htmlspecialchars($message['type']);
        $texte = htmlspecialchars($message['texte']);

        echo '<div class="message message-' . $type . '">' . $texte . '</div>';
    }

    // On vide la liste pour ne pas afficher deux fois les messages
    $_SESSION['messages'] = [];
}

?>