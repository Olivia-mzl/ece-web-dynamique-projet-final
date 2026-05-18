<?php
$titre_page = "OmnesEvent - Contact";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Contact</h1>

    <section>
        <h2>À propos d'OmnesEvent</h2>
        <p>
            OmnesEvent est une plateforme centralisée de billetterie et de gestion
            d'événements dédiée aux étudiants et au personnel d'Omnes. Elle regroupe
            les événements du BDE, du BDS, de la Junior Entreprise et de toutes les
            associations partenaires.
        </p>
    </section>

    <section class="form-container">
        <h2>Envoyer un message</h2>
        <form action="#" method="post">

            <label for="nom">Votre nom :</label>
            <input type="text" id="nom" name="nom" required>

            <label for="email">Votre email :</label>
            <input type="email" id="email" name="email" required>

            <label for="sujet">Sujet :</label>
            <input type="text" id="sujet" name="sujet" required>

            <label for="message">Message :</label>
            <textarea id="message" name="message" rows="6" required></textarea>

            <button type="submit" class="btn-primary">Envoyer</button>
        </form>
    </section>

</main>

<?php include "includes/footer.php"; ?>