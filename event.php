<?php
$titre_page = "OmnesEvent - Détail événement";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <!-- Lien de retour -->
    <p><a href="events.php">← Retour aux événements</a></p>

    <!-- ===== DÉTAIL ÉVÉNEMENT ===== -->
    <article class="event-detail">

        <img src="assets/images/soiree-bde.jpg" alt="Affiche soirée d'intégration BDE">

        <h1>Soirée d'intégration BDE</h1>

        <ul class="event-info">
            <li><strong>Date :</strong> 25 septembre 2026</li>
            <li><strong>Heure :</strong> 20h00</li>
            <li><strong>Lieu :</strong> Campus Lyon - Salle des fêtes</li>
            <li><strong>Association :</strong> BDE</li>
            <li><strong>Catégorie :</strong> Soirée</li>
            <li><strong>Capacité :</strong> 45 / 100 inscrits</li>
        </ul>

        <h2>Description</h2>
        <p>
            Viens fêter le début de l'année avec le BDE ! Une soirée pour rencontrer
            les nouveaux et anciens étudiants, dans une ambiance conviviale.
            Boissons et snacks offerts. DJ sur place.
        </p>

        <!-- Bouton de réservation (non fonctionnel pour l'instant) -->
        <a href="login.php" class="btn-primary">Réserver ma place</a>

    </article>

</main>

<?php include "includes/footer.php"; ?>