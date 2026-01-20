# LaVision - Système de Gestion Scolaire

LaVision est une application web moderne de gestion scolaire complète conçue pour l'administration efficace des établissements d'enseignement. Elle offre une plateforme centralisée et scalable pour gérer les élèves, les professeurs, les classes, les notes, les finances et bien plus encore.

L'application est développée en **PHP** sans l'utilisation d'un framework majeur, en suivant une architecture modulaire et une programmation procédurale pour une maintenance facilitée.

## Fonctionnalités Principales

### 🎓 Gestion Académique
- **Gestion des années scolaires, niveaux, sections et classes** avec organisation hiérarchique
- **Inscription et admission des élèves** avec suivi des dossiers d'admission
- **Gestion des matières et coefficients** pour le calcul automatique des moyennes
- **Suivi des présences et absences** avec statistiques détaillées
- **Planification des emplois du temps** avec gestion des conflits
- **Saisie et consultation des notes et bulletins** avec génération PDF

### 👥 Gestion des Utilisateurs
- **Gestion complète des élèves, parents/tuteurs et professeurs** avec profils détaillés
- **Gestion des utilisateurs administratifs** avec système de rôles granulaire (Superadmin, Admin, Secrétaire, etc.)
- **Authentification sécurisée** avec gestion des sessions et protection CSRF
- **Système de permissions** basé sur les rôles pour un contrôle d'accès fin

### 💰 Gestion Financière
- **Suivi des frais de scolarité** avec différents types de frais (inscription, mensualités, etc.)
- **Enregistrement des paiements** (complets ou partiels) avec génération de reçus
- **Gestion des impayés** avec relances automatiques
- **Rapports financiers** détaillés avec graphiques

### 🛡️ Administration et Sécurité
- **Journalisation complète des actions** pour traçabilité
- **Paramètres système configurables** via interface web
- **Système de sauvegarde automatique** de la base de données
- **Protection contre les attaques** XSS, CSRF, injection SQL
- **Gestion des sessions sécurisée** avec expiration automatique

## Architecture Technique

### Nouvelles Fonctionnalités (Refactorisation v2.0)

#### 🏗️ Architecture Modulaire Avancée
- **Autochargement automatique** des classes avec `src/Config/autoload.php`
- **Contrôleur de base** (`BaseController`) avec fonctionnalités communes
- **Modèle de base** (`BaseModel`) avec CRUD générique et pagination
- **Système de routes** configurable via `src/Config/routes.php`
- **Templates unifiés** avec header, footer, sidebar et gestion d'erreurs

#### 🔐 Sécurité Renforcée
- **Protection CSRF** automatique sur tous les formulaires
- **Validation avancée** avec services spécialisés par domaine
- **Authentification robuste** avec gestion des rôles et permissions
- **Logs de sécurité** complets avec traçabilité des actions
- **Protection contre les injections** et attaques XSS

#### 📊 Gestion Complète des Rapports
- **Tableau de bord** avec métriques en temps réel
- **Rapports spécialisés** : élèves, académique, financier, sécurité
- **Statistiques avancées** avec graphiques interactifs
- **Export de données** en PDF et Excel
- **Filtres dynamiques** et recherche avancée

#### 🎨 Interface Utilisateur Modernisée
- **Templates responsives** avec Bootstrap 5
- **Navigation intelligente** avec sidebar adaptative
- **Gestion d'erreurs** conviviale avec pages dédiées
- **Thème sombre/clair** (extensible)
- **Animations et transitions** fluides

