<?php
/**
 * Modèle d'administration
 * Gestion des utilisateurs, paramètres système, logs, sauvegardes
 */

class AdminModel {
    private $db;

    public function __construct() {
        $this->db = get_db_connection();
    }

    /**
     * Récupère la liste des utilisateurs avec pagination
     */
    public function getUtilisateurs($page = 1, $search = '', $role = '', $statut = '') {
        $limit = ITEMS_PER_PAGE;
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
        $total = $this->db->fetch($countQuery, $params)['total'];

        // Récupérer les utilisateurs
        $query = "SELECT u.*, COUNT(l.log_id) as nb_actions
                  FROM user_admins u
                  LEFT JOIN logs l ON u.user_id = l.user_id
                  $whereClause
                  GROUP BY u.user_id
                  ORDER BY u.date_creation DESC
                  LIMIT $limit OFFSET $offset";

        $utilisateurs = $this->db->fetchAll($query, $params);

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
    public function getRoles() {
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
    public function getParametres() {
        $query = "SELECT * FROM parametres ORDER BY categorie, cle";
        $parametres = $this->db->fetchAll($query);

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
    public function updateParametres($parametres) {
        $this->db->beginTransaction();

        try {
            foreach ($parametres as $cle => $valeur) {
                $query = "UPDATE parametres SET valeur = ?, date_modif = NOW() WHERE cle = ?";
                $this->db->execute($query, [$valeur, $cle]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Récupère toutes les années scolaires
     */
    public function getAnneesScolaires() {
        $query = "SELECT * FROM annees_scolaire ORDER BY date_debut DESC";
        return $this->db->fetchAll($query);
    }

    /**
     * Crée une nouvelle année scolaire
     */
    public function creerAnneeScolaire($data) {
        $query = "INSERT INTO annees_scolaire (annee_libelle, date_debut, date_fin, statut)
                  VALUES (?, ?, ?, ?)";
        return $this->db->execute($query, [
            $data['annee_libelle'],
            $data['date_debut'],
            $data['date_fin'],
            $data['statut']
        ]);
    }

    /**
     * Modifie une année scolaire
     */
    public function modifierAnneeScolaire($annee_id, $data) {
        $query = "UPDATE annees_scolaire
                  SET annee_libelle = ?, date_debut = ?, date_fin = ?, statut = ?
                  WHERE annee_id = ?";
        return $this->db->execute($query, [
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
    public function activerAnneeScolaire($annee_id) {
        $this->db->beginTransaction();

        try {
            // Désactiver toutes les années
            $this->db->execute("UPDATE annees_scolaire SET statut = 'inactive'");

            // Activer l'année sélectionnée
            $this->db->execute("UPDATE annees_scolaire SET statut = 'active' WHERE annee_id = ?", [$annee_id]);

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
    public function getLogs($page = 1, $niveau = '', $categorie = '', $date_debut = '', $date_fin = '') {
        $limit = ITEMS_PER_PAGE;
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
        $total = $this->db->fetch($countQuery, $params)['total'];

        // Récupérer les logs
        $query = "SELECT l.*, u.identifiant, u.nom, u.prenom
                  FROM logs l
                  LEFT JOIN user_admins u ON l.user_id = u.user_id
                  $whereClause
                  ORDER BY l.date_action DESC
                  LIMIT $limit OFFSET $offset";

        $logs = $this->db->fetchAll($query, $params);

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
    public function getNiveauxLog() {
        return ['info', 'warning', 'error', 'security'];
    }

    /**
     * Récupère les catégories de log disponibles
     */
    public function getCategoriesLog() {
        $query = "SELECT DISTINCT categorie FROM logs ORDER BY categorie";
        $result = $this->db->fetchAll($query);
        return array_column($result, 'categorie');
    }

    /**
     * Récupère l'historique des sauvegardes
     */
    public function getBackupLogs() {
        $query = "SELECT * FROM backup_logs ORDER BY date_execution DESC LIMIT 50";
        return $this->db->fetchAll($query);
    }

    /**
     * Récupère les sauvegardes disponibles pour restauration
     */
    public function getAvailableBackups() {
        $query = "SELECT * FROM backup_logs
                  WHERE statut = 'success'
                  ORDER BY date_execution DESC LIMIT 20";
        return $this->db->fetchAll($query);
    }

    /**
     * Crée une nouvelle sauvegarde
     */
    public function creerSauvegarde($type) {
        // Insérer le log de sauvegarde
        $query = "INSERT INTO backup_logs (type_backup, fichier, statut, execute_par)
                  VALUES (?, ?, 'pending', ?)";

        $fichier = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $user_id = $_SESSION['user_id'] ?? null;

        if ($this->db->execute($query, [$type, $fichier, $user_id])) {
            // Ici nous lancerions le processus de sauvegarde réel
            // Pour l'instant, on simule le succès
            $backup_id = $this->db->lastInsertId();
            $this->db->execute(
                "UPDATE backup_logs SET statut = 'success', taille = ? WHERE backup_id = ?",
                [rand(1000000, 10000000), $backup_id]
            );
            return true;
        }

        return false;
    }

    /**
     * Restaure une sauvegarde
     */
    public function restaurerSauvegarde($backup_id) {
        // Vérifier que la sauvegarde existe et est valide
        $query = "SELECT * FROM backup_logs WHERE backup_id = ? AND statut = 'success'";
        $backup = $this->db->fetch($query, [$backup_id]);

        if (!$backup) {
            return false;
        }

        // Ici nous lancerions le processus de restauration réel
        // Pour l'instant, on simule le succès
        logAction('admin', 'Restauration de sauvegarde', ['backup_id' => $backup_id]);
        return true;
    }
}
?>