-- ===============================
-- DATABASE: Système de Gestion de Tournois Sportifs (MySQL)
-- ===============================

SET FOREIGN_KEY_CHECKS = 0;

-- ===============================
-- TABLE tournoi
-- ===============================
CREATE TABLE tournoi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  description TEXT,
  date_debut DATE,
  date_fin DATE,
  type_tournoi ENUM('elimination', 'poules', 'mixte'),
  nombre_equipes INT DEFAULT 16,
  tours_configuration JSON,
  statut ENUM('configuration', 'inscription', 'en_cours', 'termine', 'annule'),
  avec_petite_finale BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===============================
-- TABLE equipe
-- ===============================
CREATE TABLE equipe (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL UNIQUE,
  abrege VARCHAR(10),
  date_creation DATE,
  ville VARCHAR(100),
  pays VARCHAR(100),
  couleur_maillot VARCHAR(20),
  logo_url VARCHAR(255),
  budget DECIMAL(10,2),
  classement_national INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_equipe_nom ON equipe(nom);
CREATE INDEX idx_equipe_pays ON equipe(pays);

-- ===============================
-- TABLE coach
-- ===============================
CREATE TABLE coach (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50),
  prenom VARCHAR(50),
  date_naissance DATE,
  nationalite VARCHAR(100),
  specialite ENUM('offensif', 'defensif', 'tactique', 'physique'),
  experience_annees INT DEFAULT 0,
  diplome VARCHAR(100),
  equipe_id INT,
  date_embauche DATE,
  salaire DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (equipe_id) REFERENCES equipe(id)
);

CREATE INDEX idx_coach_nom_prenom ON coach(nom, prenom);
CREATE INDEX idx_coach_equipe ON coach(equipe_id);

-- ===============================
-- TABLE joueur
-- ===============================
CREATE TABLE joueur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50),
  prenom VARCHAR(50),
  date_naissance DATE,
  lieu_naissance VARCHAR(100),
  nationalite VARCHAR(100),
  taille_cm INT,
  poids_kg DECIMAL(5,2),

  equipe_id INT,
  date_debut_contrat DATE,
  date_fin_contrat DATE,
  numero_maillot INT,
  poste ENUM('gardien', 'defenseur', 'milieu', 'attaquant', 'remplacant'),
  pied_fort ENUM('droit', 'gauche', 'ambidextre'),
  salaire DECIMAL(10,2),
  valeur_marche DECIMAL(10,2),

  matchs_joues INT DEFAULT 0,
  buts_marques INT DEFAULT 0,
  passes_decisives INT DEFAULT 0,
  cartons_jaunes INT DEFAULT 0,
  cartons_rouges INT DEFAULT 0,

  email VARCHAR(100) UNIQUE,
  telephone VARCHAR(15),
  agent VARCHAR(100),
  statut_blessure ENUM('sain', 'leger', 'grave', 'operation') DEFAULT 'sain',
  date_retour_estime DATE,

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (equipe_id) REFERENCES equipe(id)
);

CREATE INDEX idx_joueur_nom_prenom ON joueur(nom, prenom);
CREATE INDEX idx_joueur_equipe ON joueur(equipe_id);
CREATE INDEX idx_joueur_nationalite ON joueur(nationalite);
CREATE INDEX idx_joueur_poste ON joueur(poste);

-- ===============================
-- TABLE inscription_tournoi
-- ===============================
CREATE TABLE inscription_tournoi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tournoi_id INT,
  equipe_id INT,
  date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  statut ENUM('en_attente', 'accepte', 'refuse', 'desinscrit'),

  matchs_joues INT DEFAULT 0,
  victoires INT DEFAULT 0,
  nuls INT DEFAULT 0,
  defaites INT DEFAULT 0,
  buts_pour INT DEFAULT 0,
  buts_contre INT DEFAULT 0,
  points INT DEFAULT 0,
  difference_buts INT,
  tour_elimination INT,
  position_finale INT,

  UNIQUE (tournoi_id, equipe_id),
  FOREIGN KEY (tournoi_id) REFERENCES tournoi(id),
  FOREIGN KEY (equipe_id) REFERENCES equipe(id)
);

CREATE INDEX idx_inscription_tournoi ON inscription_tournoi(tournoi_id);
CREATE INDEX idx_inscription_equipe ON inscription_tournoi(equipe_id);

