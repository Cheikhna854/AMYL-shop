-- AMYL-shop - Gestion des ventes
-- Schema MySQL

CREATE DATABASE IF NOT EXISTS `AMYL-shop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `AMYL-shop`;

CREATE TABLE IF NOT EXISTS ventes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  produit VARCHAR(150) NOT NULL,
  prix_unitaire DECIMAL(10,2) NOT NULL,
  quantite INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  date_vente DATE NOT NULL,
  heure_vente TIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dettes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom_client VARCHAR(150) NOT NULL,
  telephone VARCHAR(30) NOT NULL,
  lieu VARCHAR(150) NOT NULL,
  montant DECIMAL(10,2) NOT NULL,
  date_dette DATE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Exemple d'insertion (remplacer le hash par un vrai password_hash PHP)
-- INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES
-- ('Admin', 'admin@amyl-shop.com', '$2y$10$REPLACE_WITH_PASSWORD_HASH');
