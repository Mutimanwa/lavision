<?php
/**
 * Modèle d'administration
 * Gestion des utilisateurs, paramètres système, logs, sauvegardes
 */
require_once __DIR__ . '/../Services/database.php';
class AdminModel
{
    private $db;

    public function __construct()
    {
        $this->db = get_db_connection();
    }

    /**
     * Récupère la liste des utilisateurs avec pagination
     */
    public function getUtilisateurs($page = 1, $search = '', $role = '', $statut = '')
    {
        $limit = 10;

        // Sécurisation stricte
        $page = (int) $page;
        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(u.identifiant LIKE ? OR u.email LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)";
            $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
        }

        if ($role) {
            $where[] = "u.role = ?";
            $params[] = $role;
        }

        if ($statut) {
            $where[] = "u.statut = ?";
            $params[] = $statut;
        }

        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

        // Compter le total
        $countQuery = "SELECT COUNT(*) as total FROM user_admins u $whereClause";
        $total = db_query_one($countQuery, $params)['total'];

        // Récupérer les utilisateurs
        $query = "SELECT u.*, COUNT(l.log_id) as nb_actions
                  FROM user_admins u
                  LEFT JOIN logs l ON u.user_id = l.user_id
                  $whereClause
                  GROUP BY u.user_id
                  ORDER BY u.date_creation DESC
                  LIMIT $limit OFFSET $offset";

        $utilisateurs = db_query($query, $params);

