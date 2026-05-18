<?php
// On inclut notre fichier de configuration
require_once "config/bdd.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test PHP</title>
</head>
<body>
    <h1>Test PHP</h1>

    <p>Message renvoyé par config/bdd.php :</p>
    <p><strong><?php echo $test_php; ?></strong></p>

    <p>Version de PHP : <?php echo phpversion(); ?></p>

    <p>Date et heure du serveur : <?php echo date("d/m/Y H:i:s"); ?></p>
</body>
</html>