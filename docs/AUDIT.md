# Audit du projet initial

Audit réalisé à partir du commit `5c74c34a354abe6d64840b4d1544e2b404f3a505` (`initialisation du projet de gestion scolaire`).

## 1. Inventaire

Le dépôt initial contient **118 fichiers suivis**, environ **17 Mo** avec les métadonnées Git :

| Type | Quantité | Observation |
| --- | ---: | --- |
| PHP | 33 | pages et fragments principalement composés de HTML |
| HTML | 27 | pages de démonstration Falcon/Eduport |
| JavaScript | 20 | thème et bibliothèques vendorisées |
| CSS | 9 | thème minifié et dépendances |
| Images / données | reste | logos, avatars, icônes et données de thème |

Il n’y avait ni `package.json`, ni framework frontend, ni suite de tests, ni configuration de déploiement, ni schéma de base de données scolaire.

## 2. Architecture constatée

- pages PHP à la racine, avec inclusion de `header.php` et `footer.php` ;
- interface basée sur un thème **Falcon**, avec quelques contenus **Eduport** ;
- Bootstrap et nombreuses bibliothèques copiées dans `vendors/` ;
- données élèves, enseignants, classes et statistiques majoritairement écrites en dur dans le HTML ;
- scripts de formulaire qui affichent surtout des alertes sans persistance métier ;
- navigation très large, mais de nombreuses cibles absentes (`cours.php`, `exames.php`, `notes.php`, finance, statistiques, configuration, etc.) ;
- pages sans rapport avec la gestion scolaire : e-commerce, support desk, réseau social, FAQ du thème et timeline Apple ;
- doublons ou ébauches : `per.php` / `personnels.php`, `optionsv1.php`, et `nouveau-prof.php` reprenant largement un formulaire d’inscription élève.

L’ancien dossier `app/` entrait en conflit avec l’App Router de Next.js. Le prototype initial et ses assets ont été déplacés sans suppression dans `legacy/`.

## 3. Base de données et authentification initiales

`system_sec.php` est le seul fichier qui utilise réellement une base de données :

- SQLite via PDO, dans `database.db` à la racine ;
- création d’une table `users(id, username, password)` ;
- requêtes préparées et hachage des mots de passe avec `password_hash`, deux bons points ;
- inscription ouverte et aucune gestion de rôles ;
- absence de protection CSRF et de politique de session visible ;
- redirection après connexion vers `dashboard.php`, fichier absent du dépôt ;
- aucune table pour les élèves, inscriptions, classes, matières, notes ou proclamations.

Le fichier SQLite potentiellement sensible est désormais exclu de Git. L’ancien mécanisme n’est pas réutilisé tel quel dans Next.js.

## 4. Problèmes fonctionnels et techniques

### Critiques

- pas de source de vérité commune entre les modules ;
- pas de véritable enregistrement des inscriptions et notes ;
- pas de calcul de bulletin ou de proclamation ;
- liens de navigation et ressources locales manquants ;
- authentification non intégrée à un parcours applicatif fonctionnel.

### Importants

- contenu de démonstration mélangé au code métier ;
- nombreuses références à des assets absents (un scan automatique remonte environ 784 occurrences, en majorité des liens de démonstration du thème) ;
- absence de typage, validation partagée, tests et contrôles automatisés ;
- interface uniquement en PHP/HTML difficile à faire évoluer sans duplication ;
- aucune stratégie de sauvegarde ou déploiement reproductible.

### Maintenabilité

- `header.php` dépasse 850 lignes et contient navigation, scripts et contenus de template ;
- `index.php` dépasse 1 000 lignes et `reports.php` plus de 1 300 lignes, ce dernier étant un rapport de support technique sans lien avec l’école ;
- code JavaScript impératif propre à chaque page ;
- dépendances vendorisées sans gestion de versions applicative.

## 5. Périmètre conservé dans la refonte

Les intentions métier utiles ont été reprises :

1. tableau de bord ;
2. inscription et liste des élèves ;
3. classes, options/matières et coefficients ;
4. présences ;
5. notes et bulletins ;
6. proclamation ;
7. paramètres de l’établissement.

Les pages e-commerce, réseau social et support du thème ne font pas partie du nouveau produit. Les fichiers PHP et leurs assets sont conservés dans `legacy/` comme référence historique ; ils ne participent pas au build Next.js.

## 6. Résultat de la première migration

- Next.js App Router sous `src/app/` ;
- TypeScript strict et Tailwind CSS 4 ;
- modèle de données unifié et typé ;
- moteur académique centralisé et testé ;
- 9 routes métier exportées statiquement ;
- interface responsive et documents imprimables ;
- sauvegarde/restauration JSON et exports CSV ;
- socle Tauri 2 et workflows CI ;
- lint, tests et build de production automatisables.

## 7. Points restant obligatoires avant exploitation réelle

Cette itération ne doit pas être présentée comme un système multiutilisateur final. Avant de gérer de vraies données scolaires, il faut notamment :

- authentification serveur et rôles (direction, secrétariat, enseignant, lecture seule) ;
- base de données centralisée et migrations versionnées ;
- autorisations appliquées côté serveur ;
- journal d’audit des opérations sensibles ;
- sauvegardes automatiques et procédure de restauration ;
- validation des règles académiques avec l’établissement ;
- politique de confidentialité et protection des données mineures ;
- éventuelle signature de code et mécanisme de mise à jour du desktop.
