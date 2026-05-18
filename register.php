<?php
$titre_page = "OmnesEvent - Inscription";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <section class="form-container">

        <h1>Inscription</h1>

        <form action="#" method="post">

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required>

            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>

            <label for="login">Login :</label>
            <input type="text" id="login" name="login" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>

            <label for="confirmation">Confirmer le mot de passe :</label>
            <input type="password" id="confirmation" name="confirmation" required>

            <fieldset>
                <legend>Type de compte :</legend>
                <label>
                    <input type="radio" name="role" value="participant" checked>
                    Participant (étudiant ou personnel)
                </label>
                <label>
                    <input type="radio" name="role" value="organisateur">
                    Organisateur (représentant d'association) - demande à valider
                </label>
            </fieldset>

            <button type="submit" class="btn-primary">Créer mon compte</button>

        </form>

        <p>Déjà inscrit ? <a href="login.php">Connecte-toi ici</a></p>

    </section>

</main>

<?php include "includes/footer.php"; ?>