-- Création de la base de données
CREATE DATABASE IF NOT EXISTS alerte_qualite_services;
USE alerte_qualite_services;

-- Table des opérateurs
CREATE TABLE IF NOT EXISTS operateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des plaintes
CREATE TABLE IF NOT EXISTS plaintes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operateur_id INT,
    nom_plaignant VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    type_plainte VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    statut ENUM('en_attente', 'en_cours', 'resolue') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (operateur_id) REFERENCES operateurs(id)
);

-- Table des commentaires
CREATE TABLE IF NOT EXISTS commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plainte_id INT NOT NULL,
    nom_utilisateur VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contenu TEXT NOT NULL,
    date_commentaire DATETIME NOT NULL,
    type_reaction ENUM('like', 'dislike', 'neutre') NOT NULL DEFAULT 'neutre',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plainte_id) REFERENCES plaintes(id) ON DELETE CASCADE
);

-- Table des utilisateurs (pour l'administration)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderateur') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    related_id INT,
    lu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion des opérateurs par défaut
INSERT INTO operateurs (nom, description) VALUES
('MTN Cameroun', 'Opérateur de télécommunications mobile'),
('Orange Cameroun', 'Opérateur de télécommunications mobile'),
('Camtel', 'Opérateur de télécommunications fixe et mobile'),
('Nexttel', 'Opérateur de télécommunications mobile');

-- Insertion d'un utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Administrateur', 'admin@alertequalite.cm', 'admin123', 'admin'); 