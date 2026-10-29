# Architecture lavision

## 1. Vue d’ensemble

lavision distingue désormais le **prototype fonctionnel local** du **socle serveur M1**. Le déplacement du frontend dans le monorepo ne change ni ses routes, ni ses features, ni sa persistance actuelle. Le raccordement des repositories à l’API interviendra en M2 avec l’authentification et les autorisations.

Le parcours fonctionnel existant reste organisé autour d’un modèle académique partagé :

```text
Inscription ──► Dossier élève ──► Affectation à une classe
                                      │
                                      ▼
                            Saisie des notes par matière
                                      │
                                      ▼
                      Calculs purs du prototype, testés
                             │                  │
                             ▼                  ▼
                          Bulletin          Classement
                                                │
                                                ▼
                                          Proclamation
```

Ce schéma ne vaut pas validation du moteur officiel. Les règles locales versionnées, leur circuit de publication et les décisions officielles restent traités par les issues métier dédiées.

## 2. Monorepo

| Workspace | Responsabilité |
| --- | --- |
| `apps/web` | interfaces Next.js, features et prototype local |
| `apps/api` | monolithe NestJS/Fastify, PostgreSQL/Prisma et endpoints techniques |
| `packages/contracts` | contrats Zod, erreurs, permissions et document OpenAPI |
| `packages/testing` | fixtures fictives partagées par le seed et les tests |

Les dépendances vont des applications vers les packages partagés. Le frontend ne dépend pas de Prisma et ne reçoit aucun accès PostgreSQL. L’API ne dépend pas des composants React.

## 3. Frontend

- **App Router** : routes organisées dans `(school)` et ses sous-groupes `(dashboard)`, `(people)`, `(academic)` et `(configuration)` ; les parenthèses ne modifient pas les URL ;
- **pages `app` minimales** : métadonnées et appel des pages de features, sans duplication de logique métier ;
- **composants clients** : formulaires et interactions préfixés par `use client` ;
- **SchoolProvider** : état local partagé et opérations du prototype ;
- **Tailwind CSS** : thème, utilitaires et classes de composants métier ;
- **export statique** : artefact `apps/web/out/`, commun au Web et à Tauri.

### Organisation par features

```text
apps/web/src/
├── app/(school)/                 # points d’entrée Next.js et groupes de routes
├── components/
│   ├── forms/                    # contrôles de formulaire réutilisables
│   ├── layout/                   # shell et navigation communs
│   └── ui/                       # primitives UI et tableau générique
├── features/
│   ├── auth/data/                # catalogue initial des rôles
│   ├── dashboard/pages/
│   ├── students/pages/
│   ├── enrollments/pages/
│   ├── classes/pages/
│   ├── grades/pages/
│   ├── reports/pages/
│   ├── attendance/pages/
│   ├── proclamation/pages/
│   ├── settings/pages/
│   └── school/
│       ├── context/              # provider et contrat du contexte
│       ├── hooks/                # useSchool
│       ├── services/             # persistance locale
│       └── data/                 # données fictives de démonstration
└── lib/                          # types, calculs purs et utilitaires transverses
```

Une feature suit ce contrat lorsque ses besoins augmentent :

```text
features/<feature>/
├── pages/                        # écrans appelés par app/
├── components/                   # composants propres à la feature
├── hooks/                        # logique d’interface réutilisable
├── services/                     # accès aux données et effets externes
└── data/                         # constantes ou données locales
```

Les dossiers ne sont créés que lorsqu’ils ont un contenu réel. La feature `auth` contient le catalogue fonctionnel des rôles ; cela ne simule pas une authentification ou un RBAC serveur.

Règles de dépendance :

1. `components` ne contient que du code réellement partagé et ne dépend d’aucune page métier ;
2. une page de feature peut consommer `components`, `lib` et la feature transverse `school` ;
3. un composant propre à un seul module reste dans sa feature ;
4. les calculs purs ne dépendent ni de React ni de Next.js ;
5. les route groups ne modifient pas les URL publiques existantes.

