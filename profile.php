<?php
require_once "config/bdd.php";
require_once "includes/functions.php";

// PROTECTION : seuls les utilisateurs connectés peuvent voir cette page
exiger_connexion();

$titre_page = "OmnesEvent - Mon profil";
include "includes/header.php";
include "includes/menu.php";
$titre_page = "OmnesEvent - Mon profil";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Mon profil</h1>

    <!-- ===== INFOS PERSONNELLES ===== -->
    <section class="profile-info">
        <h2>Mes informations</h2>
        <ul>
            <li><strong>Prénom :</strong> Juju</li>
            <li><strong>Nom :</strong> Dupont</li>
            <li><strong>Email :</strong> juju.dupont@edu.ece.fr</li>
            <li><strong>Rôle :</strong> Participant</li>
        </ul>
        <a href="#" class="btn-secondary">Modifier mes infos</a>
    </section>

    <!-- ===== ÉVÉNEMENTS À VENIR ===== -->
    <section class="my-tickets">
        <h2>Mes billets - À venir</h2>

        <article class="event-card">
            <h3>Soirée d'intégration BDE</h3>
            <p class="event-date">25 septembre 2026 - 20h00</p>
            <p class="event-place">Campus Lyon - Salle des fêtes</p>
            <p class="event-status">Statut : Réservé</p>
            <a href="event.php" class="btn-secondary">Voir l'événement</a>
            <a href="#" class="btn-danger">Annuler ma réservation</a>
        </article>

        <article class="event-card">
            <h3>Conférence IA et Éthique</h3>
            <p class="event-date">15 octobre 2026 - 18h30</p>
            <p class="event-place">Amphi A1</p>
            <p class="event-status">Statut : Réservé</p>
            <a href="event.php" class="btn-secondary">Voir l'événement</a>
            <a href="#" class="btn-danger">Annuler ma réservation</a>
        </article>

    </section>

    <!-- ===== ÉVÉNEMENTS PASSÉS ===== -->
    <section class="past-events">
        <h2>Mes événements passés</h2>

        <article class="event-card">
            <h3>Tournoi de futsal - Rentrée 2025</h3>
            <p class="event-date">10 septembre 2025</p>
            <p class="event-status">Statut : Présent</p>
        </article>

        <article class="event-card">
            <h3>Welcome Day 2025</h3>
            <p class="event-date">5 septembre 2025</p>
            <p class="event-status">Statut : Présent</p>
        </article>

    </section>

    <!-- ===== DÉCONNEXION ===== -->
    <p><a href="logout.php" class="btn-danger">Se déconnecter</a></p>

</main>

<?php include "includes/footer.php"; ?>