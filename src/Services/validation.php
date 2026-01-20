<?php
/**
 * Service de validation des données
 * Contient toutes les fonctions de validation et nettoyage
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

// =============================================
// FONCTIONS DE VALIDATION GÉNÉRIQUES
// =============================================

/**
 * Nettoie une valeur string
 *
 * @param mixed $valeur Valeur à nettoyer
 * @return string Valeur nettoyée
 */
function nettoyer_string($valeur): string
{
    if (!is_string($valeur)) {
        return '';
    }

    // Suppression des espaces en début et fin
    $valeur = trim($valeur);

    // Suppression des balises HTML/PHP
    $valeur = strip_tags($valeur);

    // Échappement des caractères spéciaux
    $valeur = htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');

    return $valeur;
}

/**
 * Valide un email
 *
 * @param string $email Email à valider
 * @return bool True si valide
 */
function valider_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valide une date au format YYYY-MM-DD
 *
 * @param string $date Date à valider
 * @return bool True si valide
 */
function valider_date(string $date): bool
{
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Valide un numéro de téléphone
 *
 * @param string $telephone Numéro à valider
 * @return bool True si valide
 */
function valider_telephone(string $telephone): bool
{
    // Formats acceptés: +243XXXXXXXXX, 243XXXXXXXXX, 0XXXXXXXXX
    return preg_match('/^(\+243|243|0)[0-9]{9}$/', $telephone) === 1;
}

/**
 * Valide un mot de passe selon les critères de sécurité
 *
 * @param string $password Mot de passe à valider
 * @return array ['valide' => bool, 'erreurs' => array]
 */
function valider_mot_de_passe(string $password): array
{
    $erreurs = [];

    if (strlen($password) < 8) {
        $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $erreurs[] = "Le mot de passe doit contenir au moins une majuscule";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $erreurs[] = "Le mot de passe doit contenir au moins une minuscule";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $erreurs[] = "Le mot de passe doit contenir au moins un chiffre";
    }

    return [
        'valide' => empty($erreurs),
        'erreurs' => $erreurs
    ];
}

/**
 * Génère un token CSRF
 *
 * @return string Token généré
 */
function generer_csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 *
 * @param string $token Token à vérifier
 * @return bool True si valide
 */
function verifier_csrf_token(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// =============================================
// VALIDATION DES DONNÉES ÉLÈVES
// =============================================

/**
 * Valide les données d'un élève
 *
 * @param array $donnees Données brutes du formulaire
 * @param bool $est_modification True si modification, false si création
 * @return array ['validees' => array, 'erreurs' => array]
 */
function valider_donnees_eleve(array $donnees, bool $est_modification = false): array
{
    $validees = [];
    $erreurs = [];

    // Validation du nom
    $nom = nettoyer_string($donnees['nom'] ?? '');
    if (empty($nom)) {
        $erreurs['nom'] = "Le nom est obligatoire";
    } elseif (strlen($nom) < 2) {
        $erreurs['nom'] = "Le nom doit contenir au moins 2 caractères";
    } elseif (strlen($nom) > 50) {
        $erreurs['nom'] = "Le nom ne peut pas dépasser 50 caractères";
    } else {
        $validees['nom'] = $nom;
    }

    // Validation du post-nom
    $post_nom = nettoyer_string($donnees['post_nom'] ?? '');
    if (empty($post_nom)) {
        $erreurs['post_nom'] = "Le post-nom est obligatoire";
    } elseif (strlen($post_nom) < 2) {
        $erreurs['post_nom'] = "Le post-nom doit contenir au moins 2 caractères";
    } elseif (strlen($post_nom) > 50) {
        $erreurs['post_nom'] = "Le post-nom ne peut pas dépasser 50 caractères";
    } else {
        $validees['post_nom'] = $post_nom;
    }

    // Validation du prénom
    $prenom = nettoyer_string($donnees['prenom'] ?? '');
    if (empty($prenom)) {
        $erreurs['prenom'] = "Le prénom est obligatoire";
    } elseif (strlen($prenom) < 2) {
        $erreurs['prenom'] = "Le prénom doit contenir au moins 2 caractères";
    } elseif (strlen($prenom) > 50) {
        $erreurs['prenom'] = "Le prénom ne peut pas dépasser 50 caractères";
    } else {
        $validees['prenom'] = $prenom;
    }

    // Validation de la date de naissance
    $date_naissance = $donnees['date_naissance'] ?? '';
    if (empty($date_naissance)) {
        $erreurs['date_naissance'] = "La date de naissance est obligatoire";
    } elseif (!valider_date($date_naissance)) {
        $erreurs['date_naissance'] = "Format de date invalide (utilisez YYYY-MM-DD)";
    } else {
        // Vérification de l'âge (entre 3 et 25 ans)
        $date = new DateTime($date_naissance);
        $aujourd_hui = new DateTime();
        $age = $aujourd_hui->diff($date)->y;

        if ($age < 3 || $age > 25) {
            $erreurs['date_naissance'] = "L'âge doit être compris entre 3 et 25 ans";
        } else {
            $validees['date_naissance'] = $date_naissance;
        }
    }

    // Validation du lieu de naissance
    $lieu_naissance = nettoyer_string($donnees['lieu_naissance'] ?? '');
    if (strlen($lieu_naissance) > 100) {
        $erreurs['lieu_naissance'] = "Le lieu de naissance ne peut pas dépasser 100 caractères";
    } else {
        $validees['lieu_naissance'] = $lieu_naissance;
    }

    // Validation du genre
    $genre = $donnees['genre'] ?? '';
    $genres_valides = ['M', 'F', 'Autre'];
    if (empty($genre)) {
        $erreurs['genre'] = "Le genre est obligatoire";
    } elseif (!in_array($genre, $genres_valides)) {
        $erreurs['genre'] = "Genre invalide";
    } else {
        $validees['genre'] = $genre;
    }

    // Validation du groupe sanguin
    $groupe_sanguin = $donnees['groupe_sanguin'] ?? '';
    $groupes_valides = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    if (!empty($groupe_sanguin) && !in_array($groupe_sanguin, $groupes_valides)) {
        $erreurs['groupe_sanguin'] = "Groupe sanguin invalide";
    } else {
        $validees['groupe_sanguin'] = $groupe_sanguin;
    }

    // Validation de l'adresse
    $adresse = nettoyer_string($donnees['adresse'] ?? '');
    if (strlen($adresse) > 255) {
        $erreurs['adresse'] = "L'adresse ne peut pas dépasser 255 caractères";
    } else {
        $validees['adresse'] = $adresse;
    }

    // Validation du téléphone
    $telephone = nettoyer_string($donnees['telephone'] ?? '');
    if (!empty($telephone) && !valider_telephone($telephone)) {
        $erreurs['telephone'] = "Format de téléphone invalide (+243XXXXXXXXX, 243XXXXXXXXX ou 0XXXXXXXXX)";
    } else {
        $validees['telephone'] = $telephone;
    }

    // Validation de l'email
    $email = nettoyer_string($donnees['email'] ?? '');
    if (empty($email)) {
        $erreurs['email'] = "L'email est obligatoire";
    } elseif (!valider_email($email)) {
        $erreurs['email'] = "Format d'email invalide";
    } elseif (!$est_modification && email_existe_deja($email)) {
        $erreurs['email'] = "Cet email est déjà utilisé";
    } else {
        $validees['email'] = $email;
    }

    // Validation de la classe
    $id_classe = intval($donnees['id_classe'] ?? 0);
    if ($id_classe > 0) {
        if (!classe_existe($id_classe)) {
            $erreurs['id_classe'] = "Classe invalide";
        } else {
            $validees['id_classe'] = $id_classe;
        }
    }

    // Validation du parent
    $id_parent = intval($donnees['id_parent'] ?? 0);
    if ($id_parent > 0) {
        if (!parent_existe($id_parent)) {
            $erreurs['id_parent'] = "Parent invalide";
        } else {
            $validees['id_parent'] = $id_parent;
        }
    }

    // Validation du statut (uniquement en modification)
    if ($est_modification) {
        $statut = $donnees['statut'] ?? '';
        $statuts_valides = ['en_attente', 'actif', 'suspendu', 'desiste', 'diplome'];
        if (!empty($statut) && !in_array($statut, $statuts_valides)) {
            $erreurs['statut'] = "Statut invalide";
        } else {
            $validees['statut'] = $statut;
        }
    }

    // Validation du mot de passe (uniquement en création)
    if (!$est_modification) {
        $mot_de_passe = $donnees['mot_de_passe'] ?? '';
        $confirmation = $donnees['confirmation_mot_de_passe'] ?? '';

        if (empty($mot_de_passe)) {
            $erreurs['mot_de_passe'] = "Le mot de passe est obligatoire";
        } else {
            $validation = valider_mot_de_passe($mot_de_passe);
            if (!$validation['valide']) {
                $erreurs['mot_de_passe'] = implode(', ', $validation['erreurs']);
            } elseif ($mot_de_passe !== $confirmation) {
                $erreurs['confirmation_mot_de_passe'] = "Les mots de passe ne correspondent pas";
            } else {
                $validees['mot_de_passe'] = $mot_de_passe;
            }
        }
    }

    return [
        'validees' => $validees,
        'erreurs' => $erreurs
    ];
}

// =============================================
// FONCTIONS UTILITAIRES DE VALIDATION
// =============================================

/**
 * Vérifie si un email existe déjà dans la base
 *
 * @param string $email Email à vérifier
 * @param int $exclude_id ID à exclure de la vérification (pour modification)
 * @return bool True si existe déjà
 */
function email_existe_deja(string $email, int $exclude_id = 0): bool
{
    $sql = "SELECT COUNT(*) as count FROM utilisateurs WHERE email = ?";
    $params = [$email];

    if ($exclude_id > 0) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
    }

    $result = db_query($sql, $params);
    return $result && $result[0]['count'] > 0;
}

/**
 * Vérifie si une classe existe
 *
 * @param int $id_classe ID de la classe
 * @return bool True si existe
 */
function classe_existe(int $id_classe): bool
{
    $result = db_query("SELECT COUNT(*) as count FROM classes WHERE id = ?", [$id_classe]);
    return $result && $result[0]['count'] > 0;
}

/**
 * Vérifie si un parent existe
 *
 * @param int $id_parent ID du parent
 * @return bool True si existe
 */
function parent_existe(int $id_parent): bool
{
    $result = db_query("SELECT COUNT(*) as count FROM parents WHERE id = ?", [$id_parent]);
    return $result && $result[0]['count'] > 0;
}

/**
 * Nettoie et valide un tableau de données
 *
 * @param array $donnees Données à nettoyer
 * @return array Données nettoyées
 */
function nettoyer_donnees(array $donnees): array
{
    $nettoyees = [];

    foreach ($donnees as $cle => $valeur) {
        if (is_string($valeur)) {
            $nettoyees[$cle] = nettoyer_string($valeur);
        } elseif (is_array($valeur)) {
            $nettoyees[$cle] = nettoyer_donnees($valeur);
        } else {
            $nettoyees[$cle] = $valeur;
        }
    }

    return $nettoyees;
}

/**
 * Valide une valeur requise
 *
 * @param mixed $valeur Valeur à vérifier
 * @param string $nom_champ Nom du champ pour le message d'erreur
 * @return string|null Message d'erreur ou null si valide
 */
function valider_champ_requis($valeur, string $nom_champ): ?string
{
    if (is_string($valeur)) {
        $valeur = trim($valeur);
    }

    if (empty($valeur)) {
        return "Le champ '$nom_champ' est obligatoire";
    }

    return null;
}

/**
 * Valide la longueur d'une chaîne
 *
 * @param string $valeur Valeur à vérifier
 * @param string $nom_champ Nom du champ
 * @param int $min Longueur minimale
 * @param int $max Longueur maximale
 * @return string|null Message d'erreur ou null si valide
 */
function valider_longueur_chaine(string $valeur, string $nom_champ, int $min = 0, int $max = PHP_INT_MAX): ?string
{
    $longueur = strlen($valeur);

    if ($longueur < $min) {
        return "Le champ '$nom_champ' doit contenir au moins $min caractères";
    }

    if ($longueur > $max) {
        return "Le champ '$nom_champ' ne peut pas dépasser $max caractères";
    }

    return null;
}

// =============================================
// INCLUSION DES VALIDATIONS SPÉCIFIQUES
// =============================================

// Inclusion des validations académiques
require_once __DIR__ . '/validation_academique.php';

?>