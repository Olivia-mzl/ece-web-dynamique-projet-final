<?php
/* ============================================ */
/*  OMNESEVENT - MODIFICATION D'UN ÉVÉNEMENT    */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les organisateurs peuvent modifier
exiger_role('organisateur');


/* ============================================ */
/*  RÉCUPÉRATION ET VÉRIFICATION DE L'ID        */
/* ============================================ */

if (!isset($_GET['id']) || $_GET['id'] === '') {
    ajouter_message('erreur', "Aucun événement spécifié.");
    rediriger("organizer-dashboard.php");
}

$id_event = (int) $_GET['id'];

if ($id_event <= 0) {
    ajouter_message('erreur', "Identifiant invalide.");
    rediriger("organizer-dashboard.php");
}


/* ============================================ */
/*  CHARGEMENT DE L'ÉVÉNEMENT EN BASE           */
/* ============================================ */

$sql = "SELECT * FROM events WHERE id = ?";
$requete = $bdd->prepare($sql);
$requete->execute([$id_event]);
$event = $requete->fetch();

// L'événement existe ?
if (!$event) {
    ajouter_message('erreur', "Cet événement n'existe pas.");
    rediriger("organizer-dashboard.php");
}


/* ============================================ */
/*  VÉRIFICATION DE PROPRIÉTÉ                   */
/* ============================================ */

/*
   L'organisateur connecté est-il bien le propriétaire de cet événement ?
   Sinon, on refuse l'accès (pas de modification possible des événements
   des autres organisateurs).
*/

if ((int) $event['id_organisateur'] !== (int) $_SESSION['id_user']) {
    ajouter_message('erreur', "Tu ne peux modifier que tes propres événements.");
    rediriger("organizer-dashboard.php");
}

/* ============================================ */
/*  VÉRIFICATION : ÉVÉNEMENT PAS ENCORE PASSÉ   */
/* ============================================ */

$date_event = new DateTime($event['date_evenement']);
$aujourd_hui = new DateTime();
$aujourd_hui->setTime(0, 0);

if ($date_event < $aujourd_hui) {
    ajouter_message('erreur', "On ne peut pas modifier un événement qui est déjà passé.");
    rediriger("organizer-dashboard.php");
}


/* ============================================ */
/*  RÉCUPÉRATION DES OPTIONS DE SÉLECTION       */
/* ============================================ */

$categories   = $bdd->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll();
$associations = $bdd->query("SELECT id, nom FROM associations ORDER BY nom")->fetchAll();


/* ============================================ */
/*  PRÉ-REMPLISSAGE DES CHAMPS                  */
/* ============================================ */

/*
   Au premier affichage (GET), on pré-remplit avec les valeurs actuelles.
   Si on soumet le formulaire (POST), ces variables seront écrasées plus bas.
*/

$titre_saisi        = $event['titre'];
$description_saisie = $event['description'];
$date_saisie        = $event['date_evenement'];
$heure_saisie       = substr($event['heure_evenement'], 0, 5); // format HH:MM
$lieu_saisi         = $event['lieu'];
$coordonnees_saisies = $event['coordonnees'] ?? '';
$capacite_saisie    = $event['capacite_max'];
$categorie_saisie   = (int) $event['id_categorie'];
$association_saisie = (int) $event['id_association'];

$erreurs = [];


