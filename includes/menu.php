<?php
/* ============================================ */
/*  OMNESEVENT - MENU DE NAVIGATION             */
/* ============================================ */

/*
   Ce fichier est inclus JUSTE APRÈS header.php.
   Il contient l'en-tête visible avec logo et menu de navigation.

   Plus tard, on y ajoutera :
   - la logique "lien actif" en PHP (page courante mise en évidence)
   - l'affichage conditionnel selon le rôle de l'utilisateur
     (un participant ne voit pas le lien "Administration")
   - le message "Bonjour Prénom Nom" si connecté

   Pour l'instant, on a juste le menu statique, identique pour tous.
*/
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
