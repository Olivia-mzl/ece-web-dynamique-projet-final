<?php
/* ============================================ */
/*  OMNESEVENT - PAGE D'ACCUEIL                 */
/* ============================================ */

// On définit le titre spécifique à cette page,
// AVANT d'inclure header.php (qui s'en sert).
$titre_page = "OmnesEvent - Accueil";

// On inclut l'en-tête commun (doctype, head, ouverture body)
include "includes/header.php";

// On inclut le menu de navigation
include "includes/menu.php";
?>

<!-- ===== CONTENU PRINCIPAL ===== -->
<main>

    <!-- Bannière d'accueil -->
    <section class="hero">
        <h1>Bienvenue sur OmnesEvent</h1>
        <p>La plateforme centralisée des événements étudiants d'Omnes.</p>
        <a href="events.php" class="btn-primary">Voir tous les événements</a>
    </section>

    <!-- Section événements à venir -->
    <section class="events-preview">
        <h2>Événements à venir</h2>

        <div class="events-grid">

            <!-- Carte événement 1 -->
            <article class="event-card">
                <img src="assets/images/placeholder.jpg" alt="Affiche soirée BDE">
                <h3>Soirée d'intégration BDE</h3>
                <p class="event-date">25 septembre 2026 - 20h00</p>
                <p class="event-place">Campus Lyon - Salle des fêtes</p>
                <p class="event-category">Catégorie : Soirée</p>
                <a href="event.php" class="btn-secondary">Voir détails</a>
            </article>

            <!-- Carte événement 2 -->
            <article class="event-card">
                <img src="assets/images/placeholder.jpg" alt="Affiche tournoi sport">
                <h3>Tournoi de futsal BDS</h3>
                <p class="event-date">2 octobre 2026 - 14h00</p>
                <p class="event-place">Gymnase central</p>
                <p class="event-category">Catégorie : Sport</p>
                <a href="event.php" class="btn-secondary">Voir détails</a>
            </article>

            <!-- Carte événement 3 -->
            <article class="event-card">
                <img src="assets/images/placeholder.jpg" alt="Affiche conférence">
                <h3>Conférence IA et Éthique</h3>
                <p class="event-date">15 octobre 2026 - 18h30</p>
                <p class="event-place">Amphi A1</p>
                <p class="event-category">Catégorie : Culture</p>
                <a href="event.php" class="btn-secondary">Voir détails</a>
            </article>

        </div>

        <p style="text-align: center; margin-top: 20px;">
            <a href="events.php">Voir tous les événements →</a>
        </p>
    </section>

</main>

<?php
// On inclut le pied de page commun (footer, scripts JS, fermeture body/html)
include "includes/footer.php";
?>