        return [
            'utilisateurs' => $utilisateurs,
            'pagination' => [
                'page' => $page,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'limit' => $limit
            ]
        ];
    }

    /**
     * Récupère tous les rôles disponibles
     */
    public function getRoles()
    {
        return [
            'superadmin' => 'Super Administrateur',
            'admin' => 'Administrateur',
            'secretaire' => 'Secrétaire',
            'gestionnaire' => 'Gestionnaire',
            'proviseur' => 'Proviseur'
        ];
    }

    /**
     * Récupère tous les paramètres système
     */
    public function getParametres()
    {
        $query = "SELECT * FROM parametres ORDER BY categorie, cle";
        // $parametres = db_execute($query);
        $parametres = db_query($query);

        // Grouper par catégorie
        $grouped = [];
        foreach ($parametres as $param) {
            $grouped[$param['categorie']][] = $param;
        }

        return $grouped;
    }

    /**
     * Met à jour les paramètres système
     */
    public function updateParametres($parametres)
    {
        db_begin_transaction();

        try {
            foreach ($parametres as $cle => $valeur) {
                $query = "UPDATE parametres SET valeur = ?, date_modif = NOW() WHERE cle = ?";
                db_execute($query, [$valeur, $cle]);
            }

            db_commit();
            return true;
        } catch (Exception $e) {
            db_rollback();
            return false;
        }
    }

    /**
     * Récupère toutes les années scolaires
     */
    public function getAnneesScolaires()
    {
        $query = "SELECT * FROM annees_scolaire ORDER BY date_debut DESC";
        return db_query($query);
    }

    /**
     * Crée une nouvelle année scolaire
     */
    public function creerAnneeScolaire($data)
    {
        $query = "INSERT INTO annees_scolaire (annee_libelle, date_debut, date_fin, statut)
                  VALUES (?, ?, ?, ?)";

        return db_execute($query, [
            $data['annee_libelle'],
            $data['date_debut'],
            $data['date_fin'],
            $data['statut']
        ]);
    }

    /**
     * Modifie une année scolaire
     */
    public function modifierAnneeScolaire($annee_id, $data)
    {
        $query = "UPDATE annees_scolaire
                  SET annee_libelle = ?, date_debut = ?, date_fin = ?, statut = ?
                  WHERE annee_id = ?";
        return db_execute($query, [
            $data['annee_libelle'],
            $data['date_debut'],
            $data['date_fin'],
            $data['statut'],
            $annee_id
        ]);
    }

    /**
     * Active une année scolaire (désactive les autres)
     */
    public function activerAnneeScolaire($annee_id)
    {
        $this->db->beginTransaction();

        try {
            // Désactiver toutes les années
            db_execute("UPDATE annees_scolaire SET statut = 'inactive'");

            // Activer l'année sélectionnée
            db_execute("UPDATE annees_scolaire SET statut = 'active' WHERE annee_id = ?", [$annee_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Récupère les logs système avec pagination
     */
    public function getLogs($page = 1, $niveau = '', $categorie = '', $date_debut = '', $date_fin = '')
    {
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if ($niveau) {
            $where[] = "niveau_log = ?";
            $params[] = $niveau;
        }

        if ($categorie) {
            $where[] = "categorie = ?";
            $params[] = $categorie;
        }

        if ($date_debut) {
            $where[] = "DATE(date_action) >= ?";
            $params[] = $date_debut;
        }

        if ($date_fin) {
            $where[] = "DATE(date_action) <= ?";
            $params[] = $date_fin;
        }

        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

        // Compter le total
        $countQuery = "SELECT COUNT(*) as total FROM logs $whereClause";
        $total = db_query($countQuery, $params)['total'];

        // Récupérer les logs
        $query = "SELECT l.*, u.identifiant, u.nom, u.prenom
                  FROM logs l
                  LEFT JOIN user_admins u ON l.user_id = u.user_id
                  $whereClause
                  ORDER BY l.date_action DESC
                  LIMIT $limit OFFSET $offset";

        $logs = db_query($query, $params);

        return [
            'logs' => $logs,
            'pagination' => [
                'page' => $page,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'limit' => $limit
            ]
        ];
    }

    /**
     * Récupère les niveaux de log disponibles
     */
    public function getNiveauxLog()
    {
        return ['info', 'warning', 'error', 'security'];
    }

    /**
     * Récupère les catégories de log disponibles
     */
    public function getCategoriesLog()
    {
        $query = "SELECT DISTINCT categorie FROM logs ORDER BY categorie";
        $result = db_query($query);
        return array_column($result, 'categorie');
    }

    /**
     * Récupère l'historique des sauvegardes
     */
    public function getBackupLogs()
    {
        $query = "SELECT * FROM backup_logs ORDER BY date_execution DESC LIMIT 50";
        return db_query($query);
    }

    /**
     * Récupère les sauvegardes disponibles pour restauration
     */
    public function getAvailableBackups()
    {
        $query = "SELECT * FROM backup_logs
                  WHERE statut = 'success'
                  ORDER BY date_execution DESC LIMIT 20";
        return db_query($query);
    }

public function creerSauvegarde($type)
{
    // Insérer le log de sauvegarde
    $query = "INSERT INTO backup_logs (type_backup, fichier, statut, execute_par)
              VALUES (?, ?, 'pending', ?)";

    $timestamp = date('Y-m-d_H-i-s');
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Nom de fichier selon le type
    switch($type) {
        case 'complete':
            $fichier = 'backup_complete_' . $timestamp . '.zip';
            break;
        case 'database':
            $fichier = 'backup_database_' . $timestamp . '.zip';
            break;
        case 'files':
            $fichier = 'backup_files_' . $timestamp . '.zip';
            break;
        default:
            $fichier = 'backup_' . $timestamp . '.zip';
    }

    if (db_execute($query, [$type, $fichier, $user_id])) {
        $backup_id = $this->db->lastInsertId();
        
        try {
            // Créer le dossier de sauvegarde s'il n'existe pas
            $backupDir = BACKUPS_PATH;
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $cheminFichier = $backupDir . $fichier;
            
            // Exécuter la sauvegarde selon le type
            switch($type) {
                case 'complete':
                    $resultat = $this->creerSauvegardeComplete($cheminFichier);
                    break;
                    
                case 'database':
                    $resultat = $this->creerSauvegardeBaseDeDonnees($cheminFichier);
                    break;
                    
                case 'files':
                    $resultat = $this->creerSauvegardeFichiers($cheminFichier);
                    break;
                    
                default:
                    throw new Exception("Type de sauvegarde inconnu: " . $type);
            }
            
            if ($resultat['success']) {
                db_execute(
                    "UPDATE backup_logs SET statut = 'success', fichier = ? , taille = ?  WHERE backup_id = ?",
                    [
                        $resultat['chemin'],
                        $resultat['taille'],
                        $backup_id
                    ]
                );
                
                $this->nettoyerAnciennesSauvegardes($backupDir);
                return true;
            } else {
                throw new Exception($resultat['erreur']);
            }
            
        } catch (Exception $e) {
            db_execute(
                "UPDATE backup_logs SET statut = 'failed', erreur_message = ? WHERE backup_id = ?",
                [$e->getMessage(), $backup_id]
            );
            
            if (isset($cheminFichier) && file_exists($cheminFichier)) {
                unlink($cheminFichier);
            }
            
            error_log("Erreur sauvegarde: " . $e->getMessage());
            return false;
        }
    }

    return false;
}

private function sauvegarderBaseDeDonnees($cheminFichier)
{
    try {
        // 1. Vérifier si mysqli est disponible
        if (!function_exists('mysqli_connect')) {
            throw new Exception("Extension MySQLi non disponible");
        }
        
        // 2. Vérifier les informations de connexion
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
            throw new Exception("Configuration de la base de données manquante");
        }
        
        // 3. Tester la connexion à la base de données
        $testConn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$testConn) {
            $error = mysqli_connect_error();
            throw new Exception("Impossible de se connecter à MySQL: " . $error);
        }
        mysqli_close($testConn);
        
        // 4. Préparer la commande mysqldump (version sécurisée)
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s --routines --events --triggers --single-transaction --skip-comments 2>&1',
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_NAME)
        );
        
        // 5. Exécuter la commande et capturer la sortie
        $output = [];
        $returnCode = 0;
        
        // Écrire directement dans le fichier
        $fullCommand = $command . ' > ' . escapeshellarg($cheminFichier);
        exec($fullCommand, $output, $returnCode);
        
        // 6. Vérifier le résultat
        if ($returnCode !== 0) {
            $errorMsg = implode("\n", $output);
            error_log("mysqldump error: " . $errorMsg);
            
            // Tentative alternative avec PDO si mysqldump échoue
            return $this->sauvegarderBaseDeDonneesPDO($cheminFichier);
        }
        
        // 7. Vérifier que le fichier a été créé et n'est pas vide
        if (!file_exists($cheminFichier) || filesize($cheminFichier) === 0) {
            throw new Exception("Fichier de sauvegarde vide ou non créé");
        }
        
        $taille = filesize($cheminFichier);
        
        // 8. Obtenir des informations sur la base de données
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Obtenir la taille de la base de données
        $stmt = $pdo->query("
            SELECT SUM(data_length + index_length) as size
            FROM information_schema.TABLES 
            WHERE table_schema = '" . DB_NAME . "'
        ");
        $dbSize = $stmt->fetch(PDO::FETCH_ASSOC)['size'] ?? 0;
        
        return [
            'success' => true,
            'taille' => $taille,
            'chemin' => $cheminFichier,
            'details' => [
                'table_count' => count($tables),
                'database_name' => DB_NAME,
                'database_size' => $dbSize,
                'tables' => $tables
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'erreur' => $e->getMessage()
        ];
    }
}

// Méthode alternative utilisant PDO si mysqldump n'est pas disponible
private function sauvegarderBaseDeDonneesPDO($cheminFichier)
{
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Désactiver les clés étrangères temporairement
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        
        // Récupérer toutes les tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $sqlContent = "";
        
        // Entête du fichier SQL
        $sqlContent .= "-- Backup créé le: " . date('Y-m-d H:i:s') . "\n";
        $sqlContent .= "-- Base de données: " . DB_NAME . "\n";
        $sqlContent .= "SET NAMES utf8mb4;\n";
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            // 1. Structure de la table
            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            $sqlContent .= "--\n-- Structure de la table `{$table}`\n--\n";
            $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlContent .= $createTable['Create Table'] . ";\n\n";
            
            // 2. Données de la table
            $sqlContent .= "--\n-- Données de la table `{$table}`\n--\n";
            
            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';
                
                foreach ($rows as $row) {
                    // Échapper les valeurs
                    $escapedValues = array_map(function($value) use ($pdo) {
                        if ($value === null) return 'NULL';
                        return $pdo->quote($value);
                    }, $row);
                    
                    $sqlContent .= "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $escapedValues) . ");\n";
                }
                $sqlContent .= "\n";
            }
        }
        
        // Restaurer les clés étrangères
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Écrire dans le fichier
        file_put_contents($cheminFichier, $sqlContent);
        
        // Vérifier que le fichier a été créé
        if (!file_exists($cheminFichier)) {
            throw new Exception("Impossible de créer le fichier de sauvegarde");
        }
        
        $taille = filesize($cheminFichier);
        
        return [
            'success' => true,
            'taille' => $taille,
            'chemin' => $cheminFichier,
            'details' => [
                'table_count' => count($tables),
                'database_name' => DB_NAME,
                'method' => 'pdo_backup',
                'tables' => $tables
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'erreur' => "Backup PDO échoué: " . $e->getMessage()
        ];
    }
}

