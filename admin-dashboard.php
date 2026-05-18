<?php
require_once "config/bdd.php";
require_once "includes/functions.php";

// PROTECTION : seuls les admins peuvent voir cette page
exiger_role('admin');

$titre_page = "OmnesEvent - Espace administrateur";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Espace administrateur</h1>
    <p>Bonjour <strong>Admin</strong>, vous gérez l'ensemble de la plateforme.</p>

    <!-- ===== COMPTES ORGANISATEURS EN ATTENTE ===== -->
    <section>
        <h2>Comptes organisateurs à valider</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Association demandée</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Garnier</td>
                    <td>Pauline</td>
                    <td>pauline.garnier@edu.ece.fr</td>
                    <td>Junior Entreprise</td>
                    <td>
                        <a href="#" class="btn-primary">Valider</a>
                        <a href="#" class="btn-danger">Refuser</a>
                    </td>
                </tr>
                <tr>
                    <td>Moreau</td>
                    <td>Lucas</td>
                    <td>lucas.moreau@edu.ece.fr</td>
                    <td>BDS</td>
                    <td>
                        <a href="#" class="btn-primary">Valider</a>
                        <a href="#" class="btn-danger">Refuser</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- ===== TOUS LES UTILISATEURS ===== -->
    <section>
        <h2>Tous les utilisateurs</h2>
        <table>
            <thead>
                <tr>
                    <th>Login</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>jdupont</td>
                    <td>Juju Dupont</td>
                    <td>juju.dupont@edu.ece.fr</td>
                    <td>Participant</td>
                    <td>Actif</td>
                    <td><a href="#" class="btn-danger">Supprimer</a></td>
                </tr>
                <tr>
                    <td>mlefevre</td>
                    <td>Marie Lefèvre</td>
                    <td>marie.lefevre@edu.ece.fr</td>
                    <td>Organisateur</td>
                    <td>Actif</td>
                    <td><a href="#" class="btn-danger">Supprimer</a></td>
                </tr>
                <tr>
                    <td>pgarnier</td>
                    <td>Pauline Garnier</td>
                    <td>pauline.garnier@edu.ece.fr</td>
                    <td>Organisateur</td>
                    <td>En attente</td>
                    <td><a href="#" class="btn-danger">Supprimer</a></td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- ===== TOUS LES ÉVÉNEMENTS ===== -->
    <section>
        <h2>Tous les événements</h2>
        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date</th>
                    <th>Organisateur</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Soirée d'intégration BDE</td>
                    <td>25 sept. 2026</td>
                    <td>Marie Lefèvre</td>
                    <td>Publié</td>
                    <td><a href="#" class="btn-danger">Supprimer</a></td>
                </tr>
                <tr>
                    <td>Gala de fin d'année</td>
                    <td>15 juin 2027</td>
                    <td>Marie Lefèvre</td>
                    <td>En attente</td>
                    <td>
                        <a href="#" class="btn-primary">Valider</a>
                        <a href="#" class="btn-danger">Refuser</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

</main>

<?php include "includes/footer.php"; ?>