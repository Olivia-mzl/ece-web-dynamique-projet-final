<?php
/* ============================================ */
/*  OMNESEVENT - MENU DE NAVIGATION             */
/* ============================================ */

/*
   Ce fichier est inclus JUSTE APRÈS header.php.
   Il affiche un menu différent selon que l'utilisateur est :
   - non connecté
   - connecté comme participant
   - connecté comme organisateur
   - connecté comme admin

   Il affiche aussi un message "Bonjour Prénom Nom" si connecté,
   et les messages flash éventuels.
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

            <!-- Liens communs à tous (connectés ou non) -->
            <li><a href="index.php">Accueil</a></li>
            <li><a href="events.php">Événements</a></li>
            <li><a href="contact.php">Contact</a></li>

            <?php if (est_connecte()): ?>

                <!-- Liens spécifiques selon le rôle -->
                <?php if (a_le_role('admin')): ?>
                    <li><a href="admin-dashboard.php">Administration</a></li>

                <?php elseif (a_le_role('organisateur')): ?>
                    <li><a href="organizer-dashboard.php">Mon espace organisateur</a></li>

                <?php else: ?>
                    <!-- Participant -->
                    <li><a href="profile.php">Mon profil</a></li>
                <?php endif; ?>

                <!-- Lien de déconnexion (pour tous les connectés) -->
                <li><a href="logout.php">Déconnexion</a></li>

            <?php else: ?>

                <!-- Liens pour visiteurs non connectés -->
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>

            <?php endif; ?>

        </ul>
    </nav>
</header>

<?php if (est_connecte()): ?>
    <!-- Bandeau de bienvenue affiché en haut de chaque page -->
    <div class="bandeau-bienvenue">
        Bonjour
        <strong>
            <?php echo htmlspecialchars($_SESSION['prenom']); ?>
            <?php echo htmlspecialchars($_SESSION['nom']); ?>
        </strong>
        !
    </div>
<?php endif; ?>

<?php
// Affiche les messages flash éventuels (succès / erreur)
afficher_messages();
?>