## 4. Modèle du prototype

Les contrats locaux se trouvent dans `apps/web/src/lib/types.ts`.

| Entité | Rôle |
| --- | --- |
| `Student` | identité, affectation, statut et responsable légal |
| `SchoolClass` | niveau, salle, capacité et professeur principal |
| `Subject` | nom, code et coefficient du prototype |
| `Grade` | note d’un élève pour une matière et une période |
| `AttendanceRecord` | statut quotidien de présence |
| `Publication` | état de publication local du prototype |
| `SchoolSettings` | identité de l’école et paramètres locaux |
| `AppState` | agrégat versionné des données du prototype |

`apps/web/src/lib/academic.ts` contient les fonctions pures existantes pour les lignes de bulletin, les moyennes du prototype, le classement et les ex æquo. Leurs tests restent exécutés après le déplacement. Elles ne sont pas présentées comme le moteur officiel configurable de l’issue #39.

## 5. Persistance locale actuelle

`SchoolProvider` délègue la clé `lavision.school.v1` à `apps/web/src/features/school/services/school-storage.ts`, qui encapsule `localStorage` et la validation de la sauvegarde.

Avantages : démonstration sans infrastructure, même code Web/desktop et export/import JSON versionné.

Limites : données propres au navigateur ou à la WebView, absence de synchronisation, de contrôle d’accès serveur et de sauvegarde centralisée.

## 6. Socle serveur initial

L’API utilise NestJS avec l’adaptateur Fastify et le préfixe `/api/v1`. Les premiers modules sont :

- `SystemModule` : liveness, readiness, version et OpenAPI ;
- `InfrastructureModule` : client Prisma/PostgreSQL partagé ;
- filtre global Problem Details pour les erreurs HTTP stables.

Le schéma `apps/api/prisma/schema.prisma` couvre uniquement les agrégats de socle : organisations, sites, utilisateurs et appartenances. Les contraintes PostgreSQL complètent les schémas Zod. La migration SQL et le seed fictif sont versionnés ; le client Prisma est généré.

Le frontend n’est pas encore connecté à ces modules. La procédure de recette et les limites sont détaillées dans [API.md](API.md).

## 7. Raccordement progressif

L’évolution prévue consiste à introduire des repositories sans réécrire les composants :

```text
SchoolProvider
      │
      ▼
SchoolRepository (interface)
      ├── LocalSchoolRepository   # prototype / repli local explicite
      └── ApiSchoolRepository     # cible multiutilisateur authentifiée
                │
                ▼
       API authentifiée + PostgreSQL
```

L’API devra valider les entrées, appliquer les permissions et portées, recalculer ou vérifier les décisions sensibles et empêcher qu’un client impose directement une moyenne, un rang ou une publication. L’authentification/MFA (#6) et le RBAC/audit (#7) précèdent ce raccordement métier.

## 8. Desktop

Tauri embarque `apps/web/out/` dans une WebView native. Rust fournit le shell et les installateurs ; aucune duplication du frontend n’est nécessaire.

- développement : Tauri charge le serveur Next.js local ;
- production desktop : Tauri charge les fichiers statiques intégrés ;
- données actuelles : stockage local de la WebView ;
- cible : client online-first HTTPS, sans connexion directe à PostgreSQL, Redis ou S3.

## 9. Cible M1 et ADR

La cible M1 est un **monolithe modulaire NestJS**, avec PostgreSQL/Prisma, contrats OpenAPI, stockage S3 privé, worker Redis/BullMQ et clients Web/Tauri authentifiés. Seul le premier socle API/PostgreSQL est implémenté ici ; les autres composants restent des évolutions planifiées.

Les huit ADR sont **acceptés pour M1** depuis le 29 octobre 2026. Leur validation autorise #5 sans clôturer #3 ni valider une infrastructure de production. Les menaces, frontières de domaines, stratégies de retour arrière et exigences d’environnement sont regroupées dans le [dossier des ADR](adr/README.md).
