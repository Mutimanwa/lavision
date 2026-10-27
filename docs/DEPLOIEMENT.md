# Déploiement web

## 1. Vérifications avant livraison

```bash
npm ci
npm test
npm run lint
npm run build
```

Le résultat attendu est un dossier `out/` contenant les pages statiques et leurs assets. Ne pas committer `out/` : il est régénéré par la CI ou au déploiement.

## 2. Hébergement statique

Publier le contenu de `out/` sur n’importe quel serveur statique :

- Netlify : build `npm run build`, dossier de publication `out` ;
- Vercel : importer le dépôt et conserver la détection Next.js ;
- serveur mutualisé : téléverser le contenu de `out/` dans le document root ;
- S3 / stockage objet : activer l’hébergement statique et configurer `404.html` comme document d’erreur.

Les routes sont exportées avec un slash final (`/eleves/`, `/notes/`, etc.). Le serveur doit servir le fichier `index.html` présent dans chaque dossier de route.

## 3. Docker

Le `Dockerfile` construit le frontend puis le sert avec Nginx :

```bash
docker build -t lavision:0.1.0 .
docker run --rm -p 8080:80 --name lavision lavision:0.1.0
```

L’application est alors disponible sur `http://localhost:8080`.

Pour un déploiement public, placer le conteneur derrière le reverse proxy de la plateforme et activer HTTPS.

## 4. GitHub Actions

Le workflow `.github/workflows/ci.yml` exécute les tests, le lint et le build, puis conserve `out/` comme artefact temporaire. Il peut servir d’étape de validation avant n’importe quel déploiement.

Le job Windows desktop dépend de la réussite des contrôles web et publie ses installateurs dans le même pipeline.

## 5. Aperçu de développement

Dans Arena, le serveur est lié à `0.0.0.0:3000`, ce qui permet au proxy d’aperçu d’y accéder. Aucun appel navigateur vers un second service `localhost` n’est utilisé.

## 6. Limites du déploiement statique actuel

Un hébergement statique publie le **frontend local**. Chaque visiteur dispose de son propre jeu de données dans son navigateur. C’est adapté à une démonstration, pas à la gestion partagée d’un établissement.

Avant une mise en production avec des données réelles :

1. ajouter un backend et une base centralisée ;
2. imposer une authentification et des rôles côté serveur ;
3. activer HTTPS et des en-têtes de sécurité adaptés ;
4. sauvegarder la base et tester la restauration ;
5. configurer supervision, journalisation et alertes ;
6. faire valider les règles de notation par l’établissement ;
7. réaliser un test de charge et un audit de sécurité.

Aucun nom de domaine ni secret de fournisseur cloud n’est supposé dans ce dépôt. Le raccordement à une plateforme de production nécessite le compte et la cible choisis par le propriétaire du projet.
