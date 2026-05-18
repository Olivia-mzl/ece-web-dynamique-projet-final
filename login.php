<?php
/* OMNESEVENT - CONNEXION */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Si l'utilisateur est déjà connecté, on le renvoie vers l'accueil.
if (est_connecte()) {
    rediriger("index.php");
}

$erreurs = [];
$login_saisi = "";


/* TRAITEMENT DU FORMULAIRE */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Récupération des données
    $login        = nettoyer($_POST['login']        ?? '');
    $mot_de_passe = $_POST['mot_de_passe']          ?? '';

    $login_saisi = $login;

    // 2. Validation basique
    if ($login === "" || $mot_de_passe === "") {
        $erreurs[] = "Merci de remplir les deux champs.";
    }

    // 3. Recherche en base et vérification
    if (empty($erreurs)) {

        $sql = "SELECT id, nom, prenom, email, login, mot_de_passe, role, statut
                FROM users
                WHERE login = ? OR email = ?";

        $requete = $bdd->prepare($sql);
        $requete->execute([$login, $login]);
        $utilisateur = $requete->fetch();

        // Cas 1 : aucun utilisateur trouvé
        if (!$utilisateur) {
            $erreurs[] = "Login ou mot de passe incorrect.";
        }
        // Cas 2 : utilisateur trouvé, on vérifie le mot de passe
        elseif (!password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            $erreurs[] = "Login ou mot de passe incorrect.";
        }
        // Cas 3 : utilisateur valide, on vérifie son statut
        elseif ($utilisateur['statut'] === 'en_attente') {
            $erreurs[] = "Ton compte organisateur n'a pas encore ete valide par un administrateur.";
        }
        elseif ($utilisateur['statut'] === 'refuse') {
            $erreurs[] = "Ton compte a ete refuse par un administrateur.";
        }
        // Cas 4 : tout est OK, on cree la session
        else {
            $_SESSION['id_user'] = $utilisateur['id'];
            $_SESSION['login']   = $utilisateur['login'];
            $_SESSION['prenom']  = $utilisateur['prenom'];
            $_SESSION['nom']     = $utilisateur['nom'];
            $_SESSION['email']   = $utilisateur['email'];
            $_SESSION['role']    = $utilisateur['role'];

            ajouter_message('succes', "Bienvenue, " . $utilisateur['prenom'] . " !");

            if ($utilisateur['role'] === 'admin') {
                rediriger("admin-dashboard.php");
            } elseif ($utilisateur['role'] === 'organisateur') {
                rediriger("organizer-dashboard.php");
            } else {
                rediriger("profile.php");
            }
        }
    }
}


/* AFFICHAGE DE LA PAGE */

$titre_page = "OmnesEvent - Connexion";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <section class="form-container">

        <h1>Connexion</h1>

        <?php if (!empty($erreurs)): ?>
            <div class="message message-erreur">
                <?php foreach ($erreurs as $erreur): ?>
                    <p><?php echo htmlspecialchars($erreur); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">

            <label for="login">Login ou email :</label>
            <input type="text" id="login" name="login"
                   value="<?php echo htmlspecialchars($login_saisi); ?>" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>

            <button type="submit" class="btn-primary">Se connecter</button>

        </form>

        <p>Pas encore de compte ? <a href="register.php">Inscris-toi ici</a></p>

    </section>

</main>

<?php include "includes/footer.php"; ?>