# ADR-003 — Contrats partagés et OpenAPI

- **Date :** 29 octobre 2026
- **Statut :** accepté pour M1
- **Validation produit :** 29 octobre 2026
- **Décideurs attendus :** tech lead, frontend et API

## Contexte

Le web, Tauri et les intégrations doivent utiliser les mêmes contrats. Partager seulement des types TypeScript ne valide rien à l’exécution et peut laisser le client envoyer une moyenne ou une décision qu’il n’a pas le droit de fixer.

## Décision

Définir les schémas de requêtes et de réponses dans `packages/contracts` avec **Zod**, puis publier un document **OpenAPI 3.1** généré à partir des contrats de l’API NestJS. Les types du client sont générés ou dérivés de ces sources ; ils ne sont pas recopiés à la main.

Principes :

- validation des entrées à la frontière API et interdiction des champs inconnus sensibles ;
- sorties calculées en lecture seule : moyenne, rang, statut de publication et permissions effectives ;
- erreurs `application/problem+json` avec code stable, message sûr et identifiant de corrélation ;
- pagination par curseur sur les grandes listes et tri sur liste blanche ;
- clé d’idempotence sur les commandes financières, publications et imports susceptibles d’être rejoués ;
- compatibilité ascendante à l’intérieur de `/api/v1` ; toute rupture passe par une nouvelle version et une période de migration ;
- vérification OpenAPI en CI pour détecter les ruptures non annoncées.

Les fonctions pures de calcul peuvent vivre dans `packages/domain`. Le serveur reste autoritaire et choisit la version de règle applicable ; recevoir du code ou une formule arbitraire depuis un client est interdit.

## Alternatives évaluées

- **types TypeScript dupliqués** : rejeté, car ils divergent et ne protègent pas l’exécution ;
- **GraphQL** : non retenu pour la cible initiale, le cahier des charges et les intégrations étant orientés REST/OpenAPI ;
- **validation uniquement dans NestJS** : rejeté pour les contrats réellement partagés, tout en conservant les validations de sécurité propres au serveur.

## Conséquences

- contrats et fixtures à maintenir avec les changements métier ;
- génération de documentation et de clients reproductible ;
- migrations compatibles plus faciles entre web, desktop et API ;
- la validation Zod ne remplace ni les contraintes PostgreSQL ni le RBAC.

## Critères de vérification

- OpenAPI est produit sans démarrer une base de production et contrôlé en CI ;
- un champ calculé envoyé par le client est ignoré ou refusé, jamais accepté comme vérité ;
- un contrat incompatible échoue avant déploiement ;
- les réponses d’erreur ne divulguent ni stack, ni SQL, ni donnée personnelle d’un autre dossier.
