-- ===============================
-- INSERT للجداول (بدون foreign keys أولاً)
-- ===============================

-- 1️⃣ TERRAIN (ما فيهش foreign keys)
INSERT INTO terrain (id, nom, localisation, ville, capacite, type_surface, dimension_longueur, dimension_largeur, eclairage, tribunes_couvertes, prix_location_heure, disponibilite) VALUES
(1, 'Stade Mohammed V', 'Boulevard de l''Armée Royale', 'Casablanca', 45000, 'gazon', 105, 68, TRUE, TRUE, 5000.00, TRUE),
(2, 'Complexe Sportif Moulay Abdellah', 'Quartier Al Inara', 'Rabat', 52000, 'gazon', 105, 68, TRUE, TRUE, 6000.00, TRUE),
(3, 'Stade Municipal', 'Avenue Hassan II', 'Marrakech', 18000, 'gazon', 105, 68, TRUE, FALSE, 3000.00, TRUE),
(4, 'Stade Adrar', 'Route de Taroudant', 'Agadir', 45480, 'gazon', 105, 68, TRUE, TRUE, 4500.00, TRUE),
(5, 'Stade de Fès', 'Route d''Imouzzer', 'Fès', 35000, 'gazon', 105, 68, TRUE, TRUE, 3500.00, TRUE);

-- 2️⃣ EQUIPE (ما فيهش foreign keys)
INSERT INTO equipe (id, nom, abrege, date_creation, ville, pays, couleur_maillot, budget, classement_national) VALUES
(1, 'Raja Casablanca', 'RCA', '1949-03-20', 'Casablanca', 'Maroc', 'Vert', 50000000.00, 1),
(2, 'Wydad Casablanca', 'WAC', '1937-05-08', 'Casablanca', 'Maroc', 'Rouge', 48000000.00, 2),
(3, 'AS FAR Rabat', 'FAR', '1958-01-01', 'Rabat', 'Maroc', 'Vert et Jaune', 35000000.00, 3),
(4, 'Renaissance Berkane', 'RSB', '1938-01-01', 'Berkane', 'Maroc', 'Orange', 25000000.00, 4),
(5, 'Maghreb Fès', 'MAS', '1946-01-01', 'Fès', 'Maroc', 'Jaune et Noir', 20000000.00, 5),
(6, 'Hassania Agadir', 'HUSA', '1946-01-01', 'Agadir', 'Maroc', 'Orange', 18000000.00, 6),
(7, 'Ittihad Tanger', 'IRT', '1983-01-01', 'Tanger', 'Maroc', 'Blanc', 15000000.00, 7),
(8, 'Difaa El Jadida', 'DHJ', '1948-01-01', 'El Jadida', 'Maroc', 'Rouge et Blanc', 12000000.00, 8),
(9, 'Olympique Safi', 'OCS', '1921-01-01', 'Safi', 'Maroc', 'Noir et Blanc', 10000000.00, 9),
(10, 'Moghreb Tétouan', 'MAT', '1922-01-01', 'Tétouan', 'Maroc', 'Jaune', 9000000.00, 10),
(11, 'Chabab Rif Hoceima', 'CRRH', '2004-01-01', 'Al Hoceima', 'Maroc', 'Bleu', 8000000.00, 11),
(12, 'Rapide Oued Zem', 'RCOZ', '1997-01-01', 'Oued Zem', 'Maroc', 'Vert et Rouge', 7000000.00, 12),
(13, 'Youssoufia Berrechid', 'CAYB', '1964-01-01', 'Berrechid', 'Maroc', 'Rouge', 6500000.00, 13),
(14, 'Renaissance Zemamra', 'RCZ', '1985-01-01', 'Zemamra', 'Maroc', 'Bleu et Blanc', 6000000.00, 14),
(15, 'Union Touarga', 'UTS', '1956-01-01', 'Rabat', 'Maroc', 'Bleu', 5500000.00, 15),
(16, 'Kawkab Marrakech', 'KACM', '1947-01-01', 'Marrakech', 'Maroc', 'Rouge', 5000000.00, 16);

