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

/* ============================================ */
/*  7. UPLOAD D'IMAGE                           */
/* ============================================ */

/*
   uploader_image($champ_fichier, $dossier_destination) :
   Gère l'upload sécurisé d'une image envoyée via un formulaire.

   - $champ_fichier : nom du champ dans le formulaire (ex: 'image')
   - $dossier_destination : chemin où sauvegarder (ex: 'assets/uploads')

   RETOUR :
   - tableau ['succes' => true,  'nom_fichier' => 'xxx.jpg']  si OK
   - tableau ['succes' => false, 'erreur' => '...']           si problème
   - tableau ['succes' => true,  'nom_fichier' => null]       si pas de fichier envoyé (cas normal)
*/
function uploader_image($champ_fichier, $dossier_destination) {

    // Cas 1 : pas de fichier envoyé (champ vide) -> ce n'est pas une erreur
    if (!isset($_FILES[$champ_fichier]) || $_FILES[$champ_fichier]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['succes' => true, 'nom_fichier' => null];
    }

    $fichier = $_FILES[$champ_fichier];

    // Cas 2 : erreur d'upload PHP (taille trop grande, etc.)
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        return ['succes' => false, 'erreur' => "Erreur lors de l'upload (code " . $fichier['error'] . ")."];
    }

    // Cas 3 : vérifier que c'est bien un fichier uploadé via HTTP
    if (!is_uploaded_file($fichier['tmp_name'])) {
        return ['succes' => false, 'erreur' => "Fichier suspect, upload refusé."];
    }

    // Cas 4 : vérifier la taille (2 Mo max)
    $taille_max = 2 * 1024 * 1024; // 2 Mo en octets
    if ($fichier['size'] > $taille_max) {
        return ['succes' => false, 'erreur' => "Le fichier est trop lourd (2 Mo maximum)."];
    }

    // Cas 5 : vérifier l'extension
    $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $extensions_autorisees)) {
        return ['succes' => false, 'erreur' => "Format d'image non autorisé (jpg, jpeg, png, webp uniquement)."];
    }

    // Cas 6 : vérifier le VRAI type MIME du fichier
    // (l'extension peut être trompeuse, on lit les premiers octets du fichier)
    $types_mime_autorises = ['image/jpeg', 'image/png', 'image/webp'];
    $type_mime = mime_content_type($fichier['tmp_name']);

    if (!in_array($type_mime, $types_mime_autorises)) {
        return ['succes' => false, 'erreur' => "Le fichier ne semble pas être une image valide."];
    }

    // Cas 7 : générer un nom de fichier unique pour éviter les collisions
    // Format : timestamp_chaineAleatoire.extension
    $nom_unique = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

    // S'assurer que le dossier existe
    if (!is_dir($dossier_destination)) {
        mkdir($dossier_destination, 0755, true);
    }

    // Déplacer le fichier depuis le dossier temporaire vers le dossier final
    $chemin_final = $dossier_destination . '/' . $nom_unique;
    if (!move_uploaded_file($fichier['tmp_name'], $chemin_final)) {
        return ['succes' => false, 'erreur' => "Impossible de sauvegarder le fichier."];
    }

    // Tout est OK : on retourne le nom du fichier
    return ['succes' => true, 'nom_fichier' => $nom_unique];
}

?>