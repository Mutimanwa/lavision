# API, PostgreSQL, authentification et RBAC M1

## 1. Périmètre livré

Le workspace `apps/api` contient le monolithe modulaire NestJS/Fastify avec :

- préfixe global `/api/v1`, contrats Zod partagés et OpenAPI 3.1 ;
- réponses `application/problem+json` avec `code`, `instance` et `requestId` ;
- PostgreSQL 16, Prisma 7, migrations expand-only et seed strictement fictif ;
- authentification #6 : invitation, connexion, déconnexion, récupération, sessions opaques révocables, rotation et liste des sessions ;
- Argon2id, cookies HttpOnly/Secure/SameSite=Strict, protection CSRF par double submit ;
- MFA TOTP pour les rôles sensibles de la matrice et codes de récupération à usage unique ;
- limitation progressive des tentatives et événements de sécurité minimisés ;
- noyau #7 : catalogue des 13 rôles, refus par défaut, portées, relations et ressources enregistrées ;
- gestion technique des rôles/permissions et journal d’audit append-only ;
- tests unitaires et tests PostgreSQL exécutés en CI ;
- logs HTTP structurés avec `requestId` et métriques Prometheus protégées par un token technique.

**Non inclus :** endpoints et tables des notes, élèves, paiements, paie et proclamations ; calculs officiels et stratégies métier restantes de #39, raccordement frontend/Tauri et déploiement de production. Les API #8 sont livrées séparément des écrans d’administration, dont le raccordement reste à faire sans réécrire la démonstration existante.

## 2. Démarrage vierge en une commande

Prérequis : Node.js 22.18+, npm et Docker Compose v2.

```bash
npm ci
npm run dev:api:local
```

`dev:api:local` copie `apps/api/.env.example` vers `apps/api/.env` sans écraser un fichier existant, démarre PostgreSQL, génère Prisma, applique les migrations, exécute le seed fictif puis lance l’API.

Pour préparer la base sans lancer le serveur :

```bash
npm run setup:local
```

La base et les identifiants de l’exemple sont exclusivement jetables. Les clés d’exemple ne doivent jamais être utilisées en staging ou production.

## 3. Configuration et commandes manuelles

```bash
docker compose up -d --wait postgres
npm run build:packages
npm run prisma:generate
npm run prisma:migrate
npm run prisma:seed
npm run dev:api
```

| Variable | Rôle |
| --- | --- |
| `DATABASE_URL` | connexion PostgreSQL obligatoire |
| `PORT` | port HTTP, `3001` dans l’exemple |
| `APP_VERSION` | version technique retournée par `/version` |
| `APP_ENV` | contexte technique `local`, `ci` ou `staging` |
| `LOG_LEVEL` | `debug`, `info`, `warn`, `error` ou `silent` |
| `METRICS_TOKEN` | secret technique exigé par `/metrics`, indépendant des sessions |
| `TRUST_PROXY` | à activer uniquement avec une chaîne de reverse proxies maîtrisée |
| `AUTH_ENCRYPTION_KEY` | 32 octets en base64 pour AES-256-GCM et les hashes pseudonymisés ; obligatoire |
| `AUTH_COOKIE_SECURE` | `true` en production ; `false` uniquement pour le HTTP local |
| `AUTH_WEB_BASE_URL` | origine Web utilisée dans les liens d’invitation/récupération |
| `SMTP_URL` | transport SMTP, `smtp://localhost:1025` dans l’exemple |
| `AUTH_MAIL_FROM` | expéditeur des messages transactionnels |

La clé de chiffrement doit être générée et stockée par le coffre de secrets de l’environnement cible. Sa rotation nécessite une procédure dédiée : elle n’est pas simulée dans #6.

## 4. Endpoints techniques et d’authentification

Endpoints publics techniques : `GET /api/v1/health/live`, `GET /api/v1/health/ready`, `GET /api/v1/version` et `GET /api/v1/openapi.json`. `GET /api/v1/metrics` est un endpoint technique protégé : sans `METRICS_TOKEN` configuré il répond `404`, et sans en-tête `Authorization: Bearer …` ou `x-metrics-token` valide il répond `403`.

