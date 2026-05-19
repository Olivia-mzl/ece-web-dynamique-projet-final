<?php
/* ============================================ */
/*  OMNESEVENT - SUPPRIMER UN UTILISATEUR       */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les admins peuvent supprimer des utilisateurs
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
/*  PROTECTION : NE PAS SE SUPPRIMER SOI-MÊME   */
/* ============================================ */

if ((int) $id_user === (int) $_SESSION['id_user']) {
    ajouter_message('erreur', "Tu ne peux pas supprimer ton propre compte.");
    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  CHARGEMENT DE L'UTILISATEUR                 */
/* ============================================ */

$sql = "SELECT id, prenom, nom, email, login, role, statut FROM users WHERE id = ?";
$requete = $bdd->prepare($sql);
$requete->execute([$id_user]);
$utilisateur = $requete->fetch();

if (!$utilisateur) {
    ajouter_message('erreur', "Cet utilisateur n'existe pas.");
    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  PROTECTION : NE PAS SUPPRIMER UN ADMIN      */
/* ============================================ */

if ($utilisateur['role'] === 'admin') {
    ajouter_message('erreur', "Tu ne peux pas supprimer un autre administrateur.");
    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  CALCUL DE L'IMPACT                          */
/* ============================================ */

/*
   Avant de demander confirmation, on calcule combien de données
   vont être supprimées en cascade :
   - événements créés (si organisateur)
   - réservations actives (si participant)
*/

// Compter les événements créés par cet utilisateur
$req = $bdd->prepare("SELECT COUNT(*) FROM events WHERE id_organisateur = ?");
$req->execute([$id_user]);
$nb_events = (int) $req->fetchColumn();

// Compter ses réservations
$req2 = $bdd->prepare("SELECT COUNT(*) FROM reservations WHERE id_user = ?");
$req2->execute([$id_user]);
$nb_reservations = (int) $req2->fetchColumn();


/* ============================================ */
/*  TRAITEMENT DE LA SUPPRESSION (POST)         */
/* ============================================ */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // -------- 1. Récupérer les images des événements de l'utilisateur --------
    /*
       Avant de supprimer en base (qui va déclencher la cascade),
       on liste les images des événements pour les supprimer du disque.
    */

    $req_images = $bdd->prepare("SELECT image FROM events WHERE id_organisateur = ? AND image IS NOT NULL");
    $req_images->execute([$id_user]);
    $images_a_supprimer = $req_images->fetchAll();


    // -------- 2. Supprimer l'utilisateur (cascade automatique) --------

    $req_delete = $bdd->prepare("DELETE FROM users WHERE id = ?");
    $req_delete->execute([$id_user]);


    // -------- 3. Supprimer les fichiers images du disque --------

    foreach ($images_a_supprimer as $img) {
        $chemin = 'assets/uploads/' . $img['image'];
        if (file_exists($chemin)) {
            unlink($chemin);
        }
    }


    // -------- 4. Message + redirection --------

    ajouter_message('succes',
        "Le compte de " . $utilisateur['prenom'] . " " . $utilisateur['nom'] . " a été supprimé.");

    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  AFFICHAGE PAGE DE CONFIRMATION (GET)        */
/* ============================================ */

$titre_page = "OmnesEvent - Supprimer un utilisateur";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <section class="form-container" style="max-width: 600px;">

        <h1>Supprimer cet utilisateur ?</h1>

        <div class="message message-erreur">
            <strong>⚠️ Action irréversible</strong>
            <p>
                Tu es sur le point de supprimer définitivement le compte de
                <strong>
                    <?php echo htmlspecialchars($utilisateur['prenom']); ?>
                    <?php echo htmlspecialchars($utilisateur['nom']); ?>
                </strong>
                (<?php echo htmlspecialchars($utilisateur['email']); ?>).
            </p>

            <p><strong>Conséquences :</strong></p>
            <ul>
                <li>Le compte sera définitivement effacé de la base.</li>
                <?php if ($nb_events > 0): ?>
                    <li>
                        <strong><?php echo $nb_events; ?></strong>
                        événement<?php echo $nb_events > 1 ? 's' : ''; ?>
                        créé<?php echo $nb_events > 1 ? 's' : ''; ?>
                        par cet utilisateur sera<?php echo $nb_events > 1 ? 'ont' : ''; ?>
                        également supprimé<?php echo $nb_events > 1 ? 's' : ''; ?>.
                    </li>
                <?php endif; ?>
                <?php if ($nb_reservations > 0): ?>
                    <li>
                        <strong><?php echo $nb_reservations; ?></strong>
                        réservation<?php echo $nb_reservations > 1 ? 's' : ''; ?>
                        de cet utilisateur sera<?php echo $nb_reservations > 1 ? 'ont' : ''; ?>
                        supprimée<?php echo $nb_reservations > 1 ? 's' : ''; ?>.
                    </li>
                <?php endif; ?>
                <li>Cette action ne peut pas être annulée.</li>
            </ul>
        </div>

        <form action="delete-user.php?id=<?php echo $id_user; ?>" method="post">

            <p>
                <button type="submit" class="btn-danger">Oui, supprimer définitivement</button>
            </p>

        </form>

        <p>
            <a href="admin-dashboard.php" class="btn-secondary">
                Annuler et revenir au tableau de bord
            </a>
        </p>

    </section>

</main>

<?php include "includes/footer.php"; ?>