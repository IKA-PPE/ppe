DROP DATABASE IF EXISTS graille;
CREATE DATABASE graille;
USE graille;

-- 1. Tables sans clé étrangère


CREATE TABLE etablissement (
    id_etablissement INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6)
);

CREATE TABLE regime (
    id_regime INT AUTO_INCREMENT PRIMARY KEY,
    libelle_regime VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE type_cuisine (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    libelle_type VARCHAR(100) NOT NULL UNIQUE
);


CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mdp VARCHAR(255) NOT NULL,
    budget_mensuel DECIMAL(7,2) DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    role ENUM('etudiant', 'admin') NOT NULL DEFAULT 'etudiant',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_etablissement INT NOT NULL,
    FOREIGN KEY (id_etablissement) REFERENCES etablissement(id_etablissement)
);


CREATE TABLE groupe (
    id_groupe INT AUTO_INCREMENT PRIMARY KEY,
    nom_groupe VARCHAR(255) NOT NULL,
    code_invitation VARCHAR(10) NOT NULL UNIQUE,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE restaurant (
    id_restaurant INT AUTO_INCREMENT PRIMARY KEY,
    google_place_id VARCHAR(255) NOT NULL UNIQUE,
    nom_restaurant VARCHAR(255) NOT NULL,
    adresse_restaurant VARCHAR(255) NOT NULL,
    ville_restaurant VARCHAR(100) NOT NULL,
    id_type INT NOT NULL,
    FOREIGN KEY (id_type) REFERENCES type_cuisine(id_type)
);


CREATE TABLE session_vote (
    id_session INT AUTO_INCREMENT PRIMARY KEY,
    date_sortie DATETIME NOT NULL,
    budget_max DECIMAL(7,2) NOT NULL,
    statut DATETIME NOT NULL,
    mode_decision ENUM('majorite', 'consensus') NOT NULL,
    id_groupe INT NOT NULL,
    id_restaurant INT NULL,
    FOREIGN KEY (id_groupe) REFERENCES groupe(id_groupe) ON DELETE CASCADE,
    FOREIGN KEY (id_restaurant) REFERENCES restaurant(id_restaurant) ON DELETE SET NULL
);


CREATE TABLE proposition (
    id_proposition INT AUTO_INCREMENT PRIMARY KEY,
    date_proposition DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    id_restaurant INT NOT NULL,
    id_session INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_restaurant) REFERENCES restaurant(id_restaurant) ON DELETE CASCADE,
    FOREIGN KEY (id_session) REFERENCES session_vote(id_session) ON DELETE CASCADE
);


CREATE TABLE depense (
    id_depense INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(6,2) NOT NULL,
    date_depense DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    id_restaurant INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_restaurant) REFERENCES restaurant(id_restaurant) ON DELETE CASCADE
);


CREATE TABLE liste (
    id_liste INT AUTO_INCREMENT PRIMARY KEY,
    nom_liste VARCHAR(255) NOT NULL,
    aleatoire BOOLEAN NOT NULL DEFAULT FALSE,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);


-- 3. Tables issues des associations n,n :    (clé primaire composée de deux clés étrangères)



CREATE TABLE suivre (
    id_utilisateur INT NOT NULL,
    id_regime INT NOT NULL,
    PRIMARY KEY (id_utilisateur, id_regime),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_regime) REFERENCES regime(id_regime) ON DELETE CASCADE
);


CREATE TABLE compatible (
    id_restaurant INT NOT NULL,
    id_regime INT NOT NULL,
    PRIMARY KEY (id_restaurant, id_regime),
    FOREIGN KEY (id_restaurant) REFERENCES restaurant(id_restaurant) ON DELETE CASCADE,
    FOREIGN KEY (id_regime) REFERENCES regime(id_regime) ON DELETE CASCADE
);

CREATE TABLE contenir (
    id_liste INT NOT NULL,
    id_restaurant INT NOT NULL,
    PRIMARY KEY (id_liste, id_restaurant),
    FOREIGN KEY (id_liste) REFERENCES liste(id_liste) ON DELETE CASCADE,
    FOREIGN KEY (id_restaurant) REFERENCES restaurant(id_restaurant) ON DELETE CASCADE
);


CREATE TABLE voter (
    id_utilisateur INT NOT NULL,
    id_proposition INT NOT NULL,
    date_vote DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur, id_proposition),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_proposition) REFERENCES proposition(id_proposition) ON DELETE CASCADE
);


CREATE TABLE appartenir (
    id_utilisateur INT NOT NULL,
    id_groupe INT NOT NULL,
    role VARCHAR(50) NOT NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_utilisateur, id_groupe),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_groupe) REFERENCES groupe(id_groupe) ON DELETE CASCADE
);