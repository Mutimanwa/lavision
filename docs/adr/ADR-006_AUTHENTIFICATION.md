# ADR-006 — Authentification, sessions et MFA

- **Date :** 29 octobre 2026
- **Statut :** accepté pour M1
- **Validation produit :** 29 octobre 2026
- **Décideurs attendus :** tech lead, sécurité et représentant métier

## Contexte

Le sélecteur de rôle du prototype est une aide UX, pas une habilitation. La cible doit gérer des sessions révocables, des rôles combinables, des portées et une MFA pour les profils sensibles. Le web et Tauri ne peuvent pas stocker leurs secrets de la même façon.

## Décision

Utiliser des **sessions opaques révocables**, enregistrées côté serveur sous forme hashée, plutôt que des JWT de longue durée sans révocation. Les mots de passe sont hashés avec **Argon2id** et une politique de paramètres documentée lors de M1.

- **Web :** cookie de session `HttpOnly`, `Secure`, `SameSite` adapté et protection CSRF sur les mutations ;
- **Desktop :** autorisation via navigateur système avec **PKCE**, retour local ou deep link contrôlé, jeton d’accès court et refresh token rotatif dans le coffre de secrets de l’OS ;
- **MFA :** TOTP pour les profils sensibles définis dans Q-SEC-04, codes de récupération hashés et réauthentification pour les opérations très sensibles ;
- **Autorisation :** deny-by-default, permissions et portées évaluées à chaque commande API, jamais à partir d’un rôle envoyé par le client ;
- **Défense :** limitation de débit, verrouillage progressif sans déni de service trivial, alertes et révocation des sessions ;
- **Audit :** connexion, échec, MFA, récupération et changement de rôle sans enregistrer mot de passe, secret TOTP ou jeton.

Un fournisseur OIDC pourra être retenu plus tard si l’établissement impose une fédération. Cette possibilité ne dispense pas de préciser les sessions et la MFA du produit.

## Alternatives évaluées

- **JWT stateless longue durée** : rejeté comme mécanisme principal pour la difficulté de révocation ;
- **localStorage pour les jetons web ou desktop** : rejeté pour les secrets de session ;
- **SMS comme MFA obligatoire** : non retenu par défaut pour le coût, la disponibilité et les risques de réaffectation de numéro ;
- **sécurité par menus masqués** : rejetée ; le frontend ne fait qu’expliquer les permissions effectives de l’API.

## Conséquences

- stockage et expiration des sessions à administrer ;
- parcours de récupération MFA et support à concevoir sans contourner la sécurité ;
- tests distincts pour cookies web et coffre desktop ;
- les règles exactes de MFA et les exceptions restent validées par le métier.

## Critères de vérification

- une session révoquée cesse immédiatement d’autoriser les nouvelles commandes ;
- un token desktop n’apparaît ni dans les logs ni dans un fichier en clair du projet ;
- un utilisateur ne peut pas changer sa portée ou son rôle via la requête ;
- CSRF, fixation de session, rotation et récupération sont testés ;
- SEC-01 à SEC-20 sont intégrés aux tests d’autorisation applicables.
