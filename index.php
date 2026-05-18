<?php
/* ============================================ */
/*  OMNESEVENT - PAGE D'ACCUEIL                 */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";


/* ============================================ */
/*  RÉCUPÉRATION DES PROCHAINS ÉVÉNEMENTS       */
/* ============================================ */

/*
   On récupère les 3 prochains événements publiés.
   - WHERE date_evenement >= CURDATE() : à venir uniquement
   - AND statut = 'publie' : pas les annulés / en attente / refusés
   - ORDER BY date_evenement ASC : du plus proche au plus lointain
   - LIMIT 3 : les 3 premiers seulement

   On fait des JOIN pour récupérer le nom de la catégorie et de l'association.
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
        c.nom AS categorie_nom,
        a.nom AS association_nom
    FROM events e
    LEFT JOIN categories c   ON e.id_categorie  = c.id
    LEFT JOIN associations a ON e.id_association = a.id
    WHERE e.date_evenement >= CURDATE()
      AND e.statut = 'publie'
    ORDER BY e.date_evenement ASC
    LIMIT 3
";

$requete = $bdd->query($sql);
$evenements = $requete->fetchAll();


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - Accueil";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <!-- Bannière d'accueil -->
    <section class="hero">
        <h1>Bienvenue sur OmnesEvent</h1>
        <p>La plateforme centralisée des événements étudiants d'Omnes.</p>
        <a href="events.php" class="btn-primary">Voir tous les événements</a>
    </section>

    <!-- Section événements à venir -->
    <section class="events-preview">
        <h2>Prochains événements</h2>

        <?php if (empty($evenements)): ?>

            <!-- Aucun événement à venir -->
            <p>Aucun événement à venir pour le moment.</p>

        <?php else: ?>

            <div class="events-grid">

                <?php foreach ($evenements as $event): ?>

                    <article class="event-card">

                        <!-- Image (ou placeholder si pas d'image) -->
                        <?php if (!empty($event['image'])): ?>
                            <img src="assets/uploads/<?php echo htmlspecialchars($event['image']); ?>"
                                 alt="Affiche <?php echo htmlspecialchars($event['titre']); ?>">
                        <?php else: ?>
                            <img src="assets/images/placeholder.jpg"
                                 alt="Affiche par défaut">
                        <?php endif; ?>

                        <h3><?php echo htmlspecialchars($event['titre']); ?></h3>

                        <p class="event-date">
                            <?php
                            // Formatage de la date en français
                            $date = new DateTime($event['date_evenement']);
                            echo $date->format('d/m/Y');
                            ?>
                            -
                            <?php
                            // Formatage de l'heure (HH:MM)
                            echo substr($event['heure_evenement'], 0, 5);
                            ?>
                        </p>

                        <p class="event-place">
                            <?php echo htmlspecialchars($event['lieu']); ?>
                        </p>

                        <p class="event-category">
                            Catégorie : <?php echo htmlspecialchars($event['categorie_nom'] ?? 'Non classée'); ?>
                        </p>

                        <a href="event.php?id=<?php echo $event['id']; ?>" class="btn-secondary">
                            Voir détails
                        </a>

                    </article>

                <?php endforeach; ?>

            </div>

            <p style="text-align: center; margin-top: 20px;">
                <a href="events.php">Voir tous les événements →</a>
            </p>

        <?php endif; ?>

    </section>

</main>

<?php include "includes/footer.php"; ?>