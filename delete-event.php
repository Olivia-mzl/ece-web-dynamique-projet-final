<?php
/* ============================================ */
/*  OMNESEVENT - SUPPRESSION D'UN ÉVÉNEMENT     */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les utilisateurs connectés peuvent accéder ici
// (la vérification fine du rôle/propriété se fait juste après)
exiger_connexion();


/* ============================================ */
/*  RÉCUPÉRATION ET VÉRIFICATION DE L'ID        */
/* ============================================ */

if (!isset($_GET['id']) || $_GET['id'] === '') {
    ajouter_message('erreur', "Aucun événement spécifié.");
    rediriger("index.php");
}

$id_event = (int) $_GET['id'];

if ($id_event <= 0) {
    ajouter_message('erreur', "Identifiant invalide.");
    rediriger("index.php");
}


/* ============================================ */
/*  CHARGEMENT DE L'ÉVÉNEMENT                   */
/* ============================================ */

$sql = "SELECT id, titre, image, id_organisateur FROM events WHERE id = ?";
$requete = $bdd->prepare($sql);
$requete->execute([$id_event]);
$event = $requete->fetch();

if (!$event) {
    ajouter_message('erreur', "Cet événement n'existe pas.");
    rediriger("index.php");
}


/* ============================================ */
/*  VÉRIFICATION DES DROITS                     */
/* ============================================ */

/*
   L'utilisateur peut supprimer s'il est :
   - le propriétaire (organisateur qui a créé l'événement)
   - OU un admin
*/

$est_proprietaire = ((int) $event['id_organisateur'] === (int) $_SESSION['id_user']);
$est_admin        = a_le_role('admin');

if (!$est_proprietaire && !$est_admin) {
    ajouter_message('erreur', "Tu n'as pas le droit de supprimer cet événement.");
    rediriger("index.php");
}


/* ============================================ */
/*  TRAITEMENT DE LA SUPPRESSION (POST)         */
/* ============================================ */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // -------- 1. Supprimer le fichier image (si existant) --------

    if (!empty($event['image'])) {
        $chemin_image = 'assets/uploads/' . $event['image'];
        if (file_exists($chemin_image)) {
            unlink($chemin_image);
        }
    }


    // -------- 2. Supprimer l'événement en base --------

    /*
       Grâce aux ON DELETE CASCADE de la table reservations,
       les réservations liées à cet événement seront automatiquement
       supprimées par MySQL. On n'a rien à faire pour ça.
    */

    $sql = "DELETE FROM events WHERE id = ?";
    $requete = $bdd->prepare($sql);
    $requete->execute([$id_event]);

    ajouter_message('succes', "L'événement \"" . $event['titre'] . "\" a été supprimé.");

    // Redirection selon le rôle
    if ($est_admin) {
        rediriger("admin-dashboard.php");
    } else {
        rediriger("organizer-dashboard.php");
    }
}


/* ============================================ */
/*  AFFICHAGE DE LA PAGE DE CONFIRMATION (GET)  */
/* ============================================ */

$titre_page = "OmnesEvent - Supprimer un événement";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <section class="form-container" style="max-width: 600px;">

        <h1>Supprimer cet événement ?</h1>

        <div class="message message-erreur">
            <strong>⚠️ Action irréversible</strong>
            <p>
                Tu es sur le point de supprimer définitivement l'événement
                <strong>« <?php echo htmlspecialchars($event['titre']); ?> »</strong>.
            </p>
            <p>
                Toutes les réservations liées à cet événement seront également supprimées.
                Cette action ne peut pas être annulée.
            </p>
        </div>

        <form action="delete-event.php?id=<?php echo $id_event; ?>" method="post">

            <p>
                <button type="submit" class="btn-danger">Oui, supprimer définitivement</button>
            </p>

        </form>

        <p>
            <a href="event.php?id=<?php echo $id_event; ?>" class="btn-secondary">
                Annuler et revenir à l'événement
            </a>
        </p>

    </section>

</main>

<?php include "includes/footer.php"; ?>