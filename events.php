<?php
$titre_page = "OmnesEvent - Événements";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Tous les événements</h1>

    <!-- ===== FILTRES DE RECHERCHE ===== -->
    <section class="filters">
        <form>
            <label for="filtre-date">Date :</label>
            <input type="date" id="filtre-date" name="date">

            <label for="filtre-categorie">Catégorie :</label>
            <select id="filtre-categorie" name="categorie">
                <option value="">Toutes</option>
                <option value="soiree">Soirée</option>
                <option value="sport">Sport</option>
                <option value="culture">Culture</option>
                <option value="conference">Conférence</option>
            </select>

            <label for="filtre-association">Association :</label>
            <select id="filtre-association" name="association">
                <option value="">Toutes</option>
                <option value="bde">BDE</option>
                <option value="bds">BDS</option>
                <option value="je">Junior Entreprise</option>
            </select>

            <button type="submit">Filtrer</button>
        </form>
    </section>

    <!-- ===== LISTE DES ÉVÉNEMENTS ===== -->
    <section class="events-grid">

        <article class="event-card" data-categorie="soiree" data-association="bde" data-date="2026-09-25">
            <img src="assets/images/soiree-bde.jpg" alt="Affiche soirée BDE">
            <h3>Soirée d'intégration BDE</h3>
            <p class="event-date">25 septembre 2026 - 20h00</p>
            <p class="event-place">Campus Lyon - Salle des fêtes</p>
            <p class="event-category">Soirée - BDE</p>
            <a href="event.php" class="btn-secondary">Voir détails</a>
        </article>

        <article class="event-card" data-categorie="sport" data-association="bds" data-date="2026-10-02">
            <img src="assets/images/tournois-sport.png" alt="Affiche tournoi sport">
            <h3>Tournoi de futsal BDS</h3>
            <p class="event-date">2 octobre 2026 - 14h00</p>
            <p class="event-place">Gymnase central</p>
            <p class="event-category">Sport - BDS</p>
            <a href="event.php" class="btn-secondary">Voir détails</a>
        </article>

        <article class="event-card" data-categorie="culture" data-association="je" data-date="2026-10-15">
            <img src="assets/images/conference-IA.jpg" alt="Affiche conférence IA">
            <h3>Conférence IA et Éthique</h3>
            <p class="event-date">15 octobre 2026 - 18h30</p>
            <p class="event-place">Amphi A1</p>
            <p class="event-category">Culture - Junior Entreprise</p>
            <a href="event.php" class="btn-secondary">Voir détails</a>
        </article>

        <article class="event-card" data-categorie="conference" data-association="je" data-date="2026-10-22">
            <img src="assets/images/atelier-CV.jpg" alt="Affiche atelier">
            <h3>Atelier CV et entretiens</h3>
            <p class="event-date">22 octobre 2026 - 17h00</p>
            <p class="event-place">Salle 204</p>
            <p class="event-category">Conférence - Junior Entreprise</p>
            <a href="event.php" class="btn-secondary">Voir détails</a>
        </article>

        <article class="event-card" data-categorie="soiree" data-association="bde" data-date="2026-10-31">
            <img src="assets/images/soiree-Halloween.png" alt="Affiche soirée Halloween">
            <h3>Soirée Halloween</h3>
            <p class="event-date">31 octobre 2026 - 21h00</p>
            <p class="event-place">Le Sucre</p>
            <p class="event-category">Soirée - BDE</p>
            <a href="event.php" class="btn-secondary">Voir détails</a>
        </article>

        <article class="event-card" data-categorie="sport" data-association="bds" data-date="2026-11-05">
            <img src="assets/images/tournois-esport.jpg" alt="Affiche tournoi e-sport">
            <h3>Tournoi e-sport League of Legends</h3>
            <p class="event-date">5 novembre 2026 - 19h00</p>
            <p class="event-place">Salle gaming</p>
            <p class="event-category">Sport - BDS</p>
            <a href="event.php" class="btn-secondary">Voir détails</a>
        </article>

    </section>

</main>

<?php include "includes/footer.php"; ?>