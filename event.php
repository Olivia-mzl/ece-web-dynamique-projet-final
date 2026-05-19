<?php
/* ============================================ */
/*  OMNESEVENT - DÉTAIL D'UN ÉVÉNEMENT          */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";


/* ============================================ */
/*  RÉCUPÉRATION ET SÉCURISATION DE L'ID        */
/* ============================================ */

/*
   L'ID vient de l'URL : event.php?id=X
   On vérifie qu'il est présent et valide.
*/

if (!isset($_GET['id']) || $_GET['id'] === '') {
    // Pas d'ID fourni : on redirige vers le catalogue
    ajouter_message('erreur', "Aucun événement spécifié.");
    rediriger("events.php");
}

// Cast en entier pour sécuriser
$id_event = (int) $_GET['id'];

// Si l'ID n'est pas un nombre valide (ex: ?id=abc devient 0)
if ($id_event <= 0) {
    ajouter_message('erreur', "Identifiant d'événement invalide.");
    rediriger("events.php");
}


/* ============================================ */
/*  RÉCUPÉRATION DE L'ÉVÉNEMENT EN BASE         */
/* ============================================ */

/*
   Requête préparée pour récupérer l'événement avec :
   - le nom de la catégorie
   - le nom de l'association
   - le prénom + nom de l'organisateur
*/

$sql = "
    SELECT
        e.id,
        e.titre,
        e.description,
        e.date_evenement,
        e.heure_evenement,
        e.lieu,
        e.image,
        e.capacite_max,
        e.statut,
        e.id_organisateur,
        c.nom AS categorie_nom,
        a.nom AS association_nom,
        u.prenom AS organisateur_prenom,
        u.nom AS organisateur_nom
    FROM events e
    LEFT JOIN categories c    ON e.id_categorie    = c.id
    LEFT JOIN associations a  ON e.id_association  = a.id
    JOIN users u              ON e.id_organisateur = u.id
    WHERE e.id = ?
";

$requete = $bdd->prepare($sql);
$requete->execute([$id_event]);
$event = $requete->fetch();

// L'événement n'existe pas
if (!$event) {
    ajouter_message('erreur', "Cet événement n'existe pas.");
    rediriger("events.php");
}


/* ============================================ */
/*  CALCUL DES PLACES RESTANTES                 */
/* ============================================ */

/*
   On compte combien de réservations actives ont déjà été faites.
   Les réservations annulées ne comptent pas.
*/

$sql_count = "
    SELECT COUNT(*) AS nb_reservations
    FROM reservations
    WHERE id_event = ?
      AND statut IN ('reserve', 'present')
";

$req_count = $bdd->prepare($sql_count);
$req_count->execute([$id_event]);
$resultat_count = $req_count->fetch();

$nb_reservations = (int) $resultat_count['nb_reservations'];
$places_restantes = $event['capacite_max'] - $nb_reservations;


/* ============================================ */
/*  DÉTERMINER SI L'ÉVÉNEMENT EST PASSÉ          */
/* ============================================ */

$date_event = new DateTime($event['date_evenement']);
$aujourd_hui = new DateTime();
$aujourd_hui->setTime(0, 0); // on compare à minuit, pas à l'heure actuelle

