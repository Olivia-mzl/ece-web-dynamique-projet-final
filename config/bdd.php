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
*/


/* ----- Paramètres de connexion (locaux WAMP) ----- */

$hote         = "localhost";       // serveur de la base (WAMP en local)
$nom_bdd      = "omnesevent";      // nom de la base de données
$utilisateur  = "root";            // utilisateur MySQL par défaut sous WAMP
$mot_de_passe = "";                // mot de passe vide par défaut sous WAMP
$charset      = "utf8mb4";         // encodage : utf8mb4 supporte tous les caractères


/* ----- Connexion PDO ----- */

try {
    // Création de la connexion
    $bdd = new PDO(
        "mysql:host=$hote;dbname=$nom_bdd;charset=$charset",
        $utilisateur,
        $mot_de_passe
    );

    // Options de PDO :
    // ERRMODE_EXCEPTION : on demande à PDO de lancer une exception
    // si une requête échoue, pour qu'on puisse l'attraper proprement.
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // FETCH_ASSOC : les résultats seront sous forme de tableau associatif
    // (avec les noms de colonnes comme clés), pas des tableaux numériques.
    $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $erreur) {
    // En cas d'erreur de connexion : on affiche un message et on arrête le script.
    // EN PRODUCTION : ne jamais afficher le message d'erreur réel
    // (révèle des infos sensibles). On le fait ici pour le dev.
    die("Erreur de connexion à la base de données : " . $erreur->getMessage());
}

?>