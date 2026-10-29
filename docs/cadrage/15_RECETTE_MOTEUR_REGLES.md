# Recette technique du moteur de règles #39

**Statut :** socle API implémenté ; recette fonctionnelle métier et écrans encore ouverts
**Références :** issue #39, `docs/API.md`, `10_CONFIGURATION_METIER_AVANCEE.md`

## 1. Ce qui est vérifiable dans l’API

- catalogue fermé de définitions typées et validation sans évaluation de code ;
- ensembles par organisation/domaine, avec `scopeOrder` explicite et ressources de portée contrôlées ;
- brouillons, duplication, différences, dates d’effet et statuts de workflow ;
- résolution déterministe avec provenance, héritage, manquants et conflits ;
- simulations append-only fondées sur le même résolveur que la lecture publiée ;
- préparation → validation pédagogique → approbation → publication, avec refus de l’auto-validation du préparateur ;
- simulation réussie et hash identique exigés avant publication ;
- protection SQL des versions publiées, simulations, snapshots et révisions ;
- snapshots de configuration pour les futurs documents officiels.

## 2. Scénarios de preuve

Les fixtures utilisent uniquement des organisations, programmes et valeurs fictifs. Elles vérifient notamment :

1. une base organisationnelle `0–20`, une adaptation de programme à `120` et une autre à `80`, sans valeur globale imposée ;
2. l’héritage du minimum et la provenance de chaque valeur ;
3. une duplication future qui ne change pas la résolution avant sa date d’effet ;
4. une soumission incomplète et une incohérence de barème rejetées avec des issues ;
5. une portée d’une autre organisation et une écriture dans un domaine non autorisé refusées ;
6. l’auto-validation du préparateur refusée ;
7. deux adaptations différentes de même portée/date signalées comme ambiguës ;
8. l’impossibilité de modifier une version publiée par HTTP ou SQL, y compris son `archivedAt` sans transition ;
9. une simulation complète mais hors portée qui ne permet pas la publication de la candidate ;
10. un snapshot qui conserve le maximum de sa version après publication d’une version future.

Le run CI PostgreSQL associé au commit courant constitue la preuve d’intégration ; un sandbox sans PostgreSQL ne suffit pas.

## 3. Limites explicites avant recette métier

- Le catalogue initial ne couvre pas encore toutes les listes structurées, tables de décision et règles fiscales/paie du cahier des charges.
- La simulation actuelle compare des configurations résolues et leur cohérence ; elle ne produit pas encore de bulletin, de moyenne, de facture ou de paie officielle.
- Les stratégies de calcul prédéfinies et leurs exemples métier doivent être ajoutés seulement après validation des questions `RUL`, `ACA`, `FIN` et `PAY`.
- Les entités sections/options/programmes sont des types de ressources RBAC disponibles pour leurs futurs services ; les écrans et la composition pédagogique détaillée ne sont pas livrés par ce socle.
- L’API de snapshot est une primitive technique : le module documentaire devra vérifier le document réel et enregistrer le snapshot dans sa propre transaction.
- Les habilitations nominatives, les règles de duplication entre établissements, les quotas de simulateurs par domaine et les RPO/RTO restent des décisions ouvertes.

Ces limites ne doivent pas être présentées comme une recette UI complète ni comme une validation des valeurs académiques, fiscales ou sociales d’un établissement.
