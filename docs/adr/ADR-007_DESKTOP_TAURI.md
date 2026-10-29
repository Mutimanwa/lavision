# ADR-007 — Desktop Tauri

- **Date :** 29 octobre 2026
- **Statut :** proposé
- **Décideurs attendus :** tech lead, desktop et sécurité

## Contexte

Le shell Tauri et les builds Windows CI existent déjà. La production doit cependant empêcher le client desktop de devenir un accès privilégié à la base ou un stockage de secrets en clair. Le mode hors ligne multiutilisateur n’est pas un acquis de la v1.

## Décision proposée

Conserver **Tauri** et le même frontend Next.js statique pour Windows. Le client de production est **online-first** et communique uniquement en HTTPS avec l’API ; il ne reçoit aucun accès direct à PostgreSQL, Redis ou S3.

- authentification PKCE via le navigateur système et secrets dans le coffre de l’OS ;
- CSP stricte, allowlist des endpoints API et aucun chargement de contenu distant arbitraire dans la WebView ;
- commandes Rust limitées aux capacités natives nécessaires, avec permissions Tauri minimales ;
- impression et export PDF testés dans WebView2, avec contrôle des permissions comme sur le web ;
- installateurs NSIS/MSI produits en CI, signature de code et mécanisme de mise à jour avant diffusion M6 ;
- compatibilité de version client/API affichée et mise à jour forcée uniquement en cas de risque ou d’incompatibilité documentée ;
- aucune synchronisation hors ligne implicite : un besoin SQLite et de résolution de conflits fera l’objet d’un ADR séparé au M7.

## Alternatives évaluées

- **Electron** : non retenu, car Tauri est déjà intégré et répond à la cible sans réécrire le shell ;
- **application Windows native séparée** : rejetée pour la duplication du frontend et des contrats ;
- **base locale de production synchronisée dès M1** : rejetée pour éviter un périmètre de synchronisation et de conflits non validé ;
- **connexion directe à PostgreSQL** : rejetée.

## Conséquences

- dépendance réseau explicite pour les fonctions de production ;
- certificat de signature, serveur de mise à jour et procédure de révocation à prévoir ;
- tests sur Windows réel nécessaires en plus du build CI ;
- le prototype local actuel ne doit pas être présenté comme un mode hors ligne sécurisé.

## Critères de vérification

- installation et désinstallation sur un Windows propre ;
- exécutable signé et provenance de l’artefact vérifiables ;
- perte réseau sans fausse confirmation d’écriture ;
- secrets absents de la WebView et des fichiers de configuration ;
- retour à une version desktop compatible possible après échec d’une mise à jour.
