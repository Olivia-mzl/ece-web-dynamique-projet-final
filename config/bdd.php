<?php
/* ============================================ */
/*  OMNESEVENT - CONFIGURATION BASE DE DONNÉES  */
/*  VERSION DE PRODUCTION (hébergement distant) */
/* ============================================ */

$hote         = 'fdb1031.your-hosting.net';
$nom_bdd      = '4761047_omnesevent';
$utilisateur  = '4761047_omnesevent';
$mot_de_passe = '9?TOFBIf8ld!22ey';
$charset      = 'utf8mb4';

try {
    $bdd = new PDO(
        "mysql:host=$hote;dbname=$nom_bdd;charset=$charset",
        $utilisateur,
        $mot_de_passe
    );

    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $erreur) {
    die("Erreur de connexion à la base de données : " . $erreur->getMessage());
}
?>