| Méthode et chemin | Objet |
| --- | --- |
| `POST /api/v1/auth/login` | mot de passe ; retourne une session ou un challenge MFA `202` |
| `POST /api/v1/auth/admin/invitations` | invitation par administration technique |
| `POST /api/v1/auth/invitations/accept` | activation avec mot de passe, puis MFA si requis |
| `POST /api/v1/auth/password-reset/request` | demande de récupération, réponse générique `202` |
| `POST /api/v1/auth/password-reset/confirm` | nouveau mot de passe et révocation des sessions |
| `POST /api/v1/auth/mfa/setup` | création du secret TOTP chiffré et de l’URI `otpauth://` |
| `POST /api/v1/auth/mfa/setup/confirm` | confirmation TOTP et remise unique des codes de récupération |
| `POST /api/v1/auth/mfa/verify` | vérification du second facteur et ouverture de session |
| `POST /api/v1/auth/mfa/recovery` | consommation d’un code de récupération |
| `GET /api/v1/auth/me` | identité, appartenances et session courante |
| `POST /api/v1/auth/refresh` | rotation atomique du jeton opaque et du CSRF |
| `POST /api/v1/auth/logout` | révocation de la session courante |
| `GET /api/v1/auth/sessions` | sessions actives du compte |
| `DELETE /api/v1/auth/sessions/:id` | révocation d’une session du compte |
| `POST /api/v1/auth/admin/users/:id/disable` | désactivation et révocation immédiate |
| `POST /api/v1/auth/admin/users/:id/sessions/revoke` | révocation administrative des sessions |

Les routes d’administration ci-dessus sont limitées à `technical_admin` dans l’organisation indiquée et exigent une session MFA vérifiée. Elles complètent la matrice de permissions métier et les portées de #7, sans donner un accès implicite aux domaines métier.

### Cookies, CSRF et sessions

- `lavision_session` est opaque, hashé en base, HttpOnly, SameSite=Strict et Secure en production ;
- `lavision_csrf` est lisible par le client same-origin, mais sa valeur seule ne suffit pas : toute mutation doit aussi envoyer `x-csrf-token` ;
- les jetons de session, challenges et liens à usage unique ne sont jamais stockés en clair ;
- une session inactive expire après 8 heures et ne peut dépasser 7 jours ; le refresh ne prolonge pas cette limite absolue ;
- la rotation remplace le hash de l’ancien jeton dans la même ligne et rend l’ancien cookie inutilisable ;
- la désactivation, la réinitialisation de mot de passe et la révocation administrative ferment les sessions actives.

### MFA et SEC-18

Les rôles `technical_admin`, `direction`, `accountant`, `cashier` et `hr_manager` exigent un second facteur. Un utilisateur qui a volontairement activé TOTP est également soumis à cette vérification.

Un mot de passe valide ne crée **aucune session métier** pour ces profils : le serveur émet seulement un challenge MFA de 5 minutes. L’enrôlement doit être confirmé par un TOTP, puis les dix codes de récupération sont affichés une seule fois. Le dernier pas TOTP accepté est enregistré pour empêcher le rejeu ; les codes de récupération sont hashés et consommés atomiquement.

## 5. Noyau RBAC, portées et audit

Le catalogue `packages/contracts/src/permissions.ts` est la référence des permissions et des permissions de rôle. Il couvre les treize rôles validés, dont `technical_admin`, `direction`, `teacher`, `guardian`, `auditor` et les rôles financiers. Une permission absente du rôle et sans attribution explicite active est refusée.

`AuthorizationService` vérifie, côté serveur : appartenance active, permission de rôle ou attribution explicite, ressource enregistrée dans la même organisation, périmètre organisation/site/classe/matière et relation enseignant–classe ou responsable–élève. Une ressource inconnue, une autre organisation, une relation absente ou une portée non couvrante produit un refus ; aucun identifiant fourni par le client ne contourne ces contrôles.

Les organisations et sites sont enregistrés automatiquement par la migration #7. Les classes, élèves et ressources métier doivent être enregistrés par leurs futurs services avec leur organisation, leur site éventuel et leur parent. Les attributions explicites et les relations sont séparées des rôles de base et sont révocables sans supprimer l’historique.