-- 3️⃣ COACH (فيه foreign key: equipe_id)
INSERT INTO coach (id, nom, prenom, date_naissance, nationalite, specialite, experience_annees, diplome, equipe_id, date_embauche, salaire) VALUES
(1, 'Benzarti', 'Faouzi', '1950-11-06', 'Tunisie', 'tactique', 30, 'CAF Pro License', 1, '2023-07-01', 80000.00),
(2, 'Gamondi', 'Ricardo', '1963-02-15', 'Argentine', 'offensif', 25, 'CONMEBOL Pro', 2, '2022-09-15', 85000.00),
(3, 'Moumen', 'Abdelhak', '1975-03-20', 'Maroc', 'defensif', 12, 'CAF A License', 3, '2023-01-10', 50000.00),
(4, 'Ammouta', 'Mouin', '1970-08-22', 'Maroc', 'tactique', 18, 'CAF Pro License', 4, '2021-06-01', 60000.00),
(5, 'El Berkaoui', 'Mustapha', '1968-05-14', 'Maroc', 'offensif', 15, 'CAF A License', 5, '2023-03-01', 45000.00),
(6, 'Rachid', 'Taoussi', '1973-11-08', 'Maroc', 'physique', 10, 'CAF B License', 6, '2022-12-01', 40000.00),
(7, 'Rajevac', 'Milovan', '1954-01-02', 'Serbie', 'tactique', 28, 'UEFA Pro License', 7, '2023-05-15', 70000.00),
(8, 'Benali', 'Youssef', '1980-07-25', 'Maroc', 'defensif', 8, 'CAF A License', 8, '2022-08-20', 38000.00);

-- 4️⃣ JOUEUR (فيه foreign key: equipe_id)
INSERT INTO joueur (id, nom, prenom, date_naissance, lieu_naissance, nationalite, taille_cm, poids_kg, equipe_id, date_debut_contrat, date_fin_contrat, numero_maillot, poste, pied_fort, salaire, valeur_marche, matchs_joues, buts_marques, passes_decisives, cartons_jaunes, cartons_rouges, email, telephone, statut_blessure) VALUES
-- Raja Casablanca
(1, 'Zniti', 'Anas', '1994-03-15', 'Casablanca', 'Maroc', 185, 78.5, 1, '2022-07-01', '2025-06-30', 1, 'gardien', 'droit', 35000.00, 800000.00, 45, 0, 0, 3, 0, 'a.zniti@raja.ma', '0612345601', 'sain'),
(2, 'Hafidi', 'Mohcine', '1996-01-20', 'Casablanca', 'Maroc', 178, 72.0, 1, '2021-07-01', '2024-06-30', 8, 'milieu', 'droit', 45000.00, 1200000.00, 52, 8, 12, 7, 0, 'm.hafidi@raja.ma', '0612345602', 'sain'),
(3, 'Ben Malango', 'Malango', '1998-11-05', 'Pointe-Noire', 'Congo', 182, 76.0, 1, '2023-01-15', '2026-06-30', 9, 'attaquant', 'droit', 50000.00, 1500000.00, 38, 15, 6, 5, 1, 'malango@raja.ma', '0612345603', 'sain'),
(4, 'El Jadeyaoui', 'Youssef', '1997-08-12', 'Rabat', 'Maroc', 180, 74.0, 1, '2022-07-01', '2025-06-30', 6, 'defenseur', 'gauche', 38000.00, 900000.00, 48, 2, 3, 8, 0, 'y.eljadeyaoui@raja.ma', '0612345604', 'sain'),

-- Wydad Casablanca
(5, 'Tagnaouti', 'Ahmed', '1996-05-22', 'Casablanca', 'Maroc', 190, 82.0, 2, '2020-07-01', '2024-06-30', 1, 'gardien', 'droit', 40000.00, 1000000.00, 58, 0, 0, 2, 0, 'a.tagnaouti@wydad.ma', '0612345605', 'sain'),
(6, 'Aouk', 'Yahya', '1993-02-10', 'Casablanca', 'Maroc', 175, 70.0, 2, '2019-07-01', '2024-06-30', 10, 'milieu', 'droit', 55000.00, 1800000.00, 65, 12, 15, 9, 0, 'y.aouk@wydad.ma', '0612345606', 'sain'),
(7, 'Msuva', 'Simon', '1991-08-14', 'Dar es Salaam', 'Tanzanie', 178, 73.0, 2, '2022-07-01', '2024-06-30', 19, 'attaquant', 'droit', 48000.00, 1300000.00, 42, 14, 8, 6, 1, 's.msuva@wydad.ma', '0612345607', 'leger'),
(8, 'Attiyat Allah', 'Yahia', '1995-02-02', 'Casablanca', 'Maroc', 177, 71.5, 2, '2021-07-01', '2025-06-30', 25, 'defenseur', 'gauche', 42000.00, 1100000.00, 54, 3, 7, 10, 0, 'y.attiyat@wydad.ma', '0612345608', 'sain'),

