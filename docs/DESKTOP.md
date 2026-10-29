# lavision Desktop avec Tauri

## Pourquoi Tauri ?

Tauri embarque le frontend Next.js dans la WebView du système et fournit un shell natif léger. La même base `apps/web` sert le Web et le desktop, sans duplication des interfaces.

## Prérequis sur Windows

- Windows 10 ou 11 ;
- Node.js 22.18+ et npm ;
- Rust 1.90+ installé avec `rustup` ;
- Microsoft C++ Build Tools ;
- WebView2 Runtime, normalement présent sur Windows à jour.

Vérifier l’environnement :

```bash
node --version
rustc --version
cargo --version
npm run tauri info
```

## Développement desktop

```bash
npm ci
npm run tauri dev
```

Tauri exécute `npm run dev:web`, puis ouvre la fenêtre native sur `http://localhost:3000`. PostgreSQL n’est pas nécessaire pour le prototype local actuel.

## Produire l’exécutable Windows

Sur une machine Windows :

```bash
npm ci
npm run tauri build
```

Tauri exécute `npm run build:web`, intègre `apps/web/out/`, compile Rust et produit notamment :

```text
src-tauri/target/release/lavision.exe
src-tauri/target/release/bundle/nsis/lavision_0.1.0_x64-setup.exe
src-tauri/target/release/bundle/msi/lavision_0.1.0_x64_en-US.msi
```

Les noms exacts varient selon la langue et l’architecture. L’installateur **NSIS** est le `.exe` à privilégier pour une distribution simple. Le build desktop ne compile pas inutilement l’API.

## Build Windows dans GitHub Actions

Le job **Windows Desktop** du workflow `.github/workflows/ci.yml` est disponible :

- automatiquement après la réussite de tous les contrôles, dont PostgreSQL, sur les pushes et Pull Requests ;
- manuellement via **Actions → lavision CI → Run workflow** lorsque le workflow est présent sur la branche principale.

Les bundles NSIS et MSI sont conservés comme artefacts de workflow. Aucun binaire `.exe` n’est stocké dans Git.

## Données desktop

Dans le prototype actuel, les données restent dans la WebView locale et ne sont pas copiées entre ordinateurs. **Paramètres → Télécharger une sauvegarde** permet d’exporter le JSON local.

La cible M1 validée est **online-first** : le client se connectera à l’API en HTTPS, sans accès direct à PostgreSQL, Redis ou S3. Le raccordement du frontend est prévu en M2 après l’authentification et les autorisations. Tout mode hors ligne robuste fera l’objet d’une conception et d’une validation séparées ; il n’est pas simulé par #5.

## Exigences pour le raccordement authentifié

Le shell Tauri ne doit pas recevoir directement les cookies de session ni les jetons durables dans la WebView. Lors du raccordement M2, appliquer ADR-006 : ouvrir le parcours dans le **navigateur système**, utiliser **PKCE**, échanger des jetons courts avec rotation et conserver les secrets dans le **coffre de l’OS**. Aucun jeton durable ne doit être placé dans `localStorage` ou dans un fichier de la WebView. L’API reste la seule autorité pour les sessions, la MFA et la révocation.

## Avant diffusion aux utilisateurs

- tester l’installateur sur une machine Windows propre ;
- vérifier l’impression et l’export PDF dans WebView2 ;
- signer l’exécutable avec un certificat de signature de code ;
- définir la politique de mise à jour ;
- tester la restauration des sauvegardes ;
- faire valider les règles de confidentialité et les habilitations de l’établissement.

> Le sandbox Linux ne contient pas Rust ni WebKit. Le frontend et la configuration Tauri sont vérifiés localement ; le binaire Windows est compilé par le workflow GitHub Actions.
