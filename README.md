# OmnesEvent

Plateforme web de billetterie centralisée pour les événements organisés par
les associations étudiantes du groupe Omnes (BDE, BDS, Junior Entreprise,
Bureau Culturel).

Projet réalisé dans le cadre du cours "Web Dynamique" en ING2 à l'ECE.

---

## 🚀 Fonctionnalités

### Visiteur (non connecté)
- Consulter la liste des événements à venir
- Filtrer par catégorie, association et date
- Consulter le détail d'un événement
- S'inscrire (en tant que participant ou organisateur)
- Se connecter

### Participant
- Toutes les actions visiteur
- Réserver une place sur un événement
- Annuler une réservation
- Consulter son profil avec ses billets (à venir, passés, annulés)

### Organisateur (validé par admin)
- Toutes les actions participant (sauf réservation)
- Créer un événement avec affiche
- Modifier ses propres événements
- Supprimer ses propres événements
- Consulter son tableau de bord avec stats

### Administrateur
- Valider ou refuser les comptes organisateurs en attente
- Supprimer des utilisateurs (cascade auto sur événements/réservations)
- Supprimer n'importe quel événement (modération)
- Vue d'ensemble de la plateforme (statistiques globales)

---

## 🏗️ Stack technique

- **Front** : HTML5, CSS3 (responsive mobile-first), JavaScript + jQuery (CDN)
- **Back** : PHP 8 (procédural), avec `require_once` pour la factorisation
- **Base de données** : MySQL via PDO (requêtes préparées)
- **Serveur local** : WAMP (Apache + MySQL + PHP)

Aucun framework n'a été utilisé : le projet illustre les fondamentaux du
développement web côté serveur.

---

## 🔧 Installation locale

### Pré-requis
- WAMP (ou XAMPP, MAMP) installé
- PHP 8.0 minimum
- MySQL 5.7 minimum

### Étapes

1. **Cloner ou copier le projet** dans le dossier `www/` de WAMP : C:/wamp64/www/ece-web-dynamique-projet-final/

2. **Démarrer WAMP** (icône verte attendue).

3. **Créer la base de données** :
   - Accéder à phpMyAdmin via `http://localhost/phpmyadmin`
   - Créer une base nommée `omnesevent` en encodage `utf8mb4_unicode_ci`
   - Importer le fichier `sql/omnesevent.sql` (tables + données de test)

4. **Vérifier la configuration** dans `config/bdd.php` :
   - `$hote = "localhost"`
   - `$nom_bdd = "omnesevent"`
   - `$utilisateur = "root"`
   - `$mot_de_passe = ""`

5. **Accéder au site** : http://localhost/ece-web-dynamique-projet-final/
---

## 👥 Comptes de test

| Rôle | Login | Mot de passe | Particularité |
|------|-------|--------------|---------------|
| Admin | `admin` | `admin123` | Accès total |
| Organisateur BDE | `marie` | `orga123` | Compte actif |
| Organisateur BDS | `lucas` | `orga123` | Compte actif |
| Organisateur JE | `paul` | `orga123` | Compte en attente de validation |
| Participant | `juju` | `parti123` | A déjà des réservations |
| Participant | `lea` | `parti123` | A déjà des réservations |
| Participant | `tom` | `parti123` | A déjà des réservations |

---

## 📁 Architecture du projet
ece-web-dynamique-projet-final/
│
├── index.php                    Accueil (3 prochains événements)
├── events.php                   Catalogue + filtres serveur
├── event.php                    Détail d'un événement
├── register.php                 Inscription
├── login.php                    Connexion
├── logout.php                   Déconnexion
├── profile.php                  Profil participant
├── organizer-dashboard.php      Tableau de bord organisateur
├── admin-dashboard.php          Tableau de bord admin
├── create-event.php             Création d'événement
├── edit-event.php               Modification d'événement
├── delete-event.php             Suppression d'événement (confirmation)
├── reserve.php                  Réservation
├── cancel-reservation.php       Annulation de réservation
├── validate-orga.php            Validation organisateur (admin)
├── reject-orga.php              Refus organisateur (admin)
├── delete-user.php              Suppression utilisateur (admin)
├── contact.php                  Page contact
├── 404.php                      Page erreur personnalisée
├── .htaccess                    Configuration Apache
│
├── config/
│   └── bdd.php                  Connexion PDO
│
├── includes/
│   ├── header.php               En-tête HTML
│   ├── menu.php                 Menu de navigation (dynamique selon rôle)
│   ├── footer.php               Pied de page
│   └── functions.php            Fonctions utilitaires (auth, sessions...)
│
├── assets/
│   ├── css/style.css            Feuille de style principale
│   ├── js/script.js             JavaScript (menu mobile, confirmations...)
│   ├── images/                  Images statiques
│   └── uploads/                 Affiches d'événements (upload)
│
└── sql/
└── omnesevent.sql           Script de création de la base + données test

---

## 🔐 Sécurité mise en place

| Attaque ciblée | Protection appliquée |
|----------------|----------------------|
| Injection SQL | Requêtes préparées PDO partout |
| XSS | `htmlspecialchars()` à chaque affichage |
| Mots de passe | Hachage bcrypt (`password_hash` / `password_verify`) |
| Accès non autorisé | Fonctions `exiger_connexion()` et `exiger_role()` |
| Escalade de privilèges | Liste blanche stricte sur le rôle à l'inscription |
| Fuite d'information au login | Message d'erreur générique |
| Upload malicieux | 5 vérifications (taille, extension, MIME, présence, renommage) |
| Auto-suppression admin | Vérification serveur `id !== $_SESSION['id_user']` |
| Modification d'événement d'autrui | Vérification de propriété + double `WHERE` SQL |
| Double réservation | Vérification PHP + contrainte UNIQUE en base |
| Suppression accidentelle | Page de confirmation GET puis action POST |

---

## 💾 Modèle de données

5 tables principales :

- **`users`** : utilisateurs (admin, organisateur, participant)
- **`associations`** : BDE, BDS, JE, Bureau Culturel
- **`categories`** : Soirée, Sport, Culture, Conférence, Atelier
- **`events`** : événements (FK vers users, associations, categories)
- **`reservations`** : table de jonction users ↔ events (relation N:N)

Les contraintes `ON DELETE CASCADE` sur les FK garantissent l'intégrité
référentielle : la suppression d'un utilisateur ou d'un événement
nettoie automatiquement les données liées.

---

## 🧰 Choix techniques

- **PHP procédural** plutôt qu'orienté objet : le sujet du cours impose
  la stack, et l'objectif pédagogique est d'illustrer les fondamentaux.
- **Pas de framework** : pas de Laravel, Symfony, etc. Tout est écrit "à la main".
- **jQuery via CDN** : pour les interactions JS (menu mobile, confirmations).
- **Stockage des affiches sur le disque** (`assets/uploads/`) avec renommage
  aléatoire, plutôt qu'en base : c'est plus efficace pour servir les images.
- **Soft delete** pour les annulations de réservation : on garde l'historique.

---

## 👤 Auteur

Projet réalisé par Olivia, Florence et Rayane
Cours "Web Dynamique" — ECE Lyon G1 - 2025/2026