-- AS FAR Rabat
(9, 'Benoun', 'Ayoub', '1997-04-12', 'Rabat', 'Maroc', 183, 77.0, 3, '2023-07-01', '2026-06-30', 5, 'defenseur', 'gauche', 36000.00, 850000.00, 35, 2, 1, 6, 0, 'a.benoun@far.ma', '0612345609', 'sain'),
(10, 'Zouhir', 'Amine', '1995-09-18', 'Rabat', 'Maroc', 181, 75.0, 3, '2022-07-01', '2025-06-30', 7, 'milieu', 'droit', 38000.00, 950000.00, 47, 9, 10, 7, 0, 'a.zouhir@far.ma', '0612345610', 'sain'),
(11, 'Kaabi', 'Hamza', '1998-01-25', 'Rabat', 'Maroc', 179, 72.0, 3, '2021-07-01', '2024-06-30', 11, 'attaquant', 'droit', 40000.00, 1050000.00, 51, 16, 5, 8, 1, 'h.kaabi@far.ma', '0612345611', 'sain'),
(12, 'Bounou', 'Yassine', '1991-04-05', 'Montreal', 'Maroc', 192, 84.0, 3, '2023-07-01', '2025-06-30', 1, 'gardien', 'droit', 60000.00, 2000000.00, 32, 0, 0, 1, 0, 'y.bounou@far.ma', '0612345612', 'sain'),

-- Renaissance Berkane
(13, 'Krouch', 'Hamza', '1996-07-08', 'Berkane', 'Maroc', 180, 74.0, 4, '2022-07-01', '2025-06-30', 10, 'milieu', 'droit', 32000.00, 750000.00, 49, 7, 9, 6, 0, 'h.krouch@rsb.ma', '0612345613', 'sain'),
(14, 'El Fahli', 'Youssef', '1995-03-14', 'Oujda', 'Maroc', 176, 70.0, 4, '2021-07-01', '2024-06-30', 8, 'milieu', 'gauche', 34000.00, 800000.00, 55, 10, 12, 8, 0, 'y.elfahli@rsb.ma', '0612345614', 'sain'),
(15, 'Dayo', 'Youssef', '1997-11-22', 'Abidjan', 'Côte d''Ivoire', 184, 79.0, 4, '2023-01-10', '2025-06-30', 9, 'attaquant', 'droit', 38000.00, 900000.00, 28, 11, 4, 4, 0, 'y.dayo@rsb.ma', '0612345615', 'sain'),
(16, 'Mokadem', 'Zakaria', '1998-06-30', 'Nador', 'Maroc', 186, 80.0, 4, '2022-07-01', '2025-06-30', 1, 'gardien', 'droit', 28000.00, 600000.00, 41, 0, 0, 2, 0, 'z.mokadem@rsb.ma', '0612345616', 'sain'),

-- Maghreb Fès
(17, 'Khadrouf', 'Sofiane', '1994-02-18', 'Fès', 'Maroc', 178, 73.0, 5, '2022-07-01', '2024-06-30', 7, 'milieu', 'droit', 30000.00, 650000.00, 46, 6, 8, 7, 0, 's.khadrouf@mas.ma', '0612345617', 'sain'),
(18, 'Rahimi', 'Soufiane', '1996-06-11', 'Fès', 'Maroc', 182, 76.0, 5, '2021-07-01', '2024-06-30', 9, 'attaquant', 'droit', 35000.00, 850000.00, 52, 13, 7, 6, 1, 's.rahimi@mas.ma', '0612345618', 'leger'),
(19, 'Badda', 'Anas', '1997-09-05', 'Meknès', 'Maroc', 179, 72.0, 5, '2023-01-15', '2025-06-30', 10, 'milieu', 'gauche', 28000.00, 700000.00, 35, 5, 6, 5, 0, 'a.badda@mas.ma', '0612345619', 'sain'),
(20, 'Aït Brahim', 'Youssef', '1995-12-20', 'Fès', 'Maroc', 188, 81.0, 5, '2022-07-01', '2024-06-30', 1, 'gardien', 'droit', 26000.00, 550000.00, 44, 0, 0, 3, 0, 'y.aitbrahim@mas.ma', '0612345620', 'sain'),