#### ⚡ Performance et Maintenabilité
- **Cache intelligent** des requêtes fréquentes
- **Lazy loading** des composants JavaScript
- **Structure MVC-like** en programmation procédurale
- **Code réutilisable** avec héritage des classes de base
- **Tests automatisés** facilités par la modularité
```
app/
├── index.php                    # Point d'entrée principal (NOUVEAU)
├── .htaccess                    # Configuration Apache avancée (MISE À JOUR)
├── src/                         # Code source principal
│   ├── Config/                  # Configurations système
│   │   ├── config.php          # Configuration principale
│   │   ├── routes.php          # Configuration des routes (NOUVEAU)
│   │   └── autoload.php        # Autochargement des classes (NOUVEAU)
│   ├── Controllers/            # Logique de contrôle
│   │   ├── BaseController.php  # Contrôleur de base (NOUVEAU)
│   │   ├── EleveController.php
│   │   ├── AcademiqueController.php
│   │   ├── PersonnelController.php
│   │   ├── FinanceController.php
│   │   ├── RapportsController.php
│   │   └── ...
│   ├── Models/                 # Modèles de données
│   │   ├── BaseModel.php       # Modèle de base (NOUVEAU)
│   │   ├── EleveModel.php
│   │   ├── AcademiqueModel.php
│   │   ├── PersonnelModel.php
│   │   ├── FinanceModel.php
│   │   ├── RapportsModel.php
│   │   └── ...
│   ├── Services/               # Services utilitaires
│   │   ├── database.php        # Gestion BDD
│   │   ├── router.php          # Routage
│   │   ├── auth.php            # Authentification (MISE À JOUR)
│   │   ├── validation.php      # Validation données
│   │   ├── validation_academique.php  # Validation académique (NOUVEAU)
│   │   ├── personnel.php       # Validation personnel (NOUVEAU)
│   │   └── functions.php       # Fonctions utilitaires (MISE À JOUR)
│   ├── Middleware/             # Middleware (réservé pour évolution future)
│   ├── Routes/                 # Configuration des routes (réservé)
│   └── Views/                  # Templates et vues
│       ├── templates/          # Templates de base (MISE À JOUR)
│       │   ├── header.php      # En-tête HTML (NOUVEAU)
│       │   ├── footer.php      # Pied de page (NOUVEAU)
│       │   ├── sidebar.php     # Barre latérale (NOUVEAU)
│       │   ├── auth_template.php # Template authentification (NOUVEAU)
│       │   └── error_template.php # Template erreurs (NOUVEAU)
│       ├── eleves/             # Vues élèves
│       ├── academique/         # Vues académiques
│       ├── personnel/          # Vues personnel (NOUVEAU)
│       ├── finance/            # Vues financières (NOUVEAU)
│       ├── rapports/           # Vues rapports (NOUVEAU)
│       └── ...
├── public/                     # Point d'entrée public (ARCHITECTURE CHANGÉE)
│   ├── index.php              # Redirection vers app/index.php (MODIFIÉ)
│   ├── assets/                # CSS, JS, images
│   └── uploads/               # Fichiers uploadés
├── logs/                      # Logs système
│   ├── actions/               # Logs des actions
│   └── errors/                # Logs d'erreurs
├── backups/                   # Sauvegardes BDD
├── tests/                     # Tests unitaires
├── README.md                  # Documentation (MISE À JOUR)
├── sql.sql                    # Schéma BDD initial
└── reinit_db.php             # Script réinitialisation
```

### Technologies Utilisées
- **Backend**: PHP 7.4+ (programmation procédurale modulaire)
- **Base de données**: MySQL/MariaDB avec PDO et requêtes préparées
- **Frontend**: HTML5, CSS3, JavaScript (ES6+) avec Bootstrap 5
- **UI Framework**: Bootstrap 5 avec composants personnalisés et FontAwesome 6
- **Charts**: ECharts pour les graphiques interactifs
- **Architecture**: MVC-like procédural avec autochargement automatique
- **Sécurité**: CSRF, XSS, validation avancée, chiffrement bcrypt

## Migration depuis l'ancienne version

### Changements Majeurs (v1.x → v2.0)
- ✅ **Réorganisation complète** : Passage de `includes/donnees/` vers `src/` modulaire
- ✅ **Suppression du code obsolète** : Nettoyage des fichiers inutiles et duplications
- ✅ **Nouveau système de routage** : Routes configurables au lieu de fichiers épars
- ✅ **Templates unifiés** : Header, footer, sidebar et gestion d'erreurs centralisée
- ✅ **Services spécialisés** : Validation par domaine (académique, personnel, finance)
- ✅ **Sécurité renforcée** : Protection CSRF, validation stricte, logs complets
- ✅ **Rapports complets** : Tableaux de bord et statistiques avancées
- ✅ **Architecture scalable** : BaseController/BaseModel pour extension facile

### Compatibilité
- ✅ **Base de données** : Schéma existant préservé, migrations transparentes
- ✅ **Données utilisateur** : Tous les comptes et permissions maintenus
- ✅ **Fonctionnalités** : Toutes les fonctionnalités v1.x préservées et étendues
- ✅ **URLs** : Redirections automatiques pour compatibilité descendante

## Prérequis Techniques

### Serveur
- **Serveur Web**: Apache 2.4+ ou Nginx 1.18+
- **PHP**: Version 7.4 ou supérieure (recommandé 8.1+)
- **Extensions PHP requises**:
  - `pdo_mysql` (connexion BDD)
  - `mbstring` (support Unicode)
  - `openssl` (chiffrement)
  - `gd` (images)
  - `zip` (archives)

### Base de Données
- **SGBD**: MySQL 5.7+ ou MariaDB 10.3+
- **Charset**: UTF8MB4 pour support complet Unicode
- **Collation**: `utf8mb4_unicode_ci`

### Navigateur
- **Chrome**: 90+
- **Firefox**: 88+
- **Safari**: 14+
- **Edge**: 90+

## Installation et Configuration

### 1. Préparation de l'Environnement
```bash
# Cloner le dépôt
git clone https://github.com/Mutimanwa/lavision.git lavision
cd lavision/app

# Créer la base de données
mysql -u root -p
CREATE DATABASE gestion_academique_ecole CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 2. Configuration de l'Application
```bash
# Importer le schéma de base de données
mysql -u root -p gestion_academique_ecole < sql.sql

