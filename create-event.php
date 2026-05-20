<?php
/* ============================================ */
/*  OMNESEVENT - CRÉATION D'UN ÉVÉNEMENT        */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

/*
   PROTECTION : seuls les organisateurs peuvent accéder à cette page.
   (Et les admins pourraient si on voulait, mais on a choisi de séparer
   strictement les rôles dans ce projet.)
*/
exiger_role('organisateur');


/* ============================================ */
/*  RÉCUPÉRATION DES OPTIONS DE SÉLECTION       */
/* ============================================ */

$categories   = $bdd->query("SELECT id, nom FROM categories ORDER BY nom")->fetchAll();
$associations = $bdd->query("SELECT id, nom FROM associations ORDER BY nom")->fetchAll();


/* ============================================ */
/*  TRAITEMENT DU FORMULAIRE                    */
/* ============================================ */

$erreurs = [];

// Variables pour pré-remplir le formulaire en cas d'erreur
$coordonnees_saisies = "";
$titre_saisi          = "";
$description_saisie   = "";
$date_saisie          = "";
$heure_saisie         = "";
$lieu_saisi           = "";
$capacite_saisie      = "50";  // valeur par défaut
$categorie_saisie     = "";
$association_saisie   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // -------- 1. Récupération des données --------

    $titre        = nettoyer($_POST['titre']        ?? '');
    $description  = nettoyer($_POST['description']  ?? '');
    $date_event   = nettoyer($_POST['date_event']   ?? '');
    $heure        = nettoyer($_POST['heure']        ?? '');
    $lieu         = nettoyer($_POST['lieu']         ?? '');
    $coordonnees  = nettoyer($_POST['coordonnees']  ?? '');
    $capacite     = (int) ($_POST['capacite']       ?? 0);
    $id_categorie = (int) ($_POST['id_categorie']   ?? 0);
    $id_assoc     = (int) ($_POST['id_association'] ?? 0);

    // Mémoriser pour pré-remplir
    $titre_saisi        = $titre;
    $description_saisie = $description;
    $date_saisie        = $date_event;
    $heure_saisie       = $heure;
    $lieu_saisi         = $lieu;
    $coordonnees_saisies = $coordonnees;
    $capacite_saisie    = $capacite;
    $categorie_saisie   = $id_categorie;
    $association_saisie = $id_assoc;


    // -------- 2. Validation des champs --------

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
    } else {
        // Vérifier que la date n'est pas dans le passé
        $date_choisie = new DateTime($date_event);
        $aujourd_hui  = new DateTime();
        $aujourd_hui->setTime(0, 0);

        if ($date_choisie < $aujourd_hui) {
            $erreurs[] = "La date doit être aujourd'hui ou dans le futur.";
        }
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
        $erreurs[] = "La capacité semble trop élevée (10 000 max).";
    }

    if ($id_categorie <= 0) {
        $erreurs[] = "Merci de choisir une catégorie.";
    }

    // L'association est optionnelle (un événement peut être indépendant)
    // mais si elle est saisie, on vérifie qu'elle existe
    if ($id_assoc > 0) {
        $req = $bdd->prepare("SELECT id FROM associations WHERE id = ?");
        $req->execute([$id_assoc]);
        if (!$req->fetch()) {
            $erreurs[] = "Association invalide.";
        }
    }

    // Vérifier que la catégorie existe vraiment
    if ($id_categorie > 0) {
        $req = $bdd->prepare("SELECT id FROM categories WHERE id = ?");
        $req->execute([$id_categorie]);
        if (!$req->fetch()) {
            $erreurs[] = "Catégorie invalide.";
        }
    }


    // -------- 3. Insertion en base si tout est OK --------
    // --------  Tentative d'upload de l'image --------

    $nom_image = null; // par défaut, pas d'image

    if (empty($erreurs)) {
    $resultat_upload = uploader_image('image', 'assets/uploads');

    if (!$resultat_upload['succes']) {
        $erreurs[] = $resultat_upload['erreur'];
    } else {
        $nom_image = $resultat_upload['nom_fichier'];
    }
    }

    if (empty($erreurs)) {

        // L'association peut être NULL (événement sans association)
        $id_assoc_final = ($id_assoc > 0) ? $id_assoc : null;

        $sql = "INSERT INTO events
                (titre, description, date_evenement, heure_evenement, lieu, coordonnees, image,
                 capacite_max, statut, id_organisateur, id_association, id_categorie)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'publie', ?, ?, ?)";

        $requete = $bdd->prepare($sql);
        $requete->execute([
            $titre,
            $description,
            $date_event,
            $heure,
            $lieu,
            $coordonnees,
            $nom_image,
            $capacite,
            $_SESSION['id_user'],
            $id_assoc_final,
            $id_categorie
        ]);

        // Récupérer l'ID du nouvel événement créé
        $nouvel_id = $bdd->lastInsertId();

        ajouter_message('succes', "Ton événement a été créé avec succès !");

        // Redirection vers la page de détail du nouvel événement
        rediriger("event.php?id=" . $nouvel_id);
    }
}


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - Créer un événement";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Créer un nouvel événement</h1>

    <p><a href="organizer-dashboard.php">← Retour au tableau de bord</a></p>

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

        <form action="create-event.php" method="post" enctype="multipart/form-data">

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

            <label for="image">Affiche (facultatif) :</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                <small style="display: block; color: #666; margin-bottom: 1rem;">Formats acceptés : JPG, PNG, WEBP. Taille max : 2 Mo.</small>

            <button type="submit" class="btn-primary">Créer l'événement</button>

        </form>

    </section>

</main>

<?php include "includes/footer.php"; ?>