-- Hassania Agadir
(21, 'Benabid', 'Zakaria', '1996-04-16', 'Agadir', 'Maroc', 177, 71.0, 6, '2022-07-01', '2025-06-30', 8, 'milieu', 'droit', 27000.00, 600000.00, 48, 8, 9, 6, 0, 'z.benabid@husa.ma', '0612345621', 'sain'),
(22, 'Karamoko', 'Abdoul', '1998-08-09', 'Bamako', 'Mali', 181, 75.0, 6, '2023-01-10', '2025-06-30', 11, 'attaquant', 'droit', 32000.00, 750000.00, 31, 9, 5, 4, 0, 'a.karamoko@husa.ma', '0612345622', 'sain'),
(23, 'Ennaffati', 'Mohamed', '1997-03-28', 'Agadir', 'Maroc', 175, 69.0, 6, '2021-07-01', '2024-06-30', 7, 'milieu', 'gauche', 25000.00, 550000.00, 53, 7, 11, 8, 0, 'm.ennaffati@husa.ma', '0612345623', 'sain'),
(24, 'Lagraini', 'Brahim', '1996-11-14', 'Tiznit', 'Maroc', 189, 82.0, 6, '2022-07-01', '2025-06-30', 1, 'gardien', 'droit', 24000.00, 500000.00, 42, 0, 0, 2, 0, 'b.lagraini@husa.ma', '0612345624', 'sain'),

-- Ittihad Tanger
(25, 'Morsli', 'Badreddine', '1995-05-12', 'Tanger', 'Maroc', 180, 74.0, 7, '2022-07-01', '2024-06-30', 6, 'defenseur', 'droit', 26000.00, 580000.00, 47, 3, 2, 9, 1, 'b.morsli@irt.ma', '0612345625', 'sain'),
(26, 'Hrimat', 'Zakaria', '1997-02-25', 'Tanger', 'Maroc', 176, 71.0, 7, '2021-07-01', '2024-06-30', 10, 'milieu', 'droit', 28000.00, 620000.00, 51, 6, 8, 7, 0, 'z.hrimat@irt.ma', '0612345626', 'sain'),
(27, 'Nahiri', 'Karim', '1998-07-18', 'Tétouan', 'Maroc', 183, 77.0, 7, '2023-01-15', '2025-06-30', 9, 'attaquant', 'droit', 30000.00, 700000.00, 33, 8, 4, 5, 0, 'k.nahiri@irt.ma', '0612345627', 'sain'),
(28, 'El Ouahabi', 'Anas', '1996-10-08', 'Larache', 'Maroc', 187, 80.0, 7, '2022-07-01', '2024-06-30', 1, 'gardien', 'droit', 23000.00, 480000.00, 43, 0, 0, 3, 0, 'a.elouahabi@irt.ma', '0612345628', 'sain'),

-- Difaa El Jadida
(29, 'Ajaray', 'Abdelilah', '1994-08-22', 'El Jadida', 'Maroc', 179, 73.0, 8, '2022-07-01', '2024-06-30', 5, 'defenseur', 'gauche', 25000.00, 550000.00, 49, 2, 3, 8, 0, 'a.ajaray@dhj.ma', '0612345629', 'sain'),
(30, 'Ouattara', 'Issoufou', '1996-12-15', 'Ouagadougou', 'Burkina Faso', 182, 76.0, 8, '2023-01-10', '2025-06-30', 11, 'attaquant', 'droit', 29000.00, 650000.00, 29, 7, 3, 4, 0, 'i.ouattara@dhj.ma', '0612345630', 'sain'),
(31, 'Benkaaba', 'Yassine', '1997-04-30', 'Safi', 'Maroc', 177, 72.0, 8, '2021-07-01', '2024-06-30', 8, 'milieu', 'droit', 27000.00, 600000.00, 52, 5, 7, 6, 0, 'y.benkaaba@dhj.ma', '0612345631', 'sain'),
(32, 'Rahouli', 'Mohamed', '1995-09-11', 'El Jadida', 'Maroc', 185, 79.0, 8, '2022-07-01', '2024-06-30', 1, 'gardien', 'droit', 22000.00, 450000.00, 45, 0, 0, 2, 0, 'm.rahouli@dhj.ma', '0612345632', 'sain');

