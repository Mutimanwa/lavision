<?php
/**
 * Gestion de la connexion et des opérations de base de données
 * Utilise PDO pour des requêtes sécurisées
 * Programmation procédurale pour maintenir la cohérence du projet
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Config/config.php';

// =============================================
// VARIABLES GLOBALES POUR LA CONNEXION
// =============================================

/**
 * Instance globale de la connexion PDO
 * @var PDO|null
 */
global $pdo_connection;
$pdo_connection = null;

/**
 * Compteur de requêtes pour le débogage
 * @var int
 */
global $query_count;
$query_count = 0;

/**
 * Dernière requête exécutée pour le débogage
 * @var string
 */
global $last_query;
$last_query = '';

// =============================================
// FONCTIONS DE CONNEXION
// =============================================

/**
 * Établit la connexion à la base de données MySQL via PDO
 * Utilise le pattern Singleton pour éviter les connexions multiples
 *
 * @return PDO Objet de connexion PDO
 * @throws Exception En cas d'erreur de connexion
 */
function get_db_connection(): PDO
{
    global $pdo_connection;

    // Vérifier si la connexion existe déjà
    if ($pdo_connection !== null) {
        return $pdo_connection;
    }

    try {
        // Options PDO pour la sécurité et performance
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,        // Lancer des exceptions en cas d'erreur
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,              // Retourner les résultats sous forme de tableau associatif
            PDO::ATTR_EMULATE_PREPARES   => false,                         // Utiliser les requêtes préparées natives
            PDO::ATTR_PERSISTENT         => false,                         // Pas de connexions persistantes pour éviter les blocages
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATION, // Configuration charset
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false               // Désactiver vérification SSL en développement
        ];

        // Construction du DSN (Data Source Name)
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        // Création de la connexion PDO
        $pdo_connection = new PDO($dsn, DB_USER, DB_PASS, $options);

        // Journaliser la connexion réussie
        logAction('Connexion DB établie', 'Etablissement de la connexion à la base de données', [
            'host' => DB_HOST,
            'base' => DB_NAME,
            'charset' => DB_CHARSET
        ]);

        return $pdo_connection;

    } catch (PDOException $e) {
        // Journaliser l'erreur détaillée
        $error_msg = "Erreur de connexion à la base de données: " . $e->getMessage();
        error_log($error_msg);

        // En mode production, afficher un message générique
        if (!DEBUG_MODE) {
            throw new Exception("Erreur de connexion à la base de données. Contactez l'administrateur.");
        } else {
            // En développement, afficher l'erreur complète pour le débogage
            throw new Exception($error_msg);
        }
    }
}

/**
 * Ferme la connexion à la base de données
 * À utiliser principalement pour les tests ou nettoyage manuel
 */
function db_close(): void
{
    global $pdo_connection;
    $pdo_connection = null;
}

// =============================================
// FONCTIONS D'EXÉCUTION DE REQUÊTES
// =============================================

/**
 * Exécute une requête SELECT et retourne les résultats
 *
 * @param string $sql Requête SQL préparée
 * @param array $params Paramètres pour la requête préparée
 * @param int $fetch_mode Mode de récupération (PDO::FETCH_ASSOC par défaut)
 * @return array Résultats de la requête
 */
function db_query(string $sql, array $params = [], int $fetch_mode = PDO::FETCH_ASSOC): array
{
    global $query_count, $last_query;

    $pdo = get_db_connection();
    $last_query = $sql;
    $query_count++;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Journaliser la requête en mode debug
        if (DEBUG_MODE) {
            logAction('Requête exécutée', 'Exexution requête SELECT',[
                'sql' => $sql,
                'params' => $params,
                'lignes' => $stmt->rowCount()
            ]);
        }

        return $stmt->fetchAll($fetch_mode);

    } catch (PDOException $e) {
        // Journaliser l'erreur
        logError('Erreur requête SELECT', [
            'sql' => $sql,
            'params' => $params,
            'error' => $e->getMessage()
        ]);

        if (DEBUG_MODE) {
            throw $e;
        } else {
            return [];
        }
    }
}

/**
 * Exécute une requête INSERT, UPDATE ou DELETE
 *
 * @param string $sql Requête SQL préparée
 * @param array $params Paramètres pour la requête préparée
 * @return int|bool Nombre de lignes affectées ou false en cas d'erreur
 */
