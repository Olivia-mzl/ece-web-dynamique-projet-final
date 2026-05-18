<?php
/* ============================================ */
/*  OMNESEVENT - EN-TÊTE COMMUN (HTML head)     */
/* ============================================ */

/*
   Ce fichier est inclus AU DÉBUT de chaque page PHP.
   Il contient :
   - le doctype
   - la balise <html>
   - la balise <head> complète
   - l'ouverture de <body>

   La variable $titre_page doit être définie AVANT d'inclure ce fichier
   dans chaque page. Si elle ne l'est pas, on met un titre par défaut.
*/

// Si la page qui inclut ce fichier n'a pas défini de titre,
// on met une valeur par défaut.
if (!isset($titre_page)) {
    $titre_page = "OmnesEvent";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre_page; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>