private function creerSauvegardeComplete($cheminDestination)
{
    $details = [];
    
    // Créer un fichier temporaire pour la base de données
    $tempSqlFile = sys_get_temp_dir() . '/backup_temp_' . uniqid() . '.sql';
    
    // Sauvegarde de la base de données
    $resultDb = $this->sauvegarderBaseDeDonnees($tempSqlFile);
    
    if (!$resultDb['success']) {
        return ['success' => false, 'erreur' => 'Échec sauvegarde BD: ' . $resultDb['erreur']];
    }
    
    $details['database'] = $resultDb['details'];
    
    // Créer l'archive ZIP
    $zip = new ZipArchive();
    if ($zip->open($cheminDestination, ZipArchive::CREATE) !== TRUE) {
        return ['success' => false, 'erreur' => 'Impossible de créer l\'archive ZIP'];
    }
    
    // Ajouter le dump SQL
    $zip->addFile($tempSqlFile, 'database_backup.sql');
    
    // Sauvegarde des fichiers
    $projectRoot = realpath(BACKUPS_PATH); // Ajuster selon votre structure
    $fileCount = $this->ajouterFichiersAuZip($zip, $projectRoot, $details);
    $details['total_files'] = $fileCount;
    
    // Ajouter un fichier info
    $infoContent = "SAUVEGARDE COMPLÈTE\n";
    $infoContent .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $infoContent .= "Base de données: " . DB_NAME . "\n";
    $infoContent .= "Tables: " . count($details['database']['tables'] ?? []) . "\n";
    $infoContent .= "Fichiers inclus: " . $fileCount . "\n";
    $infoContent .= "Taille BD: " . $this->formatBytes($resultDb['taille']) . "\n";
    $zip->addFromString('README.txt', $infoContent);
    
    $zip->close();
    
    // Nettoyer
    if (file_exists($tempSqlFile)) {
        unlink($tempSqlFile);
    }
    
    $taille = filesize($cheminDestination);
    $details['total_size'] = $taille;
    
    return [
        'success' => true,
        'taille' => $taille,
        'chemin' => $cheminDestination,
        'details' => $details
    ];
}

