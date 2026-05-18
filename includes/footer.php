<?php
/* ============================================ */
/*  OMNESEVENT - PIED DE PAGE COMMUN            */
/* ============================================ */

/*
   Ce fichier est inclus À LA FIN de chaque page PHP.
   Il contient :
   - le <footer> visible
   - le chargement des scripts JS (jQuery + script.js)
   - la fermeture de <body> et </html>

   IMPORTANT : on charge les scripts ICI (en bas de page)
   pour que tout le HTML soit chargé avant que le JS s'exécute.
   C'est une bonne pratique (vu dans le cours JS).
*/
?>
    <footer>
        <p>&copy; 2026 OmnesEvent - Projet ING2</p>
        <ul>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="#">Mentions légales</a></li>
        </ul>
    </footer>

    <!-- jQuery via CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Notre script -->
    <script src="assets/js/script.js"></script>
</body>
</html>