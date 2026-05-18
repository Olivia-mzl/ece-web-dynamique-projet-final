<?php
/* ============================================ */
/*  OMNESEVENT - CATALOGUE DES ÉVÉNEMENTS       */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";


/* ============================================ */
/*  RÉCUPÉRATION DES FILTRES                    */
/* ============================================ */

/*
   Les filtres viennent en $_GET car le formulaire utilise method="get".
   On les nettoie et on les caste en entier quand c'est un ID.
   Si le filtre est vide, on met null pour le distinguer.
*/

$filtre_categorie   = isset($_GET['categorie'])   && $_GET['categorie']   !== '' ? (int) $_GET['categorie']   : null;
$filtre_association = isset($_GET['association']) && $_GET['association'] !== '' ? (int) $_GET['association'] : null;
$filtre_date        = isset($_GET['date'])        && $_GET['date']        !== '' ? nettoyer($_GET['date'])    : null;


/* ============================================ */
/*  CONSTRUCTION DE LA REQUÊTE DYNAMIQUE        */
/* ============================================ */

/*
   On part d'une requête de base, et on ajoute des conditions
   selon les filtres saisis. Les paramètres sont stockés dans
   un tableau séparé pour l'exécution préparée.
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
    WHERE e.statut = 'publie'
";

$parametres = [];

// Filtre catégorie
if ($filtre_categorie !== null) {
    $sql .= " AND e.id_categorie = ?";
    $parametres[] = $filtre_categorie;
}

// Filtre association
if ($filtre_association !== null) {
    $sql .= " AND e.id_association = ?";
    $parametres[] = $filtre_association;
}

// Filtre date : afficher les événements à partir de cette date
if ($filtre_date !== null) {
    $sql .= " AND e.date_evenement >= ?";
    $parametres[] = $filtre_date;
} else {
    // Sans filtre date, on n'affiche que les événements à venir
    $sql .= " AND e.date_evenement >= CURDATE()";
}

$sql .= " ORDER BY e.date_evenement ASC";

// Exécution de la requête préparée
$requete = $bdd->prepare($sql);
$requete->execute($parametres);
$evenements = $requete->fetchAll();


/* ============================================ */
/*  RÉCUPÉRATION DES OPTIONS DE FILTRE          */
/* ============================================ */

/*
   On lit toutes les catégories et associations pour les afficher
   dans les <select> du formulaire. Comme ça, les options sont
   automatiquement à jour si on en ajoute en base.
*/

$categories   = $bdd->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll();
$associations = $bdd->query("SELECT id, nom FROM associations ORDER BY nom")->fetchAll();


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - Événements";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Tous les événements</h1>

    <!-- ===== FILTRES DE RECHERCHE ===== -->
    <section class="filters">
        <form action="events.php" method="get">

            <label for="filtre-date">Date à partir de :</label>
            <input type="date" id="filtre-date" name="date"
                   value="<?php echo htmlspecialchars($filtre_date ?? ''); ?>">

            <label for="filtre-categorie">Catégorie :</label>
            <select id="filtre-categorie" name="categorie">
                <option value="">Toutes</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"
                            <?php echo ($filtre_categorie === (int) $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="filtre-association">Association :</label>
            <select id="filtre-association" name="association">
                <option value="">Toutes</option>
                <?php foreach ($associations as $asso): ?>
                    <option value="<?php echo $asso['id']; ?>"
                            <?php echo ($filtre_association === (int) $asso['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($asso['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filtrer</button>

            <?php if ($filtre_categorie || $filtre_association || $filtre_date): ?>
                <a href="events.php" class="btn-secondary">Réinitialiser</a>
            <?php endif; ?>

        </form>
    </section>


    <!-- ===== RÉSULTATS ===== -->
    <?php if (empty($evenements)): ?>

        <p style="text-align: center; padding: 2rem;">
            Aucun événement ne correspond à ta recherche.
        </p>

    <?php else: ?>

        <p style="margin-bottom: 1rem;">
            <strong><?php echo count($evenements); ?></strong>
            événement<?php echo count($evenements) > 1 ? 's' : ''; ?> trouvé<?php echo count($evenements) > 1 ? 's' : ''; ?>.
        </p>

        <section class="events-grid">

            <?php foreach ($evenements as $event): ?>

                <article class="event-card">

                    <?php if (!empty($event['image'])): ?>
                        <img src="assets/uploads/<?php echo htmlspecialchars($event['image']); ?>"
                             alt="Affiche <?php echo htmlspecialchars($event['titre']); ?>">
                    <?php else: ?>
                        <img src="assets/images/placeholder.jpg" alt="Affiche par défaut">
                    <?php endif; ?>

                    <h3><?php echo htmlspecialchars($event['titre']); ?></h3>

                    <p class="event-date">
                        <?php
                        $date = new DateTime($event['date_evenement']);
                        echo $date->format('d/m/Y');
                        ?>
                        - <?php echo substr($event['heure_evenement'], 0, 5); ?>
                    </p>

                    <p class="event-place"><?php echo htmlspecialchars($event['lieu']); ?></p>

                    <p class="event-category">
                        <?php echo htmlspecialchars($event['categorie_nom'] ?? 'Non classée'); ?>
                        -
                        <?php echo htmlspecialchars($event['association_nom'] ?? 'Indépendant'); ?>
                    </p>

                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn-secondary">
                        Voir détails
                    </a>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>

<?php include "includes/footer.php"; ?>