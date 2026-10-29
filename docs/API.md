# API, PostgreSQL et authentification M1

## 1. Périmètre livré

Le workspace `apps/api` contient le monolithe modulaire NestJS/Fastify avec :

- préfixe global `/api/v1`, contrats Zod partagés et OpenAPI 3.1 ;
- réponses `application/problem+json` avec `code`, `instance` et `requestId` ;
- PostgreSQL 16, Prisma 7, migrations expand-only et seed strictement fictif ;
- authentification #6 : invitation, connexion, déconnexion, récupération, sessions opaques révocables, rotation et liste des sessions ;
- Argon2id, cookies HttpOnly/Secure/SameSite=Strict, protection CSRF par double submit ;
- MFA TOTP pour les rôles sensibles de la matrice et codes de récupération à usage unique ;
- limitation progressive des tentatives et événements de sécurité minimisés ;
- tests unitaires et tests PostgreSQL exécutés en CI.

**Non inclus :** RBAC métier et portées complètes de #7, audit métier, référentiels scolaires, règles académiques, calculs officiels, finance/paie, raccordement frontend/Tauri et déploiement de production.

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
| `TRUST_PROXY` | à activer uniquement avec une chaîne de reverse proxies maîtrisée |
| `AUTH_ENCRYPTION_KEY` | 32 octets en base64 pour AES-256-GCM et les hashes pseudonymisés ; obligatoire |
| `AUTH_COOKIE_SECURE` | `true` en production ; `false` uniquement pour le HTTP local |
| `AUTH_WEB_BASE_URL` | origine Web utilisée dans les liens d’invitation/récupération |
| `SMTP_URL` | transport SMTP, `smtp://localhost:1025` dans l’exemple |
| `AUTH_MAIL_FROM` | expéditeur des messages transactionnels |

La clé de chiffrement doit être générée et stockée par le coffre de secrets de l’environnement cible. Sa rotation nécessite une procédure dédiée : elle n’est pas simulée dans #6.

## 4. Endpoints techniques et d’authentification

Endpoints publics techniques : `GET /api/v1/health/live`, `GET /api/v1/health/ready`, `GET /api/v1/version` et `GET /api/v1/openapi.json`.

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

Les routes d’administration ci-dessus sont limitées à `technical_admin` dans l’organisation indiquée et exigent une session MFA vérifiée. Elles ne constituent pas la matrice de permissions métier de #7.

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

## 5. Limitation et journalisation

Les échecs de connexion sont limités par compte et par IP dans une fenêtre de 15 minutes, avec blocage progressif borné. Les challenges MFA ont en outre un compteur de cinq essais. Les demandes de récupération sont limitées pour réduire le spam et conservent une réponse identique pour les adresses inconnues.

`auth_events` enregistre l’action, le résultat, un motif technique, la date et des hashes HMAC du sujet/IP. Il ne contient ni mot de passe, ni hash de mot de passe, ni jeton brut, ni secret TOTP, ni code de récupération, ni email en clair. Les erreurs SMTP et les détails Prisma ne sont pas renvoyés au client.

## 6. Migration, seed et contrats

- Schéma : `apps/api/prisma/schema.prisma` ;
- migration initiale : `20261029140000_initial_core` ;
- migration #6 : `20261029153000_authentication_mfa` ;
- seed : `apps/api/prisma/seed-core.ts` et `packages/testing/src/fixtures.ts` ;
- contrat versionné : `apps/api/openapi/openapi.json`.

```bash
npm run openapi        # régénère OpenAPI
npm run openapi:check  # échoue si le document diffère
```

Les migrations sont appliquées avec `prisma migrate deploy`, jamais avec `db push`. La migration #6 est additive et ne réécrit pas les documents historiques ni les règles métier.

## 7. Tests et CI

```bash
npm test
npm run test:integration
npm run typecheck
```

Les tests PostgreSQL couvrent les contraintes du socle et les parcours #6 : invitation, MFA obligatoire, anti-rejeu TOTP, codes de récupération, expiration, rotation, CSRF, récupération de mot de passe, déconnexion, révocation, désactivation et force brute. Ils vérifient aussi la minimisation des événements et l’absence de sessions avant le second facteur.

La CI utilise PostgreSQL 16 isolé, `LAVISION_SKIP_DOCKER=true`, une clé de test non réutilisable et le même chemin de migration/seed que le démarrage local. Le sandbox sans PostgreSQL ne constitue pas une preuve d’intégration.

## 8. Limites d’exploitation

Le frontend n’appelle pas encore cette API : le raccordement Web/Tauri reste prévu en M2. Pour Tauri, ce raccordement devra respecter ADR-006 : navigateur système, PKCE, jetons courts avec rotation et stockage dans le coffre de l’OS, jamais de jeton durable dans `localStorage`.

Avant toute production : livrer #7, raccorder les clients, choisir l’hébergement et les RPO/RTO, configurer les secrets/SMTP/supervision, tester les restaurations et faire valider les règles métier. Aucun calcul officiel, donnée réelle ou déploiement de production n’est introduit par #6.
