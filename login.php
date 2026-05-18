<?php
$titre_page = "OmnesEvent - Connexion";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <section class="form-container">

        <h1>Connexion</h1>

        <form action="#" method="post">

            <label for="login">Login ou email :</label>
            <input type="text" id="login" name="login" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>

            <button type="submit" class="btn-primary">Se connecter</button>

        </form>

        <p>Pas encore de compte ? <a href="register.php">Inscris-toi ici</a></p>

    </section>

</main>

<?php include "includes/footer.php"; ?>