<?php
/* ============================================ */
/*  OMNESEVENT - REFUSER UN ORGANISATEUR        */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les admins peuvent refuser
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

$sql = "SELECT id, prenom, nom, email, role, statut FROM users WHERE id = ?";
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

// Doit être un organisateur
if ($utilisateur['role'] !== 'organisateur') {
    ajouter_message('erreur', "Cette action concerne uniquement les comptes organisateurs.");
    rediriger("admin-dashboard.php");
}

// Doit être en attente
if ($utilisateur['statut'] !== 'en_attente') {
    ajouter_message('erreur', "Ce compte n'est pas en attente de validation.");
    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  TRAITEMENT DU REFUS (POST)                  */
/* ============================================ */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sql_update = "UPDATE users SET statut = 'refuse' WHERE id = ?";
    $req_update = $bdd->prepare($sql_update);
    $req_update->execute([$id_user]);

    ajouter_message('succes',
        "Le compte de " . $utilisateur['prenom'] . " " . $utilisateur['nom'] . " a été refusé.");

    rediriger("admin-dashboard.php");
}


/* ============================================ */
/*  AFFICHAGE PAGE DE CONFIRMATION (GET)        */
/* ============================================ */

$titre_page = "OmnesEvent - Refuser un organisateur";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <section class="form-container" style="max-width: 600px;">

        <h1>Refuser ce compte organisateur ?</h1>

        <div class="message message-erreur">
            <strong>⚠️ Action sensible</strong>
            <p>
                Tu es sur le point de refuser le compte organisateur de
                <strong>
                    <?php echo htmlspecialchars($utilisateur['prenom']); ?>
                    <?php echo htmlspecialchars($utilisateur['nom']); ?>
                </strong>
                (<?php echo htmlspecialchars($utilisateur['email']); ?>).
            </p>
            <p>
                Cette personne ne pourra plus se connecter avec ce compte.
                Le compte restera en base mais marqué comme refusé.
            </p>
        </div>

        <form action="reject-orga.php?id=<?php echo $id_user; ?>" method="post">

            <p>
                <button type="submit" class="btn-danger">Oui, refuser ce compte</button>
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