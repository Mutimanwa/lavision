# ADR-001 — API en monolithe modulaire

- **Date :** 29 octobre 2026
- **Statut :** accepté pour M1
- **Validation produit :** 29 octobre 2026
- **Décideurs attendus :** tech lead et représentant métier

## Contexte

Les domaines de lavision partagent des inscriptions, des règles versionnées, des publications et des transactions financières. Des microservices multiplieraient les transactions distribuées et l’exploitation avant que les volumes ne le justifient. Le frontend doit néanmoins cesser d’être la source de vérité.

## Décision

Utiliser **NestJS avec l’adaptateur Fastify** pour une API REST versionnée `/api/v1`, livrée comme un monolithe modulaire. Le worker est un processus séparé issu du même dépôt et des mêmes modules métier, pas un service indépendant.

Le monorepo utilisera les workspaces npm et conservera les route groups et features du frontend :

```text
apps/
├── web/                 # Next.js, app/ et features/<feature>/
├── api/                 # modules NestJS et contrôleurs REST
└── worker/              # consommateurs BullMQ et tâches planifiées
packages/
├── contracts/           # schémas de requêtes/réponses et erreurs
├── domain/              # calculs purs et règles typées partageables
└── testing/             # fixtures non nominatives et utilitaires de test
```

Cette arborescence est une cible M1 : les fichiers ne sont pas déplacés pendant M0. Chaque module API expose un contrat applicatif et possède ses repositories. Les accès croisés passent par ce contrat ou par une transaction orchestrée explicitement ; aucun module ne contourne les permissions d’un autre.

## Alternatives évaluées

- **Routes serveur Next.js uniquement** : rejeté pour la cible, car les traitements desktop, workers et intégrations gagneraient en couplage avec le frontend ;
- **microservices par domaine** : rejeté à ce stade pour le coût de déploiement, d’observabilité et de cohérence transactionnelle ;
- **Fastify sans NestJS** : possible techniquement, mais moins aligné avec le cahier des charges et les conventions modulaires recherchées.

## Conséquences

- transactions locales simples et un seul modèle de déploiement métier ;
- frontières de modules à faire respecter par tests d’architecture et revues ;
- extraction future possible d’un module seulement après preuve de besoin ;
- montée en charge initiale par réplication de l’API et du worker, sans multiplier les bases.

## Critères de vérification

- une route métier ne peut pas accéder à PostgreSQL depuis le client ;
- les dépendances intermodules autorisées sont testées en CI ;
- les opérations de paiement et de publication sont transactionnelles ;
- API et worker peuvent être arrêtés et redémarrés indépendamment sans perdre les écritures confirmées.
