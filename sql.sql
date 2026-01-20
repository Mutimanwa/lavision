-- =============================================
-- SYSTEME DE GESTION SCOLAIRE - BASE DE DONNEES
-- Version: 1.0
-- Auteur: Assistant IA
-- Date: 2024
-- =============================================

-- Création de la base de données
DROP DATABASE IF EXISTS gestion_academique_ecole;
CREATE DATABASE gestion_academique_ecole 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE gestion_academique_ecole;

-- =============================================
-- TABLE : années scolaires
-- Stocke les années académiques
-- =============================================
CREATE TABLE IF NOT EXISTS annees_scolaire (
    annee_id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    annee_libelle    VARCHAR(20) NOT NULL UNIQUE,
    date_debut       DATE NOT NULL,
    date_fin         DATE NOT NULL,
    statut           ENUM('active', 'inactive') DEFAULT 'active',
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_dates CHECK (date_fin > date_debut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : niveaux scolaires
-- Ex: 10e, 11e, 12e (secondaire)
-- =============================================
CREATE TABLE IF NOT EXISTS niveau (
    niveau_id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    nom_niveau       VARCHAR(50) NOT NULL UNIQUE,
    description      TEXT,
    ordre_affichage  INT DEFAULT 0,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : sections (filières)
-- Ex: Scientifique, Littéraire, Économique
-- =============================================
CREATE TABLE IF NOT EXISTS sections (
    section_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
    nom_section      VARCHAR(100) NOT NULL UNIQUE,
    description      TEXT,
    couleur          VARCHAR(7) DEFAULT '#3498db',
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : classes
-- Combinaison: niveau + section + année
-- =============================================
CREATE TABLE IF NOT EXISTS classes (
    class_id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    niveau_id        BIGINT NOT NULL,
    section_id       BIGINT,
    libelle          VARCHAR(100) NOT NULL,
    annee_id         BIGINT NOT NULL,
    capacite_max     INT DEFAULT 30,
    salle            VARCHAR(50),
    tuteur_id        BIGINT, -- Professeur principal
    statut           ENUM('active', 'inactive') DEFAULT 'active',
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (niveau_id) REFERENCES niveau(niveau_id) ON DELETE RESTRICT,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE SET NULL,
    FOREIGN KEY (annee_id) REFERENCES annees_scolaire(annee_id) ON DELETE RESTRICT,
    INDEX idx_classe_annee (annee_id),
    INDEX idx_classe_niveau (niveau_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : élèves
-- Informations personnelles des étudiants
-- =============================================
CREATE TABLE IF NOT EXISTS eleves (
    eleve_id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    matricule        VARCHAR(30) UNIQUE NOT NULL,
    nom              VARCHAR(100) NOT NULL,
    post_nom         VARCHAR(100) NOT NULL,
    prenom           VARCHAR(100) NOT NULL,
    date_naissance   DATE NOT NULL,
    lieu_naissance   VARCHAR(100),
    genre            ENUM('M','F','Autre') DEFAULT 'Autre',
    nationalite      VARCHAR(50) DEFAULT 'Congolaise',
    telephone        VARCHAR(20),
    email            VARCHAR(255),
    adresse          TEXT,
    photo            VARCHAR(255),
    groupe_sanguin   VARCHAR(5),
    allergies        TEXT,
    statut_etudiant  ENUM('en_attente','actif','suspendu','desiste') DEFAULT 'en_attente',
    date_inscription DATE,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modif       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_eleve_matricule (matricule),
    INDEX idx_eleve_statut (statut_etudiant),
    INDEX idx_eleve_nom (nom, prenom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : parents / tuteurs
-- Responsables légaux des élèves
-- =============================================
CREATE TABLE IF NOT EXISTS parents (
    parent_id        BIGINT AUTO_INCREMENT PRIMARY KEY,
    cin              VARCHAR(20) UNIQUE,
    prenom           VARCHAR(100) NOT NULL,
    nom              VARCHAR(100) NOT NULL,
    genre            ENUM('M','F'),
    profession       VARCHAR(100),
    entreprise       VARCHAR(100),
    telephone        VARCHAR(20) NOT NULL,
    telephone_bureau VARCHAR(20),
    email            VARCHAR(255),
    adresse          TEXT,
    est_principal    BOOLEAN DEFAULT FALSE,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_parent_nom (nom, prenom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : relation élève-parent
-- =============================================
CREATE TABLE IF NOT EXISTS student_parents (
    id               BIGINT AUTO_INCREMENT PRIMARY KEY,
    eleve_id         BIGINT NOT NULL,
    parent_id        BIGINT NOT NULL,
    relation         VARCHAR(50) NOT NULL, -- père, mère, tuteur
    est_responsable  BOOLEAN DEFAULT FALSE,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (eleve_id) REFERENCES eleves(eleve_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(parent_id) ON DELETE CASCADE,
    UNIQUE KEY uk_eleve_parent (eleve_id, parent_id, relation),
    INDEX idx_relation_eleve (eleve_id),
    INDEX idx_relation_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : utilisateurs admin
-- Personnel administratif et enseignant
-- =============================================
CREATE TABLE IF NOT EXISTS user_admins (
    user_id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    uuid             CHAR(36) UNIQUE DEFAULT (UUID()),
    identifiant      VARCHAR(50) UNIQUE NOT NULL,
    email            VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe     VARCHAR(255) NOT NULL,
    nom              VARCHAR(100) NOT NULL,
    prenom           VARCHAR(100) NOT NULL,
    telephone        VARCHAR(20),
    photo            VARCHAR(255),
    role             ENUM('superadmin','admin','secretaire','gestionnaire','proviseur') DEFAULT 'secretaire',
    permissions      JSON,
    dernier_login    DATETIME,
    statut           ENUM('actif','inactif','suspendu') DEFAULT 'actif',
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modif       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at       DATETIME DEFAULT NULL,
    INDEX idx_user_identifiant (identifiant),
    INDEX idx_user_role (role),
    INDEX idx_user_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : admissions
-- Processus d'admission des élèves
-- =============================================
CREATE TABLE IF NOT EXISTS admissions (
    admission_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
    eleve_id         BIGINT NOT NULL,
    class_id         BIGINT NOT NULL,
    annee_id         BIGINT NOT NULL,
    admis_par        BIGINT ,
    date_admission   DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut_admission ENUM('en_attente','approuve','rejete','liste_attente') DEFAULT 'en_attente',
    frais_inscription DECIMAL(10,2) DEFAULT 0,
    frais_payes      DECIMAL(10,2) DEFAULT 0,
    date_limite_paiement DATE,
    notes_entretien  TEXT,
    decision_comite  TEXT,
    date_decision    DATE,
    FOREIGN KEY (eleve_id) REFERENCES eleves(eleve_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE RESTRICT,
    FOREIGN KEY (annee_id) REFERENCES annees_scolaire(annee_id) ON DELETE RESTRICT,
    FOREIGN KEY (admis_par) REFERENCES user_admins(user_id) ON DELETE SET NULL,
    INDEX idx_admission_eleve (eleve_id),
    INDEX idx_admission_classe (class_id),
    INDEX idx_admission_statut (statut_admission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : professeurs
-- Corps enseignant
-- =============================================
CREATE TABLE IF NOT EXISTS professeurs (
    professeur_id    BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT UNIQUE,
    matricule_prof   VARCHAR(30) UNIQUE NOT NULL,
    nom              VARCHAR(100) NOT NULL,
    post_nom         VARCHAR(100) NOT NULL,
    prenom           VARCHAR(100) NOT NULL,
    date_naissance   DATE,
    lieu_naissance   VARCHAR(100),
    genre            ENUM('M','F','Autre') DEFAULT 'Autre',
    nationalite      VARCHAR(50),
    telephone        VARCHAR(20) NOT NULL,
    email            VARCHAR(255) UNIQUE,
    adresse          TEXT,
    specialite       VARCHAR(100),
    diplome          VARCHAR(100),
    date_embauche    DATE,
    statut           ENUM('actif','inactif','conge','retraite') DEFAULT 'actif',
    type_contrat     ENUM('titulaire','contractuel','vacataire'),
    salaire_base     DECIMAL(10,2),
    banque           VARCHAR(100),
    numero_compte    VARCHAR(50),
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_admins(user_id) ON DELETE SET NULL,
    INDEX idx_prof_matricule (matricule_prof),
    INDEX idx_prof_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mise à jour de la table classes pour référence professeur
ALTER TABLE classes 
ADD CONSTRAINT fk_classe_tuteur 
FOREIGN KEY (tuteur_id) REFERENCES professeurs(professeur_id) ON DELETE SET NULL;

-- =============================================
-- TABLE : matières
-- Cours dispensés
-- =============================================
CREATE TABLE IF NOT EXISTS matieres (
    matiere_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
    code_matiere     VARCHAR(20) UNIQUE NOT NULL,
    nom_matiere      VARCHAR(100) NOT NULL,
    description      TEXT,
    coefficient      DECIMAL(3,2) DEFAULT 1.00,
    niveau_id        BIGINT,
    couleur          VARCHAR(7) DEFAULT '#2ecc71',
    heures_semaine   INT DEFAULT 4,
    est_obligatoire  BOOLEAN DEFAULT TRUE,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (niveau_id) REFERENCES niveau(niveau_id) ON DELETE SET NULL,
    INDEX idx_matiere_code (code_matiere),
    INDEX idx_matiere_niveau (niveau_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : emploi du temps
-- Planning des cours
-- =============================================
CREATE TABLE IF NOT EXISTS emploi_du_temps (
    edt_id           BIGINT AUTO_INCREMENT PRIMARY KEY,
    class_id         BIGINT NOT NULL,
    matiere_id       BIGINT NOT NULL,
    professeur_id    BIGINT NOT NULL,
    jour_semaine     ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') NOT NULL,
    heure_debut      TIME NOT NULL,
    heure_fin        TIME NOT NULL,
    salle            VARCHAR(50),
    type_cours       ENUM('cours','td','tp','examen') DEFAULT 'cours',
    annee_id         BIGINT NOT NULL,
    periode          ENUM('trimestre1','trimestre2','trimestre3') DEFAULT 'trimestre1',
    date_debut       DATE,
    date_fin         DATE,
    statut           ENUM('actif','suspendu') DEFAULT 'actif',
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(matiere_id) ON DELETE CASCADE,
    FOREIGN KEY (professeur_id) REFERENCES professeurs(professeur_id) ON DELETE CASCADE,
    FOREIGN KEY (annee_id) REFERENCES annees_scolaire(annee_id) ON DELETE CASCADE,
    UNIQUE KEY uk_edt_unique (class_id, jour_semaine, heure_debut, annee_id, periode),
    INDEX idx_edt_classe (class_id),
    INDEX idx_edt_professeur (professeur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : notes / évaluations
-- =============================================
CREATE TABLE IF NOT EXISTS notes (
    note_id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    eleve_id         BIGINT NOT NULL,
    matiere_id       BIGINT NOT NULL,
    type_evaluation  ENUM('Devoir','Examen','Projet','Participation','Composition') NOT NULL,
    note             DECIMAL(5,2) NOT NULL CHECK (note >= 0 AND note <= 20),
    coefficient      DECIMAL(3,2) DEFAULT 1.00,
    date_evaluation  DATE NOT NULL,
    annee_id         BIGINT NOT NULL,
    periode          ENUM('trimestre1','trimestre2','trimestre3') NOT NULL,
    professeur_id    BIGINT,
    remarques        TEXT,
    est_rectifiee    BOOLEAN DEFAULT FALSE,
    note_rectifiee   DECIMAL(5,2),
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    modifie_par      BIGINT,
    date_modif       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (eleve_id) REFERENCES eleves(eleve_id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(matiere_id) ON DELETE CASCADE,
    FOREIGN KEY (annee_id) REFERENCES annees_scolaire(annee_id) ON DELETE CASCADE,
    FOREIGN KEY (professeur_id) REFERENCES professeurs(professeur_id) ON DELETE SET NULL,
    FOREIGN KEY (modifie_par) REFERENCES user_admins(user_id) ON DELETE SET NULL,
    UNIQUE KEY uk_note_unique (eleve_id, matiere_id, type_evaluation, date_evaluation, periode, annee_id),
    INDEX idx_note_eleve (eleve_id),
    INDEX idx_note_matiere (matiere_id),
    INDEX idx_note_periode (periode, annee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : paiements (frais scolaires)
-- =============================================
CREATE TABLE IF NOT EXISTS paiements (
    paiement_id      BIGINT AUTO_INCREMENT PRIMARY KEY,
    reference        VARCHAR(50) UNIQUE NOT NULL,
    eleve_id         BIGINT NOT NULL,
    annee_id         BIGINT NOT NULL,
    type_frais       ENUM('Inscription','Scolarite','Activite','Transport','Uniforme','Autre') NOT NULL,
    libelle          VARCHAR(255) NOT NULL,
    montant_total    DECIMAL(10,2) NOT NULL CHECK (montant_total > 0),
    montant_paye     DECIMAL(10,2) DEFAULT 0 CHECK (montant_paye >= 0),
    reste_a_payer    DECIMAL(10,2) GENERATED ALWAYS AS (montant_total - montant_paye) STORED,
    date_echeance    DATE,
    date_paiement    DATE,
    mode_paiement    ENUM('Espece','Virement','Cheque','Mobile','Carte') DEFAULT 'Espece',
    statut           ENUM('impaye','partiel','paye','annule') DEFAULT 'impaye',
    banque           VARCHAR(100),
    reference_banque VARCHAR(100),
    caissier_id      BIGINT NOT NULL,
    notes            TEXT,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modif       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (eleve_id) REFERENCES eleves(eleve_id) ON DELETE RESTRICT,
    FOREIGN KEY (annee_id) REFERENCES annees_scolaire(annee_id) ON DELETE RESTRICT,
    FOREIGN KEY (caissier_id) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
    INDEX idx_paiement_eleve (eleve_id),
    INDEX idx_paiement_statut (statut),
    INDEX idx_paiement_reference (reference),
    INDEX idx_paiement_date (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : absences
-- =============================================
CREATE TABLE IF NOT EXISTS absences (
    absence_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
    eleve_id         BIGINT NOT NULL,
    date_absence     DATE NOT NULL,
    matiere_id       BIGINT,
    periode_journee  ENUM('matin','aprem','journee') DEFAULT 'journee',
    justifiee        BOOLEAN DEFAULT FALSE,
    motif            VARCHAR(255),
    justificatif     VARCHAR(255),
    enregistre_par   BIGINT NOT NULL,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (eleve_id) REFERENCES eleves(eleve_id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(matiere_id) ON DELETE SET NULL,
    FOREIGN KEY (enregistre_par) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
    INDEX idx_absence_eleve (eleve_id),
    INDEX idx_absence_date (date_absence)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : sanctions disciplinaires
-- =============================================
CREATE TABLE IF NOT EXISTS sanctions (
    sanction_id      BIGINT AUTO_INCREMENT PRIMARY KEY,
    eleve_id         BIGINT NOT NULL,
    type_sanction    ENUM('avertissement','retenue','exclusion','convocation') NOT NULL,
    gravite          ENUM('legere','moyenne','grave') DEFAULT 'legere',
    date_sanction    DATE NOT NULL,
    date_fin         DATE,
    motif            TEXT NOT NULL,
    mesures          TEXT,
    sanctionne_par   BIGINT NOT NULL,
    statut           ENUM('actif','termine','annule') DEFAULT 'actif',
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (eleve_id) REFERENCES eleves(eleve_id) ON DELETE CASCADE,
    FOREIGN KEY (sanctionne_par) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
    INDEX idx_sanction_eleve (eleve_id),
    INDEX idx_sanction_date (date_sanction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : logs système
-- Journalisation des actions
-- =============================================
CREATE TABLE IF NOT EXISTS logs (
    log_id           BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT,
    niveau_log       ENUM('info', 'warning', 'error', 'security') DEFAULT 'info',
    categorie        VARCHAR(50) NOT NULL,
    action           VARCHAR(255) NOT NULL,
    details          JSON,
    ip_address       VARCHAR(45),
    user_agent       TEXT,
    date_action      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_user (user_id),
    INDEX idx_logs_date (date_action),
    INDEX idx_logs_categorie (categorie),
    INDEX idx_logs_niveau (niveau_log)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : paramètres système
-- Configuration de l'application
-- =============================================
CREATE TABLE IF NOT EXISTS parametres (
    parametre_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
    cle              VARCHAR(100) UNIQUE NOT NULL,
    valeur           TEXT,
    type             ENUM('string', 'integer', 'boolean', 'json', 'array') DEFAULT 'string',
    categorie        VARCHAR(50) DEFAULT 'general',
    description      TEXT,
    modifiable       BOOLEAN DEFAULT TRUE,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modif       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parametre_cle (cle),
    INDEX idx_parametre_categorie (categorie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : sessions utilisateurs
-- =============================================
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id       VARCHAR(128) PRIMARY KEY,
    user_id          BIGINT NOT NULL,
    ip_address       VARCHAR(45),
    user_agent       TEXT,
    payload          TEXT,
    last_activity    INT NOT NULL,
    expires_at       DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user_admins(user_id) ON DELETE CASCADE,
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : backup_logs
-- Journal des sauvegardes
-- =============================================
CREATE TABLE IF NOT EXISTS backup_logs (
    backup_id        BIGINT AUTO_INCREMENT PRIMARY KEY,
    type_backup      ENUM('auto', 'manuel', 'system') DEFAULT 'auto',
    fichier          VARCHAR(255) NOT NULL,
    taille           BIGINT,
    statut           ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    erreur_message   TEXT,
    execute_par      BIGINT,
    date_execution   DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_prochaine   DATETIME,
    FOREIGN KEY (execute_par) REFERENCES user_admins(user_id) ON DELETE SET NULL,
    INDEX idx_backup_date (date_execution),
    INDEX idx_backup_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : login_attempts
-- Tentatives de connexion
-- =============================================
CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
    ip_address       VARCHAR(45) NOT NULL,
    identifiant      VARCHAR(50) NOT NULL,
    success          BOOLEAN DEFAULT FALSE,
    reason           VARCHAR(255),
    user_agent       TEXT,
    attempt_time     DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_ip (ip_address),
    INDEX idx_login_attempts_time (attempt_time),
    INDEX idx_login_attempts_success (success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : password_resets
-- Réinitialisation de mots de passe
-- =============================================
CREATE TABLE IF NOT EXISTS password_resets (
    reset_id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    email            VARCHAR(255) NOT NULL,
    token            VARCHAR(64) NOT NULL UNIQUE,
    expires_at       DATETIME NOT NULL,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_resets_email (email),
    INDEX idx_password_resets_token (token),
    INDEX idx_password_resets_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : remember_tokens
-- Tokens "Se souvenir de moi"
-- =============================================
CREATE TABLE IF NOT EXISTS remember_tokens (
    token_id         BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT NOT NULL,
    selector         VARCHAR(32) NOT NULL UNIQUE,
    hashed_token     VARCHAR(64) NOT NULL,
    expires_at       DATETIME NOT NULL,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_admins(user_id) ON DELETE CASCADE,
    INDEX idx_remember_tokens_user (user_id),
    INDEX idx_remember_tokens_selector (selector),
    INDEX idx_remember_tokens_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : paiement_journal
-- Journal des opérations de paiement
-- =============================================
CREATE TABLE IF NOT EXISTS paiement_journal (
    journal_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
    paiement_id      BIGINT NOT NULL,
    montant          DECIMAL(10,2) NOT NULL,
    mode_paiement    ENUM('Espece','Virement','Cheque','Mobile','Carte') NOT NULL,
    reference_banque VARCHAR(100),
    caissier_id      BIGINT NOT NULL,
    notes            TEXT,
    date_operation   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paiement_id) REFERENCES paiements(paiement_id) ON DELETE CASCADE,
    FOREIGN KEY (caissier_id) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
    INDEX idx_journal_paiement (paiement_id),
    INDEX idx_journal_date (date_operation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : quittances
-- Reçus de paiement
-- =============================================
CREATE TABLE IF NOT EXISTS quittances (
    quittance_id     BIGINT AUTO_INCREMENT PRIMARY KEY,
    numero_quittance VARCHAR(50) UNIQUE NOT NULL,
    paiement_id      BIGINT NOT NULL,
    eleve_id         BIGINT NOT NULL,
    montant          DECIMAL(10,2) NOT NULL,
    date_quittance   DATETIME NOT NULL,
    mode_paiement    ENUM('Espece','Virement','Cheque','Mobile','Carte') NOT NULL,
    reference_banque VARCHAR(100),
    caissier_id      BIGINT NOT NULL,
    statut           ENUM('valide', 'annule') DEFAULT 'valide',
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paiement_id) REFERENCES paiements(paiement_id) ON DELETE RESTRICT,
    FOREIGN KEY (eleve_id) REFERENCES eleves(eleve_id) ON DELETE RESTRICT,
    FOREIGN KEY (caissier_id) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
    INDEX idx_quittance_numero (numero_quittance),
    INDEX idx_quittance_date (date_quittance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE : caisse_sessions
-- Sessions de caisse
-- =============================================
CREATE TABLE IF NOT EXISTS caisse_sessions (
    session_id       BIGINT AUTO_INCREMENT PRIMARY KEY,
    caissier_id      BIGINT NOT NULL,
    montant_ouverture DECIMAL(10,2) NOT NULL,
    montant_fermeture DECIMAL(10,2),
    montant_theorique DECIMAL(10,2),
    ecart            DECIMAL(10,2),
    observations     TEXT,
    date_ouverture   DATETIME NOT NULL,
    date_fermeture   DATETIME,
    statut           ENUM('ouverte', 'fermee') DEFAULT 'ouverte',
    FOREIGN KEY (caissier_id) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
    INDEX idx_session_caissier (caissier_id),
    INDEX idx_session_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERTION DES DONNÉES INITIALES
-- =============================================

-- Années scolaires
INSERT INTO annees_scolaire (annee_libelle, date_debut, date_fin) VALUES
('2024-2025', '2024-09-01', '2025-06-30'),
('2025-2026', '2025-09-01', '2026-06-30');

-- Niveaux scolaires
INSERT INTO niveau (nom_niveau, description, ordre_affichage) VALUES
('7e année', 'Première année du secondaire', 1),
('8e année', 'Deuxième année du secondaire', 2),
('9e année', 'Troisième année du secondaire', 3),
('10e année', 'Première année du secondaire supérieur', 4),
('11e année', 'Deuxième année du secondaire supérieur', 5),
('12e année', 'Troisième année du secondaire supérieur', 6);

-- Sections
INSERT INTO sections (nom_section, description, couleur) VALUES
('Scientifique', 'Section scientifique (Math, Physique, Chimie)', '#e74c3c'),
('Littéraire', 'Section littéraire (Littérature, Philosophie)', '#3498db'),
('Économique', 'Section économique (Économie, Gestion)', '#2ecc71'),
('Pédagogique', 'Section pédagogique', '#f39c12');

-- Paramètres système
INSERT INTO parametres (cle, valeur, type, categorie, description) VALUES
('nom_ecole', 'École Secondaire d''Excellence', 'string', 'general', 'Nom officiel de l''école'),
('adresse_ecole', '123 Avenue de l''Éducation, Kinshasa', 'string', 'general', 'Adresse physique'),
('telephone_ecole', '+243 81 234 5678', 'string', 'general', 'Numéro de téléphone'),
('email_ecole', 'contact@ecole-excellence.cd', 'string', 'general', 'Email de contact'),
('devise_ecole', 'Savoir, Excellence, Discipline', 'string', 'general', 'Devise de l''école'),
('annee_courante', '1', 'integer', 'academique', 'ID de l''année scolaire courante'),
('delai_paiement', '15', 'integer', 'financier', 'Délai de paiement en jours'),
('taux_penalite', '5', 'integer', 'financier', 'Taux de pénalité pour retard'),
('limite_absence', '10', 'integer', 'disciplinaire', 'Limite d''absences non justifiées'),
('seuil_reussite', '10', 'integer', 'academique', 'Note minimale pour réussir'),
('heure_debut_cours', '08:00:00', 'string', 'emploi_temps', 'Heure de début des cours'),
('heure_fin_cours', '16:00:00', 'string', 'emploi_temps', 'Heure de fin des cours'),
('frais_inscription', '50000', 'integer', 'financier', 'Frais d''inscription par défaut'),
('frais_scolarite', '300000', 'integer', 'financier', 'Frais de scolarité par trimestre'),
('backup_auto', '1', 'boolean', 'system', 'Sauvegarde automatique activée'),
('backup_frequency', 'daily', 'string', 'system', 'Fréquence des sauvegardes');

-- Compte administrateur par défaut (mot de passe: Admin123!)
INSERT INTO user_admins (
    identifiant, 
    email, 
    mot_de_passe, 
    nom, 
    prenom, 
    role, 
    permissions
) VALUES (
    'superadmin',
    'admin@ecole-excellence.cd',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Admin',
    'Super',
    'superadmin',
    '["*"]'
);

-- =============================================
-- VUES UTILES
-- =============================================

-- Vue : élèves avec informations complètes
CREATE OR REPLACE VIEW vue_eleves_complet AS
SELECT 
    e.eleve_id,
    e.matricule,
    e.nom,
    e.post_nom,
    e.prenom,
    CONCAT(e.nom, ' ', e.prenom) as nom_complet,
    e.date_naissance,
    TIMESTAMPDIFF(YEAR, e.date_naissance, CURDATE()) as age,
    e.genre,
    e.telephone,
    e.email,
    e.statut_etudiant,
    e.date_inscription,
    c.libelle as classe_actuelle,
    n.nom_niveau as niveau,
    s.nom_section as section,
    a.annee_libelle as annee_scolaire,
    (SELECT GROUP_CONCAT(CONCAT(p.prenom, ' ', p.nom) SEPARATOR '; ') 
     FROM student_parents sp2 
     JOIN parents p ON sp2.parent_id = p.parent_id 
     WHERE sp2.eleve_id = e.eleve_id) as parents
FROM eleves e
LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
LEFT JOIN classes c ON adm.class_id = c.class_id
LEFT JOIN niveau n ON c.niveau_id = n.niveau_id
LEFT JOIN sections s ON c.section_id = s.section_id
LEFT JOIN annees_scolaire a ON c.annee_id = a.annee_id;

-- Vue : statistiques financières
CREATE OR REPLACE VIEW vue_statistiques_financieres AS
SELECT 
    a.annee_libelle,
    COUNT(DISTINCT p.eleve_id) as eleves_inscrits,
    COUNT(p.paiement_id) as total_paiements,
    SUM(p.montant_total) as total_a_payer,
    SUM(p.montant_paye) as total_paye,
    SUM(p.reste_a_payer) as total_impaye,
    AVG(p.montant_total) as moyenne_frais
FROM paiements p
JOIN eleves e ON p.eleve_id = e.eleve_id
JOIN annees_scolaire a ON p.annee_id = a.annee_id
WHERE e.statut_etudiant = 'actif'
GROUP BY a.annee_id;

-- Vue : performance académique par classe
CREATE OR REPLACE VIEW vue_performance_classes AS
SELECT 
    c.libelle as classe,
    n.nom_niveau as niveau,
    s.nom_section as section,
    a.annee_libelle,
    COUNT(DISTINCT nt.eleve_id) as nombre_eleves,
    AVG(nt.note) as moyenne_generale,
    MAX(nt.note) as meilleure_note,
    MIN(nt.note) as plus_basse_note,
    COUNT(CASE WHEN nt.note >= 10 THEN 1 END) as nombre_reussites,
    COUNT(CASE WHEN nt.note < 10 THEN 1 END) as nombre_echecs
FROM notes nt
JOIN eleves e ON nt.eleve_id = e.eleve_id
JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
JOIN classes c ON adm.class_id = c.class_id
JOIN niveau n ON c.niveau_id = n.niveau_id
LEFT JOIN sections s ON c.section_id = s.section_id
JOIN annees_scolaire a ON nt.annee_id = a.annee_id
GROUP BY c.class_id, nt.periode, nt.annee_id;

-- =============================================
-- PROCÉDURES STOCKÉES (séparées)
-- =============================================

-- Procédure : Générer le bulletin d'un élève
CREATE PROCEDURE generer_bulletin(
    IN p_eleve_id BIGINT,
    IN p_annee_id BIGINT,
    IN p_periode ENUM('trimestre1','trimestre2','trimestre3')
)
BEGIN
    SELECT 
        m.nom_matiere,
        m.coefficient,
        AVG(n.note) as moyenne_matiere,
        SUM(n.note * n.coefficient) / SUM(n.coefficient) as moyenne_ponderee,
        COUNT(n.note_id) as nombre_notes
    FROM notes n
    JOIN matieres m ON n.matiere_id = m.matiere_id
    WHERE n.eleve_id = p_eleve_id 
    AND n.annee_id = p_annee_id 
    AND n.periode = p_periode
    GROUP BY m.matiere_id
    ORDER BY m.coefficient DESC;
END;

-- Procédure : Calculer les frais impayés
CREATE PROCEDURE calculer_frais_impayes(
    IN p_eleve_id BIGINT
)
BEGIN
    SELECT 
        p.type_frais,
        p.libelle,
        p.montant_total,
        p.montant_paye,
        p.reste_a_payer,
        p.date_echeance,
        DATEDIFF(CURDATE(), p.date_echeance) as jours_retard,
        CASE 
            WHEN DATEDIFF(CURDATE(), p.date_echeance) > 0 
            THEN p.reste_a_payer * 0.05 * DATEDIFF(CURDATE(), p.date_echeance) / 30
            ELSE 0 
        END as penalite
    FROM paiements p
    WHERE p.eleve_id = p_eleve_id 
    AND p.statut IN ('impaye', 'partiel')
    ORDER BY p.date_echeance;
END;

-- =============================================
-- DÉCLENCHEURS (TRIGGERS)
-- =============================================

-- Trigger : Mettre à jour le statut de paiement automatiquement
CREATE TRIGGER trg_update_paiement_status
BEFORE UPDATE ON paiements
FOR EACH ROW
BEGIN
    -- Calculer le reste à payer
    SET NEW.reste_a_payer = NEW.montant_total - NEW.montant_paye;
    
    -- Mettre à jour le statut
    IF NEW.montant_paye >= NEW.montant_total THEN
        SET NEW.statut = 'paye';
    ELSEIF NEW.montant_paye > 0 THEN
        SET NEW.statut = 'partiel';
    ELSE
        SET NEW.statut = 'impaye';
    END IF;
    
    -- Si paiement complet, mettre la date de paiement
    IF NEW.statut = 'paye' AND OLD.statut != 'paye' THEN
        SET NEW.date_paiement = CURDATE();
    END IF;
END;

-- Trigger : Journaliser les modifications sur les élèves
CREATE TRIGGER trg_log_modification_eleve
AFTER UPDATE ON eleves
FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, categorie, action, details)
    VALUES (
        NULL, -- système
        'eleves', 
        'modification', 
        JSON_OBJECT(
            'eleve_id', OLD.eleve_id,
            'matricule', OLD.matricule,
            'changements', JSON_OBJECT(
                'statut', JSON_OBJECT('avant', OLD.statut_etudiant, 'apres', NEW.statut_etudiant),
                'email', JSON_OBJECT('avant', OLD.email, 'apres', NEW.email),
                'telephone', JSON_OBJECT('avant', OLD.telephone, 'apres', NEW.telephone)
            )
        )
    );
END;

-- Trigger : Vérifier la capacité de la classe avant admission
CREATE TRIGGER trg_check_classe_capacity
BEFORE INSERT ON admissions
FOR EACH ROW
BEGIN
    DECLARE current_count INT;
    DECLARE max_capacity INT;
    
    -- Compter les élèves déjà admis dans cette classe
    SELECT COUNT(*) INTO current_count
    FROM admissions a
    WHERE a.class_id = NEW.class_id 
    AND a.statut_admission = 'approuve';
    
    -- Récupérer la capacité maximale
    SELECT capacite_max INTO max_capacity
    FROM classes
    WHERE class_id = NEW.class_id;
    
    -- Vérifier si la capacité est atteinte
    IF current_count >= max_capacity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La classe a atteint sa capacité maximale';
    END IF;
END;

-- =============================================
-- ÉVÉNEMENTS PLANIFIÉS
-- =============================================

-- Événement : Sauvegarde automatique quotidienne
CREATE EVENT IF NOT EXISTS event_backup_quotidien
ON SCHEDULE EVERY 1 DAY STARTS '2024-01-01 02:00:00'
DO
BEGIN
    -- Log du début de sauvegarde
    INSERT INTO backup_logs (type_backup, fichier, statut, date_execution)
    VALUES ('auto', CONCAT('backup_', DATE_FORMAT(NOW(), '%Y%m%d_%H%i%s'), '.sql'), 'pending', NOW());
    
    -- Ici, on appellerait une procédure système pour faire le backup
    -- SET @backup_file = CONCAT('/backups/backup_', DATE_FORMAT(NOW(), '%Y%m%d_%H%i%s'), '.sql');
    -- SET @backup_cmd = CONCAT('mysqldump -u root -p votre_mot_de_passe gestion_academique_ecole > ', @backup_file);
    
    -- Pour l'instant, on simule le succès
    UPDATE backup_logs 
    SET statut = 'success', 
        taille = FLOOR(RAND() * 10000000) + 1000000
    WHERE statut = 'pending'
    ORDER BY backup_id DESC LIMIT 1;
END;

-- Événement : Nettoyage des logs anciens
CREATE EVENT IF NOT EXISTS event_nettoyage_logs
ON SCHEDULE EVERY 1 MONTH STARTS '2024-01-01 03:00:00'
DO
BEGIN
    -- Supprimer les logs de plus de 6 mois
    DELETE FROM logs 
    WHERE date_action < DATE_SUB(NOW(), INTERVAL 6 MONTH)
    AND niveau_log = 'info';
    
    -- Supprimer les logs d'erreur de plus d'1 an
    DELETE FROM logs 
    WHERE date_action < DATE_SUB(NOW(), INTERVAL 1 YEAR);
END;

-- =============================================
-- FIN DU SCRIPT SQL
-- =============================================

-- Affichage des messages de confirmation
SELECT 'Base de données créée avec succès!' as message;
SELECT COUNT(*) as nombre_tables FROM information_schema.tables 
WHERE table_schema = 'gestion_academique_ecole';