/* ============================================ */
/*  OMNESEVENT - SCRIPT PRINCIPAL              */
/*  Fonctionnalités côté client (jQuery)        */
/* ============================================ */

/*
   IMPORTANT : tout ce code n'est qu'une aide à l'utilisateur.
   La vraie sécurité se fera côté serveur en PHP.
   Ne JAMAIS faire confiance au JavaScript pour la sécurité.
*/

$(document).ready(function () {

    /*
       $(document).ready(...) attend que tout le HTML soit chargé
       avant d'exécuter notre code. C'est la base de jQuery.
       Équivalent : window.onload (vu dans ton cours).
    */


    /* ============================================ */
    /*  1. MENU BURGER (mobile)                     */
    /* ============================================ */

    // Quand on clique sur le bouton burger, on bascule la classe "ouvert"
    // sur le menu principal. Le CSS s'occupe du reste (display: flex).
    $("#menu-burger").on("click", function () {
        $("#menu-principal").toggleClass("ouvert");
    });


    /* ============================================ */
    /*    2. FILTRES ÉVÉNEMENTS (désactivé)         */
    /* ============================================ */

    /*
       Les filtres étaient gérés côté client.
       Ensuite, le filtrage est fait côté serveur en PHP
       (méthode plus robuste). On laisse donc le formulaire envoyer
       sa requête naturellement à events.php via GET.
    */


    /* ============================================ */
    /*  3. CONFIRMATION AVANT SUPPRESSION           */
    /* ============================================ */

    /*
       Pour tous les boutons "btn-danger" (annuler, supprimer),
       on demande confirmation avant de laisser l'action se faire.

       Plus tard en PHP, c'est le serveur qui fera la vraie suppression.
       Cette confirmation n'est qu'un garde-fou visuel.
    */
    $(".btn-danger").on("click", function (event) {
        var confirmation = confirm("Êtes-vous sûr(e) ? Cette action ne peut pas être annulée.");
        if (!confirmation) {
            event.preventDefault(); // si l'utilisateur annule, on bloque l'action
        }
    });


    /* ============================================ */
    /*  4. AFFICHER/MASQUER LE MOT DE PASSE         */
    /* ============================================ */

    /*
       Pour chaque champ password, on ajoute un petit bouton "Afficher"
       qui permet de voir ce qu'on tape. Pratique sur mobile.
    */
    $("input[type='password']").each(function () {
        var champ = $(this);

        // Crée un bouton "Afficher" juste après le champ
        var bouton = $('<button type="button" class="btn-toggle-password">Afficher</button>');
        champ.after(bouton);

        // Au clic sur le bouton, on bascule entre "password" et "text"
        bouton.on("click", function () {
            if (champ.attr("type") === "password") {
                champ.attr("type", "text");
                bouton.text("Masquer");
            } else {
                champ.attr("type", "password");
                bouton.text("Afficher");
            }
        });
    });


    /* ============================================ */
    /*  5. VALIDATION FORMULAIRE D'INSCRIPTION      */
    /* ============================================ */

    /*
       Vérifie que les deux mots de passe correspondent
       avant de soumettre le formulaire d'inscription.

       Le navigateur fait déjà la vérification "required" tout seul,
       on n'a donc pas besoin de revérifier la présence des champs.
    */
    $("form").on("submit", function (event) {
        var motDePasse = $(this).find("#mot_de_passe").val();
        var confirmation = $(this).find("#confirmation").val();

        // Si le champ "confirmation" existe (donc c'est register.html)
        if (confirmation !== undefined) {

            // Vérifie que les mots de passe correspondent
            if (motDePasse !== confirmation) {
                alert("Les deux mots de passe ne correspondent pas.");
                event.preventDefault(); // empêche l'envoi du formulaire
                return;
            }

            // Vérifie que le mot de passe fait au moins 6 caractères
            if (motDePasse.length < 6) {
                alert("Le mot de passe doit faire au moins 6 caractères.");
                event.preventDefault();
                return;
            }
        }
    });


}); // fin de $(document).ready