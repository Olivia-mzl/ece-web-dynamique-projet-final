<?php
/* ============================================ */
/*  OMNESEVENT - MENU DE NAVIGATION             */
/* ============================================ */

/*
   Ce fichier est inclus JUSTE APRÈS header.php.
   Il contient l'en-tête visible avec logo et menu de navigation,
   ainsi que les messages flash éventuels.

   Plus tard, on y ajoutera :
   - la logique "lien actif" en PHP
   - l'affichage conditionnel selon le rôle de l'utilisateur
   - le message "Bonjour Prénom Nom" si connecté
*/

// On charge les fonctions utilitaires (qui démarrent aussi la session)
require_once __DIR__ . "/functions.php";
?>
<header>
    <div class="logo">
        <a href="index.php"><strong>OmnesEvent</strong></a>
    </div>
    <nav>
        <button id="menu-burger" aria-label="Ouvrir le menu">☰</button>
        <ul id="menu-principal">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="events.php">Événements</a></li>
            <li><a href="profile.php">Mon profil</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="login.php">Connexion</a></li>
            <li><a href="register.php">Inscription</a></li>
        </ul>
    </nav>
</header>

<?php
// Affiche les messages flash éventuels (succès / erreur)
afficher_messages();
?>