function db_execute(string $sql, array $params = [])
{
    global $query_count, $last_query;

    $pdo = get_db_connection();
    $last_query = $sql;
    $query_count++;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $affected_rows = $stmt->rowCount();

        // Journaliser la requête en mode debug
        if (DEBUG_MODE) {
            logAction('Requête exécutée', 'Exécution requête INSERT/UPDATE/DELETE',[
                'sql' => $sql,
                'params' => $params,
                'lignes_affectees' => $affected_rows
            ]);
        }

        return $affected_rows;

    } catch (PDOException $e) {
        // Journaliser l'erreur
        logError('Erreur requête exécution', [
            'sql' => $sql,
            'params' => $params,
            'error' => $e->getMessage()
        ]);

        if (DEBUG_MODE) {
            throw $e;
        } else {
            return false;
        }
    }
}

/**
 * Exécute une requête et retourne le dernier ID inséré
 *
 * @param string $sql Requête INSERT préparée
 * @param array $params Paramètres pour la requête préparée
 * @return int|string Dernier ID inséré
 */
function db_insert(string $sql, array $params = [])
{
    $result = db_execute($sql, $params);

    if ($result !== false) {
        $pdo = get_db_connection();
        return $pdo->lastInsertId();
    }

    return false;
}

/**
 * Démarre une transaction
 */
function db_begin_transaction(): bool
{
    try {
        $pdo = get_db_connection();
        return $pdo->beginTransaction();
    } catch (PDOException $e) {
        logError('Erreur début transaction', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Valide une transaction
 */
function db_commit(): bool
{
    try {
        $pdo = get_db_connection();
        return $pdo->commit();
    } catch (PDOException $e) {
        logError('Erreur validation transaction', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Annule une transaction
 */
function db_rollback(): bool
{
    try {
        $pdo = get_db_connection();
        return $pdo->rollBack();
    } catch (PDOException $e) {
        logError('Erreur annulation transaction', ['error' => $e->getMessage()]);
        return false;
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Échappe une valeur pour utilisation dans une requête SQL
 * ATTENTION: Préférez les requêtes préparées quand possible
 *
 * @param mixed $value Valeur à échapper
 * @return string Valeur échappée
 */
function db_escape($value): string
{
    $pdo = get_db_connection();
    return $pdo->quote($value);
}

/**
 * Retourne le nombre total de requêtes exécutées
 *
 * @return int Nombre de requêtes
 */
function db_get_query_count(): int
{
    global $query_count;
    return $query_count;
}

/**
 * Retourne la dernière requête exécutée
 *
 * @return string Dernière requête
 */
function db_get_last_query(): string
{
    global $last_query;
    return $last_query;
}

/**
 * Vérifie si une table existe dans la base de données
 *
 * @param string $table_name Nom de la table
 * @return bool True si la table existe
 */
function db_table_exists(string $table_name): bool
{
    try {
        $result = db_query("SHOW TABLES LIKE ?", [$table_name]);
        return count($result) > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Obtient la liste des colonnes d'une table
 *
 * @param string $table_name Nom de la table
 * @return array Liste des colonnes
 */
function db_get_columns(string $table_name): array
{
    try {
        $result = db_query("DESCRIBE `$table_name`");
        return array_column($result, 'Field');
    } catch (Exception $e) {
        return [];
    }
}

// =============================================
// FONCTIONS DE MAINTENANCE
// =============================================

/**
 * Optimise une table pour améliorer les performances
 *
 * @param string $table_name Nom de la table
 * @return bool Succès de l'opération
 */
function db_optimize_table(string $table_name): bool
{
    try {
        db_execute("OPTIMIZE TABLE `$table_name`");
        logAction('Table optimisée', ['table' => $table_name]);
        return true;
    } catch (Exception $e) {
        logError('Erreur optimisation table', [
            'table' => $table_name,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Répare une table MyISAM corrompue
 *
 * @param string $table_name Nom de la table
 * @return bool Succès de l'opération
 */
function db_repair_table(string $table_name): bool
{
    try {
        db_execute("REPAIR TABLE `$table_name`");
        logAction('Table réparée', ['table' => $table_name]);
        return true;
    } catch (Exception $e) {
        logError('Erreur réparation table', [
            'table' => $table_name,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

?>