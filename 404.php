<?php
/* ============================================ */
/*  OMNESEVENT - PAGE 404                       */
/* ============================================ */

/*
   Cette page est affichée quand un utilisateur tente d'accéder
   à une URL qui n'existe pas sur le site.
   Le fichier .htaccess à la racine dit à Apache de servir ce fichier
   en cas d'erreur 404.
*/

require_once "includes/functions.php";

// On envoie le bon code HTTP au navigateur (important pour le SEO
// et les moteurs de recherche : ils sauront que la page n'existe pas)
http_response_code(404);

$titre_page = "OmnesEvent - Page introuvable";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <section style="text-align: center; padding: 3rem 1rem;">

        <h1 style="font-size: 4rem; margin-bottom: 0.5rem;">404</h1>

        <h2>Cette page n'existe pas</h2>

        <p style="margin: 1.5rem 0; color: var(--couleur-texte-doux);">
            Oups ! La page que tu cherches n'est pas (ou plus) sur OmnesEvent.
            Peut-être un lien cassé, ou une URL mal tapée.
        </p>

        <p>
            <a href="index.php" class="btn-primary">Retour à l'accueil</a>
            <a href="events.php" class="btn-secondary">Voir les événements</a>
        </p>

    </section>

</main>

<?php include "includes/footer.php"; ?>