-- 5️⃣ TOURNOI (ما فيهش foreign keys)
INSERT INTO tournoi (id, nom, description, date_debut, date_fin, type_tournoi, nombre_equipes, statut, avec_petite_finale) VALUES
(1, 'Coupe du Trône 2024', 'Compétition nationale à élimination directe', '2024-01-15', '2024-05-30', 'elimination', 16, 'termine', TRUE),
(2, 'Championnat Maroc Telecom 2024', 'Championnat national en système de poules', '2024-08-01', '2025-05-31', 'poules', 16, 'en_cours', FALSE),
(3, 'Supercoupe du Maroc 2024', 'Match unique entre champion et vainqueur de la coupe', '2024-07-15', '2024-07-15', 'elimination', 2, 'termine', FALSE),
(4, 'Coupe du Trône 2025', 'Compétition nationale à élimination directe', '2025-01-20', '2025-06-15', 'elimination', 16, 'inscription', TRUE);

-- 6️⃣ UTILISATEUR (فيه foreign key: equipe_favorite_id)
INSERT INTO utilisateur (id, username, email, password_hash, role, nom_complet, telephone, date_naissance, equipe_favorite_id, langue, est_actif) VALUES
(1, 'admin_master', 'admin@tournoi.ma', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'super_admin', 'Mohammed Alami', '0661234501', '1985-03-15', 1, 'fr', TRUE),
(2, 'arbitre_hassan', 'h.benali@arbitre.ma', '$2y$10$abcdefghijklmnopqrstuvwxyz123457', 'arbitre', 'Hassan Benali', '0661234502', '1980-07-22', NULL, 'fr', TRUE),
(3, 'scoreur_karim', 'k.tazi@scoreur.ma', '$2y$10$abcdefghijklmnopqrstuvwxyz123458', 'scoreur', 'Karim Tazi', '0661234503', '1992-11-08', 2, 'fr', TRUE),
(4, 'journaliste_sara', 's.idrissi@media.ma', '$2y$10$abcdefghijklmnopqrstuvwxyz123459', 'journaliste', 'Sara Idrissi', '0661234504', '1988-04-18', 3, 'fr', TRUE),
(5, 'spectateur_youssef', 'y.amrani@email.ma', '$2y$10$abcdefghijklmnopqrstuvwxyz123460', 'spectateur', 'Youssef Amrani', '0661234505', '1995-09-25', 1, 'fr', TRUE);

-- 7️⃣ INSCRIPTION_TOURNOI (فيه foreign keys: tournoi_id, equipe_id)
INSERT INTO inscription_tournoi (id, tournoi_id, equipe_id, statut, matchs_joues, victoires, nuls, defaites, buts_pour, buts_contre, points, difference_buts, position_finale) VALUES
-- Coupe du Trône 2024
(1, 1, 1, 'accepte', 5, 4, 0, 1, 12, 4, 0, 8, 1),
(2, 1, 2, 'accepte', 5, 3, 1, 1, 10, 6, 0, 4, 2),
(3, 1, 3, 'accepte', 4, 2, 1, 1, 8, 5, 0, 3, 3),
(4, 1, 4, 'accepte', 4, 2, 1, 1, 7, 5, 0, 2, 4),
(5, 1, 5, 'accepte', 3, 2, 0, 1, 6, 4, 0, 2, 5),
(6, 1, 6, 'accepte', 3, 1, 1, 1, 5, 5, 0, 0, 6),
(7, 1, 7, 'accepte', 2, 1, 0, 1, 3, 3, 0, 0, 7),
(8, 1, 8, 'accepte', 2, 1, 0, 1, 4, 5, 0, -1, 8),
(9, 1, 9, 'accepte', 1, 0, 0, 1, 1, 3, 0, -2, 9),
(10, 1, 10, 'accepte', 1, 0, 0, 1, 0, 2, 0, -2, 10),
(11, 1, 11, 'accepte', 1, 0, 0, 1, 1, 4, 0, -3, 11),
(12, 1, 12, 'accepte', 1, 0, 0, 1, 0, 3, 0, -3, 12),
(13, 1, 13, 'accepte', 1, 0, 0, 1, 2, 5, 0, -3, 13),
(14, 1, 14, 'accepte', 1, 0, 0, 1, 1, 4, 0, -3, 14),
(15, 1, 15, 'accepte', 1, 0, 0, 1, 0, 3, 0, -3, 15),
(16, 1, 16, 'accepte', 1, 0, 0, 1, 1, 5, 0, -4, 16),

