<?php
/* ============================================ */
/*  OMNESEVENT - INSCRIPTION                    */
/* ============================================ */

// On charge la connexion à la base et les fonctions utilitaires
require_once "config/bdd.php";
require_once "includes/functions.php";

// Liste des erreurs qu'on va éventuellement collecter
$erreurs = [];

// Variables pour pré-remplir le formulaire en cas d'erreur
// (l'utilisateur ne devra pas tout retaper)
$prenom_saisi = "";
$nom_saisi    = "";
$email_saisi  = "";
$login_saisi  = "";
$role_saisi   = "participant"; // valeur par défaut


/* ============================================ */
/*  TRAITEMENT DU FORMULAIRE (méthode POST)     */
/* ============================================ */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // -------- 1. Récupération et nettoyage des données --------

    $prenom        = nettoyer($_POST['prenom']        ?? '');
    $nom           = nettoyer($_POST['nom']           ?? '');
    $email         = nettoyer($_POST['email']         ?? '');
    $login         = nettoyer($_POST['login']         ?? '');
    $mot_de_passe  = $_POST['mot_de_passe']           ?? '';  // pas de trim, on garde le mdp tel quel
    $confirmation  = $_POST['confirmation']           ?? '';
    $role          = nettoyer($_POST['role']          ?? 'participant');

    // On mémorise les valeurs saisies pour ré-afficher le formulaire si erreur
    $prenom_saisi = $prenom;
    $nom_saisi    = $nom;
    $email_saisi  = $email;
    $login_saisi  = $login;
    $role_saisi   = $role;


    // -------- 2. Validation des champs --------

    // Champs obligatoires non vides
    if ($prenom === "") {
        $erreurs[] = "Le prénom est obligatoire.";
    }
    if ($nom === "") {
        $erreurs[] = "Le nom est obligatoire.";
    }
    if ($email === "") {
        $erreurs[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var avec FILTER_VALIDATE_EMAIL = validation d'email
        $erreurs[] = "L'email n'est pas valide.";
    }
    if ($login === "") {
        $erreurs[] = "Le login est obligatoire.";
    } elseif (strlen($login) < 3) {
        $erreurs[] = "Le login doit faire au moins 3 caractères.";
    }
    if ($mot_de_passe === "") {
        $erreurs[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($mot_de_passe) < 6) {
        $erreurs[] = "Le mot de passe doit faire au moins 6 caractères.";
    }
    if ($mot_de_passe !== $confirmation) {
        $erreurs[] = "Les deux mots de passe ne correspondent pas.";
    }
    if ($role !== 'participant' && $role !== 'organisateur') {
        // Sécurité : on n'accepte QUE ces deux valeurs
        // (un malin pourrait modifier le HTML pour mettre 'admin')
        $erreurs[] = "Type de compte invalide.";
    }


    // -------- 3. Vérification d'unicité (email et login) --------

    // On ne fait ces vérifications que si les autres validations sont OK
    if (empty($erreurs)) {

        // On vérifie si l'email existe déjà
        $requete = $bdd->prepare("SELECT id FROM users WHERE email = ?");
        $requete->execute([$email]);
        if ($requete->fetch()) {
            $erreurs[] = "Cet email est déjà utilisé par un autre compte.";
        }

        // On vérifie si le login existe déjà
        $requete = $bdd->prepare("SELECT id FROM users WHERE login = ?");
        $requete->execute([$login]);
        if ($requete->fetch()) {
            $erreurs[] = "Ce login est déjà pris, choisis-en un autre.";
        }
    }


    // -------- 4. Insertion en base si tout est OK --------

    if (empty($erreurs)) {

        // Hachage du mot de passe avec bcrypt
        $hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

        // Détermination du statut :
        //  - Participant       => actif immédiatement
        //  - Organisateur      => en attente de validation par un admin
        $statut = ($role === 'organisateur') ? 'en_attente' : 'actif';

        // Requête préparée pour l'INSERT
        $sql = "INSERT INTO users (nom, prenom, email, login, mot_de_passe, role, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $requete = $bdd->prepare($sql);
        $requete->execute([$nom, $prenom, $email, $login, $hash, $role, $statut]);

        // Message de succès adapté selon le rôle
        if ($role === 'organisateur') {
            ajouter_message('succes',
                "Ton compte organisateur a été créé. Il doit être validé par un administrateur avant que tu puisses te connecter.");
        } else {
            ajouter_message('succes',
                "Ton compte a été créé avec succès. Tu peux maintenant te connecter.");
        }

        // Redirection vers la page de connexion
        rediriger("login.php");
    }
}


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - Inscription";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <section class="form-container">

        <h1>Inscription</h1>

        <?php
        /* Affichage des éventuelles erreurs de validation */
        if (!empty($erreurs)):
        ?>
            <div class="message message-erreur">
                <strong>Le formulaire contient des erreurs :</strong>
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo htmlspecialchars($erreur); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom"
                   value="<?php echo htmlspecialchars($prenom_saisi); ?>" required>

            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom"
                   value="<?php echo htmlspecialchars($nom_saisi); ?>" required>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email"
                   value="<?php echo htmlspecialchars($email_saisi); ?>" required>

            <label for="login">Login :</label>
            <input type="text" id="login" name="login"
                   value="<?php echo htmlspecialchars($login_saisi); ?>" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>

            <label for="confirmation">Confirmer le mot de passe :</label>
            <input type="password" id="confirmation" name="confirmation" required>

            <fieldset>
                <legend>Type de compte :</legend>
                <label>
                    <input type="radio" name="role" value="participant"
                           <?php echo ($role_saisi === 'participant') ? 'checked' : ''; ?>>
                    Participant (étudiant ou personnel)
                </label>
                <label>
                    <input type="radio" name="role" value="organisateur"
                           <?php echo ($role_saisi === 'organisateur') ? 'checked' : ''; ?>>
                    Organisateur (représentant d'association) - demande à valider
                </label>
            </fieldset>

            <button type="submit" class="btn-primary">Créer mon compte</button>

        </form>

        <p>Déjà inscrit ? <a href="login.php">Connecte-toi ici</a></p>

    </section>

</main>

<?php include "includes/footer.php"; ?>