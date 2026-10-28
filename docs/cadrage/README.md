# Dossier de cadrage métier M0

**Projet :** lavision
**Version :** 0.3 — 28 octobre 2026
**Issues concernées :** [#2](https://github.com/Mutimanwa/lavision/issues/2), [#4](https://github.com/Mutimanwa/lavision/issues/4) et préparation de [#39](https://github.com/Mutimanwa/lavision/issues/39)
**Statut :** dossier de travail à compléter avec l’établissement pilote

> Aucune valeur métier proposée dans ce dossier ne devient une règle officielle par sa seule présence ici. Une décision n’est réputée validée que si son responsable, sa date et ses exemples sont consignés dans le [registre des décisions](05_REGISTRE_DES_DECISIONS.md).

## 1. Objectif

Ce dossier sert à faire valider les processus et les règles avant de figer le modèle PostgreSQL et les calculs de l’API. Il distingue :

- les principes produit déjà arrêtés ;
- les comportements actuels du prototype, qui restent à confirmer ;
- les propositions de travail, qui facilitent l’atelier sans préjuger de la décision ;
- les informations propres à l’établissement, qui restent à collecter.

Le périmètre fonctionnel de la v1 n’est pas élargi par ces ateliers. Ils précisent les modules déjà décrits dans le [cahier des charges](../CAHIER_DES_CHARGES.md).

## 2. Contenu du dossier

| Document | Utilisation | Résultat attendu |
| --- | --- | --- |
| [Processus et questions](01_PROCESSUS_ET_QUESTIONS.md) | dérouler les parcours réels, y compris les exceptions | propriétaire et schéma validés pour chaque processus critique |
| [Catalogue des règles](02_CATALOGUE_DES_REGLES.md) | choisir les paramètres, leurs portées et leurs limites | catalogue sans règle critique implicite |
| [Exemples de recette](03_EXEMPLES_DE_RECETTE.md) | recalculer des cas concrets avec les utilisateurs | entrées et résultats attendus acceptés |
| [Permissions et confidentialité](04_PERMISSIONS_ET_CONFIDENTIALITE.md) | vérifier qui voit et modifie chaque information | matrice et scénarios de refus validés |
| [Registre des décisions](05_REGISTRE_DES_DECISIONS.md) | tracer les choix et les points ouverts | décisions datées, attribuées et révisables |
| [Questionnaire de l’établissement](06_QUESTIONNAIRE_ETABLISSEMENT.md) | répondre progressivement aux informations nécessaires | structure scolaire, concours, barèmes et règles propres au pilote |
| [Base officielle RDC](07_BASE_OFFICIELLE_RDC.md) | distinguer le référentiel national des décisions locales | cycles, TENASOSP/EXETAT, calendrier et programmes sourcés |
| [Analyse du bulletin 1ère/2e secondaire](08_ANALYSE_BULLETIN_1E_2E.md) | relever les maxima, totaux et champs du modèle fourni | formules vérifiées et règles de repêchage restant à documenter |
| [Analyse du bulletin 5e HP](09_ANALYSE_BULLETIN_5E_HP.md) | comparer une option des humanités et contrôler un bulletin rempli | 22 branches, deux schémas d’agrégation, incohérences et questions ciblées documentés |
| [Configuration métier avancée](10_CONFIGURATION_METIER_AVANCEE.md) | permettre toutes les variations de règles dans le temps sans code arbitraire | parcours, héritage, versions, simulation, permissions, snapshots et critères d’acceptation |
| [Interfaces par rôle et activation progressive](11_INTERFACES_PAR_ROLE_ET_ACTIVATION.md) | définir les espaces de travail, menus et premiers pas de chaque rôle | matrice d’interfaces, prérequis d’activation, guide et scénarios UX validés |

## 3. Ateliers proposés

| Atelier | Participants indispensables | Contenu | Preuve de sortie |
| --- | --- | --- | --- |
| A1 — Scolarité et pédagogie | direction, secrétariat, enseignants, responsable de délibération | admission, inscription, périodes, notes, absences, bulletins, proclamation | exemples de bulletins recalculés et décisions académiques signées |
| A2 — Finance | direction, comptable, caissier | tarifs, remises, factures, paiements, avoirs, caisse, impayés | exemples de factures et de caisse recalculés |
| A3 — Personnel et paie | direction, RH, représentant du personnel, expert paie local | contrats, présences, congés, variables et validation de paie | exemple fictif de paie validé par l’expert |
| A4 — Accès, documents et conservation | direction, administrateur, représentants des domaines | rôles, portées, données sensibles, documents officiels, conservation | matrice d’accès et inventaire documentaire validés |

Les ateliers A1 à A3 peuvent être conduits séparément. A4 consolide leurs besoins d’accès. Un même participant peut couvrir plusieurs fonctions dans un petit établissement, mais les validations finance, paie et proclamation doivent rester explicites.

## 4. Déroulement d’un atelier

1. Partir d’un cas réel anonymisé, pas de l’écran du prototype.
2. Décrire le parcours normal, puis les annulations, corrections et cas incomplets.
3. Identifier la personne qui saisit, celle qui vérifie et celle qui autorise.
4. Relever chaque règle dans le catalogue et chaque choix dans le registre.
5. Recalculer les exemples à la main ou avec les documents actuels.
6. Faire relire le compte rendu par le propriétaire métier.
7. Ne marquer une décision « Validée » qu’après accord explicite.

### Règles de preuve

- utiliser uniquement des données fictives ou anonymisées dans Git ;
- déposer les modèles officiels contenant des données réelles dans un espace contrôlé, puis n’en inventorier ici que les champs ;
- enregistrer la source d’une règle : document officiel, pratique validée ou décision de direction ;
- conserver les divergences entre sites ou niveaux comme configurations distinctes, sans les réduire à une moyenne ;
- ne jamais inventer une obligation fiscale, une cotisation ou une durée légale de conservation.

## 5. Suivi de préparation

| Exigence de sortie M0 | État actuel | Preuve encore attendue |
| --- | --- | --- |
| Processus critiques documentés | Préparé | validation des propriétaires métier |
| Catalogue de règles configurable | Préparé ; configuration avancée spécifiée | couverture exhaustive, valeurs, portées et contraintes validées |
| Questionnaire de l’établissement pilote | Préparé ; premiers éléments à confirmer | réponses locales sur les sections/options, les barèmes par cours et les documents |
| Base officielle RDC | Consultée et sourcée | confirmation de son adoption par le pilote et des instructions applicables à chaque année |
| Exemples académiques chiffrés | Modèles 1ère/2e et 5e HP analysés ; contrôles et écarts documentés | arbitrage des anomalies, règles de pourcentage/repêchage et exemples corrigés validés avec l’établissement |
| Exemples financiers chiffrés | Préparé avec hypothèses explicites | résultats recalculés et acceptés |
| Exemple de paie | Trame uniquement | exemple validé par un expert local |
| Matrice des permissions | Catalogue initial de 13 rôles et matrice proposée | validation de la direction, portées nominatives et scénarios de test associés |
| Interfaces et activation guidée | Matrice des espaces par rôle, cycle d’activation et guide proposés | validation des parcours par rôle, responsables d’activation et maquettes correspondantes |
| Modèles de documents officiels | Inventaire à compléter | documents anonymisés et champs relevés |
| Décisions et points ouverts | Registre initialisé | responsables et dates de validation |

**L’issue #2 ne peut pas être clôturée sur la seule base de ces modèles.** Elle le sera lorsque les preuves attendues seront effectivement validées.

## 6. Passage au développement

Après validation des décisions structurantes :

1. convertir les exemples acceptés en fixtures de test anonymisées ;
2. finaliser les ADR de l’issue #3 avec les contraintes constatées ;
3. définir le schéma initial de l’issue #5 ;
4. implémenter le RBAC et les référentiels avant le moteur de règles #39 ;
5. ne brancher les calculs M2, M3 et M4 qu’aux versions publiées des règles.

Une valeur encore indéterminée doit rester un point ouvert explicite. Elle ne doit pas être remplacée silencieusement par la valeur du prototype.