-- Championnat 2024
(17, 2, 1, 'accepte', 20, 14, 4, 2, 42, 15, 46, 27, 1),
(18, 2, 2, 'accepte', 20, 13, 5, 2, 38, 14, 44, 24, 2),
(19, 2, 3, 'accepte', 20, 12, 5, 3, 35, 18, 41, 17, 3),
(20, 2, 4, 'accepte', 20, 11, 6, 3, 32, 16, 39, 16, 4),
(21, 2, 5, 'accepte', 20, 10, 5, 5, 28, 20, 35, 8, 5),
(22, 2, 6, 'accepte', 20, 9, 6, 5, 26, 21, 33, 5, 6),
(23, 2, 7, 'accepte', 20, 8, 6, 6, 24, 22, 30, 2, 7),
(24, 2, 8, 'accepte', 20, 7, 7, 6, 22, 23, 28, -1, 8),
(25, 2, 9, 'accepte', 20, 6, 7, 7, 20, 24, 25, -4, 9),
(26, 2, 10, 'accepte', 20, 6, 6, 8, 19, 26, 24, -7, 10),
(27, 2, 11, 'accepte', 20, 5, 6, 9, 18, 28, 21, -10, 11),
(28, 2, 12, 'accepte', 20, 4, 7, 9, 17, 29, 19, -12, 12),
(29, 2, 13, 'accepte', 20, 4, 5, 11, 16, 32, 17, -16, 13),
(30, 2, 14, 'accepte', 20, 3, 6, 11, 15, 33, 15, -18, 14),
(31, 2, 15, 'accepte', 20, 2, 5, 13, 12, 36, 11, -24, 15),
(32, 2, 16, 'accepte', 20, 1, 4, 15, 10, 40, 7, -30, 16);

-- 8️⃣ TOUR (فيه foreign key: tournoi_id)
INSERT INTO tour (id, tournoi_id, nom, ordre, type_tour, matchs_prevus, equipes_requises, date_debut, date_fin, statut) VALUES
-- Coupe du Trône 2024
(1, 1, 'Huitièmes de finale', 1, 'elimination', 8, 16, '2024-01-15', '2024-02-10', 'termine'),
(2, 1, 'Quarts de finale', 2, 'elimination', 4, 8, '2024-02-20', '2024-03-15', 'termine'),
(3, 1, 'Demi-finales', 3, 'elimination', 2, 4, '2024-04-01', '2024-04-20', 'termine'),
(4, 1, 'Petite finale', 4, 'petite_finale', 1, 2, '2024-05-25', '2024-05-25', 'termine'),
(5, 1, 'Finale', 5, 'finale', 1, 2, '2024-05-30', '2024-05-30', 'termine'),

-- Championnat 2024
(6, 2, 'Phase aller', 1, 'poule', 120, 16, '2024-08-01', '2024-12-31', 'termine'),
(7, 2, 'Phase retour', 2, 'poule', 120, 16, '2025-01-15', '2025-05-31', 'en_cours');

