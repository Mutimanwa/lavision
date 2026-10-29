# ADR-008 — Environnements et exploitation

- **Date :** 29 octobre 2026
- **Statut :** proposé
- **Décideurs attendus :** tech lead, DevOps et Direction

## Contexte

Aucun hébergement de production définitif n’est encore choisi. Les migrations, sauvegardes et retours arrière doivent être conçus avant l’initialisation de l’API afin d’éviter une infrastructure dépendante d’un poste ou d’un secret partagé.

## Décision proposée

Prévoir quatre contextes : **local**, **CI**, **staging** et **production**. Docker Compose fournit localement PostgreSQL, Redis et un S3 compatible. La CI utilise des services isolés et des données fictives. Staging et production utilisent des images immuables construites une seule fois puis promues.

- configuration par variables validées au démarrage et secrets dans un gestionnaire dédié, jamais dans Git ;
- frontend statique derrière le reverse proxy, API et worker comme conteneurs séparés ;
- healthchecks, logs structurés avec corrélation, métriques et alertes ; traces OpenTelemetry ajoutées si nécessaire ;
- comptes et données de production interdits sur staging ; anonymisation vérifiée pour toute copie de diagnostic ;
- migrations Prisma exécutées par une étape dédiée, avec artefact et schéma identifiés ;
- déploiement staging automatisable après CI verte, production après approbation explicite et sauvegarde ;
- conservation des artefacts précédents pour rollback applicatif ;
- sauvegardes PostgreSQL chiffrées, versioning du stockage objet et restauration périodique sur environnement vierge.

Les fournisseurs, domaines, RPO/RTO et durées de conservation restent ouverts. Ils doivent être chiffrés et validés dans OPS-001 et OPS-002 avant production.

## Alternatives évaluées

- **installation manuelle sur un serveur unique** : rejetée comme procédure principale pour le manque de reproductibilité ;
- **staging alimenté par une copie brute de production** : rejeté pour la confidentialité ;
- **migration destructive automatique au démarrage de chaque réplique** : rejetée ;
- **sauvegarde sans exercice de restauration** : rejetée, car elle ne prouve pas la reprise.

## Conséquences

- coût minimal de deux environnements non locaux et d’un stockage de sauvegardes ;
- pipeline et runbooks à tester avant mise en production ;
- aucune date de disponibilité publique n’est déduite de cet ADR ;
- la haute disponibilité multi-site n’est pas présumée et dépendra des objectifs validés.

## Critères de vérification

- environnement local reproductible depuis une documentation et des secrets factices ;
- staging déployé avec CI verte et migrations contrôlées ;
- restauration PostgreSQL et fichiers vérifiée sur un environnement vierge ;
- rollback applicatif exécuté en exercice avant production ;
- alerte sur API, worker, base, Redis, stockage et échec de sauvegarde.
