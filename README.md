# LaVision - Système de Gestion Scolaire

LaVision est une application web de gestion scolaire complète conçue pour l'administration des établissements d'enseignement. Elle offre une plateforme centralisée pour gérer les élèves, les professeurs, les classes, les notes, les finances et bien plus encore.

L'application est développée en **PHP** sans l'utilisation d'un framework majeur, en suivant une architecture de type "front controller" et un modèle MVC-like.

## Fonctionnalités Principales

- **Gestion Académique :**
  - Gestion des années scolaires, niveaux, sections et classes.
  - Inscription et admission des élèves.
  - Gestion des matières et des coefficients.
  - Planification des emplois du temps.
  - Saisie et consultation des notes et bulletins.

- **Gestion des Utilisateurs :**
  - Gestion des élèves, parents/tuteurs et professeurs.
  - Gestion des utilisateurs administratifs avec différents rôles (Superadmin, Admin, Secrétaire, etc.).
  - Authentification et gestion des permissions.

- **Gestion Financière :**
  - Suivi des frais de scolarité et autres types de frais.
  - Enregistrement des paiements (complets ou partiels).
  - Génération de quittances et suivi des impayés.

- **Administration et Sécurité :**
  - Journalisation des actions et des erreurs.
  - Paramètres système configurables.
  - Système de sauvegarde de la base de données.
  - Protection contre les attaques CSRF.

## Prérequis Techniques

- **Serveur Web :** Apache ou Nginx.
- **PHP :** Version 7.4 ou supérieure.
- **Extensions PHP :** `pdo_mysql`.
- **Base de données :** MySQL ou MariaDB.
- **Navigateur Web :** Chrome, Firefox, Safari, ou Edge à jour.

## Installation

Suivez ces étapes pour installer et configurer l'application sur un environnement de développement local (par exemple avec XAMPP ou WampServer).

1.  **Cloner le Projet**
    Clonez ou téléchargez ce dépôt dans le répertoire de votre serveur web (ex: `c:/xampp/htdocs/`).
    ```bash
    git clone https://github.com/Mutimanwa/lavision.git lavision
    ```

2.  **Créer la Base de Données**
    - Ouvrez votre outil de gestion de base de données (phpMyAdmin, DBeaver, etc.).
    - Créez une nouvelle base de données nommée `gestion_academique_ecole` avec l'interclassement `utf8mb4_unicode_ci`.
    - Importez le fichier `sql.sql` situé à la racine du projet. Cela créera toutes les tables, vues, et données initiales nécessaires.

3.  **Configurer l'Application**
    - Ouvrez le fichier `app/includes/config/config.php`.
    - **Base de données :** Assurez-vous que les constantes `DB_HOST`, `DB_NAME`, `DB_USER`, et `DB_PASS` correspondent à votre configuration locale.
      ```php
      define('DB_HOST', 'localhost');
      define('DB_NAME', 'gestion_academique_ecole');
      define('DB_USER', 'root');
      define('DB_PASS', '');
      ```
    - **URL de base :** La constante `BASE_URL` est généralement détectée automatiquement. Cependant, si vous rencontrez des problèmes de liens ou de redirection, vous pouvez la définir manuellement. Le chemin doit pointer vers le dossier `public`.
      ```php
      // Exemple pour une installation dans http://localhost/lavision/
      define('BASE_URL', 'http://localhost/lavision/app/public/');
      ```

4.  **Accéder à l'Application**
    - Lancez votre serveur web.
    - Ouvrez votre navigateur et accédez à l'URL que vous avez configurée, par exemple : `http://localhost/lavision/app/public/`
    - Vous devriez voir la page de connexion.

## Accès par Défaut

Un compte super-administrateur est créé lors de l'importation de la base de données.

-   **Identifiant :** `superadmin`
-   **Mot de passe :** `password`

Il est fortement recommandé de changer ce mot de passe après la première connexion.

## Structure du Projet

```
app/
├── backups/          # Sauvegardes de la base de données
├── donnees/          # Fichiers de logique de données (data logic)
├── includes/
│   ├── config/       # Fichier de configuration principal
│   ├── core/         # Cœur de l'application (auth, BDD, routeur)
│   ├── modules/      # Logique métier (business logic)
│   └── pages/        # Fichiers de vue (pages)
├── logs/             # Fichiers de log (actions, erreurs)
├── public/
│   ├── assets/       # CSS, JS, images, librairies front-end
│   └── index.php     # Point d'entrée unique de l'application
├── .htaccess         # Configuration Apache (sécurité)
├── reinit_db.php     # Script pour réinitialiser la BDD
└── sql.sql           # Schéma et données initiales de la BDD
```