-- 9️⃣ MATCH (فيه foreign keys: tournoi_id, tour_id, equipe_domicile_id, equipe_exterieur_id, vainqueur_id, perdant_id, terrain_id)
INSERT INTO `match` (id, tournoi_id, tour_id, equipe_domicile_id, equipe_exterieur_id, type_match, type_confrontation, nom_tour, score_domicile, score_exterieur, vainqueur_id, perdant_id, est_nul, date_match, terrain_id, arbitre_principal, statut) VALUES
-- Huitièmes de finale - Coupe du Trône 2024
(1, 1, 1, 1, 16, 'elimination', 'simple', 'Huitièmes', 3, 1, 1, 16, FALSE, '2024-01-15 20:00:00', 1, 'Redouane Jiyed', 'termine'),
(2, 1, 1, 2, 15, 'elimination', 'simple', 'Huitièmes', 2, 0, 2, 15, FALSE, '2024-01-16 20:00:00', 1, 'Samir Guezzaz', 'termine'),
(3, 1, 1, 3, 14, 'elimination', 'simple', 'Huitièmes', 2, 1, 3, 14, FALSE, '2024-01-20 20:00:00', 2, 'Noureddine El Jaafari', 'termine'),
(4, 1, 1, 4, 13, 'elimination', 'simple', 'Huitièmes', 1, 2, 13, 4, FALSE, '2024-01-21 20:00:00', 4, 'Mustapha Ghorbal', 'termine'),
(5, 1, 1, 5, 12, 'elimination', 'simple', 'Huitièmes', 2, 0, 5, 12, FALSE, '2024-02-05 20:00:00', 5, 'Hicham Tiazi', 'termine'),
(6, 1, 1, 6, 11, 'elimination', 'simple', 'Huitièmes', 1, 1, 6, 11, FALSE, '2024-02-06 20:00:00', 4, 'Redouane Jiyed', 'termine'),
(7, 1, 1, 7, 10, 'elimination', 'simple', 'Huitièmes', 2, 0, 7, 10, FALSE, '2024-02-08 20:00:00', 2, 'Samir Guezzaz', 'termine'),
(8, 1, 1, 8, 9, 'elimination', 'simple', 'Huitièmes', 2, 1, 8, 9, FALSE, '2024-02-10 20:00:00', 1, 'Noureddine El Jaafari', 'termine'),

-- Quarts de finale
(9, 1, 2, 1, 8, 'elimination', 'simple', 'Quarts', 3, 1, 1, 8, FALSE, '2024-02-20 20:00:00', 1, 'Mustapha Ghorbal', 'termine'),
(10, 1, 2, 2, 7, 'elimination', 'simple', 'Quarts', 2, 1, 2, 7, FALSE, '2024-02-25 20:00:00', 1, 'Hicham Tiazi', 'termine'),
(11, 1, 2, 3, 6, 'elimination', 'simple', 'Quarts', 2, 1, 3, 6, FALSE, '2024-03-10 20:00:00', 2, 'Redouane Jiyed', 'termine'),
(12, 1, 2, 13, 5, 'elimination', 'simple', 'Quarts', 1, 2, 5, 13, FALSE, '2024-03-15 20:00:00', 5, 'Samir Guezzaz', 'termine'),

-- Demi-finales
(13, 1, 3, 1, 5, 'elimination', 'simple', 'Demi-finale', 3, 1, 1, 5, FALSE, '2024-04-01 20:00:00', 1, 'Noureddine El Jaafari', 'termine'),
(14, 1, 3, 2, 3, 'elimination', 'simple', 'Demi-finale', 1, 1, 2, 3, FALSE, '2024-04-20 20:00:00', 2, 'Mustapha Ghorbal', 'termine'),

-- Petite finale
(15, 1, 4, 5, 3, 'petite_finale', 'simple', 'Petite finale', 2, 1, 5, 3, FALSE, '2024-05-25 20:00:00', 2, 'Hicham Tiazi', 'termine'),

-- Finale
(16, 1, 5, 1, 2, 'finale', 'simple', 'Finale', 2, 1, 1, 2, FALSE, '2024-05-30 20:00:00', 1, 'Redouane Jiyed', 'termine'),

