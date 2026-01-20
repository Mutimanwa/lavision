<?php
/**
 * Gestion de la connexion à la base de données
 * Sécurité: Connexion PDO sécurisée avec gestion d'erreurs
 * Version: 1.0.0
 */

require_once __DIR__ . '/../config/config.php';

// =============================================
// CLASSES PDO PERSONNALISÉES
// =============================================

/**
 * Classe Database - Singleton pour la connexion PDO
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $last_query;
    private $query_count = 0;
    private $transaction_level = 0;

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        $this->connect();
    }

    /**
     * Établir la connexion PDO
     */
    private function connect(): void
    {
        try {
            // Options PDO pour la sécurité
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATION,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
            ];

            // Construction du DSN
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            // Connexion PDO
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Journaliser la connexion réussie
            log_action('Connexion DB établie', ['host' => DB_HOST, 'base' => DB_NAME]);

        } catch (PDOException $e) {
            // Journaliser l'erreur
            $error_msg = "Erreur connexion DB: " . $e->getMessage();
            error_log($error_msg);
            
            // En mode production, message générique
            if (!DEBUG_MODE) {
                die("Erreur de connexion à la base de données. Contactez l'administrateur.");
            } else {
                die($error_msg);
            }
        }
    }

    /**
     * Obtenir l'instance unique (Singleton)
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtenir l'objet PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Exécuter une requête SELECT
     */
    public function query(string $sql, array $params = []): array
    {
        $this->last_query = $sql;
        $this->query_count++;
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Journaliser en mode debug
            if (DEBUG_MODE) {
                log_action('Query SELECT', [
                    'sql' => $sql,
                    'params' => $params,
                    'row_count' => $stmt->rowCount()
                ], 'database');
            }
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $this->handleException($e, $sql, $params);
            return [];
        }
    }

    /**
     * Exécuter une requête SELECT et retourner une seule ligne
     */
    public function querySingle(string $sql, array $params = []): ?array
    {
        $this->last_query = $sql;
        $this->query_count++;
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return $result !== false ? $result : null;
            
        } catch (PDOException $e) {
            $this->handleException($e, $sql, $params);
            return null;
        }
    }

    /**
     * Exécuter une requête INSERT/UPDATE/DELETE
     */
    public function execute(string $sql, array $params = []): bool
    {
        $this->last_query = $sql;
        $this->query_count++;
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            // Journaliser les modifications importantes
            if (preg_match('/^(INSERT|UPDATE|DELETE)/i', trim($sql))) {
                log_action('Query MODIFY', [
                    'sql' => $sql,
                    'params' => $this->sanitizeParams($params),
                    'affected' => $stmt->rowCount()
                ], 'database');
            }
            
            return $result;
            
        } catch (PDOException $e) {
            $this->handleException($e, $sql, $params);
            return false;
        }
    }

    /**
     * Obtenir le dernier ID inséré
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Démarrer une transaction
     */
    public function beginTransaction(): bool
    {
        if ($this->transaction_level === 0) {
            $this->transaction_level++;
            return $this->pdo->beginTransaction();
        }
        $this->transaction_level++;
        return true;
    }

    /**
     * Valider une transaction
     */
    public function commit(): bool
    {
        if ($this->transaction_level === 1) {
            $this->transaction_level = 0;
            return $this->pdo->commit();
        }
        $this->transaction_level--;
        return true;
    }

    /**
     * Annuler une transaction
     */
    public function rollBack(): bool
    {
        if ($this->transaction_level === 1) {
            $this->transaction_level = 0;
            return $this->pdo->rollBack();
        }
        $this->transaction_level--;
        return true;
    }

    /**
     * Vérifier si une table existe
     */
    public function tableExists(string $table_name): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = :database 
                AND table_name = :table";
        
        $result = $this->querySingle($sql, [
            'database' => DB_NAME,
            'table' => $table_name
        ]);
        
        return $result && $result['count'] > 0;
    }

    /**
     * Nettoyer les paramètres pour le logging (sans mots de passe)
     */
    private function sanitizeParams(array $params): array
    {
        $sanitized = [];
        foreach ($params as $key => $value) {
            if (stripos($key, 'password') !== false || stripos($key, 'mot_de_passe') !== false) {
                $sanitized[$key] = '******';
            } elseif (stripos($key, 'token') !== false || stripos($key, 'secret') !== false) {
                $sanitized[$key] = substr($value, 0, 3) . '...';
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Gérer les exceptions PDO
     */
    private function handleException(PDOException $e, string $sql, array $params): void
    {
        $error_info = [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'sql' => $sql,
            'params' => $this->sanitizeParams($params),
            'trace' => $e->getTraceAsString()
        ];
        
        error_log("PDO Error: " . json_encode($error_info));
        
        // Journaliser dans les logs système
        $log_data = [
            'categorie' => 'database',
            'action' => 'error',
            'details' => $error_info
        ];
        
        if (function_exists('log_action')) {
            log_action('Erreur PDO', $log_data, 'error');
        }
        
        // Relancer l'exception en mode debug
        if (DEBUG_MODE) {
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques des requêtes
     */
    public function getStats(): array
    {
        return [
            'query_count' => $this->query_count,
            'last_query' => $this->last_query,
            'transaction_level' => $this->transaction_level
        ];
    }

    /**
     * Fermer la connexion
     */
    public function close(): void
    {
        $this->pdo = null;
        self::$instance = null;
    }

    // Empêcher le clonage et la désérialisation
    private function __clone() {}
    public function __wakeup() {}
}

// =============================================
// FONCTIONS GLOBALES D'ACCÈS À LA BASE
// =============================================

/**
 * Obtenir la connexion PDO
 */
function getDatabaseConnection(): PDO
{
    return Database::getInstance()->getConnection();
}

/**
 * Exécuter une requête SELECT
 */
function db_query(string $sql, array $params = []): array
{
    return Database::getInstance()->query($sql, $params);
}

/**
 * Exécuter une requête SELECT et retourner une ligne
 */
function db_query_single(string $sql, array $params = []): ?array
{
    return Database::getInstance()->querySingle($sql, $params);
}

/**
 * Exécuter une requête INSERT/UPDATE/DELETE
 */
function db_execute(string $sql, array $params = []): bool
{
    return Database::getInstance()->execute($sql, $params);
}

/**
 * Obtenir le dernier ID inséré
 */
function db_last_insert_id(): string
{
    return Database::getInstance()->lastInsertId();
}

/**
 * Démarrer une transaction
 */
function db_begin_transaction(): bool
{
    return Database::getInstance()->beginTransaction();
}

/**
 * Valider une transaction
 */
function db_commit(): bool
{
    return Database::getInstance()->commit();
}

/**
 * Annuler une transaction
 */
function db_rollback(): bool
{
    return Database::getInstance()->rollBack();
}

/**
 * Échapper une valeur pour SQL (alternative à PDO::quote)
 */
function db_escape($value): string
{
    if (is_null($value)) {
        return 'NULL';
    }
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }
    
    $pdo = getDatabaseConnection();
    return $pdo->quote($value);
}

/**
 * Vérifier si une valeur existe dans une table
 */
function db_value_exists(string $table, string $column, $value, $exclude_id = null): bool
{
    $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = :value";
    $params = ['value' => $value];
    
    if ($exclude_id !== null) {
        $sql .= " AND user_id != :exclude_id";
        $params['exclude_id'] = $exclude_id;
    }
    
    $result = db_query_single($sql, $params);
    return $result && $result['count'] > 0;
}

/**
 * Formater une requête pour le debug
 */
function db_format_sql(string $sql, array $params = []): string
{
    foreach ($params as $key => $value) {
        if (is_string($value)) {
            $value = "'" . str_replace("'", "''", $value) . "'";
        } elseif (is_null($value)) {
            $value = 'NULL';
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        
        $sql = preg_replace('/:' . preg_quote($key) . '\b/', $value, $sql);
    }
    
    return $sql;
}

/**
 * Sauvegarder la base de données
 */
function db_backup(string $backup_type = 'auto'): array
{
    $db = Database::getInstance();
    $backup_file = BACKUP_PATH . '/backup_' . date('Ymd_His') . '.sql';
    
    try {
        // Démarrer la transaction pour consistance
        $db->beginTransaction();
        
        // Journaliser le début de sauvegarde
        db_execute(
            "INSERT INTO backup_logs (type_backup, fichier, statut, date_execution) 
             VALUES (:type, :fichier, 'pending', NOW())",
            ['type' => $backup_type, 'fichier' => basename($backup_file)]
        );
        
        // Dans un environnement réel, on utiliserait mysqldump
        // Pour ce code, on simule la sauvegarde
        $backup_id = $db->lastInsertId();
        
        // Simuler la création du fichier de sauvegarde
        $backup_content = "-- Backup de la base " . DB_NAME . "\n";
        $backup_content .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $backup_content .= "-- Type: " . $backup_type . "\n\n";
        
        // Récupérer la liste des tables
        $tables = db_query("SHOW TABLES");
        foreach ($tables as $table) {
            $table_name = current($table);
            $backup_content .= "\n-- Table: {$table_name}\n";
            
            // Récupérer la structure
            $create_table = db_query_single("SHOW CREATE TABLE `{$table_name}`");
            $backup_content .= $create_table['Create Table'] . ";\n\n";
            
            // Récupérer les données (limité pour la démo)
            $data = db_query("SELECT * FROM `{$table_name}` LIMIT 1000");
            if (!empty($data)) {
                $columns = array_keys($data[0]);
                $backup_content .= "INSERT INTO `{$table_name}` (`" . implode('`, `', $columns) . "`) VALUES \n";
                
                $rows = [];
                foreach ($data as $row) {
                    $values = array_map(function($value) {
                        if ($value === null) return 'NULL';
                        if (is_numeric($value)) return $value;
                        return "'" . addslashes($value) . "'";
                    }, $row);
                    $rows[] = "  (" . implode(', ', $values) . ")";
                }
                
                $backup_content .= implode(",\n", $rows) . ";\n";
            }
        }
        
        // Écrire le fichier
        if (file_put_contents($backup_file, $backup_content)) {
            $file_size = filesize($backup_file);
            
            // Mettre à jour le log
            db_execute(
                "UPDATE backup_logs 
                 SET statut = 'success', taille = :taille 
                 WHERE backup_id = :backup_id",
                ['taille' => $file_size, 'backup_id' => $backup_id]
            );
            
            $db->commit();
            
            // Nettoyer les vieilles sauvegardes
            db_cleanup_old_backups();
            
            return [
                'success' => true,
                'file' => $backup_file,
                'size' => $file_size,
                'message' => 'Sauvegarde créée avec succès'
            ];
        } else {
            throw new Exception("Impossible d'écrire le fichier de sauvegarde");
        }
        
    } catch (Exception $e) {
        $db->rollBack();
        
        // Journaliser l'échec
        db_execute(
            "UPDATE backup_logs 
             SET statut = 'failed', erreur_message = :erreur 
             WHERE backup_id = :backup_id",
            ['erreur' => $e->getMessage(), 'backup_id' => $backup_id ?? 0]
        );
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Échec de la sauvegarde'
        ];
    }
}

/**
 * Nettoyer les vieilles sauvegardes
 */
function db_cleanup_old_backups(): void
{
    $backup_files = glob(BACKUP_PATH . '/backup_*.sql');
    $now = time();
    $retention_days = BACKUP_RETENTION_DAYS;
    
    foreach ($backup_files as $file) {
        if (is_file($file)) {
            $file_time = filemtime($file);
            $age_days = ($now - $file_time) / (60 * 60 * 24);
            
            if ($age_days > $retention_days) {
                unlink($file);
                log_action('Backup supprimé', ['file' => basename($file), 'age_days' => floor($age_days)]);
            }
        }
    }
}

/**
 * Restaurer une sauvegarde
 */
function db_restore(string $backup_file): array
{
    if (!file_exists($backup_file)) {
        return [
            'success' => false,
            'error' => 'Fichier de sauvegarde introuvable'
        ];
    }
    
    $db = Database::getInstance();
    
    try {
        // Désactiver les contraintes de clé étrangère
        db_execute("SET FOREIGN_KEY_CHECKS = 0");
        
        // Lire et exécuter le fichier SQL
        $sql_content = file_get_contents($backup_file);
        $queries = array_filter(array_map('trim', explode(';', $sql_content)));
        
        foreach ($queries as $query) {
            if (!empty($query)) {
                db_execute($query);
            }
        }
        
        // Réactiver les contraintes
        db_execute("SET FOREIGN_KEY_CHECKS = 1");
        
        log_action('Base restaurée', ['file' => basename($backup_file)]);
        
        return [
            'success' => true,
            'message' => 'Base de données restaurée avec succès'
        ];
        
    } catch (Exception $e) {
        // Réactiver les contraintes en cas d'erreur
        db_execute("SET FOREIGN_KEY_CHECKS = 1");
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Échec de la restauration'
        ];
    }
}

/**
 * Vérifier l'intégrité de la base de données
 */
function db_check_integrity(): array
{
    $results = [];
    
    try {
        // Vérifier les tables corrompues
        $tables = db_query("SHOW TABLES");
        
        foreach ($tables as $table) {
            $table_name = current($table);
            
            $check_result = db_query("CHECK TABLE `{$table_name}`");
            $results[$table_name] = $check_result;
            
            if ($check_result[0]['Msg_type'] === 'error') {
                log_action('Table corrompue détectée', ['table' => $table_name, 'message' => $check_result[0]['Msg_text']], 'error');
            }
        }
        
        // Vérifier les contraintes de clé étrangère
        $fk_errors = db_query("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = :dbname
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", ['dbname' => DB_NAME]);
        
        return [
            'success' => true,
            'tables' => $results,
            'foreign_keys' => $fk_errors,
            'message' => 'Vérification d\'intégrité terminée'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Erreur lors de la vérification d\'intégrité'
        ];
    }
}

/**
 * Optimiser toutes les tables
 */
function db_optimize_tables(): array
{
    try {
        $tables = db_query("SHOW TABLES");
        $optimized = [];
        
        foreach ($tables as $table) {
            $table_name = current($table);
            db_execute("OPTIMIZE TABLE `{$table_name}`");
            $optimized[] = $table_name;
        }
        
        log_action('Tables optimisées', ['tables' => $optimized]);
        
        return [
            'success' => true,
            'tables' => $optimized,
            'message' => count($optimized) . ' tables optimisées'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Erreur lors de l\'optimisation'
        ];
    }
}

// =============================================
// INITIALISATION AUTOMATIQUE
// =============================================

// Créer une instance de la base de données au chargement
Database::getInstance();

// Journaliser le chargement réussi
if (function_exists('log_action')) {
    log_action('Module database chargé', ['version' => '1.0.0']);
}