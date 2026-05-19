<?php
/* ============================================ */
/*  OMNESEVENT - TABLEAU DE BORD ORGANISATEUR   */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les organisateurs peuvent accéder à cette page
exiger_role('organisateur');


/* ============================================ */
/*  RÉCUPÉRATION DES ÉVÉNEMENTS DE L'ORGANISATEUR */
/* ============================================ */

/*
   On récupère tous les événements créés par l'utilisateur connecté.
   Pour chaque événement, on calcule le nombre de réservations actives
   via une sous-requête.
*/

$sql = "
    SELECT
        e.id,
        e.titre,
        e.date_evenement,
        e.heure_evenement,
        e.lieu,
        e.capacite_max,
        e.statut,
        c.nom AS categorie_nom,
        a.nom AS association_nom,
        (SELECT COUNT(*)
         FROM reservations r
         WHERE r.id_event = e.id
           AND r.statut IN ('reserve', 'present')
        ) AS nb_inscrits
    FROM events e
    LEFT JOIN categories c    ON e.id_categorie    = c.id
    LEFT JOIN associations a  ON e.id_association  = a.id
    WHERE e.id_organisateur = ?
    ORDER BY e.date_evenement DESC
";

$requete = $bdd->prepare($sql);
$requete->execute([$_SESSION['id_user']]);
$evenements = $requete->fetchAll();


/* ============================================ */
/*  COMPTAGE RAPIDE POUR LES STATS              */
/* ============================================ */

/*
   On calcule quelques stats utiles pour le dashboard :
   - nombre total d'événements
   - nombre d'événements à venir
   - nombre total d'inscrits cumulé
*/

$nb_total = count($evenements);
$nb_a_venir = 0;
$nb_inscrits_total = 0;

$aujourd_hui = new DateTime();
$aujourd_hui->setTime(0, 0);

foreach ($evenements as $e) {
    $date_event = new DateTime($e['date_evenement']);
    if ($date_event >= $aujourd_hui && $e['statut'] === 'publie') {
        $nb_a_venir++;
    }
    $nb_inscrits_total += (int) $e['nb_inscrits'];
}


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - Tableau de bord organisateur";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Tableau de bord organisateur</h1>
    <p>
        Bonjour <strong><?php echo htmlspecialchars($_SESSION['prenom']); ?></strong>,
        bienvenue dans ton espace organisateur.
    </p>


    <!-- ===== STATISTIQUES RAPIDES ===== -->
    <section class="stats-row" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-number"><?php echo $nb_total; ?></div>
            <div class="stat-label">événement<?php echo $nb_total > 1 ? 's' : ''; ?> créé<?php echo $nb_total > 1 ? 's' : ''; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $nb_a_venir; ?></div>
            <div class="stat-label">à venir</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $nb_inscrits_total; ?></div>
            <div class="stat-label">inscrit<?php echo $nb_inscrits_total > 1 ? 's' : ''; ?> au total</div>
        </div>
    </section>


    <!-- ===== BOUTON CRÉATION ===== -->
    <section style="margin-bottom: 2rem;">
        <a href="create-event.php" class="btn-primary">+ Créer un nouvel événement</a>
    </section>


    <!-- ===== LISTE DES ÉVÉNEMENTS ===== -->
    <section>
        <h2>Mes événements</h2>

        <?php if (empty($evenements)): ?>

            <p>
                Tu n'as encore créé aucun événement.
                <a href="create-event.php">Crée ton premier événement</a> !
            </p>

        <?php else: ?>

            <?php foreach ($evenements as $event): ?>

                <?php
                // Déterminer si l'événement est passé
                $date_event = new DateTime($event['date_evenement']);
                $event_passe = ($date_event < $aujourd_hui);

                // Capacité atteinte ?
                $complet = ((int) $event['nb_inscrits'] >= (int) $event['capacite_max']);
                ?>

                <article class="event-card" style="margin-bottom: 1rem;">

                    <h3>
                        <a href="event.php?id=<?php echo $event['id']; ?>">
                            <?php echo htmlspecialchars($event['titre']); ?>
                        </a>
                    </h3>

                    <p class="event-date">
                        📅
                        <?php
                        $date = new DateTime($event['date_evenement']);
                        echo $date->format('d/m/Y');
                        ?>
                        - <?php echo substr($event['heure_evenement'], 0, 5); ?>
                    </p>

                    <p class="event-place">📍 <?php echo htmlspecialchars($event['lieu']); ?></p>

                    <p>
                        <strong>Statut :</strong>
                        <?php
                        // Affichage du statut avec un peu de couleur
                        $statut = $event['statut'];
                        if ($event_passe) {
                            echo '<span style="color: #6b7280;">Passé</span>';
                        } elseif ($statut === 'publie') {
                            echo '<span style="color: #1e7c3a;">Publié</span>';
                        } elseif ($statut === 'annule') {
                            echo '<span style="color: #c0392b;">Annulé</span>';
                        } elseif ($statut === 'en_attente') {
                            echo '<span style="color: #d97706;">En attente de validation</span>';
                        } elseif ($statut === 'refuse') {
                            echo '<span style="color: #c0392b;">Refusé</span>';
                        }
                        ?>
                    </p>

                    <p>
                        <strong>Inscrits :</strong>
                        <?php echo $event['nb_inscrits']; ?> / <?php echo $event['capacite_max']; ?>
                        <?php if ($complet && !$event_passe): ?>
                            <span style="color: #c0392b;"> (complet)</span>
                        <?php endif; ?>
                    </p>

                    <p>
                        <a href="event.php?id=<?php echo $event['id']; ?>" class="btn-secondary">
                            Voir la fiche
                        </a>

                        <?php if (!$event_passe): ?>
                            <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn-secondary">
                                Modifier
                            </a>
                        <?php endif; ?>

                        <a href="delete-event.php?id=<?php echo $event['id']; ?>" class="btn-danger">
                            Supprimer
                        </a>
                    </p>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</main>

<?php include "includes/footer.php"; ?>