/* ============================================ */
/*  TRAITEMENT DU FORMULAIRE (POST)             */
/* ============================================ */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // -------- 1. Récupération des nouvelles valeurs --------

    $titre        = nettoyer($_POST['titre']        ?? '');
    $description  = nettoyer($_POST['description']  ?? '');
    $date_event   = nettoyer($_POST['date_event']   ?? '');
    $heure        = nettoyer($_POST['heure']        ?? '');
    $lieu         = nettoyer($_POST['lieu']         ?? '');
    $coordonnees  = nettoyer($_POST['coordonnees']  ?? '');
    $capacite     = (int) ($_POST['capacite']       ?? 0);
    $id_categorie = (int) ($_POST['id_categorie']   ?? 0);
    $id_assoc     = (int) ($_POST['id_association'] ?? 0);

    // Mémoriser pour pré-remplir en cas d'erreur
    $titre_saisi        = $titre;
    $description_saisie = $description;
    $date_saisie        = $date_event;
    $heure_saisie       = $heure;
    $lieu_saisi         = $lieu;
    $coordonnees_saisies = $coordonnees;
    $capacite_saisie    = $capacite;
    $categorie_saisie   = $id_categorie;
    $association_saisie = $id_assoc;


    // -------- 2. Validation --------

    if ($titre === "") {
        $erreurs[] = "Le titre est obligatoire.";
    } elseif (strlen($titre) > 150) {
        $erreurs[] = "Le titre est trop long (150 caractères max).";
    }

    if ($description === "") {
        $erreurs[] = "La description est obligatoire.";
    }

    if ($date_event === "") {
        $erreurs[] = "La date est obligatoire.";
    }

    if ($heure === "") {
        $erreurs[] = "L'heure est obligatoire.";
    }

    if ($lieu === "") {
        $erreurs[] = "Le lieu est obligatoire.";
    } elseif (strlen($lieu) > 150) {
        $erreurs[] = "Le lieu est trop long (150 caractères max).";
    }

    if ($capacite <= 0) {
        $erreurs[] = "La capacité doit être supérieure à zéro.";
    } elseif ($capacite > 10000) {
        $erreurs[] = "La capacité semble trop élevée.";
    }

    if ($id_categorie <= 0) {
        $erreurs[] = "Merci de choisir une catégorie.";
    }

    // Vérifier l'existence de la catégorie
    if ($id_categorie > 0) {
        $req = $bdd->prepare("SELECT id FROM categories WHERE id = ?");
        $req->execute([$id_categorie]);
        if (!$req->fetch()) {
            $erreurs[] = "Catégorie invalide.";
        }
    }

    // Vérifier l'existence de l'association (si choisie)
    if ($id_assoc > 0) {
        $req = $bdd->prepare("SELECT id FROM associations WHERE id = ?");
        $req->execute([$id_assoc]);
        if (!$req->fetch()) {
            $erreurs[] = "Association invalide.";
        }
    }


    // -------- 3. Gestion de la nouvelle image (facultative) --------

    /*
       Si une nouvelle image est uploadée, on l'enregistre.
       Si non, on garde l'ancienne (déjà en base).
       Si on remplace, on supprime l'ancienne du disque pour économiser de la place.
    */

    $nom_image_a_garder = $event['image']; // par défaut, on garde l'ancienne

    if (empty($erreurs)) {
        $resultat_upload = uploader_image('image', 'assets/uploads');

        if (!$resultat_upload['succes']) {
            $erreurs[] = $resultat_upload['erreur'];
        } elseif ($resultat_upload['nom_fichier'] !== null) {
            // Une nouvelle image a été uploadée
            $nom_image_a_garder = $resultat_upload['nom_fichier'];

            // Supprimer l'ancienne image du disque (si elle existait)
            if (!empty($event['image'])) {
                $ancien_chemin = 'assets/uploads/' . $event['image'];
                if (file_exists($ancien_chemin)) {
                    unlink($ancien_chemin);
                }
            }
        }
    }


    // -------- 4. UPDATE en base si tout est OK --------

    if (empty($erreurs)) {

        $id_assoc_final = ($id_assoc > 0) ? $id_assoc : null;

        $sql = "UPDATE events
                SET titre = ?,
                    description = ?,
                    date_evenement = ?,
                    heure_evenement = ?,
                    lieu = ?,
                    coordonnees = ?,
                    image = ?,
                    capacite_max = ?,
                    id_association = ?,
                    id_categorie = ?
                WHERE id = ?
                  AND id_organisateur = ?";

        /*
           Notez les 2 conditions WHERE :
           - id = ? : c'est l'événement qu'on veut modifier
           - id_organisateur = ? : SÉCURITÉ supplémentaire (double protection)

           Même si on a déjà vérifié la propriété plus haut, on ré-applique
           la condition au niveau de la requête SQL. Si quelqu'un manipule
           l'id en POST, le UPDATE ne touchera rien.
        */

        $requete = $bdd->prepare($sql);
        $requete->execute([
            $titre,
            $description,
            $date_event,
            $heure,
            $lieu,
            $coordonnees,
            $nom_image_a_garder,
            $capacite,
            $id_assoc_final,
            $id_categorie,
            $id_event,
            $_SESSION['id_user']
        ]);

        ajouter_message('succes', "L'événement a été modifié avec succès !");
        rediriger("event.php?id=" . $id_event);
    }
}


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - Modifier un événement";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Modifier l'événement</h1>

    <p>
        <a href="event.php?id=<?php echo $id_event; ?>">← Voir la page de l'événement</a>
        |
        <a href="organizer-dashboard.php">Retour au tableau de bord</a>
    </p>

    <section class="form-container" style="max-width: 700px;">

        <?php if (!empty($erreurs)): ?>
            <div class="message message-erreur">
                <strong>Le formulaire contient des erreurs :</strong>
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo htmlspecialchars($erreur); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="edit-event.php?id=<?php echo $id_event; ?>" method="post" enctype="multipart/form-data">

            <label for="titre">Titre de l'événement :</label>
            <input type="text" id="titre" name="titre" maxlength="150" required
                   value="<?php echo htmlspecialchars($titre_saisi); ?>">

            <label for="description">Description :</label>
            <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($description_saisie); ?></textarea>

            <label for="date_event">Date :</label>
            <input type="date" id="date_event" name="date_event" required
                   value="<?php echo htmlspecialchars($date_saisie); ?>">

            <label for="heure">Heure :</label>
            <input type="time" id="heure" name="heure" required
                   value="<?php echo htmlspecialchars($heure_saisie); ?>">

            <label for="lieu">Lieu :</label>
            <input type="text" id="lieu" name="lieu" maxlength="150" required
                   value="<?php echo htmlspecialchars($lieu_saisi); ?>">

            <label for="coordonnees">Coordonnées GPS (facultatif) :</label>
            <input type="text" id="coordonnees" name="coordonnees" placeholder="Ex: 45.7640,4.8357"
                   value="<?php echo htmlspecialchars($coordonnees_saisies); ?>">
            <small style="display: block; color: #666; margin-bottom: 1rem;"> Format : latitude,longitude. Récupère-les sur Google Maps (clic droit sur le lieu → copier).</small>

            <label for="capacite">Capacité maximale :</label>
            <input type="number" id="capacite" name="capacite" min="1" max="10000" required
                   value="<?php echo htmlspecialchars($capacite_saisie); ?>">

            <label for="id_categorie">Catégorie :</label>
            <select id="id_categorie" name="id_categorie" required>
                <option value="">-- Choisir une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"
                            <?php echo ($categorie_saisie === (int) $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_association">Association (facultatif) :</label>
            <select id="id_association" name="id_association">
                <option value="">-- Indépendant --</option>
                <?php foreach ($associations as $asso): ?>
                    <option value="<?php echo $asso['id']; ?>"
                            <?php echo ($association_saisie === (int) $asso['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($asso['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Aperçu de l'image actuelle -->
            <?php if (!empty($event['image'])): ?>
                <label>Affiche actuelle :</label>
                <img src="assets/uploads/<?php echo htmlspecialchars($event['image']); ?>"
                     alt="Affiche actuelle"
                     style="max-width: 250px; border-radius: var(--rayon); margin-bottom: 1rem;">
            <?php endif; ?>

            <label for="image">
                <?php echo !empty($event['image']) ? "Remplacer l'affiche (facultatif) :" : "Ajouter une affiche (facultatif) :"; ?>
            </label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
            <small style="display: block; color: #666; margin-bottom: 1rem;">
                Formats acceptés : JPG, PNG, WEBP. Taille max : 2 Mo. Laisser vide pour garder l'affiche actuelle.
            </small>

            <button type="submit" class="btn-primary">Enregistrer les modifications</button>

        </form>

    </section>

</main>

<?php include "includes/footer.php"; ?>