### Endpoints de gestion technique

Ces endpoints sont protégés par `security.permissions.manage` et `audit.read` selon le cas. Ils exigent la session et le CSRF pour les mutations, et ne permettent pas à un utilisateur de se modifier lui-même.

| Méthode et chemin | Objet |
| --- | --- |
| `POST /api/v1/security/permissions/grant` | attribuer une permission avec une portée |
| `POST /api/v1/security/permissions/revoke` | révoquer une attribution sans effacer son historique |
| `POST /api/v1/security/roles/assign` | attribuer un rôle dans une organisation, un site, une classe ou une matière |
| `POST /api/v1/security/roles/revoke` | révoquer une attribution de rôle |
| `GET /api/v1/security/audit?organizationId=...` | consulter les événements avec filtres et limite bornée |

La gestion d’une organisation cible vérifie d’abord la permission dans cette organisation, sans exiger que la ressource métier cible existe déjà. Un administrateur d’une autre organisation reste donc refusé ; un administrateur valide peut ensuite créer ou modifier l’attribution demandée.

### Audit et raccordement des futurs modules

`AuditService.record` reçoit l’acteur, l’action, la ressource, le résultat, la date serveur, le `requestId` et un contexte technique minimisé. Seules des clés autorisées sont conservées ; les secrets et objets arbitraires sont écartés. Les triggers PostgreSQL bloquent UPDATE et DELETE du journal, tandis que les suppressions de maintenance utilisent des opérations contrôlées.

Les futurs modules doivent appeler `assertAuthorized` avant chaque commande et écrire l’événement dans la même unité de travail métier que la mutation. Les actions `notes.*`, `payments.*`, `payroll.*`, `proclamations.*` et `permissions.*` disposent ainsi d’un point de journalisation commun ; leurs tables et règles de calcul ne sont pas créées par ce noyau.

La rétention du journal n’est pas fixée arbitrairement ici : elle reste une décision de gouvernance à valider avant production. Aucun événement ne contient de mot de passe, jeton, secret TOTP ou donnée scolaire nominative par défaut.

## 6. Référentiels et calendrier scolaire

Les routes `/api/v1/school` couvrent l’organisation, les sites, niveaux, salles, années scolaires, périodes et jours non ouvrés. Les réponses sont des présentateurs explicites et les entrées sont des contrats Zod stricts. Chaque niveau, salle, année, période et jour est aussi enregistré dans le registre de ressources RBAC avec son organisation, son site éventuel et son parent.

Les années suivent un cycle contrôlé : `DRAFT` → `ACTIVE` → `CLOSED`. L’activation exige au moins une période et une seule année active est autorisée par organisation. Une clôture exige `calendar.close` et un motif ; toute modification ultérieure est refusée. La réouverture exige la permission distincte `calendar.reopen` et un motif, puis remet l’année en `DRAFT` pour correction et nouvelle activation. Les dates de période doivent rester dans l’année ; une contrainte d’exclusion PostgreSQL et les contrôles transactionnels empêchent les chevauchements, y compris en cas de requêtes concurrentes.

Les codes, numéros de séquence et jours non ouvrés ont des contraintes d’unicité. Les référentiels ne sont jamais supprimés physiquement par l’API : les commandes `archive` conservent les lignes et les historiques. Les usages métier futurs devront conserver des références restrictives plutôt que supprimer une ressource utilisée.

Chaque mutation crée, dans la même transaction, une révision avant/après avec acteur, date, `requestId` et motif éventuel, ainsi qu’un événement `AuditService`. `GET /school/history` permet une consultation autorisée et filtrée de ces révisions. Les permissions de préparation des référentiels sont distinctes de celles du calendrier et des règles #39. Le cycle `DRAFT`/`ACTIVE`/`CLOSED` décrit ici l’état opérationnel d’une année ; il ne remplace pas le circuit préparation → validation pédagogique → approbation → publication des référentiels académiques versionnés, dont la composition détaillée des programmes reste un raccordement métier séparé.

