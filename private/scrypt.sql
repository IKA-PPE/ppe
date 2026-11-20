Create Database graille;
use graille;

CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mdp VARCHAR(255) NOT NULL,
    etablissement VARCHAR(255) NOT NULL,
    budget_mensuel DECIMAL(7,2) DEFAULT NULL,
    role ENUM('etudiant', 'admin') NOT NULL DEFAULT 'etudiant',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE depense (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    nom_restaurant VARCHAR(255) NOT NULL,
    type_restaurant VARCHAR(100) NOT NULL,
    montant DECIMAL(6,2) NOT NULL,
    date_depense DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
);
