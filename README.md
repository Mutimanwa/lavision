# lavision

**Logiciel de gestion scolaire, de l’inscription à la proclamation.**

Cette version constitue la migration du prototype PHP/HTML vers **Next.js, React, TypeScript et Tailwind CSS**. Elle fournit un parcours académique cohérent et une base unique pour le web et le desktop avec **Tauri (Rust)**.

## Fonctionnalités livrées

- **Tableau de bord** : effectifs, moyennes, réussite, progression des notes et présences ;
- **Inscription en 4 étapes** : identité, scolarité, responsable légal et confirmation, avec matricule automatique ;
- **Répertoire des élèves** : recherche, filtres, dossier détaillé, modification et export CSV ;
- **Classes et matières** : capacités, salles, professeurs principaux et coefficients modifiables ;
- **Notes** : saisie groupée par classe/matière/période, validation sur 20 et export ;
- **Bulletins** : moyenne pondérée, rang, appréciations, assiduité, impression/PDF et CSV ;
- **Proclamation** : classement avec ex æquo, contrôle des dossiers complets, décisions et publication officielle ;
- **Présences** : appel quotidien, retards, absences, excuses et historique ;
- **Paramètres** : identité de l’établissement, période active, seuil de réussite et sauvegardes JSON ;
- **Desktop** : socle Tauri 2 et workflow Windows pour produire les installateurs.

> **État des données :** cette itération utilise `localStorage` avec un jeu de démonstration fictif. Elle est adaptée à la validation fonctionnelle et au prototype desktop monoposte. Elle ne constitue pas encore un backend multiutilisateur sécurisé.

## Stack

| Couche | Technologie |
| --- | --- |
| Frontend | Next.js 16, React 19, TypeScript |
| Styles | Tailwind CSS 4 + composants métier |
| Icônes | Lucide React |
| Tests | Vitest |
| Desktop | Tauri 2 / Rust |
| Build web | Export statique dans `out/` |
| CI | GitHub Actions |

## Démarrage local

Prérequis : **Node.js 22.12 ou supérieur** et npm.

```bash
npm ci
npm run dev
```

Ouvrir `http://localhost:3000`.

### Commandes

```bash
npm run dev          # serveur de développement sur 0.0.0.0
npm test             # tests du moteur académique
npm run lint         # ESLint
npm run build        # vérifications TypeScript + export statique
npm run tauri dev    # application desktop (Rust requis)
npm run tauri build  # bundles desktop de la plateforme
```

## Structure

```text
src/
├── app/(school)/           # routes groupées, sans changement des URL
│   ├── (dashboard)/        # tableau de bord
│   ├── (people)/           # élèves et inscriptions
│   ├── (academic)/         # classes, notes, bulletins, présences, proclamation
│   └── (configuration)/    # paramètres
├── components/             # uniquement les composants partagés
│   ├── forms/              # champs et contrôles de formulaire
│   ├── layout/             # shell, navigation et identité visuelle
│   └── ui/                 # primitives, tableaux, menus et feedback
├── features/               # pages et logique propres à chaque module
│   ├── auth/               # catalogue des rôles pour le futur RBAC
│   ├── dashboard/
│   ├── students/
│   ├── enrollments/
│   ├── classes/
│   ├── grades/
│   ├── reports/
│   ├── attendance/
│   ├── proclamation/
│   ├── settings/
│   └── school/             # contexte, hook, stockage et données partagées
└── lib/                    # contrats, calculs purs et utilitaires transverses
public/                     # icône et ressources statiques
src-tauri/                  # application Rust/Tauri
legacy/                     # prototype PHP/HTML initial, conservé comme référence
docs/                       # audit, architecture et guides de livraison
.github/workflows/          # contrôles web et build Windows
```

Chaque feature peut contenir `pages`, `components`, `hooks`, `services` et `data` selon ses besoins. Les dossiers ne sont créés que lorsqu’ils ont un contenu réel. Voir [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) pour les règles de dépendance.

## Règles académiques

- chaque note est comprise entre **0 et 20** ;
- la moyenne d’un bulletin est la somme `note × coefficient` divisée par les coefficients des matières notées ;
- une note absente n’est pas une note de zéro ;
- un élève sans toutes ses notes est marqué **Incomplet**, sans rang définitif ;
- la proclamation est bloquée tant qu’un dossier de la classe est incomplet ;
- un dossier complet est **Admis** si sa moyenne atteint le seuil configuré, sinon **Ajourné** ;
- les ex æquo partagent le même rang.

Ces règles sont centralisées dans `src/lib/academic.ts`, jamais dupliquées dans les écrans.

## Sauvegarde et restauration

La page **Paramètres** permet de télécharger une sauvegarde JSON et de la restaurer. Les exports CSV utilisent un encodage UTF-8 avec séparateur `;` pour faciliter leur ouverture dans les tableurs francophones.

## Déploiement

```bash
npm run build
```

Le dossier `out/` peut être publié sur un hébergement statique. Un `Dockerfile` est également fourni :

```bash
docker build -t lavision .
docker run --rm -p 8080:80 lavision
```

Voir [`docs/DEPLOIEMENT.md`](docs/DEPLOIEMENT.md).

## Application Windows `.exe`

Le build Windows s’effectue sur Windows ou via le workflow GitHub Actions fourni :

```bash
npm run tauri build
```

Les artefacts sont générés sous `src-tauri/target/release/bundle/` (NSIS `.exe` et MSI). Voir [`docs/DESKTOP.md`](docs/DESKTOP.md).

## Documentation

- [Cahier des charges complet](docs/CAHIER_DES_CHARGES.md)
- [Plan de réalisation](docs/PLAN_DE_REALISATION.md)
- [Backlog GitHub — 38 issues](docs/BACKLOG.md)
- [Dossier de cadrage métier M0](docs/cadrage/README.md) — ateliers, règles à valider et exemples de recette
- [Questionnaire de l’établissement pilote](docs/cadrage/06_QUESTIONNAIRE_ETABLISSEMENT.md)
- [Interfaces par rôle et activation progressive](docs/cadrage/11_INTERFACES_PAR_ROLE_ET_ACTIVATION.md)
- [Base officielle RDC pour le cadrage académique](docs/cadrage/07_BASE_OFFICIELLE_RDC.md)
- [Audit du projet initial](docs/AUDIT.md)
- [Architecture et modèle métier](docs/ARCHITECTURE.md)
- [ADR de l’architecture cible](docs/adr/README.md) — API, base, fichiers, worker, authentification et déploiement proposés
- [Déploiement web](docs/DEPLOIEMENT.md)
- [Application desktop Tauri](docs/DESKTOP.md)
- [Feuille de route production](docs/ROADMAP.md)
