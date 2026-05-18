<?php
/* ============================================ */
/*  OMNESEVENT - CONFIGURATION BASE DE DONNÉES  */
/* ============================================ */

/*
   Ce fichier établit la connexion à la base de données MySQL via PDO.
   Il est inclus (via require_once) dans toutes les pages PHP
   qui ont besoin d'accéder à la base.

   PDO (PHP Data Objects) est l'API recommandée en PHP moderne
   car elle permet d'utiliser des "requêtes préparées" qui protègent
   automatiquement contre les injections SQL.

   ATTENTION : pour l'instant, ce fichier ne FAIT RIEN.
   La base de données sera créée en Phase 5.
   On garde le code de connexion en commentaire jusque-là.
*/


/* ----- Paramètres de connexion (à activer en Phase 5) ----- */

// $hote = "localhost";              // serveur de la base (WAMP en local)
// $nom_bdd = "omnesevent";          // nom de la base de données
// $utilisateur = "root";            // utilisateur MySQL par défaut sous WAMP
// $mot_de_passe = "";               // mot de passe vide par défaut sous WAMP
// $charset = "utf8mb4";             // encodage : utf8mb4 supporte tous les caractères (accents, emojis...)


/* ----- Connexion PDO (à activer en Phase 5) ----- */

/*
try {
    // Création de la connexion
    $bdd = new PDO(
        "mysql:host=$hote;dbname=$nom_bdd;charset=$charset",
        $utilisateur,
        $mot_de_passe
    );

    // Options de PDO : on demande à PDO de lancer une exception
    // si une requête échoue, pour qu'on puisse l'attraper proprement.
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // On demande à PDO de retourner les résultats sous forme de tableau associatif.
    $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $erreur) {
    // En cas d'erreur de connexion : on affiche un message et on arrête le script.
    // En production réelle, on n'afficherait pas le message d'erreur technique.
    die("Erreur de connexion à la base de données : " . $erreur->getMessage());
}
*/


/* ----- Test temporaire (à supprimer en Phase 5) ----- */

// Cette ligne nous permet juste de vérifier que PHP s'exécute bien.
// On la supprimera quand la vraie connexion sera active.
$test_php = "PHP fonctionne correctement.";

?>