# Configurer les permissions
chmod 755 public/
chmod 777 logs/
chmod 777 backups/
chmod 777 public/uploads/
```

### 3. Configuration PHP
Modifier le fichier `src/Config/config.php` :
```php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_academique_ecole');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');

// Configuration de sécurité
define('DEBUG_MODE', false); // À mettre à false en production
define('SECURITY_KEY', 'votre_cle_secrete_unique_de_32_caracteres_minimum');

// URL de base (adapter selon votre installation)
define('BASE_URL', 'https://votredomaine.com/app/public/');
```

### 4. Configuration du Serveur Web

#### Apache (avec .htaccess)
```apache
<VirtualHost *:80>
    ServerName votredomaine.com
    DocumentRoot /var/www/lavision/app/public

    <Directory /var/www/lavision/app/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Sécurité - Bloquer l'accès aux fichiers sensibles
    <Directory /var/www/lavision/app/src>
        Require all denied
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name votredomaine.com;
    root /var/www/lavision/app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Bloquer l'accès aux fichiers sensibles
    location ~ /(src|logs|backups|tests)/ {
        deny all;
    }
}
```

### 5. Accès Initial
- **URL**: `http://votredomaine.com/app/public/`
- **Utilisateur par défaut**: `superadmin`
- **Mot de passe par défaut**: `password`
- ⚠️ **Important**: Changez immédiatement ce mot de passe après la première connexion !

## Utilisation Avancée

### Gestion des Rôles et Permissions
Le système supporte une hiérarchie de rôles :
- **Super Admin**: Accès complet à toutes les fonctionnalités
- **Admin**: Gestion académique et utilisateurs
- **Secrétaire**: Gestion élèves et finance
- **Professeur**: Consultation et saisie des notes
- **Élève**: Accès à son profil et notes
- **Parent**: Accès aux informations de ses enfants

### API REST (Extension Future)
L'architecture modulaire permet l'ajout facile d'une API REST pour :
- Intégration avec applications mobiles
- Synchronisation avec autres systèmes
- Automatisation des tâches

### Personnalisation
- **Thème**: Modification des couleurs via `public/assets/css/theme.min.css`
- **Langue**: Support multi-langue via fichiers de traduction
- **Rapports**: Génération de rapports personnalisés

## Sécurité

### Mesures Implémentées
- **Chiffrement des mots de passe** avec bcrypt
- **Protection CSRF** sur tous les formulaires
- **Échappement automatique** des données affichées
- **Validation stricte** des entrées utilisateur
- **Logs de sécurité** pour audit
- **Sessions sécurisées** avec régénération périodique

### Recommandations de Production
- Utiliser HTTPS obligatoire
- Configurer un firewall
- Mettre à jour régulièrement PHP et MySQL
- Sauvegarde automatique quotidienne
- Monitoring des logs

## Maintenance et Évolutivité

### Structure Modulaire
L'architecture refactorisée permet :
- **Ajout facile de nouvelles fonctionnalités**
- **Maintenance simplifiée** du code
- **Tests unitaires** isolés
- **Réutilisation** des composants

### Performance
- **Cache des requêtes** fréquentes
- **Optimisation BDD** avec index appropriés
- **Lazy loading** des ressources JavaScript
- **Compression** des assets statiques

### Sauvegarde
```bash
# Sauvegarde automatique (cron recommandé)
mysqldump -u user -p gestion_academique_ecole > backup_$(date +%Y%m%d_%H%M%S).sql

# Restauration
mysql -u user -p gestion_academique_ecole < backup_file.sql
```

## Support et Contribution

### Documentation Développeur
- **Architecture**: `docs/architecture.md`
- **API**: `docs/api.md`
- **Déploiement**: `docs/deployment.md`

### Contribution
1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonction`)
3. Commit les changements (`git commit -am 'Ajout nouvelle fonction'`)
4. Push la branche (`git push origin feature/nouvelle-fonction`)
5. Créer une Pull Request

### Support
- **Issues**: GitHub Issues pour les bugs
- **Discussions**: GitHub Discussions pour les questions
- **Email**: contact@ecole-excellence.cd

## Roadmap

### Version 2.1 (Q1 2026)
- [ ] Application mobile native
- [ ] API REST complète
- [ ] Notifications en temps réel
- [ ] Intégration calendrier Google

### Version 2.2 (Q2 2026)
- [ ] Système de messagerie interne
- [ ] Gestion des ressources pédagogiques
- [ ] Statistiques avancées avec IA
- [ ] Support multi-établissements

### Version 3.0 (2027)
- [ ] Migration vers microservices
- [ ] Support cloud (Azure/AWS)
- [ ] Intelligence artificielle pour prédictions

---

**LaVision** - Gestion scolaire moderne et efficace pour l'éducation de demain.
