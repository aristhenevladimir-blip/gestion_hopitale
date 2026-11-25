CREATE DATABASE IF NOT EXISTS gestion_hopital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_hopital;

CREATE TABLE IF NOT EXISTS patient (
    Id_Patient INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(50) NOT NULL, Prenom VARCHAR(50) NOT NULL,
    Sexe VARCHAR(8) DEFAULT NULL, Tel VARCHAR(20) DEFAULT NULL,
    Adresse VARCHAR(100) DEFAULT NULL, Email VARCHAR(50) DEFAULT NULL,
    Date_enregistrement DATE DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS consultation (
    no_Consultation INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Id_Patient INT(11) NOT NULL, Nom_Prenom_medecin VARCHAR(100) DEFAULT NULL,
    Telephone VARCHAR(15) DEFAULT NULL, Poids DOUBLE DEFAULT NULL,
    Taille_cm DOUBLE DEFAULT NULL, Diagnostique TEXT DEFAULT NULL,
    Date_Consultation DATE DEFAULT NULL,
    KEY Id_Patient (Id_Patient),
    CONSTRAINT fk_consultation_patient FOREIGN KEY (Id_Patient) REFERENCES patient (Id_Patient) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS prescription (
    no_consultation INT(11) NOT NULL, prescription VARCHAR(250) NOT NULL,
    KEY no_consultation (no_consultation),
    CONSTRAINT fk_prescription_consultation FOREIGN KEY (no_consultation) REFERENCES consultation (no_Consultation) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS utilisateur (
    id_utilisateur INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user', date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO utilisateur (username, password_hash, role) VALUES
('admin', '$2y$10$/6lXTrJ1qBVscA3YtiI9ZuM1l9xop4TbLhS3oZkrFLT1xczttSQpS', 'admin')
ON DUPLICATE KEY UPDATE username = username;
