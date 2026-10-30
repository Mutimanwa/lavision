# lavision

**Logiciel de gestion scolaire, de l’inscription à la proclamation.**

Le dépôt est un monorepo **Next.js / NestJS / PostgreSQL**. Il contient le prototype d’interfaces déjà livré et le premier socle technique M1, sans réécrire les features existantes.

## État actuel

- **Web et desktop :** prototype Next.js, TypeScript, Tailwind CSS et Tauri. Les modules de démonstration restent locaux ; **Paramètres → Configuration scolaire** dispose désormais d’un premier parcours API authentifié pour le catalogue et les brouillons de règles.
- **API :** socle NestJS/Fastify versionné sous `/api/v1`, contrats Zod/OpenAPI, authentification/MFA, RBAC à portées, référentiels #8 et moteur technique #39.
- **Base et worker :** PostgreSQL/Prisma, migrations contrôlées, seed fictif, Redis et worker technique supervisé, sans job métier officiel.
- **Exploitation :** images Web/API/worker/migration, Compose local et staging, logs JSON, métriques protégées et exercice de rollback.
- **CI :** tests unitaires et d’intégration PostgreSQL, typecheck, lint, builds Web/API/worker, installateurs Windows et smoke staging avec rollback.

> **Limites :** le raccordement API est progressif : seul le parcours de préparation des règles dans **Paramètres → Configuration scolaire** est couvert à ce stade ; les autres modules conservent leurs données de démonstration locales. Les validations métier/UI détaillées de #8 et #39 restent ouvertes. Aucune donnée réelle, règle officielle, alerte externe ou infrastructure de production n’est livrée. Le moteur académique officiel et la finance/paie restent dans leurs issues dédiées. Le sélecteur de rôle du frontend est uniquement un aperçu UX.

## Structure du monorepo

```text
apps/
├── web/                       # frontend Next.js et features existantes
│   ├── src/app/(school)/      # route groups, URL publiques inchangées
│   ├── src/components/        # composants réutilisables partagés
│   ├── src/features/          # pages, hooks, services et données métier
│   ├── src/lib/               # types et calculs purs du prototype
│   └── out/                   # export statique généré, non versionné
├── api/                       # API NestJS/Fastify
│   ├── prisma/                # schéma, migrations et seed fictif
│   ├── src/modules/           # système, auth, RBAC, école et règles
│   ├── src/database/          # client PostgreSQL/Prisma partagé
│   ├── test/integration/      # tests sur PostgreSQL réel
│   └── openapi/openapi.json   # contrat OpenAPI 3.1 généré
└── worker/                    # processus technique Redis/PostgreSQL supervisé
packages/
├── contracts/                 # schémas Zod, erreurs, permissions, OpenAPI
└── testing/                   # fixtures entièrement fictives
scripts/                       # serveur statique et initialisation locale
src-tauri/                     # shell Windows/Rust
compose.yml                    # stack local Web/API/worker/PostgreSQL/Redis
deploy/                        # staging, Nginx, Prometheus et alertes initiales
legacy/                        # prototype PHP/HTML initial, référence seulement
docs/                          # cadrage, architecture et guides
```

