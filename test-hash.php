<?php
require_once "config/bdd.php";

// On récupère le hash de Juju en base
$requete = $bdd->prepare("SELECT login, mot_de_passe FROM users WHERE login = ?");
$requete->execute(['juju']);
$utilisateur = $requete->fetch();

echo "<h1>Diagnostic du mot de passe</h1>";
echo "<p><strong>Login en base :</strong> " . htmlspecialchars($utilisateur['login']) . "</p>";
echo "<p><strong>Hash stocké :</strong> <code>" . htmlspecialchars($utilisateur['mot_de_passe']) . "</code></p>";
echo "<p><strong>Longueur du hash :</strong> " . strlen($utilisateur['mot_de_passe']) . " caractères</p>";

// Test : est-ce que "parti123" correspond au hash en base ?
$test = password_verify('parti123', $utilisateur['mot_de_passe']);
echo "<p><strong>Test password_verify('parti123', hash) :</strong> " . ($test ? "✅ VRAI" : "❌ FAUX") . "</p>";

// On génère un nouveau hash pour "parti123" et on teste sa validité
$nouveau_hash = password_hash('parti123', PASSWORD_BCRYPT);
echo "<p><strong>Nouveau hash pour parti123 :</strong><br><code>" . htmlspecialchars($nouveau_hash) . "</code></p>";

$test2 = password_verify('parti123', $nouveau_hash);
echo "<p><strong>Test du nouveau hash :</strong> " . ($test2 ? "✅ VRAI" : "❌ FAUX") . "</p>";
?>