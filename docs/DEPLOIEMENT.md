# Déploiement, environnements et limites

Le socle #9 distingue le prototype local, la CI, le staging jetable et une future production. Le runbook opérationnel détaillé est dans [`RUNBOOK_EXPLOITATION.md`](RUNBOOK_EXPLOITATION.md). Aucun hébergeur, domaine, secret ou déploiement de production n’est supposé.

## 1. Vérifications avant livraison

```bash
npm ci
npm test
npm run test:deployment
npm run check:secrets
npm run typecheck
npm run lint
npm run openapi:check
npm run build
```

Les tests PostgreSQL nécessitent une base jetable migrée et `DATABASE_URL` :

```bash
npm run test:integration
```

La CI exécute en plus la validation Compose, le démarrage d’un staging jetable, les healthchecks, un rollback applicatif compatible et le refus d’un rollback après une migration SQL temporaire. Elle bloque toute régression de tests, lint, typecheck, build, migration ou recette de déploiement.

## 2. Stack Docker local

Le `Dockerfile` racine expose quatre cibles :

- `web` : export Next.js servi par Nginx avec proxy `/api` vers l’API ;
- `api` : runtime NestJS/Prisma sans client de développement ni migration automatique ;
- `worker` : processus technique supervisé, avec healthchecks et métriques ;
- `migrate` : image one-shot contenant Prisma pour `migrate deploy`.

Démarrer le stack complet :

```bash
npm ci
docker compose up --build --wait
```

Le Web est disponible sur `http://localhost:8080`, l’API sur `http://localhost:3001`. PostgreSQL et Redis utilisent des volumes locaux explicitement supprimables. Le worker n’enregistre pas encore de job métier ; il fournit le point d’ancrage supervisé pour les futures files BullMQ/outbox.

## 3. Staging reproductible

`deploy/compose.staging.yml` ne contient aucune instruction `build` : le déploiement utilise des images taguées de façon immutable. PostgreSQL et Redis ne sont pas publiés sur l’hôte, les logs Docker sont bornés et seuls le Web et ses appels API sont exposés par le port configuré.

```bash
cp deploy/staging.env.example .env.staging
# Renseigner les secrets hors Git, puis :
node --env-file=.env.staging scripts/deploy-staging.mjs plan
node --env-file=.env.staging scripts/deploy-staging.mjs deploy
```

Le script sépare migration et démarrage, vérifie les trois services et conserve un état sans secret dans `.lavision/staging-state.json`. Les mots de passe, clés et tokens ne sont jamais écrits dans les fichiers de déploiement.

## 4. Rollback et diagnostic

Le rollback conserve les images précédentes et exige une confirmation explicite. Il est autorisé automatiquement uniquement lorsque l’empreinte des migrations est identique entre les deux versions ; aucune migration `down` n’est inventée.

```bash
ROLLBACK_CONFIRM=YES node --env-file=.env.staging scripts/deploy-staging.mjs rollback
```

Les endpoints de santé, les logs JSON corrélés et les métriques Prometheus protégées sont décrits dans le runbook. Les règles initiales couvrent l’API, le worker, Redis/PostgreSQL observés par le worker et les erreurs 5xx. Alertmanager et le suivi d’erreurs externe restent à choisir et tester.

## 5. Hébergement statique et desktop

L’export `apps/web/out/` peut toujours être publié séparément pour la démonstration statique. Cette variante utilise les données locales du navigateur et ne déploie pas l’API ni les fonctions multiutilisateurs.

Pour le desktop, Tauri consomme le même export et la CI produit les installateurs Windows comme artefacts temporaires. Aucun exécutable n’est versionné.

## 6. Limites avant production

Le frontend n’est pas encore raccordé à l’API authentifiée et les calculs officiels restent soumis aux validations métier de #39. Le staging de la CI est jetable et ne contient aucune donnée réelle.

Avant une exploitation réelle, il faut encore :

1. choisir l’hébergement, les domaines, le registre et les RPO/RTO ;
2. gérer les secrets dans un coffre externe et tester leur rotation ;
3. terminer TLS, la supervision externe, les alertes et le suivi d’erreurs ;
4. définir et tester les sauvegardes chiffrées et la restauration PostgreSQL/objet sur environnement vierge ;
5. raccorder le frontend, valider les règles locales et réaliser les tests de charge et de sécurité.
