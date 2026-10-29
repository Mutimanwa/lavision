# Revue de sortie M0 et feu vert pour l’issue #5

**Date :** 29 octobre 2026

**Références :** issues #2, #3, #4 et #5

**Statut :** feu vert limité à l’initialisation du socle M1 ; M0 n’est pas déclaré entièrement clôturé

## 1. Objet

Cette revue distingue les décisions nécessaires pour initialiser l’API des validations qui peuvent se poursuivre sans bloquer ce socle. Elle évite deux écueils : commencer sans architecture ni permissions, ou attendre inutilement que chaque établissement ait renseigné toutes ses valeurs locales.

## 2. Validations enregistrées le 29 octobre 2026

| Sujet | Réponse du représentant produit | Décision |
| --- | --- | --- |
| Configuration académique | quatre étapes : préparation, validation pédagogique, approbation et publication ; référentiels scolaires et règles académiques séparés, avec validateurs potentiellement différents | DEC-010 |
| Architecture | huit ADR acceptés pour M1 et pour permettre #5 | DEC-011 |
| Permissions et confidentialité | base des 13 rôles, portées, refus par défaut, administration technique limitée et scénarios SEC acceptée pour M1 | DEC-012 |

Ces validations autorisent le développement du socle. Elles ne créent aucune habilitation nominative et ne remplacent pas les revues techniques, métier, sécurité et DevOps nécessaires à la clôture des issues ou à la production.

## 3. Verdict

**GO pour l’initialisation de l’issue #5**, dans le périmètre suivant :

- structure du monorepo et préservation du frontend/Tauri existants ;
- API NestJS/Fastify versionnée `/api/v1` ;
- contrats Zod, OpenAPI et erreurs `application/problem+json` ;
- PostgreSQL/Prisma, migrations et seed strictement anonymisé ;
- premiers agrégats organisations, sites et utilisateurs avec contraintes ;
- endpoints de santé et de version ;
- tests d’intégration sur PostgreSQL en CI.

**Hors de ce premier incrément :** authentification réelle, habilitations effectives, moteur de calcul officiel, finance, paie, données réelles et déploiement de production.

## 4. Points qui restent ouverts sans bloquer #5

| Point ouvert | Pourquoi il ne bloque pas le socle | Ce qu’il bloque encore |
| --- | --- | --- |
| Valeurs des sections, options, cours et maxima de chaque école | elles seront des données locales versionnées, pas des constantes du code | publication de résultats officiels pour le programme concerné |
| Noms des validateurs et approbateurs | les permissions et le circuit peuvent être modélisés sans attribuer les personnes | habilitation effective et publication réelle |
| Hébergement, domaines, RPO/RTO et durées de sauvegarde | le socle local et la CI utilisent des services jetables | staging définitif et production |
| Durées de conservation et destinataires externes | aucune donnée réelle n’est reprise dans #5 | politique de confidentialité finale et exploitation réelle |
| Règles de repêchage, délibération et pondérations locales | le moteur doit conserver l’état « à configurer » et bloquer les résultats inconnus | calculs et proclamations officiels |
| Règles financières, fiscales et de paie | elles ne font pas partie du schéma initial #5 | modules M3/M4 réels |

## 5. Issues M0 maintenues ouvertes

- **#2 :** propriétaires de processus, exemples finance/paie et validations métier restent à compléter.
- **#3 :** les ADR sont acceptés pour M1, mais les revues tech lead/sécurité/DevOps et les preuves de mise en œuvre restent attendues.
- **#4 :** la base des rôles est acceptée ; les habilitations nominatives, la politique de conservation et la validation de la direction restent ouvertes.

Aucune de ces issues ne doit être clôturée sur la seule base du présent feu vert.

## 6. Garde-fous de réalisation

1. Ne pas réécrire les interfaces par rôle déjà livrées et ne pas casser les builds Web/Tauri.
2. Ne jamais exposer PostgreSQL au navigateur ou à Tauri.
3. Utiliser uniquement des données fictives ou anonymisées dans les migrations, seeds et tests.
4. Ne pas introduire de valeur académique par défaut pour combler une règle locale manquante.
5. Distinguer dès les contrats les permissions `referentials.*` et `rules.*`.
6. Faire échouer les champs calculés envoyés par le client plutôt que les accepter comme vérité.
7. Conserver les migrations et erreurs testables en CI sur une vraie instance PostgreSQL.

## 7. Suite

Après le socle #5 : #6 authentification/MFA, #7 RBAC/audit, #8 référentiels, #39 moteur de règles et #9 environnements. Les interfaces existantes seront connectées en M2 plutôt que réécrites.