## 7. Moteur de règles #39

### Catalogue et validation

`GET /api/v1/rules/catalog?organizationId=...` expose les définitions typées, leurs domaines, portées autorisées, caractère obligatoire et bornes. `POST /api/v1/rules/validate` valide une valeur sans la persister. Les décimales sont normalisées en chaînes pour éviter une conversion binaire silencieuse ; formules, scripts, objets non prévus et valeurs hors bornes sont refusés. Le catalogue initial ne contient aucune valeur par défaut et reste à enrichir pour couvrir exhaustivement les listes structurées du cadrage.

La permission générique du guard ne suffit jamais : le service vérifie aussi la permission du domaine (`rules.academic.*`, `rules.finance.*`, etc.). Les erreurs de commandes #39 peuvent contenir des `issues` `{ path, code, message }` dans le Problem Details.

### Sets, portées et versions

Un `RuleSet` appartient à une organisation et un domaine, déclare ses `definitionKeys` et son `scopeOrder`. Cet ordre est obligatoire et visible : le moteur ne choisit pas silencieusement une priorité métier encore ouverte. Les bindings référencent des ressources RBAC de la même organisation ; les types `section`, `option` et `program` sont disponibles pour les futurs référentiels académiques.

| Méthode et chemin | Objet |
| --- | --- |
| `GET/POST /api/v1/rules/sets` | lister ou créer un ensemble cohérent |
| `GET /api/v1/rules/sets/:id` | lire l’ensemble et ses versions |
| `POST /api/v1/rules/sets/:id/versions` | créer un brouillon daté |
| `GET/PATCH /api/v1/rules/versions/:id` | lire ou modifier un brouillon uniquement |
| `POST /api/v1/rules/versions/:id/duplicate` | dupliquer en nouveau brouillon, sans copier les approbations |
| `POST /api/v1/rules/versions/:id/submit` | préparation → revue, après contrôle de la configuration résolue |
| `POST /api/v1/rules/versions/:id/review` | validation pédagogique par un acteur distinct du préparateur |
| `POST /api/v1/rules/versions/:id/approve` | approbation avec permission dédiée |
| `POST /api/v1/rules/versions/:id/publish` | publication ou planification après simulation réussie du hash exact |
| `POST /api/v1/rules/versions/:id/archive` | archiver sans supprimer les valeurs ni l’historique |
| `GET /api/v1/rules/versions/:id/diff/:againstId` | comparer valeurs, bindings et dates |
| `GET /api/v1/rules/versions/:id/revisions` | consulter les révisions avant/après |

Les statuts sont `DRAFT`, `IN_REVIEW`, `VALIDATED`, `APPROVED`, `PUBLISHED`, `SCHEDULED` et `ARCHIVED`. Les dates sont des intervalles `[effectiveFrom, effectiveTo)` et le fuseau IANA est obligatoire. À portée égale, la version dont la date d’effet est la plus récente et déjà applicable succède à la précédente ; deux versions différentes avec la même date, la même portée et la même clé sont ambiguës et rejetées. Une version publiée/planifiée est protégée par PostgreSQL contre toute modification de contenu ; son archivage ne réécrit pas ses valeurs.

### Résolution, simulation et snapshots

`POST /api/v1/rules/resolve` utilise uniquement les versions publiées/planifiées applicables à la date et au contexte fournis. La réponse distingue valeur, source exacte, héritage, règles manquantes, non configurées et conflits. Une adaptation partielle peut hériter d’une version organisationnelle ; la soumission et la publication contrôlent la configuration résolue, pas seulement les lignes locales.

`POST /api/v1/rules/versions/:id/simulate` appelle ce même résolveur pour le brouillon et le référentiel de comparaison. Le rapport append-only contient les cas, les provenances, les avertissements et les erreurs ; il ne crée aucune note, facture, paiement, paie ou notification. À ce stade, la simulation couvre la résolution et la cohérence des valeurs, pas encore les calculs académiques, financiers ou de paie officiels. La publication exige une simulation `PASSED` du `contentHash` exact et au moins un cas où la candidate est applicable.

