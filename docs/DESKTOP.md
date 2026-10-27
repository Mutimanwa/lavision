# LaVision Desktop avec Tauri

## Pourquoi Tauri ?

**Tauri** est le framework Rust recherché. Il embarque le frontend Next.js dans la WebView du système et fournit un shell natif léger. La même base de code sert donc le web et le desktop.

## Prérequis sur Windows

- Windows 10 ou 11 ;
- Node.js 22.12+ et npm ;
- Rust 1.90+ installé avec `rustup` ;
- Microsoft C++ Build Tools ;
- WebView2 Runtime, normalement déjà présent sur Windows à jour.

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

Tauri lance `npm run dev`, puis ouvre la fenêtre native sur `http://localhost:3000`.

## Produire l’exécutable Windows

Sur une machine Windows :

```bash
npm ci
npm run tauri build
```

Tauri exécute d’abord `npm run build`, intègre `out/`, compile Rust et produit notamment :

```text
src-tauri/target/release/lavision.exe
src-tauri/target/release/bundle/nsis/LaVision_0.1.0_x64-setup.exe
src-tauri/target/release/bundle/msi/LaVision_0.1.0_x64_en-US.msi
```

Les noms exacts peuvent varier selon la langue et l’architecture du bundle. L’installateur **NSIS** est le `.exe` à privilégier pour une distribution simple.

## Build Windows dans GitHub Actions

Le workflow `.github/workflows/desktop-windows.yml` est disponible :

- automatiquement pour un tag `v*` ou une modification du frontend/Tauri ;
- manuellement via **Actions → Windows Desktop → Run workflow** une fois le workflow présent sur la branche principale.

Les bundles NSIS et MSI sont conservés comme artefacts de workflow. Aucun binaire `.exe` n’est stocké dans Git.

## Données desktop

Dans cette version, les données sont stockées dans la WebView locale. Elles ne sont donc pas automatiquement copiées entre deux ordinateurs. Utiliser **Paramètres → Télécharger une sauvegarde** pour exporter un fichier JSON.

Pour un vrai mode desktop hors ligne multiutilisateur ou poste par poste, l’étape suivante est une couche SQLite pilotée par des commandes Rust, avec migrations et sauvegardes planifiées.

## Avant diffusion aux utilisateurs

- tester l’installateur sur une machine Windows propre ;
- vérifier l’impression et l’export PDF dans WebView2 ;
- signer l’exécutable avec un certificat de signature de code ;
- définir la politique de mise à jour ;
- tester la restauration d’une sauvegarde ;
- ajouter les règles de confidentialité de l’établissement.

> Le sandbox Linux utilisé pour cette itération ne contient pas Rust ni WebKit. Le frontend et la configuration Tauri ont été validés, mais le binaire Windows doit être compilé sur Windows ou par le workflow fourni.
