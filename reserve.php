<?php
/* ============================================ */
/*  OMNESEVENT - RÉSERVATION D'UN ÉVÉNEMENT     */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les utilisateurs connectés peuvent réserver
exiger_connexion();

// Et seuls les participants ont le droit de réserver
if (!a_le_role('participant')) {
    ajouter_message('erreur', "Seuls les participants peuvent réserver une place.");
    rediriger("index.php");
}


/* ============================================ */
/*  RÉCUPÉRATION ET VÉRIFICATION DE L'ID        */
/* ============================================ */

if (!isset($_GET['id']) || $_GET['id'] === '') {
    ajouter_message('erreur', "Aucun événement spécifié.");
    rediriger("events.php");
}

$id_event = (int) $_GET['id'];

if ($id_event <= 0) {
    ajouter_message('erreur', "Identifiant invalide.");
    rediriger("events.php");
}


/* ============================================ */
/*  CHARGEMENT DE L'ÉVÉNEMENT                   */
/* ============================================ */

$sql = "SELECT id, titre, date_evenement, capacite_max, statut
        FROM events
        WHERE id = ?";

$requete = $bdd->prepare($sql);
$requete->execute([$id_event]);
$event = $requete->fetch();

if (!$event) {
    ajouter_message('erreur', "Cet événement n'existe pas.");
    rediriger("events.php");
}


/* ============================================ */
/*  VÉRIFICATIONS MÉTIER                        */
/* ============================================ */

// Règle 1 : l'événement doit être publié
if ($event['statut'] !== 'publie') {
    ajouter_message('erreur', "Cet événement n'est pas disponible à la réservation.");
    rediriger("event.php?id=" . $id_event);
}

// Règle 2 : l'événement ne doit pas être passé
$date_event = new DateTime($event['date_evenement']);
$aujourd_hui = new DateTime();
$aujourd_hui->setTime(0, 0);

if ($date_event < $aujourd_hui) {
    ajouter_message('erreur', "Cet événement est déjà passé, plus de réservation possible.");
    rediriger("event.php?id=" . $id_event);
}

// Règle 3 : vérifier le nombre de places restantes
$sql_count = "SELECT COUNT(*) AS nb
              FROM reservations
              WHERE id_event = ?
                AND statut IN ('reserve', 'present')";

$req_count = $bdd->prepare($sql_count);
$req_count->execute([$id_event]);
$nb_reservations = (int) $req_count->fetch()['nb'];

if ($nb_reservations >= (int) $event['capacite_max']) {
    ajouter_message('erreur', "Cet événement est complet, plus de place disponible.");
    rediriger("event.php?id=" . $id_event);
}


/* ============================================ */
/*  CHERCHER UNE RÉSERVATION EXISTANTE          */
/* ============================================ */

/*
   Avant d'insérer, on regarde si l'utilisateur a déjà une réservation
   pour cet événement (peu importe le statut).

   - Si statut = 'reserve' ou 'present' : déjà réservé, refus.
   - Si statut = 'annule' : on REACTIVE la réservation (UPDATE).
   - Si aucune ligne : on INSÈRE normalement.
*/

$sql_check = "SELECT id, statut FROM reservations
              WHERE id_user = ? AND id_event = ?";

$req_check = $bdd->prepare($sql_check);
$req_check->execute([$_SESSION['id_user'], $id_event]);
$reservation_existante = $req_check->fetch();


if ($reservation_existante) {

    // Cas A : réservation déjà active
    if ($reservation_existante['statut'] === 'reserve'
        || $reservation_existante['statut'] === 'present') {

        ajouter_message('erreur', "Tu as déjà réservé une place pour cet événement.");
        rediriger("event.php?id=" . $id_event);
    }

    // Cas B : réservation annulée -> on la réactive
    if ($reservation_existante['statut'] === 'annule') {

        $sql_update = "UPDATE reservations
                       SET statut = 'reserve',
                           date_reservation = NOW()
                       WHERE id = ?
                         AND id_user = ?";

        $req_update = $bdd->prepare($sql_update);
        $req_update->execute([$reservation_existante['id'], $_SESSION['id_user']]);

        ajouter_message('succes',
            "Ta réservation pour \"" . $event['titre'] . "\" a été réactivée !");
        rediriger("profile.php");
    }
}


/* ============================================ */
/*  INSERTION D'UNE NOUVELLE RÉSERVATION        */
/* ============================================ */

// Si on arrive ici : aucune ligne existante -> on insère
try {
    $sql_insert = "INSERT INTO reservations (id_user, id_event, statut)
                   VALUES (?, ?, 'reserve')";

    $req_insert = $bdd->prepare($sql_insert);
    $req_insert->execute([$_SESSION['id_user'], $id_event]);

    ajouter_message('succes',
        "Ta réservation pour \"" . $event['titre'] . "\" a bien été enregistrée !");

    rediriger("profile.php");

} catch (PDOException $erreur) {
    // En cas d'erreur inattendue (race condition entre la vérification et l'INSERT)
    ajouter_message('erreur',
        "Une erreur est survenue lors de la réservation. Merci de réessayer.");
    rediriger("event.php?id=" . $id_event);
}
?>