`POST /api/v1/rules/snapshots` fige la configuration résolue et son empreinte pour un identifiant technique de bulletin, proclamation, facture ou paie. Le snapshot est append-only et idempotent pour un même document/version/contexte. Le service métier qui créera réellement le document devra vérifier l’existence et l’appartenance du document avant d’appeler cette primitive ; #39 ne crée aucun document officiel.

Les questions de valeurs et de politique restant à trancher sont consignées dans `docs/cadrage/14_QUESTIONS_MOTEUR_REGLES.md`.

## 8. Limitation et journalisation

Les échecs de connexion sont limités par compte et par IP dans une fenêtre de 15 minutes, avec blocage progressif borné. Les challenges MFA ont en outre un compteur de cinq essais. Les demandes de récupération sont limitées pour réduire le spam et conservent une réponse identique pour les adresses inconnues.

`auth_events` enregistre l’action, le résultat, un motif technique, la date et des hashes HMAC du sujet/IP. Il ne contient ni mot de passe, ni hash de mot de passe, ni jeton brut, ni secret TOTP, ni code de récupération, ni email en clair. Les erreurs SMTP et les détails Prisma ne sont pas renvoyés au client.

## 9. Migration, seed et contrats

- Schéma : `apps/api/prisma/schema.prisma` ;
- migration initiale : `20261029140000_initial_core` ;
- migration #6 : `20261029153000_authentication_mfa` ;
- migration #7 : `20261029163000_rbac_audit` ;
- migration #8 : `20261029173000_school_referentials` ;
- migrations #39 : `20261029190000_rule_engine` et `20261029210000_rule_archive_guard` ;
- seed : `apps/api/prisma/seed-core.ts` et `packages/testing/src/fixtures.ts` ;
- contrat versionné : `apps/api/openapi/openapi.json`.

```bash
npm run openapi        # régénère OpenAPI
npm run openapi:check  # échoue si le document diffère
```

Les migrations sont appliquées avec `prisma migrate deploy`, jamais avec `db push`. La migration #6 est additive et ne réécrit pas les documents historiques ni les règles métier.

## 10. Tests et CI

```bash
npm test
npm run test:integration
npm run typecheck
```

Les tests PostgreSQL couvrent les contraintes du socle, les parcours #6, les scénarios #7, les référentiels #8 et le moteur #39 : refus d’une classe ou d’un enfant non rattaché, isolation inter-organisations, gestion cross-tenant, attribution et révocation, portée limitée à une classe, lecture d’audit restreinte, refus UPDATE/DELETE du journal, activation unique, clôture/réouverture, doublons, chevauchements SQL, archivage et historique avant/après. Pour #39, ils couvrent l’héritage par programme, les dates successives, la duplication, la séparation préparateur/validateur, les permissions de domaine, les snapshots et les refus SQL d’altération des versions publiées. Ils vérifient aussi la minimisation des événements et l’absence de sessions avant le second facteur.

La CI utilise PostgreSQL 16 isolé, `LAVISION_SKIP_DOCKER=true`, une clé de test non réutilisable et le même chemin de migration/seed que le démarrage local. Le sandbox sans PostgreSQL ne constitue pas une preuve d’intégration.

## 11. Limites d’exploitation

Le frontend n’appelle pas encore cette API : le raccordement Web/Tauri reste prévu en M2. Pour Tauri, ce raccordement devra respecter ADR-006 : navigateur système, PKCE, jetons courts avec rotation et stockage dans le coffre de l’OS, jamais de jeton durable dans `localStorage`.

Avant toute production : brancher les modules métier sur #7 et les référentiels #8, raccorder les clients et les écrans d’administration, choisir l’hébergement et les RPO/RTO, valider la rétention d’audit, configurer les secrets/SMTP/supervision, tester les restaurations et faire valider les règles métier. Aucun calcul officiel, donnée réelle ou déploiement de production n’est introduit par #6, #7, #8 ou #39. Les écrans de configuration et les compositions détaillées sections/options/programmes restent à raccorder ; le catalogue et les stratégies de calcul métier doivent être complétés avant la recette fonctionnelle correspondante.
