# Recette technique des environnements #9

**Commit :** `6bd7e29`  
**CI :** [`40968608989`](https://github.com/Mutimanwa/lavision/actions/runs/40968608989) — `success`  
**Portée :** socle local/staging et preuve CI, pas déploiement de production

## 1. Preuves exécutées

| Exigence | Preuve obtenue | Limite |
| --- | --- | --- |
| Services Web/API/PostgreSQL/worker | images `web`, `api`, `worker`, `migrate`, Redis et healthchecks démarrés par Compose | aucun job métier dans le worker |
| Staging depuis un environnement vierge | la CI démarre des volumes jetables, applique les migrations puis vérifie Web, API et worker | pas d’hébergeur externe provisionné |
| CI bloquante | tests, intégration PostgreSQL, OpenAPI, typecheck, lint, builds et smoke sont des étapes séquentielles obligatoires | le résultat ne vaut que pour le commit vérifié |
| Secrets hors Git/logs | exemples non secrets, variables requises sur staging, contrôle de motifs et logs sans corps/query/token | le contrôle local ne remplace pas un coffre ni le secret scanning de la forge |
| Rollback compatible | deux tags sont déployés, puis le premier est restauré sans migration et revérifié | les images précédentes doivent être conservées |
| Changement de schéma | une migration SQL temporaire est réellement appliquée au staging, puis le rollback interschéma est refusé avec `ROLLBACK_BASE_REQUIRED` | aucune restauration PostgreSQL n’est prétendue |
| Observabilité initiale | logs JSON corrélés, métriques API/worker protégées et règles Prometheus versionnées | Alertmanager et suivi d’erreurs externe non configurés |
| Desktop | job `Windows Desktop` vert sur le même commit | les installateurs restent des artefacts CI |

## 2. Commandes de reproduction

Le parcours opérateur est décrit dans [`../RUNBOOK_EXPLOITATION.md`](../RUNBOOK_EXPLOITATION.md). Les points sensibles sont :

```bash
node --env-file=.env.staging scripts/deploy-staging.mjs plan
node --env-file=.env.staging scripts/deploy-staging.mjs deploy
ROLLBACK_CONFIRM=YES node --env-file=.env.staging scripts/deploy-staging.mjs rollback
```

Le fichier d’état ne contient que le tag, la date et l’empreinte des migrations. Le script exige un tag immutable et une confirmation explicite de rollback. Il ne lance jamais de migration lors d’un rollback.

## 3. Points ouverts avant production

- choisir l’hébergeur, le registre, les domaines, TLS et les comptes d’exploitation ;
- externaliser et faire tourner les secrets dans un coffre ;
- brancher Prometheus à Alertmanager et tester les destinataires ;
- choisir un suivi d’erreurs externe compatible avec la confidentialité ;
- définir les RPO/RTO, les sauvegardes chiffrées et tester une restauration sur environnement vierge ;
- conserver les images et tester la procédure chez l’hébergeur réel ;
- ajouter les consommateurs BullMQ/outbox avec les futures fonctionnalités, sans job métier implicite.

Cette recette ne ferme donc pas les décisions d’hébergement ou de reprise. Elle prouve le socle #9 dans un environnement jetable, avec des données et credentials fictifs uniquement.
