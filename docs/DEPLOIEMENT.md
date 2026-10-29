# Déploiement Web et limites du socle

## 1. Vérifications avant livraison

```bash
npm ci
npm test
npm run typecheck
npm run lint
npm run openapi:check
npm run build
```

Le build complet produit l’API compilée dans `apps/api/dist/` et le frontend statique dans `apps/web/out/`. Ces dossiers ne sont pas versionnés.

Les tests PostgreSQL nécessitent en plus une base jetable migrée :

```bash
npm run test:integration
```

## 2. Hébergement du frontend statique

Publier le contenu de `apps/web/out/` sur un serveur statique :

- Netlify ou plateforme équivalente : build racine `npm run build:web`, publication `apps/web/out` ;
- Vercel : configurer explicitement le projet pour le workspace `apps/web` et son export statique ;
- serveur mutualisé : téléverser le contenu de `apps/web/out/` dans le document root ;
- stockage objet : activer l’hébergement statique et configurer `404.html` comme document d’erreur.

Les routes sont exportées avec un slash final (`/eleves/`, `/notes/`, etc.). Le serveur doit servir le fichier `index.html` présent dans chaque dossier de route.

## 3. Docker Web

Le `Dockerfile` racine installe les workspaces, construit uniquement le frontend puis le sert avec Nginx :

```bash
docker build -t lavision:0.1.0 .
docker run --rm -p 8080:80 --name lavision lavision:0.1.0
```

L’application est disponible sur `http://localhost:8080`. Pour un déploiement public, placer le conteneur derrière le reverse proxy de la plateforme et activer HTTPS.

Ce Dockerfile ne contient pas l’API NestJS. Une image API et sa stratégie de migration contrôlée seront traitées avec les environnements de l’issue #9.

## 4. GitHub Actions

Le workflow `.github/workflows/ci.yml` :

1. démarre un service PostgreSQL 16 jetable ;
2. applique les migrations du socle et de l’authentification puis le seed fictif via la procédure d’initialisation documentée ;
3. exécute les tests unitaires et PostgreSQL, dont les parcours #6, OpenAPI, le typecheck et le lint ;
4. construit l’API et le frontend, puis conserve `apps/web/out/` comme artefact temporaire ;
5. compile les installateurs Windows après la réussite de ces contrôles.

Il ne déploie vers aucun hébergement et ne contient aucun secret de production.

## 5. Aperçu de développement

Dans Arena, le serveur Web est lié à `0.0.0.0:3000`, ce qui permet au proxy d’aperçu d’y accéder. L’API écoute sur `0.0.0.0:3001`. Aucun code navigateur ne doit appeler `localhost` pour atteindre un second service du sandbox ; le raccordement M2 utilisera des URL adaptées à l’environnement et au reverse proxy.

## 6. Limites actuelles

Un hébergement statique publie le **prototype local**. Chaque visiteur dispose de son propre jeu de données dans son navigateur. L’API contient le noyau d’authentification #6, mais le frontend ne lui est pas encore raccordé et aucune route métier n’est ouverte.

Avant une mise en production avec des données réelles :

1. livrer le RBAC/audit complet de #7 et raccorder les repositories frontend à l’API authentifiée ;
2. appliquer les exigences ADR-006 au client Tauri (navigateur système, PKCE, rotation et coffre OS) ;
3. choisir l’hébergement, les domaines, les RPO/RTO et la politique de sauvegarde ;
4. activer HTTPS, les en-têtes de sécurité, la supervision et les alertes ;
5. tester la restauration sur un environnement vierge ;
6. faire valider les règles académiques locales et la confidentialité ;
7. réaliser les tests de charge et l’audit de sécurité.

Aucun nom de domaine, fournisseur cloud ou secret de production n’est supposé dans ce dépôt.
