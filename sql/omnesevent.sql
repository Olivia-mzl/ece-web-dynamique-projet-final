-- ============================================================
-- OMNESEVENT - SCRIPT DE CRÉATION DE LA BASE DE DONNÉES
-- ============================================================
-- Ce script crée les 5 tables nécessaires au projet :
--   - users         (comptes : admin, organisateurs, participants)
--   - associations  (BDE, BDS, Junior Entreprise...)
--   - categories    (Soirée, Sport, Culture, Conférence...)
--   - events        (les événements eux-mêmes)
--   - reservations  (table de jonction utilisateurs <-> événements)
--
-- IMPORTANT : avant d'exécuter ce script, la base "omnesevent"
-- doit avoir été créée (voir étape 5.3).
-- ============================================================


-- ============================================================
-- TABLE 1 : associations
-- ============================================================
-- Cette table contient les associations d'Omnes.
-- On la crée AVANT events car events fera référence à cette table.
-- ============================================================

CREATE TABLE associations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    description     TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE 2 : categories
-- ============================================================
-- Cette table contient les catégories d'événements.
-- ============================================================

CREATE TABLE categories (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE 3 : users
-- ============================================================
-- Cette table contient tous les comptes utilisateurs.
-- Le rôle distingue admin / organisateur / participant.
-- Le statut sert pour la validation des organisateurs par l'admin.
-- ============================================================

CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    login           VARCHAR(50)  NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,
    role            ENUM('admin', 'organisateur', 'participant') NOT NULL DEFAULT 'participant',
    statut          ENUM('actif', 'en_attente', 'refuse') NOT NULL DEFAULT 'actif',
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE 4 : events
-- ============================================================
-- Cette table contient tous les événements.
-- Elle référence 3 autres tables via des clés étrangères :
--   - id_organisateur -> users.id (qui a créé l'événement)
--   - id_association  -> associations.id (quelle asso organise)
--   - id_categorie    -> categories.id (quelle catégorie)
--
-- Comportement à la suppression :
--   - Si on supprime l'organisateur (users)   : ses événements sont supprimés (CASCADE)
--   - Si on supprime une association          : id_association passe à NULL (SET NULL)
--   - Si on supprime une catégorie            : id_categorie passe à NULL (SET NULL)
-- ============================================================

CREATE TABLE events (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    titre               VARCHAR(150) NOT NULL,
    description         TEXT,
    date_evenement      DATE NOT NULL,
    heure_evenement     TIME NOT NULL,
    lieu                VARCHAR(150) NOT NULL,
    image               VARCHAR(255),
    capacite_max        INT NOT NULL DEFAULT 50,
    statut              ENUM('publie', 'annule', 'en_attente', 'refuse') NOT NULL DEFAULT 'publie',
    id_organisateur     INT NOT NULL,
    id_association      INT,
    id_categorie        INT,
    date_creation       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Clés étrangères
    CONSTRAINT fk_events_organisateur
        FOREIGN KEY (id_organisateur) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_events_association
        FOREIGN KEY (id_association) REFERENCES associations(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_events_categorie
        FOREIGN KEY (id_categorie) REFERENCES categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE 5 : reservations
-- ============================================================
-- Cette table est la "table de jonction" entre users et events
-- pour représenter la relation N:N (un user peut réserver
-- plusieurs events, un event peut avoir plusieurs users).
--
-- La contrainte UNIQUE sur (id_user, id_event) empêche un
-- utilisateur de réserver deux fois le même événement.
--
-- Comportement à la suppression :
--   - Si on supprime l'utilisateur : ses réservations sont supprimées
--   - Si on supprime l'événement   : les réservations sont supprimées
-- ============================================================

CREATE TABLE reservations (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    id_user             INT NOT NULL,
    id_event            INT NOT NULL,
    statut              ENUM('reserve', 'annule', 'present') NOT NULL DEFAULT 'reserve',
    date_reservation    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Clés étrangères
    CONSTRAINT fk_reservations_user
        FOREIGN KEY (id_user) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reservations_event
        FOREIGN KEY (id_event) REFERENCES events(id)
        ON DELETE CASCADE,

    -- Contrainte d'unicité : un user ne peut pas réserver
    -- deux fois le même événement.
    CONSTRAINT uniq_user_event UNIQUE (id_user, id_event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- FIN DU SCRIPT
-- ============================================================

-- ============================================================
-- INSERTION DE DONNÉES DE TEST
-- ============================================================
-- Ces données servent à tester l'application dès la Phase 6.
-- Elles peuvent être adaptées ou enrichies plus tard.
-- ============================================================


-- ============================================================
-- 1. CATÉGORIES D'ÉVÉNEMENTS
-- ============================================================

INSERT INTO categories (nom) VALUES
    ('Soirée'),
    ('Sport'),
    ('Culture'),
    ('Conférence'),
    ('Atelier');


-- ============================================================
-- 2. ASSOCIATIONS
-- ============================================================

INSERT INTO associations (nom, description) VALUES
    ('BDE',              'Bureau des étudiants : soirées, weekends, intégration'),
    ('BDS',              'Bureau des sports : tournois, sorties, activités sportives'),
    ('Junior Entreprise','Conférences, ateliers professionnels, networking'),
    ('Bureau Culturel',  'Sorties culturelles, cinéma, expositions');


-- ============================================================
-- 3. UTILISATEURS DE TEST
-- ============================================================
-- Mots de passe en clair (UNIQUEMENT POUR LE DÉV !):
--   - admin / admin@omnesevent.fr     -> "admin123"
--   - marie / marie.bde@omnesevent.fr -> "orga123"
--   - lucas / lucas.bds@omnesevent.fr -> "orga123"
--   - paul  / paul.je@omnesevent.fr   -> "orga123" (en attente de validation)
--   - juju  / juju@omnesevent.fr      -> "parti123"
--   - lea   / lea@omnesevent.fr       -> "parti123"
--   - tom   / tom@omnesevent.fr       -> "parti123"
-- ============================================================

-- Compte admin
INSERT INTO users (nom, prenom, email, login, mot_de_passe, role, statut) VALUES
    ('Admin', 'Super', 'admin@omnesevent.fr', 'admin',
     '$2y$10$M97Tp.S4N8tw9P7H1MqIY.OWsRrqLR4UQQABBmgAGtnDLAcZUcSny',
     'admin', 'actif');

-- Comptes organisateurs (validés)
INSERT INTO users (nom, prenom, email, login, mot_de_passe, role, statut) VALUES
    ('Lefèvre', 'Marie', 'marie.bde@omnesevent.fr', 'marie',
     '$2y$10$F0iVdzwoWKp7p6tCO13X3uZbBgL1J3DAcGOFTzcfmh11JOZuirpzG',
     'organisateur', 'actif'),

    ('Moreau', 'Lucas', 'lucas.bds@omnesevent.fr', 'lucas',
     '$2y$10$F0iVdzwoWKp7p6tCO13X3uZbBgL1J3DAcGOFTzcfmh11JOZuirpzG',
     'organisateur', 'actif');

-- Compte organisateur en attente de validation
INSERT INTO users (nom, prenom, email, login, mot_de_passe, role, statut) VALUES
    ('Garnier', 'Paul', 'paul.je@omnesevent.fr', 'paul',
     '$2y$10$F0iVdzwoWKp7p6tCO13X3uZbBgL1J3DAcGOFTzcfmh11JOZuirpzG',
     'organisateur', 'en_attente');

-- Comptes participants
INSERT INTO users (nom, prenom, email, login, mot_de_passe, role, statut) VALUES
    ('Dupont', 'Juju', 'juju@omnesevent.fr', 'juju',
     '$2y$10$xjy9N8nuvwwfwQwOzWdsFOVw6tLqMQwgi6Wjj1nXuCS6OGuT5DOMu',
     'participant', 'actif'),

    ('Martin', 'Léa', 'lea@omnesevent.fr', 'lea',
     '$2y$10$xjy9N8nuvwwfwQwOzWdsFOVw6tLqMQwgi6Wjj1nXuCS6OGuT5DOMu',
     'participant', 'actif'),

    ('Bernard', 'Tom', 'tom@omnesevent.fr', 'tom',
     '$2y$10$xjy9N8nuvwwfwQwOzWdsFOVw6tLqMQwgi6Wjj1nXuCS6OGuT5DOMu',
     'participant', 'actif');


-- ============================================================
-- 4. ÉVÉNEMENTS DE TEST
-- ============================================================
-- On suppose les id automatiques attribués dans l'ordre d'insertion :
--   - users.id = 1 -> admin
--   - users.id = 2 -> Marie (BDE)
--   - users.id = 3 -> Lucas (BDS)
--   - users.id = 4 -> Paul (en_attente)
--   - users.id = 5 -> Juju
--   - users.id = 6 -> Léa
--   - users.id = 7 -> Tom
--
--   - associations.id = 1 -> BDE
--   - associations.id = 2 -> BDS
--   - associations.id = 3 -> Junior Entreprise
--   - associations.id = 4 -> Bureau Culturel
--
--   - categories.id = 1 -> Soirée
--   - categories.id = 2 -> Sport
--   - categories.id = 3 -> Culture
--   - categories.id = 4 -> Conférence
--   - categories.id = 5 -> Atelier
-- ============================================================

INSERT INTO events
    (titre, description, date_evenement, heure_evenement, lieu,
     capacite_max, statut, id_organisateur, id_association, id_categorie)
VALUES
    ('Soirée d''intégration BDE',
     'Viens fêter le début de l''année avec le BDE ! Boissons et snacks offerts. DJ sur place.',
     '2026-09-25', '20:00:00', 'Campus Lyon - Salle des fêtes',
     100, 'publie', 2, 1, 1),

    ('Tournoi de futsal BDS',
     'Tournoi de futsal ouvert à tous les étudiants. Équipes de 5 joueurs.',
     '2026-10-02', '14:00:00', 'Gymnase central',
     60, 'publie', 3, 2, 2),

    ('Conférence IA et Éthique',
     'Conférence d''un chercheur en IA sur les enjeux éthiques actuels.',
     '2026-10-15', '18:30:00', 'Amphi A1',
     150, 'publie', 2, 3, 3),

    ('Soirée Halloween',
     'Soirée déguisée organisée par le BDE. Concours du meilleur costume !',
     '2026-10-31', '21:00:00', 'Le Sucre',
     200, 'publie', 2, 1, 1),

    ('Atelier CV et entretiens',
     'Atelier pour booster ton CV et te préparer aux entretiens.',
     '2026-10-22', '17:00:00', 'Salle 204',
     30, 'publie', 2, 3, 5);


-- ============================================================
-- 5. RÉSERVATIONS DE TEST
-- ============================================================
-- Juju (id=5) a réservé la Soirée BDE (event 1) et la Conférence (event 3)
-- Léa (id=6) a réservé la Soirée BDE (event 1)
-- Tom (id=7) a réservé le Tournoi futsal (event 2)
-- ============================================================

INSERT INTO reservations (id_user, id_event, statut) VALUES
    (5, 1, 'reserve'),
    (5, 3, 'reserve'),
    (6, 1, 'reserve'),
    (7, 2, 'reserve');


-- ============================================================
-- FIN DES DONNÉES DE TEST
-- ============================================================