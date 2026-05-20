<?php
/* ============================================ */
/*  OMNESEVENT - MON PROFIL                     */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Tout utilisateur connecté peut accéder à son profil
exiger_connexion();


/* ============================================ */
/*  RÉCUPÉRATION DES RÉSERVATIONS               */
/* ============================================ */

/*
   On récupère TOUTES les réservations de l'utilisateur connecté,
   avec les infos de l'événement associé (via JOIN).
   On trie par date d'événement.
*/

$sql = "
    SELECT
        r.id              AS id_reservation,
        r.statut          AS statut_reservation,
        r.date_reservation,
        e.id              AS id_event,
        e.titre,
        e.date_evenement,
        e.heure_evenement,
        e.lieu,
        e.image,
        e.statut          AS statut_event,
        c.nom             AS categorie_nom,
        a.nom             AS association_nom
    FROM reservations r
    JOIN events e             ON r.id_event       = e.id
    LEFT JOIN categories c    ON e.id_categorie    = c.id
    LEFT JOIN associations a  ON e.id_association  = a.id
    WHERE r.id_user = ?
    ORDER BY e.date_evenement DESC
";

$requete = $bdd->prepare($sql);
$requete->execute([$_SESSION['id_user']]);
$toutes_reservations = $requete->fetchAll();


/* ============================================ */
/*  CLASSEMENT DES RÉSERVATIONS                 */
/* ============================================ */

/*
   On répartit les réservations en 3 catégories :
   - actives à venir   : statut 'reserve'/'present' ET date >= aujourd'hui
   - passées           : événement déjà passé
   - annulées          : statut 'annule' (peu importe la date)
*/

$billets_a_venir = [];
$billets_passes  = [];
$billets_annules = [];

$aujourd_hui = new DateTime();
$aujourd_hui->setTime(0, 0);

foreach ($toutes_reservations as $r) {
    $date_event = new DateTime($r['date_evenement']);
    $event_passe = ($date_event < $aujourd_hui);

    if ($r['statut_reservation'] === 'annule') {
        $billets_annules[] = $r;
    } elseif ($event_passe) {
        $billets_passes[] = $r;
    } else {
        $billets_a_venir[] = $r;
    }
}


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - Mon profil";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Mon profil</h1>


    <!-- ===== STATISTIQUES ===== -->
    <section class="stats-row" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($billets_a_venir); ?></div>
            <div class="stat-label">
                billet<?php echo count($billets_a_venir) > 1 ? 's' : ''; ?> à venir
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($billets_passes); ?></div>
            <div class="stat-label">
                événement<?php echo count($billets_passes) > 1 ? 's' : ''; ?> passé<?php echo count($billets_passes) > 1 ? 's' : ''; ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($billets_annules); ?></div>
            <div class="stat-label">
                annulation<?php echo count($billets_annules) > 1 ? 's' : ''; ?>
            </div>
        </div>
    </section>


    <!-- ===== INFORMATIONS PERSONNELLES ===== -->
    <section class="profile-info">
        <h2>Mes informations</h2>
        <ul>
            <li><strong>Prénom :</strong> <?php echo htmlspecialchars($_SESSION['prenom']); ?></li>
            <li><strong>Nom :</strong> <?php echo htmlspecialchars($_SESSION['nom']); ?></li>
            <li><strong>Email :</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></li>
            <li><strong>Login :</strong> <?php echo htmlspecialchars($_SESSION['login']); ?></li>
            <li><strong>Rôle :</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></li>
        </ul>
    </section>


    <!-- ===== BILLETS À VENIR ===== -->
    <section class="my-tickets">
        <h2>Mes billets à venir</h2>

        <?php if (empty($billets_a_venir)): ?>

            <p>
                Tu n'as aucun billet en cours.
                <a href="events.php">Découvrir les événements à venir</a> !
            </p>

        <?php else: ?>

            <?php foreach ($billets_a_venir as $billet): ?>

                <article class="event-card" style="margin-bottom: 1rem;">

                    <h3>
                        <a href="event.php?id=<?php echo $billet['id_event']; ?>">
                            <?php echo htmlspecialchars($billet['titre']); ?>
                        </a>
                    </h3>

                    <p class="event-date">
                        📅
                        <?php
                        $date = new DateTime($billet['date_evenement']);
                        echo $date->format('d/m/Y');
                        ?>
                        - <?php echo substr($billet['heure_evenement'], 0, 5); ?>
                    </p>

                    <p class="event-place">📍 <?php echo htmlspecialchars($billet['lieu']); ?></p>

                    <p>
                        <?php echo htmlspecialchars($billet['categorie_nom'] ?? 'Non classée'); ?>
                        -
                        <?php echo htmlspecialchars($billet['association_nom'] ?? 'Indépendant'); ?>
                    </p>

                    <p>
                        <strong>Statut :</strong>
                        <?php if ($billet['statut_reservation'] === 'present'): ?>
                            <span style="color: #1e7c3a;">✓ Présence validée</span>
                        <?php else: ?>
                            <span style="color: #0f5b6e;">Réservé</span>
                        <?php endif; ?>
                    </p>

                    <div class="qr-section" style="display: flex; align-items: center; gap: 1rem; margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: var(--rayon);">
    <div id="qr-<?php echo $billet['id_reservation']; ?>"></div>
    <div>
        <strong>🎟️ Ton billet</strong>
        <p style="margin: 0.3rem 0 0 0; color: #555; font-size: 0.9rem;">
            Présente ce QR code à l'organisateur le jour J pour valider ta présence.
        </p>
    </div>
