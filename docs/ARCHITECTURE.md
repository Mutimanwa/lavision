# Architecture lavision

## Vue d’ensemble

lavision est organisé autour d’un **modèle académique unique**. Les écrans ne possèdent pas chacun une copie de leurs données : inscriptions, notes, bulletins et proclamation consomment le même état typé.

```text
Inscription ──► Dossier élève ──► Affectation à une classe
                                      │
                                      ▼
                            Saisie des notes par matière
                                      │
                                      ▼
                         Moteur académique (pur et testé)
                             │                  │
                             ▼                  ▼
                          Bulletin          Classement
                                                │
                                                ▼
                                          Proclamation
```

## Frontend

- **App Router** : une route par module métier, organisée dans le groupe `(school)` et ses sous-groupes `(dashboard)`, `(people)`, `(academic)` et `(configuration)` ; les parenthèses ne modifient pas les URL ;
- **pages `app` minimales** : métadonnées, limites de chargement et appel de la page de feature, sans logique métier ;
- **composants clients** : formulaires et interactions, préfixés par `use client` ;
- **SchoolProvider** : état partagé et opérations de modification ;
- **Tailwind CSS** : thème et utilitaires, complétés par des classes de composants métier dans `globals.css` ;
- **export statique** : aucune route serveur n’est nécessaire pour le prototype, ce qui permet le même artefact sur le web et dans Tauri.

## Organisation par features

Le code suit un découpage par responsabilité plutôt qu’un regroupement de tous les écrans dans `components` :

```text
src/
├── app/(school)/                 # points d’entrée Next.js et groupes de routes
├── components/
│   ├── forms/                    # contrôles de formulaire réutilisables
│   ├── layout/                   # shell et navigation communs
│   └── ui/                       # primitives UI et tableau générique
├── features/
│   ├── auth/data/                  # catalogue initial des rôles
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
├── components/                   # composants utilisés uniquement par cette feature
├── hooks/                        # logique d’interface réutilisable dans la feature
├── services/                     # accès aux données et effets externes
└── data/                         # constantes ou données locales à la feature
```

Les dossiers sont créés uniquement lorsqu’ils contiennent du code. La feature `auth` contient déjà le catalogue fonctionnel des rôles dans `data` ; ses pages, ses services d’authentification et le RBAC serveur seront ajoutés pendant leur implémentation réelle, sans simuler une sécurité côté navigateur.

Règles de dépendance :

1. `components` ne contient que du code réellement partagé et ne dépend d’aucune page métier ;
2. une page de feature peut consommer `components`, `lib` et la feature transverse `school` ;
3. un composant propre à un seul module reste dans `features/<feature>/components` ou dans sa page ;
4. les calculs académiques restent des fonctions pures dans `lib`, sans dépendance à React ou à Next.js ;
5. les routes groupées ne doivent jamais modifier les URL publiques existantes.

## Modèle de données

Les contrats se trouvent dans `src/lib/types.ts`.

| Entité | Rôle |
| --- | --- |
| `Student` | identité, affectation, statut et responsable légal |
| `SchoolClass` | niveau, salle, capacité et professeur principal |
| `Subject` | nom, code et coefficient |
| `Grade` | note d’un élève pour une matière et une période |
| `AttendanceRecord` | statut quotidien de présence |
| `Publication` | publication d’une classe pour une période |
| `SchoolSettings` | identité de l’école et règles générales |
| `AppState` | agrégat versionné de toutes les données |

Les identifiants de notes sont déterministes (`élève-matière-période`), ce qui rend une nouvelle saisie idempotente.

## Moteur académique

`src/lib/academic.ts` contient uniquement des fonctions pures :

- `getStudentReport` assemble les lignes du bulletin et calcule la moyenne ;
- `getClassRanking` ordonne les dossiers complets et gère les ex æquo ;
- `getAppreciation` fournit une appréciation homogène ;
- les helpers de formatage ne modifient jamais les données sources.

Les tests couvrent la pondération, les notes nulles, les absences de notes, les ex æquo et les décisions d’admission.

## Persistance actuelle

`SchoolProvider` délègue la lecture et l’écriture de la clé `lavision.school.v1` au service `features/school/services/school-storage.ts`, qui encapsule `localStorage` et la validation de la sauvegarde.

Avantages :

- aucune infrastructure requise pour la démonstration ;
- fonctionnement web et desktop avec le même code ;
- export/import JSON versionné ;
- réinitialisation facile.

Limites :

- données propres au navigateur ou au WebView ;
- pas de synchronisation entre postes ;
- pas de contrôle d’accès ni de chiffrement applicatif ;
- pas de garantie de sauvegarde serveur.

## Évolution vers un backend

L’étape production recommandée consiste à remplacer les opérations du provider par un **repository de données** sans changer les contrats des composants :

```text
SchoolProvider
      │
      ▼
SchoolRepository (interface)
      ├── LocalSchoolRepository   # prototype / hors ligne
      └── ApiSchoolRepository     # production multiutilisateur
                │
                ▼
       API authentifiée + PostgreSQL
```

Le backend devra recalculer ou valider les décisions sensibles, appliquer les autorisations et empêcher qu’un client modifie directement une moyenne ou une publication.

## Desktop

Tauri embarque le dossier `out/` dans une WebView native. Rust fournit le shell applicatif et les installateurs ; aucune duplication du frontend n’est nécessaire.

- développement : Tauri charge le serveur Next.js local ;
- production : Tauri charge les fichiers statiques intégrés ;
- données : stockage local de la WebView dans le prototype ;
- cible future : adapter le repository local à SQLite via des commandes Rust si un mode hors ligne robuste est requis.