private function creerSauvegardeBaseDeDonnees($cheminDestination)
{
    $tempSqlFile = sys_get_temp_dir() . '/backup_db_' . uniqid() . '.sql';
    $result = $this->sauvegarderBaseDeDonnees($tempSqlFile);
    
    if ($result['success']) {
        // Créer le ZIP
        $zip = new ZipArchive();
        if ($zip->open($cheminDestination, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($tempSqlFile, basename($tempSqlFile));
            
            // Ajouter des métadonnées
            $info = "Backup Database: " . DB_NAME . "\n";
            $info .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $info .= "Tables: " . ($result['details']['table_count'] ?? 0) . "\n";
            $zip->addFromString('database_info.txt', $info);
            
            $zip->close();
            
            // Nettoyer
            if (file_exists($tempSqlFile)) {
                unlink($tempSqlFile);
            }
            
            $taille = filesize($cheminDestination);
            $result['taille'] = $taille;
            $result['chemin'] = $cheminDestination;
        }
    }
    
    return $result;
}

// Fonction utilitaire pour formater les tailles
private function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}


    private function creerSauvegardeFichiers($cheminDestination)
    {
        $details = ['files' => []];

        $zip = new ZipArchive();
        if ($zip->open($cheminDestination, ZipArchive::CREATE) !== TRUE) {
            return ['success' => false, 'erreur' => 'Impossible de créer l\'archive ZIP'];
        }

        // Définir le répertoire racine du projet
        $projectRoot = BACKUPS_PATH ; // Ajuster selon votre structure

        // Ajouter tous les fichiers
        $fileCount = $this->ajouterFichiersAuZip($zip, $projectRoot, $details['files']);

        // Ajouter un fichier info
        $infoContent = "Sauvegarde fichiers - " . date('Y-m-d H:i:s') . "\n";
        $infoContent .= "Répertoire racine: " . realpath($projectRoot) . "\n";
        $infoContent .= "Nombre de fichiers: " . $fileCount . "\n";
        $infoContent .= "Exclusions: backups/, node_modules/, vendor/, .git/, tmp/\n";
        $zip->addFromString('backup_info.txt', $infoContent);

        $zip->close();

        $taille = filesize($cheminDestination);
        $details['total_files'] = $fileCount;

        return [
            'success' => true,
            'taille' => $taille,
            'chemin' => $cheminDestination,
            'details' => $details
        ];
    }

    private function ajouterFichiersAuZip($zip, $directory, &$details, $relativePath = '')
    {
        $fileCount = 0;

        // Liste des dossiers à exclure
        $exclusions = [
            'backups',
            'node_modules',
            'vendor',
            '.git',
            'tmp',
            'logs',
            '.idea',
            '.vscode'
        ];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getPathname();
            $relativeFilePath = str_replace($directory, '', $filePath);

            // Vérifier les exclusions
            $shouldExclude = false;
            foreach ($exclusions as $exclusion) {
                if (
                    strpos($relativeFilePath, '/' . $exclusion . '/') === 0 ||
                    strpos($relativeFilePath, $exclusion . '/') === 0
                ) {
                    $shouldExclude = true;
                    break;
                }
            }

            if (!$shouldExclude && $file->isFile()) {
                $zip->addFile($filePath, ltrim($relativeFilePath, '/'));
                $fileCount++;

                // Enregistrer les détails des fichiers importants
                if (in_array(pathinfo($filePath, PATHINFO_EXTENSION), ['php', 'js', 'css', 'html'])) {
                    $details[] = [
                        'file' => $relativeFilePath,
                        'size' => $file->getSize(),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime())
                    ];
                }
            }
        }

        return $fileCount;
    }

    private function nettoyerAnciennesSauvegardes($backupDir, $maxBackups = 10)
    {
        $fichiers = glob($backupDir . 'backup_*.{zip,sql}', GLOB_BRACE);

        // Trier par date de modification (plus récent d'abord)
        usort($fichiers, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Supprimer les anciennes sauvegardes au-delà du maximum
        if (count($fichiers) > $maxBackups) {
            for ($i = $maxBackups; $i < count($fichiers); $i++) {
                unlink($fichiers[$i]);
            }
        }
    }
}