</div>
<script>
    new QRCode(document.getElementById("qr-<?php echo $billet['id_reservation']; ?>"), {
        text: "OMNESEVENT-RES-<?php echo $billet['id_reservation']; ?>-USER-<?php echo $_SESSION['id_user']; ?>-EVENT-<?php echo $billet['id_event']; ?>",
        width: 120,
        height: 120,
        colorDark : "#0f5b6e",
        colorLight : "#ffffff"
    });
</script>

                    <p>
                        <a href="event.php?id=<?php echo $billet['id_event']; ?>" class="btn-secondary">
                            Voir l'événement
                        </a>

                        <?php if ($billet['statut_reservation'] === 'reserve'): ?>
                            <a href="cancel-reservation.php?id=<?php echo $billet['id_reservation']; ?>" class="btn-danger">
                                Annuler ma réservation
                            </a>
                        <?php endif; ?>
                    </p>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>


    <!-- ===== ÉVÉNEMENTS PASSÉS ===== -->
    <section class="past-events">
        <h2>Mes événements passés</h2>

        <?php if (empty($billets_passes)): ?>

            <p>Aucun événement passé pour le moment.</p>

        <?php else: ?>

            <?php foreach ($billets_passes as $billet): ?>

                <article class="event-card" style="margin-bottom: 1rem; opacity: 0.85;">

                    <h3>
                        <a href="event.php?id=<?php echo $billet['id_event']; ?>">
                            <?php echo htmlspecialchars($billet['titre']); ?>
                        </a>
                    </h3>

                    <p class="event-date">
                        📅
                        <?php
                        $date = new DateTime($billet['date_evenement']);
                        echo $date->format('d/m/Y');
                        ?>
                    </p>

                    <p>
                        <strong>Statut :</strong>
                        <?php if ($billet['statut_reservation'] === 'present'): ?>
                            <span style="color: #1e7c3a;">✓ Tu étais présent</span>
                        <?php else: ?>
                            <span style="color: #6b7280;">Réservé (présence non confirmée)</span>
                        <?php endif; ?>
                    </p>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>


    <!-- ===== ANNULATIONS (historique) ===== -->
    <?php if (!empty($billets_annules)): ?>

        <section class="cancelled-tickets">
            <h2>Mes annulations</h2>

            <?php foreach ($billets_annules as $billet): ?>

                <article class="event-card" style="margin-bottom: 1rem; opacity: 0.7;">

                    <h3 style="text-decoration: line-through;">
                        <?php echo htmlspecialchars($billet['titre']); ?>
                    </h3>

                    <p class="event-date">
                        📅
                        <?php
                        $date = new DateTime($billet['date_evenement']);
                        echo $date->format('d/m/Y');
                        ?>
                    </p>

                    <p style="color: #c0392b;"><em>Réservation annulée</em></p>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>


    <!-- ===== DÉCONNEXION ===== -->
    <p style="margin-top: 2rem;">
        <a href="logout.php" class="btn-danger">Se déconnecter</a>
    </p>

</main>

<?php include "includes/footer.php"; ?>