<?php
/* ============================================ */
/*  OMNESEVENT - ANNULATION D'UNE RÉSERVATION   */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les participants peuvent annuler leurs réservations
exiger_connexion();

if (!a_le_role('participant')) {
    ajouter_message('erreur', "Action réservée aux participants.");
    rediriger("index.php");
}


/* ============================================ */
/*  RÉCUPÉRATION ET VÉRIFICATION DE L'ID        */
/* ============================================ */

/*
   ATTENTION : l'ID ici est celui de la RÉSERVATION,
   pas celui de l'événement.
   On passe par l'ID de réservation pour bien cibler
   la réservation de l'utilisateur connecté.
*/

if (!isset($_GET['id']) || $_GET['id'] === '') {
    ajouter_message('erreur', "Aucune réservation spécifiée.");
    rediriger("profile.php");
}

$id_reservation = (int) $_GET['id'];

if ($id_reservation <= 0) {
    ajouter_message('erreur', "Identifiant invalide.");
    rediriger("profile.php");
}


/* ============================================ */
/*  CHARGEMENT DE LA RÉSERVATION                */
/* ============================================ */

/*
   On récupère la réservation ET les infos de l'événement associé
   en une seule requête grâce à un JOIN.
*/

$sql = "
    SELECT
        r.id              AS id_reservation,
        r.id_user         AS id_user_reservation,
        r.id_event,
        r.statut          AS statut_reservation,
        e.titre           AS titre_event,
        e.date_evenement
    FROM reservations r
    JOIN events e ON r.id_event = e.id
    WHERE r.id = ?
";

$requete = $bdd->prepare($sql);
$requete->execute([$id_reservation]);
$reservation = $requete->fetch();

// La réservation existe ?
if (!$reservation) {
    ajouter_message('erreur', "Cette réservation n'existe pas.");
    rediriger("profile.php");
}


/* ============================================ */
/*  VÉRIFICATIONS MÉTIER                        */
/* ============================================ */

// Règle 1 : la réservation doit appartenir à l'utilisateur connecté
if ((int) $reservation['id_user_reservation'] !== (int) $_SESSION['id_user']) {
    ajouter_message('erreur', "Tu ne peux annuler que tes propres réservations.");
    rediriger("profile.php");
}

// Règle 2 : la réservation doit être encore active (pas déjà annulée)
if ($reservation['statut_reservation'] === 'annule') {
    ajouter_message('erreur', "Cette réservation est déjà annulée.");
    rediriger("profile.php");
}

// Règle 3 : on peut aussi vouloir empêcher d'annuler une présence validée
if ($reservation['statut_reservation'] === 'present') {
    ajouter_message('erreur', "Tu as déjà été marqué présent à cet événement, l'annulation n'est plus possible.");
    rediriger("profile.php");
}

// Règle 4 : l'événement ne doit pas être passé
$date_event = new DateTime($reservation['date_evenement']);
$aujourd_hui = new DateTime();
$aujourd_hui->setTime(0, 0);

if ($date_event < $aujourd_hui) {
    ajouter_message('erreur', "Cet événement est déjà passé, l'annulation n'est plus possible.");
    rediriger("profile.php");
}


/* ============================================ */
/*  ANNULATION (UPDATE)                         */
/* ============================================ */

/*
   On passe le statut à 'annule'.
   Double condition WHERE par sécurité (défense en profondeur) :
   - id = ? (la bonne réservation)
   - id_user = ? (le bon propriétaire)
*/

$sql_update = "UPDATE reservations
               SET statut = 'annule'
               WHERE id = ?
                 AND id_user = ?";

$req_update = $bdd->prepare($sql_update);
$req_update->execute([$id_reservation, $_SESSION['id_user']]);

ajouter_message('succes',
    "Ta réservation pour \"" . $reservation['titre_event'] . "\" a bien été annulée.");

rediriger("profile.php");
?>