$event_passe = ($date_event < $aujourd_hui);


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - " . $event['titre'];
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <!-- Lien de retour -->
    <p><a href="events.php">← Retour aux événements</a></p>

    <article class="event-detail">

        <!-- Image -->
        <?php if (!empty($event['image'])): ?>
            <img src="assets/uploads/<?php echo htmlspecialchars($event['image']); ?>"
                 alt="Affiche <?php echo htmlspecialchars($event['titre']); ?>">
        <?php else: ?>
            <img src="assets/images/placeholder.jpg" alt="Affiche par défaut">
        <?php endif; ?>

        <h1><?php echo htmlspecialchars($event['titre']); ?></h1>

        <!-- Bandeau d'alerte si l'événement est annulé ou passé -->
        <?php if ($event['statut'] === 'annule'): ?>
            <div class="message message-erreur">
                <strong>⚠️ Cet événement a été annulé.</strong>
            </div>
        <?php elseif ($event_passe): ?>
            <div class="message message-erreur">
                <strong>ℹ️ Cet événement est passé.</strong>
            </div>
        <?php endif; ?>

        <ul class="event-info">
            <li>
                <strong>Date :</strong>
                <?php
                $date = new DateTime($event['date_evenement']);
                echo $date->format('d/m/Y');
                ?>
            </li>
            <li>
                <strong>Heure :</strong>
                <?php echo substr($event['heure_evenement'], 0, 5); ?>
            </li>
            <li>
                <strong>Lieu :</strong>
                <?php echo htmlspecialchars($event['lieu']); ?>
            </li>
            <li>
                <strong>Association :</strong>
                <?php echo htmlspecialchars($event['association_nom'] ?? 'Indépendant'); ?>
            </li>
            <li>
                <strong>Catégorie :</strong>
                <?php echo htmlspecialchars($event['categorie_nom'] ?? 'Non classée'); ?>
            </li>
            <li>
                <strong>Organisateur :</strong>
                <?php echo htmlspecialchars($event['organisateur_prenom']); ?>
                <?php echo htmlspecialchars($event['organisateur_nom']); ?>
            </li>
            <li>
                <strong>Places :</strong>
                <?php echo $nb_reservations; ?> / <?php echo $event['capacite_max']; ?> inscrits
                <?php if ($places_restantes > 0): ?>
                    (<strong><?php echo $places_restantes; ?></strong> place<?php echo $places_restantes > 1 ? 's' : ''; ?> restante<?php echo $places_restantes > 1 ? 's' : ''; ?>)
                <?php else: ?>
                    (<strong>complet</strong>)
                <?php endif; ?>
            </li>
        </ul>

        <h2>Description</h2>
        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>


        <!-- Bouton de réservation (logique selon le contexte) -->
        <?php
        // On affiche le bouton seulement si l'événement est publié, à venir, et avec des places
        $peut_reserver = ($event['statut'] === 'publie'
                       && !$event_passe
                       && $places_restantes > 0);
        ?>

        <?php if ($peut_reserver): ?>

            <?php if (!est_connecte()): ?>
                <p>
                    <a href="login.php" class="btn-primary">Se connecter pour réserver</a>
                </p>

            <?php elseif (a_le_role('participant')): ?>

                <?php
                // Vérifier si l'utilisateur a déjà une réservation active pour cet événement
                $sql_ma_resa = "SELECT id, statut FROM reservations
                                WHERE id_user = ? AND id_event = ?
                                  AND statut IN ('reserve', 'present')";
                $req_ma_resa = $bdd->prepare($sql_ma_resa);
                $req_ma_resa->execute([$_SESSION['id_user'], $event['id']]);
                $ma_reservation = $req_ma_resa->fetch();
                ?>

                <?php if ($ma_reservation): ?>
                    <!-- L'utilisateur a déjà réservé -->
                    <p>
                        <strong>✓ Tu as réservé une place pour cet événement.</strong>
                    </p>
                    <?php if ($ma_reservation['statut'] === 'reserve'): ?>
                        <p>
                            <a href="cancel-reservation.php?id=<?php echo $ma_reservation['id']; ?>" class="btn-danger">
                                Annuler ma réservation
                            </a>
                        </p>
                    <?php else: ?>
                        <p><em>Tu as été marqué présent à cet événement.</em></p>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- L'utilisateur n'a pas encore réservé -->
                    <p>
                        <a href="reserve.php?id=<?php echo $event['id']; ?>" class="btn-primary">
                            Réserver ma place
                        </a>
                    </p>
                <?php endif; ?>

            <?php else: ?>
                <p><em>Seuls les participants peuvent réserver une place.</em></p>
            <?php endif; ?>

        <?php elseif ($places_restantes <= 0 && !$event_passe): ?>
            <p><strong>Cet événement est complet, plus de place disponible.</strong></p>
        <?php endif; ?>

        <!-- ===== BOUTONS DE GESTION (propriétaire ou admin) ===== -->
        <?php
        $est_proprietaire = (est_connecte()
                          && (int) $event['id_organisateur'] === (int) $_SESSION['id_user']);
        $est_admin = a_le_role('admin');

        if ($est_proprietaire || $est_admin):
        ?>

            <hr style="margin: 1.5rem 0;">

            <p style="color: var(--couleur-texte-doux); font-size: 0.9rem;">
                <strong>Outils de gestion :</strong>
            </p>

            <p>
                <?php if (!$event_passe && $est_proprietaire): ?>
                    <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn-secondary">
                        Modifier
                    </a>
                <?php endif; ?>

                <a href="delete-event.php?id=<?php echo $event['id']; ?>" class="btn-danger">
                    Supprimer
                </a>
            </p>

        <?php endif; ?>
    </article>

</main>

<?php include "includes/footer.php"; ?>