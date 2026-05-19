<?php
/* ============================================ */
/*  OMNESEVENT - VALIDER UN ORGANISATEUR        */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les admins peuvent valider
exiger_role('admin');


/* ============================================ */
/*  RÉCUPÉRATION ET VÉRIFICATION DE L'ID        */
/* ============================================ */

if (!isset($_GET['id']) || $_GET['id'] === '') {
    ajouter_message('erreur', "Aucun utilisateur spécifié.");
    rediriger("admin-dashboard.php");
}

$id_user = (int) $_GET['id'];

if ($id_user <= 0) {
    ajouter_message('erreur', "Identifiant invalide.");
    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  CHARGEMENT DE L'UTILISATEUR                 */
/* ============================================ */

$sql = "SELECT id, prenom, nom, role, statut FROM users WHERE id = ?";
$requete = $bdd->prepare($sql);
$requete->execute([$id_user]);
$utilisateur = $requete->fetch();

if (!$utilisateur) {
    ajouter_message('erreur', "Cet utilisateur n'existe pas.");
    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  VÉRIFICATIONS MÉTIER                        */
/* ============================================ */

// Règle 1 : doit être un organisateur
if ($utilisateur['role'] !== 'organisateur') {
    ajouter_message('erreur', "Cette action concerne uniquement les comptes organisateurs.");
    rediriger("admin-dashboard.php");
}

// Règle 2 : doit être en attente
if ($utilisateur['statut'] !== 'en_attente') {
    ajouter_message('erreur', "Ce compte n'est pas en attente de validation.");
    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  VALIDATION (UPDATE)                         */
/* ============================================ */

$sql_update = "UPDATE users SET statut = 'actif' WHERE id = ?";
$req_update = $bdd->prepare($sql_update);
$req_update->execute([$id_user]);

ajouter_message('succes',
    "Le compte de " . $utilisateur['prenom'] . " " . $utilisateur['nom'] . " a été validé.");

rediriger("admin-dashboard.php");
?>