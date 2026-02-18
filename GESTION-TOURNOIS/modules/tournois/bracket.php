<?php
// modules/tournois/bracket.php - Génération bracket élimination

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../includes/lib/auth.php';

class BracketGenerator {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Générer bracket pour tournoi élimination directe
     */
    public function generateBracket($tournoiId) {
        // Récupérer les équipes inscrites
        $equipes = $this->db->fetchAll(
            "SELECT e.* FROM inscription_tournoi i 
             JOIN equipe e ON i.equipe_id = e.id 
             WHERE i.tournoi_id = ? AND i.statut = 'accepte'
             ORDER BY RAND()",
            [$tournoiId]
        );
        
        $nbEquipes = count($equipes);
        $tournoi = $this->db->fetchOne("SELECT * FROM tournoi WHERE id = ?", [$tournoiId]);
        
        if ($tournoi['type_tournoi'] !== 'elimination') {
            return ['error' => 'Ce type de tournoi ne supporte pas le bracket simple'];
        }
        
        // Calculer la puissance de 2 supérieure
        $puissance = 2;
        while ($puissance < $nbEquipes) {
            $puissance *= 2;
        }
        
        $nbByes = $puissance - $nbEquipes;
        
        // Créer les tours
        $this->createRounds($tournoiId, $puissance);
        
        // Générer matchs premier tour
        return $this->generateFirstRound($tournoiId, $equipes, $nbByes);
    }
    
    /**
     * Créer les tours dans la base
     */
    private function createRounds($tournoiId, $nbEquipes) {
        $tours = [];
        $matchsPerRound = $nbEquipes / 2;
        $roundNum = 1;
        
        while ($matchsPerRound >= 1) {
            $nomTour = $this->getRoundName($matchsPerRound * 2);
            
            $this->db->execute(
                "INSERT INTO tour (tournoi_id, nom, ordre, type_tour, matchs_prevus, equipes_requises, statut) 
                 VALUES (?, ?, ?, 'elimination', ?, ?, 'a_venir')",
                [$tournoiId, $nomTour, $roundNum, $matchsPerRound, $matchsPerRound * 2]
            );
            
            $roundNum++;
            $matchsPerRound /= 2;
        }
    }
    
    private function getRoundName($nbEquipes) {
        $names = [
            2 => 'Finale',
            4 => 'Demi-finales',
            8 => 'Quarts de finale',
            16 => 'Huitièmes de finale',
            32 => 'Seizièmes de finale',
            64 => 'Trente-deuxièmes de finale',
            128 => 'Soixante-quatrièmes de finale'
        ];
        return $names[$nbEquipes] ?? "Tour $nbEquipes";
    }

    /**
     * Générer les matchs du premier tour
     */
    private function generateFirstRound($tournoiId, $equipes, $nbByes) {
        // Récupérer l'ID du premier tour
        $tourId = $this->db->fetchColumn(
            "SELECT id FROM tour WHERE tournoi_id = ? AND ordre = 1", 
            [$tournoiId]
        );
        
        if (!$tourId) {
            return ['error' => 'Impossible de trouver le premier tour'];
        }

        // Calculer le nombre d'équipes qui jouent au premier tour
        // Les équipes "Bye" sautent ce tour
        $nbJoueursT1 = count($equipes) - $nbByes;
        $equipesT1 = array_slice($equipes, 0, $nbJoueursT1);

        // Créer les matchs
        for ($i = 0; $i < count($equipesT1); $i += 2) {
            if (isset($equipesT1[$i+1])) {
                $sql = "INSERT INTO `match` (tournoi_id, tour_id, equipe_domicile_id, equipe_exterieur_id, date_match, statut) 
                        VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), 'a_venir')";
                $this->db->execute($sql, [$tournoiId, $tourId, $equipesT1[$i]['id'], $equipesT1[$i+1]['id']]);
            }
        }

        return ['success' => true];
    }
}