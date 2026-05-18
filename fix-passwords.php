<?php
require_once "config/bdd.php";

// On va régénérer les mots de passe pour tous les comptes de test
$comptes = [
    'admin' => 'admin123',
    'marie' => 'orga123',
    'lucas' => 'orga123',
    'paul'  => 'orga123',
    'juju'  => 'parti123',
    'lea'   => 'parti123',
    'tom'   => 'parti123',
];

echo "<h1>Mise à jour des mots de passe</h1>";
echo "<ul>";

foreach ($comptes as $login => $mot_de_passe_clair) {
    // On génère un VRAI hash bcrypt
    $hash = password_hash($mot_de_passe_clair, PASSWORD_BCRYPT);

    // On met à jour le hash en base
    $requete = $bdd->prepare("UPDATE users SET mot_de_passe = ? WHERE login = ?");
    $requete->execute([$hash, $login]);

    echo "<li>✅ Mot de passe mis à jour pour <strong>" . htmlspecialchars($login) . "</strong> (mot de passe : <code>" . htmlspecialchars($mot_de_passe_clair) . "</code>)</li>";
}

echo "</ul>";
echo "<p><strong>✨ Tous les comptes ont leur mot de passe valide. Tu peux maintenant te connecter !</strong></p>";
echo "<p><a href='login.php'>→ Aller à la page de connexion</a></p>";
?>