-- Quelques matchs du Championnat 2024 (Phase aller)
(17, 2, 6, 1, 2, 'poule', 'simple', 'Journée 1', 2, 1, 1, 2, FALSE, '2024-08-05 20:00:00', 1, 'Samir Guezzaz', 'termine'),
(18, 2, 6, 3, 4, 'poule', 'simple', 'Journée 1', 1, 1, NULL, NULL, TRUE, '2024-08-05 18:00:00', 2, 'Noureddine El Jaafari', 'termine'),
(19, 2, 6, 5, 6, 'poule', 'simple', 'Journée 1', 2, 0, 5, 6, FALSE, '2024-08-06 20:00:00', 5, 'Mustapha Ghorbal', 'termine'),
(20, 2, 6, 7, 8, 'poule', 'simple', 'Journée 1', 1, 1, NULL, NULL, TRUE, '2024-08-06 18:00:00', 2, 'Hicham Tiazi', 'termine'),
(21, 2, 6, 2, 3, 'poule', 'simple', 'Journée 2', 2, 0, 2, 3, FALSE, '2024-08-12 20:00:00', 1, 'Redouane Jiyed', 'termine'),
(22, 2, 6, 4, 5, 'poule', 'simple', 'Journée 2', 1, 2, 5, 4, FALSE, '2024-08-12 18:00:00', 4, 'Samir Guezzaz', 'termine'),
(23, 2, 6, 1, 6, 'poule', 'simple', 'Journée 3', 3, 0, 1, 6, FALSE, '2024-08-19 20:00:00', 1, 'Noureddine El Jaafari', 'termine'),
(24, 2, 6, 7, 2, 'poule', 'simple', 'Journée 3', 0, 1, 2, 7, FALSE, '2024-08-19 18:00:00', 2, 'Mustapha Ghorbal', 'termine');

-- 🔟 CONTRAT_JOUEUR (فيه foreign keys: joueur_id, equipe_id)
INSERT INTO contrat_joueur (id, joueur_id, equipe_id, date_debut, date_fin, type_contrat, salaire_annuel, prime_signature, prime_performance, clause_liberatoire, statut) VALUES
(1, 1, 1, '2022-07-01', '2025-06-30', 'professionnel', 420000.00, 50000.00, 30000.00, 1000000.00, 'actif'),
(2, 2, 1, '2021-07-01', '2024-06-30', 'professionnel', 540000.00, 70000.00, 40000.00, 1500000.00, 'actif'),
(3, 3, 1, '2023-01-15', '2026-06-30', 'professionnel', 600000.00, 100000.00, 50000.00, 2000000.00, 'actif'),
(4, 4, 1, '2022-07-01', '2025-06-30', 'professionnel', 456000.00, 60000.00, 35000.00, 1200000.00, 'actif'),
(5, 5, 2, '2020-07-01', '2024-06-30', 'professionnel', 480000.00, 80000.00, 45000.00, 1300000.00, 'actif'),
(6, 6, 2, '2019-07-01', '2024-06-30', 'professionnel', 660000.00, 90000.00, 55000.00, 2200000.00, 'actif'),
(7, 7, 2, '2022-07-01', '2024-06-30', 'professionnel', 576000.00, 75000.00, 42000.00, 1600000.00, 'actif'),
(8, 8, 2, '2021-07-01', '2025-06-30', 'professionnel', 504000.00, 65000.00, 38000.00, 1400000.00, 'actif'),
(9, 9, 3, '2023-07-01', '2026-06-30', 'professionnel', 432000.00, 55000.00, 32000.00, 1100000.00, 'actif'),
(10, 10, 3, '2022-07-01', '2025-06-30', 'professionnel', 456000.00, 60000.00, 34000.00, 1200000.00, 'actif'),
(11, 11, 3, '2021-07-01', '2024-06-30', 'professionnel', 480000.00, 65000.00, 36000.00, 1300000.00, 'actif'),
(12, 12, 3, '2023-07-01', '2025-06-30', 'professionnel', 720000.00, 120000.00, 60000.00, 2500000.00, 'actif'),
(13, 13, 4, '2022-07-01', '2025-06-30', 'professionnel', 384000.00, 48000.00, 28000.00, 950000.00, 'actif'),
(14, 14, 4, '2021-07-01', '2024-06-30', 'professionnel', 408000.00, 52000.00, 30000.00, 1000000.00, 'actif'),
(15, 15, 4, '2023-01-10', '2025-06-30', 'professionnel', 456000.00, 58000.00, 33000.00, 1150000.00, 'actif'),
(16, 16, 4, '2022-07-01', '2025-06-30', 'professionnel', 336000.00, 42000.00, 25000.00, 800000.00, 'actif');