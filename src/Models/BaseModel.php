<?php
/**
 * Modèle de base
 * LaVision - Système de gestion scolaire
 *
 * Ce modèle fournit les fonctionnalités communes à tous les modèles
 * de l'application (CRUD de base, recherche, pagination, etc.)
 */

class BaseModel {
    /**
     * Instance de la base de données
     * @var PDO
     */
    protected $db;

    /**
     * Nom de la table
     * @var string
     */
    protected $table;

    /**
     * Clé primaire
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Colonnes fillable (autorisé pour l'insertion/mise à jour)
     * @var array
     */
    protected $fillable = [];

    /**
     * Colonnes hidden (masquées lors de la récupération)
     * @var array
     */
    protected $hidden = [];

    /**
     * Colonnes dates (automatiquement converties en objets DateTime)
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = get_db_connection();
    }

    /**
     * Obtenir tous les enregistrements
     */
    public function all($columns = ['*']) {
        $columns_str = implode(', ', $columns);
        $sql = "SELECT $columns_str FROM {$this->table} ORDER BY {$this->primaryKey} DESC";

        try {
            $stmt = $this->db->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->processResults($results);
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération de tous les enregistrements de {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir un enregistrement par son ID
     */
    public function find($id, $columns = ['*']) {
        $columns_str = implode(', ', $columns);
        $sql = "SELECT $columns_str FROM {$this->table} WHERE {$this->primaryKey} = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $this->processResult($result) : null;
        } catch (PDOException $e) {
            logError("Erreur lors de la recherche de l'enregistrement ID $id dans {$this->table}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir le premier enregistrement correspondant aux conditions
     */
    public function first($conditions = [], $columns = ['*']) {
        $columns_str = implode(', ', $columns);
        $sql = "SELECT $columns_str FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $column => $value) {
                $where_clauses[] = "$column = :$column";
                $params[$column] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $sql .= " LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $this->processResult($result) : null;
        } catch (PDOException $e) {
            logError("Erreur lors de la recherche du premier enregistrement dans {$this->table}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir des enregistrements avec conditions
     */
    public function where($conditions = [], $columns = ['*'], $orderBy = null, $limit = null) {
        $columns_str = implode(', ', $columns);
        $sql = "SELECT $columns_str FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $where_clauses[] = "$column IN (" . str_repeat('?,', count($value) - 1) . '?)';
                    $params = array_merge($params, $value);
                } else {
                    $where_clauses[] = "$column = ?";
                    $params[] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $this->processResults($results);
        } catch (PDOException $e) {
            logError("Erreur lors de la recherche avec conditions dans {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recherche avec pagination
     */
    public function paginate($page = 1, $perPage = 25, $conditions = [], $columns = ['*'], $orderBy = null, $searchColumns = []) {
        $offset = ($page - 1) * $perPage;

        // Compter le total
        $total = $this->count($conditions, $searchColumns);

        // Récupérer les résultats
        $columns_str = implode(', ', $columns);
        $sql = "SELECT $columns_str FROM {$this->table}";
        $params = [];

        // Conditions WHERE
        $where_clauses = [];
        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $where_clauses[] = "$column IN (" . str_repeat('?,', count($value) - 1) . '?)';
                    $params = array_merge($params, $value);
                } else {
                    $where_clauses[] = "$column = ?";
                    $params[] = $value;
                }
            }
        }

        // Recherche
        if (!empty($_GET['search']) && !empty($searchColumns)) {
            $search_term = '%' . $_GET['search'] . '%';
            $search_clauses = [];
            foreach ($searchColumns as $column) {
                $search_clauses[] = "$column LIKE ?";
                $params[] = $search_term;
            }
            $where_clauses[] = '(' . implode(' OR ', $search_clauses) . ')';
        }

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        // Tri
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        } elseif (!empty($_GET['sort'])) {
            $sort_column = $_GET['sort'];
            $sort_order = strtoupper($_GET['order'] ?? 'DESC');
            $sort_order = in_array($sort_order, ['ASC', 'DESC']) ? $sort_order : 'DESC';
            $sql .= " ORDER BY $sort_column $sort_order";
        } else {
            $sql .= " ORDER BY {$this->primaryKey} DESC";
        }

        // Pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'data' => $this->processResults($results),
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total)
            ];
        } catch (PDOException $e) {
            logError("Erreur lors de la pagination dans {$this->table}: " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => 0,
                'from' => 0,
                'to' => 0
            ];
        }
    }

    /**
     * Compter les enregistrements
     */
    public function count($conditions = [], $searchColumns = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        // Conditions WHERE
        $where_clauses = [];
        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $where_clauses[] = "$column IN (" . str_repeat('?,', count($value) - 1) . '?)';
                    $params = array_merge($params, $value);
                } else {
                    $where_clauses[] = "$column = ?";
                    $params[] = $value;
                }
            }
        }

        // Recherche
        if (!empty($_GET['search']) && !empty($searchColumns)) {
            $search_term = '%' . $_GET['search'] . '%';
            $search_clauses = [];
            foreach ($searchColumns as $column) {
                $search_clauses[] = "$column LIKE ?";
                $params[] = $search_term;
            }
            $where_clauses[] = '(' . implode(' OR ', $search_clauses) . ')';
        }

        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int) $result['total'];
        } catch (PDOException $e) {
            logError("Erreur lors du comptage dans {$this->table}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Créer un nouvel enregistrement
     */
    public function create($data) {
        // Filtrer les données fillable
        $data = $this->filterFillable($data);

        // Ajouter les timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ($placeholders)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            logError("Erreur lors de la création d'un enregistrement dans {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour un enregistrement
     */
    public function update($id, $data) {
        // Filtrer les données fillable
        $data = $this->filterFillable($data);

        // Ajouter le timestamp de mise à jour
        $data['updated_at'] = date('Y-m-d H:i:s');

        $set_clauses = [];
        $params = [];

        foreach ($data as $column => $value) {
            $set_clauses[] = "$column = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set_clauses) . " WHERE {$this->primaryKey} = ?";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            return $result ? $stmt->rowCount() : false;
        } catch (PDOException $e) {
            logError("Erreur lors de la mise à jour de l'enregistrement ID $id dans {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un enregistrement
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);

            return $result ? $stmt->rowCount() : false;
        } catch (PDOException $e) {
            logError("Erreur lors de la suppression de l'enregistrement ID $id dans {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un enregistrement existe
     */
    public function exists($id) {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logError("Erreur lors de la vérification de l'existence de l'enregistrement ID $id dans {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exécuter une requête SQL personnalisée
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return $stmt->rowCount();
            }
        } catch (PDOException $e) {
            logError("Erreur lors de l'exécution de la requête SQL: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Filtrer les données selon les colonnes fillable
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Traiter un résultat unique
     */
    protected function processResult($result) {
        // Masquer les colonnes hidden
        foreach ($this->hidden as $column) {
            unset($result[$column]);
        }

        // Convertir les dates
        foreach ($this->dates as $column) {
            if (isset($result[$column]) && $result[$column]) {
                $result[$column] = new DateTime($result[$column]);
            }
        }

        return $result;
    }

    /**
     * Traiter plusieurs résultats
     */
    protected function processResults($results) {
        return array_map([$this, 'processResult'], $results);
    }

    /**
     * Commencer une transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    /**
     * Valider une transaction
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * Annuler une transaction
     */
    public function rollback() {
        return $this->db->rollBack();
    }

    /**
     * Obtenir les statistiques de la table
     */
    public function getStats() {
        try {
            // Nombre total d'enregistrements
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Enregistrements créés aujourd'hui
            $stmt = $this->db->prepare("SELECT COUNT(*) as today FROM {$this->table} WHERE DATE(created_at) = CURDATE()");
            $stmt->execute();
            $today = $stmt->fetch(PDO::FETCH_ASSOC)['today'];

            // Enregistrements créés ce mois
            $stmt = $this->db->prepare("SELECT COUNT(*) as month FROM {$this->table} WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
            $stmt->execute();
            $month = $stmt->fetch(PDO::FETCH_ASSOC)['month'];

            return [
                'total' => (int) $total,
                'today' => (int) $today,
                'month' => (int) $month
            ];
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération des statistiques de {$this->table}: " . $e->getMessage());
            return [
                'total' => 0,
                'today' => 0,
                'month' => 0
            ];
        }
    }

    /**
     * Nettoyer les anciennes données (soft delete si applicable)
     */
    public function cleanup($days = 30) {
        if (!in_array('deleted_at', $this->dates)) {
            return false; // Soft delete non activé
        }

        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE deleted_at IS NULL AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$days]);
        } catch (PDOException $e) {
            logError("Erreur lors du nettoyage des données dans {$this->table}: " . $e->getMessage());
            return false;
        }
    }
}