Les règles de dépendance du frontend sont décrites dans [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Prérequis

- **Node.js 22.18 ou supérieur** et npm ;
- **Docker avec Compose v2** pour le démarrage PostgreSQL local en une commande ;
- Rust et les prérequis Tauri uniquement pour compiler le desktop.

## Démarrer le prototype Web

```bash
npm ci
npm run dev:web
```

Ouvrir `http://localhost:3000`. Cette commande n’a pas besoin de PostgreSQL et ne modifie pas les données locales existantes.

## Démarrer le socle API sur un environnement vierge

Après `npm ci`, une seule commande prépare la base et démarre l’API :

```bash
npm run dev:api:local
```

Elle copie `apps/api/.env.example` vers `apps/api/.env` si nécessaire, démarre PostgreSQL, génère Prisma, applique les migrations, exécute le seed anonymisé puis lance l’API. Un `.env` existant n’est jamais écrasé.

Endpoints disponibles sur `http://localhost:3001` :

| Endpoint | Objet |
| --- | --- |
| `GET /api/v1/health/live` | disponibilité du processus |
| `GET /api/v1/health/ready` | vérification réelle de PostgreSQL |
| `GET /api/v1/version` | nom et versions du service/API |
| `GET /api/v1/openapi.json` | document OpenAPI 3.1 |

Les routes `/api/v1/auth/*` couvrent désormais invitation, sessions, récupération et MFA. Elles sont décrites avec leurs critères de sécurité dans [`docs/API.md`](docs/API.md). Le premier raccordement frontend utilise la session existante, sans la simuler : sélection de l’établissement, catalogue, validation d’une valeur et création d’un brouillon de règle.

Pour arrêter les conteneurs : `docker compose down`. Le volume local peut être supprimé explicitement avec `docker compose down -v` si toutes les données locales jetables peuvent être perdues.

Le guide complet, les commandes manuelles et les limites sont dans [`docs/API.md`](docs/API.md).

## Démarrer le stack Docker complet

```bash
docker compose up --build --wait
```

Le Web est servi sur `http://localhost:8080` et appelle l’API via le proxy relatif `/api`. PostgreSQL, Redis, l’API, le worker, le Web et la migration one-shot sont orchestrés ensemble. Le worker est un socle supervisé sans traitement métier officiel.

Dans **Paramètres → Configuration scolaire**, le navigateur lit `GET /api/v1/auth/me`, puis le catalogue et les ensembles de règles de l’établissement choisi. Les mutations envoient le cookie de session et le jeton CSRF ; la source, le motif, la date d’effet et le fuseau sont obligatoires. Le bouton d’enregistrement crée uniquement une version `DRAFT` : il ne soumet, n’approuve ni ne publie aucune règle. Les états sans session, sans habilitation et d’erreur API restent visibles.

Les commandes staging, les secrets, le rollback et le diagnostic sont documentés dans [`docs/RUNBOOK_EXPLOITATION.md`](docs/RUNBOOK_EXPLOITATION.md).

## Vérifications et builds

```bash
npm test                 # tests unitaires, frontend et contrats
npm run test:integration # nécessite PostgreSQL migré et DATABASE_URL
npm run typecheck        # tous les workspaces
npm run lint
npm run openapi:check    # vérifie que le document versionné est à jour
npm run build            # build API + worker + export statique Web
npm run build:web        # frontend seul, notamment pour Tauri/Docker
npm run test:deployment  # transitions de déploiement et règles de rollback
npm run check:secrets    # motifs de secrets à haute confiance dans Git
npm run check:rules      # couverture du catalogue configurable, sans valeur par défaut
```

L’export Web est généré dans `apps/web/out/`. Les clients Prisma, builds et artefacts ne sont pas versionnés ; la migration SQL et le document OpenAPI le sont. Pour servir cet export avec le parcours API hors Docker, `npm start` relaie `/api` côté serveur vers l’API locale du socle ; `API_PROXY_TARGET` permet de choisir une autre API sans mettre d’URL distante dans le navigateur. Un hébergement purement statique sans proxy ne fournit pas ce parcours.

## Prototype fonctionnel existant

Les interfaces conservées comprennent notamment le tableau de bord par rôle, les inscriptions, les élèves, les classes, les notes, les bulletins, les présences, la proclamation, les paramètres et l’activation progressive des modules. Elles continuent d’utiliser leurs services locaux actuels.

Ces écrans ne constituent pas une preuve de calcul officiel. Les sections, options, cours, maxima, pondérations et validateurs restent des configurations locales versionnées à traiter dans les issues métier ; aucune valeur inconnue n’est devinée dans le socle.

## Docker Web

La cible Docker par défaut construit le frontend statique et le sert avec Nginx ; les autres cibles couvrent l’API, le worker et les migrations :

```bash
docker build -t lavision:0.1.0 .
docker run --rm -p 8080:80 --name lavision lavision:0.1.0
```

Cela ne déploie ni l’API ni une production sécurisée. Voir [`docs/DEPLOIEMENT.md`](docs/DEPLOIEMENT.md).

## Application Windows

```bash
npm run tauri dev
npm run tauri build
```

Tauri utilise le build Web de `apps/web/out/` et produit les installateurs NSIS/MSI sous `src-tauri/target/release/bundle/`. La CI Windows les publie comme artefacts ; aucun exécutable n’est stocké dans Git. Voir [`docs/DESKTOP.md`](docs/DESKTOP.md).

## Documentation

- [Socle API, PostgreSQL et recette](docs/API.md)
- [Architecture et modèle métier](docs/ARCHITECTURE.md)
- [ADR de l’architecture M1](docs/adr/README.md)
- [Cahier des charges](docs/CAHIER_DES_CHARGES.md)
- [Plan de réalisation](docs/PLAN_DE_REALISATION.md)
- [Backlog GitHub](docs/BACKLOG.md)
- [Dossier de cadrage métier M0](docs/cadrage/README.md)
- [Revue de sortie M0 et périmètre #5](docs/cadrage/13_REVUE_DE_SORTIE_M0.md)
- [Interfaces par rôle et activation progressive](docs/cadrage/11_INTERFACES_PAR_ROLE_ET_ACTIVATION.md)
- [Déploiement et environnements](docs/DEPLOIEMENT.md)
- [Runbook local, staging, rollback et diagnostic](docs/RUNBOOK_EXPLOITATION.md)
- [Application desktop Tauri](docs/DESKTOP.md)
- [Feuille de route production](docs/ROADMAP.md)
