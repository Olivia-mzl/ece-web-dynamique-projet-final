<?php
/* ============================================ */
/*  OMNESEVENT - TABLEAU DE BORD ADMIN          */
/* ============================================ */

require_once "config/bdd.php";
require_once "includes/functions.php";

// Seuls les admins peuvent accéder à cette page
exiger_role('admin');


/* ============================================ */
/*  STATISTIQUES GLOBALES                       */
/* ============================================ */

// Comptes organisateurs en attente
$nb_orga_attente = (int) $bdd->query(
    "SELECT COUNT(*) FROM users WHERE role = 'organisateur' AND statut = 'en_attente'"
)->fetchColumn();

// Nombre total d'utilisateurs
$nb_users_total = (int) $bdd->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Nombre total d'événements
$nb_events_total = (int) $bdd->query("SELECT COUNT(*) FROM events")->fetchColumn();

// Nombre total de réservations actives
$nb_resa_total = (int) $bdd->query(
    "SELECT COUNT(*) FROM reservations WHERE statut IN ('reserve', 'present')"
)->fetchColumn();


/* ============================================ */
/*  ORGANISATEURS EN ATTENTE                    */
/* ============================================ */

$sql_attente = "
    SELECT id, nom, prenom, email, login, date_creation
    FROM users
    WHERE role = 'organisateur'
      AND statut = 'en_attente'
    ORDER BY date_creation DESC
";

$organisateurs_attente = $bdd->query($sql_attente)->fetchAll();


/* ============================================ */
/*  TOUS LES UTILISATEURS                       */
/* ============================================ */

$sql_users = "
    SELECT id, nom, prenom, email, login, role, statut, date_creation
    FROM users
    ORDER BY date_creation DESC
";

$tous_users = $bdd->query($sql_users)->fetchAll();


/* ============================================ */
/*  TOUS LES ÉVÉNEMENTS                         */
/* ============================================ */

$sql_events = "
    SELECT
        e.id,
        e.titre,
        e.date_evenement,
        e.statut,
        e.capacite_max,
        u.prenom AS orga_prenom,
        u.nom AS orga_nom,
        a.nom AS association_nom,
        (SELECT COUNT(*)
         FROM reservations r
         WHERE r.id_event = e.id
           AND r.statut IN ('reserve', 'present')
        ) AS nb_inscrits
    FROM events e
    JOIN users u              ON e.id_organisateur = u.id
    LEFT JOIN associations a  ON e.id_association   = a.id
    ORDER BY e.date_evenement DESC
";

$tous_events = $bdd->query($sql_events)->fetchAll();


/* ============================================ */
/*  AFFICHAGE DE LA PAGE                        */
/* ============================================ */

$titre_page = "OmnesEvent - Espace administrateur";
include "includes/header.php";
include "includes/menu.php";
?>

<main>

    <h1>Espace administrateur</h1>
    <p>
        Bonjour <strong><?php echo htmlspecialchars($_SESSION['prenom']); ?></strong>,
        vous gérez l'ensemble de la plateforme.
    </p>


    <!-- ===== STATISTIQUES ===== -->
    <section class="stats-row" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-number"><?php echo $nb_orga_attente; ?></div>
            <div class="stat-label">organisateur<?php echo $nb_orga_attente > 1 ? 's' : ''; ?> en attente</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $nb_users_total; ?></div>
            <div class="stat-label">utilisateur<?php echo $nb_users_total > 1 ? 's' : ''; ?> total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $nb_events_total; ?></div>
            <div class="stat-label">événement<?php echo $nb_events_total > 1 ? 's' : ''; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $nb_resa_total; ?></div>
            <div class="stat-label">réservation<?php echo $nb_resa_total > 1 ? 's' : ''; ?> active<?php echo $nb_resa_total > 1 ? 's' : ''; ?></div>
        </div>
    </section>


    <!-- ===== ORGANISATEURS À VALIDER ===== -->
    <section>
        <h2>Comptes organisateurs à valider</h2>

        <?php if (empty($organisateurs_attente)): ?>

            <p>Aucun compte organisateur en attente.</p>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Login</th>
                        <th>Date d'inscription</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($organisateurs_attente as $orga): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($orga['nom']); ?></td>
                            <td><?php echo htmlspecialchars($orga['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($orga['email']); ?></td>
                            <td><?php echo htmlspecialchars($orga['login']); ?></td>
                            <td>
                                <?php
                                $date = new DateTime($orga['date_creation']);
                                echo $date->format('d/m/Y');
                                ?>
                            </td>
                            <td>
                                <a href="validate-orga.php?id=<?php echo $orga['id']; ?>" class="btn-primary">Valider</a>
                                <a href="reject-orga.php?id=<?php echo $orga['id']; ?>" class="btn-danger">Refuser</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
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
                <?php foreach ($tous_users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['login']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($u['prenom']); ?>
                            <?php echo htmlspecialchars($u['nom']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <?php
                            // Affichage du rôle avec couleur
                            $role = $u['role'];
                            if ($role === 'admin') {
                                echo '<strong style="color: #c0392b;">Admin</strong>';
                            } elseif ($role === 'organisateur') {
                                echo '<span style="color: #0f5b6e;">Organisateur</span>';
                            } else {
                                echo 'Participant';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $statut = $u['statut'];
                            if ($statut === 'actif') {
                                echo '<span style="color: #1e7c3a;">Actif</span>';
                            } elseif ($statut === 'en_attente') {
                                echo '<span style="color: #d97706;">En attente</span>';
                            } else {
                                echo '<span style="color: #c0392b;">Refusé</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ((int) $u['id'] === (int) $_SESSION['id_user']): ?>
                                <em>(vous)</em>
                            <?php elseif ($u['role'] === 'admin'): ?>
                                <em>(admin)</em>
                            <?php else: ?>
                                <a href="delete-user.php?id=<?php echo $u['id']; ?>" class="btn-danger">Supprimer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>


    <!-- ===== TOUS LES ÉVÉNEMENTS ===== -->
    <section>
        <h2>Tous les événements</h2>

        <?php if (empty($tous_events)): ?>

            <p>Aucun événement n'a encore été créé.</p>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Organisateur</th>
                        <th>Association</th>
                        <th>Inscrits</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tous_events as $e): ?>
                        <tr>
                            <td>
                                <a href="event.php?id=<?php echo $e['id']; ?>">
                                    <?php echo htmlspecialchars($e['titre']); ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                $date = new DateTime($e['date_evenement']);
                                echo $date->format('d/m/Y');
                                ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($e['orga_prenom']); ?>
                                <?php echo htmlspecialchars($e['orga_nom']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($e['association_nom'] ?? '-'); ?></td>
                            <td><?php echo $e['nb_inscrits']; ?> / <?php echo $e['capacite_max']; ?></td>
                            <td>
                                <?php
                                $st = $e['statut'];
                                if ($st === 'publie') {
                                    echo '<span style="color: #1e7c3a;">Publié</span>';
                                } elseif ($st === 'annule') {
                                    echo '<span style="color: #c0392b;">Annulé</span>';
                                } elseif ($st === 'en_attente') {
                                    echo '<span style="color: #d97706;">En attente</span>';
                                } else {
                                    echo htmlspecialchars($st);
                                }
                                ?>
                            </td>
                            <td>
                                <a href="delete-event.php?id=<?php echo $e['id']; ?>" class="btn-danger">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </section>

</main>

<?php include "includes/footer.php"; ?>