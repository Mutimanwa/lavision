# ADR-002 — PostgreSQL, Prisma et migrations

- **Date :** 29 octobre 2026
- **Statut :** accepté pour M1
- **Validation produit :** 29 octobre 2026
- **Décideurs attendus :** tech lead et DevOps

## Contexte

Les inscriptions, notes, publications, encaissements et règles exigent des contraintes relationnelles et des transactions. Les valeurs calculées ne doivent pas dépendre d’un état local de navigateur. La migration de production doit rester réversible au niveau applicatif.

## Décision

Utiliser **PostgreSQL** comme base système de référence et **Prisma Migrate** pour versionner le schéma. La version PostgreSQL et l’image Docker seront épinglées lors de M1 après vérification de compatibilité avec Prisma ; aucune balise `latest` ne sera utilisée.

Conventions initiales :

- clés étrangères et contraintes d’unicité réelles, y compris pour le matricule et les séquences par établissement ;
- montants en `numeric`, jamais en flottants ; instants en `timestamptz` ;
- identifiants d’établissement et de portée sur chaque agrégat concerné ;
- JSONB réservé aux snapshots ou paramètres à schéma validé, pas pour masquer un modèle relationnel instable ;
- suppression physique exceptionnelle ; archivage et historique pour les objets métier référencés ;
- transactions Prisma courtes, niveaux d’isolation choisis pour les écritures financières et verrous explicites en cas de concurrence ;
- calculs sensibles exécutés côté serveur, avec snapshot de la règle applicable.

Les migrations suivent **expand → backfill → contract**. Une version d’API doit pouvoir fonctionner avec le schéma de transition. Les backfills sont reprenables et contrôlés par comptages et échantillons.

## Alternatives évaluées

- **SQLite en production serveur** : rejeté pour les écritures concurrentes multiutilisateurs ;
- **MySQL/MariaDB** : possible, mais sans avantage démontré face à PostgreSQL et aux transactions/snapshots JSONB prévus ;
- **Prisma comme seule protection** : rejeté ; les contraintes doivent aussi exister dans PostgreSQL et les scénarios de concurrence doivent être testés.

## Conséquences

- besoin d’un service PostgreSQL sauvegardé et supervisé ;
- revue obligatoire des migrations de grande table ;
- restauration et retour arrière testés sur staging avant production ;
- les snapshots publiés ne sont jamais recalculés silencieusement après changement de règle.

## Critères de vérification

- tests d’intégration sur une vraie instance PostgreSQL ;
- rejet des doublons et des montants invalides au niveau base ;
- deux encaissements concurrents ne peuvent pas produire le même reçu ;
- migration depuis la version précédente testée avec données de volume représentatif ;
- redéploiement de l’artefact précédent possible avant la phase `contract`.
