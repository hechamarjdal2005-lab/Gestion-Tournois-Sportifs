<?php
/**
 * ==========================================
 * BACKUP & RESTORE SYSTEM
 * ==========================================
 * Système de sauvegarde et restauration de la DB
 * @author Étudiant 1 - Backend Database
 */

require_once __DIR__ . '/../../includes/config/database.php';

class BackupManager {
    private $db;
    private $backupDir;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->backupDir = __DIR__ . '/';
        
        // Créer le dossier backup s'il n'existe pas
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Créer une sauvegarde complète de la base de données
     */
    public function createBackup($filename = null) {
        try {
            // Générer le nom du fichier
            if ($filename === null) {
                $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            }
            
            $filepath = $this->backupDir . $filename;
            
            // Ouvrir le fichier
            $handle = fopen($filepath, 'w+');
            if (!$handle) {
                throw new Exception("Impossible de créer le fichier de sauvegarde");
            }
            
            // Header du fichier
            $this->writeHeader($handle);
            
            // Obtenir toutes les tables
            $tables = $this->getTables();
            
            // Sauvegarder chaque table
            foreach ($tables as $table) {
                $this->backupTable($handle, $table);
            }
            
            // Footer
            $this->writeFooter($handle);
            
            fclose($handle);
            
            // Obtenir la taille du fichier
            $filesize = filesize($filepath);
            
            return [
                'status' => 'success',
                'message' => 'Sauvegarde créée avec succès',
                'filename' => $filename,
                'filepath' => $filepath,
                'filesize' => $this->formatBytes($filesize),
                'tables_count' => count($tables)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Écrire l'en-tête du fichier SQL
     */
    private function writeHeader($handle) {
        $header = "-- ==========================================\n";
        $header .= "-- Backup Database: " . DB_NAME . "\n";
        $header .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $header .= "-- ==========================================\n\n";
        $header .= "SET NAMES utf8mb4;\n";
        $header .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $header .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";
        
        fwrite($handle, $header);
    }
    
    /**
     * Écrire le pied de page
     */
    private function writeFooter($handle) {
        $footer = "\n-- ==========================================\n";
        $footer .= "-- Backup completed\n";
        $footer .= "-- ==========================================\n";
        $footer .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        fwrite($handle, $footer);
    }
    
    /**
     * Obtenir la liste des tables
     */
    private function getTables() {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Sauvegarder une table
     */
    private function backupTable($handle, $tableName) {
        $pdo = $this->db->getConnection();
        
        // Structure de la table
        fwrite($handle, "\n-- ==========================================\n");
        fwrite($handle, "-- Table: $tableName\n");
        fwrite($handle, "-- ==========================================\n\n");
        
        // DROP TABLE
        fwrite($handle, "DROP TABLE IF EXISTS `$tableName`;\n\n");
        
        // CREATE TABLE
        $stmt = $pdo->query("SHOW CREATE TABLE `$tableName`");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        fwrite($handle, $row[1] . ";\n\n");
        
        // Données de la table
        $stmt = $pdo->query("SELECT * FROM `$tableName`");
        $rowCount = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($rowCount === 0) {
                fwrite($handle, "INSERT INTO `$tableName` VALUES\n");
            }
            
            $values = array_map(function($value) use ($pdo) {
                if ($value === null) {
                    return 'NULL';
                }
                return $pdo->quote($value);
            }, array_values($row));
            
            $line = "(" . implode(", ", $values) . ")";
            
            if ($rowCount > 0) {
                fwrite($handle, ",\n" . $line);
            } else {
                fwrite($handle, $line);
            }
            
            $rowCount++;
        }
        
        if ($rowCount > 0) {
            fwrite($handle, ";\n\n");
        }
    }
    
    /**
     * Restaurer une sauvegarde
     */
    public function restoreBackup($filename) {
        try {
            $filepath = $this->backupDir . $filename;
            
            if (!file_exists($filepath)) {
                throw new Exception("Fichier de sauvegarde introuvable");
            }
            
            // Lire le fichier SQL
            $sql = file_get_contents($filepath);
            
            if (empty($sql)) {
                throw new Exception("Le fichier de sauvegarde est vide");
            }
            
            // Exécuter le SQL
            $pdo = $this->db->getConnection();
            $pdo->exec($sql);
            
            return [
                'status' => 'success',
                'message' => 'Base de données restaurée avec succès',
                'filename' => $filename
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Lister toutes les sauvegardes disponibles
     */
    public function listBackups() {
        $backups = [];
        $files = glob($this->backupDir . '*.sql');
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // Trier par date (plus récent en premier)
        usort($backups, function($a, $b) {
            return filemtime($b['filepath']) - filemtime($a['filepath']);
        });
        
        return $backups;
    }
    
    /**
     * Supprimer une sauvegarde
     */
    public function deleteBackup($filename) {
        try {
            $filepath = $this->backupDir . $filename;
            
            if (!file_exists($filepath)) {
                throw new Exception("Fichier introuvable");
            }
            
            if (unlink($filepath)) {
                return [
                    'status' => 'success',
                    'message' => 'Sauvegarde supprimée avec succès'
                ];
            } else {
                throw new Exception("Impossible de supprimer le fichier");
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Sauvegarder uniquement une table spécifique
     */
    public function backupTable_single($tableName, $filename = null) {
        try {
            if ($filename === null) {
                $filename = 'backup_' . $tableName . '_' . date('Y-m-d_H-i-s') . '.sql';
            }
            
            $filepath = $this->backupDir . $filename;
            $handle = fopen($filepath, 'w+');
            
            if (!$handle) {
                throw new Exception("Impossible de créer le fichier");
            }
            
            $this->writeHeader($handle);
            $this->backupTable($handle, $tableName);
            $this->writeFooter($handle);
            
            fclose($handle);
            
            return [
                'status' => 'success',
                'message' => "Table $tableName sauvegardée",
                'filename' => $filename
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Nettoyer les anciennes sauvegardes (garder les N dernières)
     */
    public function cleanOldBackups($keepLast = 10) {
        try {
            $backups = $this->listBackups();
            $deleted = 0;
            
            if (count($backups) > $keepLast) {
                $toDelete = array_slice($backups, $keepLast);
                
                foreach ($toDelete as $backup) {
                    if (unlink($backup['filepath'])) {
                        $deleted++;
                    }
                }
            }
            
            return [
                'status' => 'success',
                'message' => "$deleted sauvegardes supprimées",
                'deleted_count' => $deleted
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Formater la taille en octets
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

/**
 * Fonction helper
 */
function getBackupManager() {
    return new BackupManager();
}

// ==========================================
// CLI Usage (si appelé directement)
// ==========================================
if (php_sapi_name() === 'cli' && basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $backup = new BackupManager();
    
    $action = $argv[1] ?? 'create';
    
    switch ($action) {
        case 'create':
            $result = $backup->createBackup();
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'list':
            $result = $backup->listBackups();
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'restore':
            if (!isset($argv[2])) {
                echo "Usage: php backup.php restore <filename>\n";
                exit(1);
            }
            $result = $backup->restoreBackup($argv[2]);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'clean':
            $keep = $argv[2] ?? 10;
            $result = $backup->cleanOldBackups($keep);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        default:
            echo "Actions disponibles: create, list, restore, clean\n";
    }
}