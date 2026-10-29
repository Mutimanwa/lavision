# Socle API, PostgreSQL et recette

## 1. Périmètre livré

Le workspace `apps/api` initialise le monolithe modulaire M1 avec :

- NestJS et Fastify, préfixe global `/api/v1` ;
- contrats partagés Zod dans `packages/contracts` ;
- réponses d’erreur `application/problem+json` avec `code`, `instance` et `requestId` ;
- PostgreSQL 16, Prisma 7 et driver `pg` ;
- migration initiale versionnée et client Prisma généré hors Git ;
- seed idempotent strictement fictif ;
- tests unitaires et tests d’intégration PostgreSQL en CI.

Le schéma initial couvre `Organization`, `Site`, `User` et `OrganizationMembership`. Les contraintes comprennent les identifiants UUID, les clés étrangères restrictives, les codes uniques dans leur portée, l’e-mail `citext` unique et l’appartenance unique par organisation/utilisateur.

**Non inclus :** authentification, MFA, RBAC effectif, audit métier, référentiels scolaires, règles académiques, calculs officiels, finance, paie, données réelles et déploiement de production.

## 2. Démarrage vierge en une commande

Prérequis : Node.js 22.18+, npm et Docker Compose v2.

```bash
npm ci
npm run dev:api:local
```

`dev:api:local` enchaîne :

1. la copie non destructive de `apps/api/.env.example` vers `apps/api/.env` ;
2. `docker compose up -d --wait postgres` ;
3. le build des packages partagés et la génération Prisma ;
4. `prisma migrate deploy` ;
5. le seed fictif ;
6. le serveur de développement NestJS.

La base locale est `lavision_local`. Les identifiants du fichier d’exemple sont exclusivement jetables et ne doivent jamais être réutilisés en staging ou en production.

Pour préparer la base sans lancer le serveur :

```bash
npm run setup:local
```

## 3. Commandes manuelles

Ces commandes permettent de comprendre ou reprendre une étape. Elles s’exécutent depuis la racine et lisent `apps/api/.env` lorsque le workspace API est appelé.

```bash
docker compose up -d --wait postgres
npm run build:packages
npm run prisma:generate
npm run prisma:migrate
npm run prisma:seed
npm run dev:api
```

Variables reconnues :

| Variable | Comportement |
| --- | --- |
| `DATABASE_URL` | obligatoire pour le client PostgreSQL de l’API, du seed et des tests |
| `PORT` | port HTTP de l’API, `3001` dans l’exemple local |
| `APP_VERSION` | version technique retournée par `/version`, `0.1.0` dans l’exemple |
| `TRUST_PROXY` | n’activer que lorsque la chaîne de reverse proxies est maîtrisée |

## 4. Endpoints techniques

| Méthode et chemin | Succès | Vérification |
| --- | --- | --- |
| `GET /api/v1/health/live` | `200` | réponse du processus, sans prétendre que PostgreSQL est prêt |
| `GET /api/v1/health/ready` | `200` ou `503` | exécute `SELECT 1` sur PostgreSQL |
| `GET /api/v1/version` | `200` | expose nom, version applicative et version d’API |
| `GET /api/v1/openapi.json` | `200` | sert le contrat créé depuis les schémas partagés |

Une route inconnue ou une erreur est normalisée en Problem Details. Les détails internes de Prisma, les requêtes SQL et les secrets ne sont pas renvoyés au client.

## 5. OpenAPI et contrats

Le document versionné est `apps/api/openapi/openapi.json`.

```bash
npm run openapi        # régénère le document
npm run openapi:check  # échoue s’il diffère des contrats
```

Les permissions initiales distinguent explicitement `referentials.*` et `rules.*`. Cela prépare les validateurs différents sans créer d’habilitation effective dans #5. Les schémas stricts refusent les propriétés inconnues ; aucun champ calculé envoyé par un client ne devient une vérité serveur.

## 6. Migration et seed

- Schéma : `apps/api/prisma/schema.prisma`.
- Migration : `apps/api/prisma/migrations/20261029140000_initial_core/migration.sql`.
- Seed : `apps/api/prisma/seed-core.ts` et `packages/testing/src/fixtures.ts`.
- Client généré : `apps/api/src/generated/`, ignoré par Git et recréé par `npm run prisma:generate`.

Les migrations de production sont appliquées avec `prisma migrate deploy`, jamais avec `db push`. Les changements ultérieurs doivent préserver l’historique et suivre la politique expand/backfill/contract des ADR. Aucun `migrate reset` n’est autorisé sur une base contenant des données réelles.

Le seed utilise des noms fictifs et le domaine réservé `example.invalid`. Il peut être rejoué sans créer de doublon. Il ne contient ni mot de passe, ni élève, ni donnée nominative issue des documents sources.

## 7. Tests et CI

```bash
npm test
npm run test:integration
```

Les tests d’intégration exigent une base migrée et `DATABASE_URL`. Ils vérifient :

- les unicités et portées PostgreSQL, dont l’e-mail insensible à la casse ;
- les appartenances multi-organisations sans doublon ;
- l’idempotence du seed ;
- le readiness avec une vraie requête SQL ;
- OpenAPI et la normalisation HTTP des erreurs.

Ils exécutent les fichiers séquentiellement et nettoient uniquement la base jetable configurée. La CI fournit un service PostgreSQL 16 isolé, exécute la même initialisation documentée avec `LAVISION_SKIP_DOCKER=true`, puis lance tous les contrôles et les builds Web/API. Le job Windows ne démarre qu’après leur réussite.

## 8. Limites d’exploitation

Le frontend n’appelle pas encore cette API. Le raccordement est prévu en M2 avec l’authentification et les autorisations serveur. PostgreSQL ne doit jamais être exposé au navigateur ou à Tauri.

Avant toute production : compléter #6 et #7, choisir l’hébergement et les RPO/RTO, configurer les secrets et la supervision, tester les sauvegardes/restaurations et faire valider les règles métier applicables. Ce socle ne doit pas être présenté comme un environnement de production.