-- ===============================
-- TABLE tour
-- ===============================
CREATE TABLE tour (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tournoi_id INT,
  nom VARCHAR(50),
  ordre INT,
  type_tour ENUM('elimination', 'poule', 'finale', 'petite_finale'),
  matchs_prevus INT,
  equipes_requises INT,
  date_debut DATE,
  date_fin DATE,
  statut ENUM('a_venir', 'en_cours', 'termine'),
  equipes_qualifiees JSON,

  UNIQUE (tournoi_id, nom),
  UNIQUE (tournoi_id, ordre),
  FOREIGN KEY (tournoi_id) REFERENCES tournoi(id)
);

-- ===============================
-- TABLE terrain
-- ===============================
CREATE TABLE terrain (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  localisation VARCHAR(255),
  ville VARCHAR(100),
  capacite INT,
  type_surface ENUM('gazon', 'synthetique', 'parquet', 'terre'),
  dimension_longueur INT,
  dimension_largeur INT,
  eclairage BOOLEAN,
  tribunes_couvertes BOOLEAN,
  prix_location_heure DECIMAL(8,2),
  disponibilite BOOLEAN,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_terrain_ville ON terrain(ville);
CREATE INDEX idx_terrain_dispo ON terrain(disponibilite);

-- ===============================
-- TABLE match
-- ===============================
CREATE TABLE `match` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tournoi_id INT,
  tour_id INT,
  equipe_domicile_id INT,
  equipe_exterieur_id INT,

  type_match ENUM('elimination', 'poule', 'finale', 'petite_finale'),
  type_confrontation ENUM('simple', 'aller', 'retour'),
  match_retour_id INT,
  nom_tour VARCHAR(50),

  score_domicile INT,
  score_exterieur INT,
  score_prolongation_domicile INT,
  score_prolongation_exterieur INT,
  tirs_au_but_domicile INT,
  tirs_au_but_exterieur INT,

  vainqueur_id INT,
  perdant_id INT,
  est_nul BOOLEAN,
  termine_ap_prolongation BOOLEAN,
  termine_aux_tirs_au_but BOOLEAN,

  date_match TIMESTAMP,
  terrain_id INT,
  arbitre_principal VARCHAR(100),
  statut ENUM('planifie', 'en_cours', 'termine', 'reporte', 'annule'),

  FOREIGN KEY (tournoi_id) REFERENCES tournoi(id),
  FOREIGN KEY (tour_id) REFERENCES tour(id),
  FOREIGN KEY (equipe_domicile_id) REFERENCES equipe(id),
  FOREIGN KEY (equipe_exterieur_id) REFERENCES equipe(id),
  FOREIGN KEY (vainqueur_id) REFERENCES equipe(id),
  FOREIGN KEY (perdant_id) REFERENCES equipe(id),
  FOREIGN KEY (terrain_id) REFERENCES terrain(id),
  FOREIGN KEY (match_retour_id) REFERENCES `match`(id)
);

-- ===============================
-- TABLE utilisateur
-- ===============================
CREATE TABLE utilisateur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE,
  email VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255),
  role ENUM('super_admin', 'admin_tournoi', 'arbitre', 'scoreur', 'journaliste', 'spectateur'),
  nom_complet VARCHAR(100),
  telephone VARCHAR(15),
  date_naissance DATE,
  avatar_url VARCHAR(255),
  equipe_favorite_id INT,
  langue ENUM('fr', 'ar', 'en') DEFAULT 'fr',
  derniere_connexion TIMESTAMP,
  token_reset_password VARCHAR(100),
  token_expiration TIMESTAMP,
  est_actif BOOLEAN DEFAULT TRUE,
  date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_derniere_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (equipe_favorite_id) REFERENCES equipe(id)
);

-- ===============================
-- TABLE contrat_joueur
-- ===============================
CREATE TABLE contrat_joueur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  joueur_id INT,
  equipe_id INT,
  date_debut DATE,
  date_fin DATE,
  type_contrat ENUM('professionnel', 'amateur', 'pret', 'jeune'),
  salaire_annuel DECIMAL(10,2),
  prime_signature DECIMAL(10,2),
  prime_performance DECIMAL(10,2),
  clause_liberatoire DECIMAL(12,2),
  clauses_speciales TEXT,
  agent VARCHAR(100),
  statut ENUM('actif', 'resilie', 'termine', 'en_negociation'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CHECK (date_fin > date_debut),
  FOREIGN KEY (joueur_id) REFERENCES joueur(id),
  FOREIGN KEY (equipe_id) REFERENCES equipe(id)
);

SET FOREIGN_KEY_CHECKS = 1;
