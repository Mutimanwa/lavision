# Registre des décisions de cadrage

**Version :** 0.5 — 28 octobre 2026
**Statut :** registre actif pendant M0

## 1. Règle de tenue du registre

Une décision validée comporte un responsable, une date, une portée et, lorsqu’elle concerne un calcul, au moins un exemple. Pour la réviser, ajouter une nouvelle décision qui remplace explicitement l’ancienne plutôt que d’effacer l’historique.

Statuts utilisés : **Validé**, **Proposé**, **Ouvert**, **Bloquant**, **Refusé** ou **Remplacé**.

## 2. Principes déjà validés

Ces entrées reprennent les orientations explicitement confirmées pour le projet. Elles ne valident pas les valeurs académiques, financières ou sociales propres à un établissement.

| ID | Décision | Statut et source | Conséquence |
| --- | --- | --- | --- |
| DEC-001 | Les règles variables sont configurables par établissement depuis Paramètres → Configuration scolaire. | Validé — demande produit du 27/10/2026 | pas de développement spécifique par école pour les variations prévues |
| DEC-002 | Les paramètres sont typés, bornés et validés par l’API ; aucune formule ou code arbitraire utilisateur n’est exécuté. | Validé — exigence de conception du 27/10/2026 | catalogue fermé de règles et schémas de validation |
| DEC-003 | La configuration prévoit héritage contrôlé, simulation avant publication et permissions par domaine. | Validé — exigence de conception du 27/10/2026 | écrans de simulation et autorisations séparées |
| DEC-004 | Les règles ont des versions datées ; une version publiée est immutable et toute adaptation crée une nouvelle version. | Validé — exigence de conception du 27/10/2026 | cycle brouillon/publié/archivé |
| DEC-005 | Bulletins, proclamations, factures et paies conservent la version exacte des règles utilisée. | Validé — exigence de conception du 27/10/2026 | aucune réécriture silencieuse de l’historique officiel |
| DEC-006 | La v1 n’est pas élargie avec de nouveaux modules ; la suite du cadrage précise et valide les règles du périmètre existant. | Validé — arbitrage produit du 27/10/2026 | les ateliers ne créent pas de nouvelles fonctionnalités majeures |
| DEC-007 | Toutes les règles métier variables du périmètre doivent être personnalisables de manière avancée et pouvoir changer au fil du temps, y compris lorsque leurs valeurs sont encore inconnues pendant le cadrage. | Validé — arbitrage produit du 28/10/2026 | catalogue exhaustif, portées, versions datées, simulation et état explicite « à configurer » ; aucune valeur inventée ni repli silencieux ; voir [spécification](10_CONFIGURATION_METIER_AVANCEE.md) |

## 3. Décisions académiques ouvertes

| ID | Question à trancher | Propriétaire attendu | Preuve nécessaire | État |
| --- | --- | --- | --- | --- |
| ACA-001 | Structure des années, périodes, fenêtres de saisie et de publication | scolarité | calendrier réel | Base RDC et deux bulletins : 2 semestres/4 périodes au secondaire ; 3 trimestres/6 périodes au primaire ; adoption locale à confirmer |
| ACA-002 | Barèmes, pas de saisie et conversion éventuelle entre barèmes | pédagogie | exemples de notes | 1ère/2e : période sur 20/30/60 ; 5e HP : sur 10/20/40/50/100 ; programme actuel à confirmer |
| ACA-003 | Coefficients et agrégation des évaluations/matières | pédagogie | bulletin recalculé | Pas de coefficient séparé visible ; 5e HP confirme des schémas avec et sans examen ; plusieurs écarts de report à arbitrer |
| ACA-004 | Traitement des notes absentes, dispenses, absences et rattrapages | pédagogie | cas incomplets | Des tirets n’ajoutent pas de points dans certains totaux 5e HP, sans légende ; zéro/absence/dispense et publication à distinguer |
| ACA-005 | Précision, mode et étapes d’arrondi | pédagogie | cas de frontière | Pourcentages 5e HP compatibles avec une troncature à une décimale ; hypothèse à confirmer compte tenu des écarts |
| ACA-006 | Classement des ex æquo et des dossiers incomplets | direction | exemple de classement | Proposé : compétition `1, 1, 3` ; un rang annuel est visible en 5e HP, sans cas d’ex æquo |
| ACA-007 | Seuil, notes éliminatoires, compensation et appréciations | direction | exemples de décision | Ouvert ; une décision après repêchage ne suffit pas à établir un seuil |
| ACA-008 | Calcul annuel et règles de passage/redoublement | direction | exemple annuel | T.G. = somme des semestres sur plusieurs lignes, mais écarts 5e HP ; seuils et décisions à confirmer |
| ACA-009 | Conditions de blocage et de publication des bulletins | direction | cas complet et incomplet | Ouvert ; définir aussi le contrôle des totaux incohérents avant publication |
| ACA-010 | Réouverture, dérogation du conseil et rectification après publication | direction | procédure et approbateurs | Ouvert |
| ACA-011 | Appel, statuts, retards, justificatifs et alertes d’assiduité | vie scolaire | cas quotidien et par séance | Ouvert |
| ACA-012 | Contenu exact des bulletins et procès-verbaux | pédagogie/direction | modèles anonymisés | Formulaires `IGE/P.S/005` et `IGE/P.S/019` inventoriés ; version actuelle et procès-verbaux à confirmer |
| ACA-013 | Parcours 7E/8E, TENASOSP/EXETAT, résultat et certification | direction/scolarité | base RDC sourcée + documents récents anonymisés | Base nationale identifiée ; processus local et document exact à confirmer |
| ACA-014 | Organisation des sections, options et programmes de cours | scolarité/pédagogie | exemples réels de sections/options et programmes | 5e HP = Humanités pédagogiques selon l’utilisateur ; formulaire Pédagogie générale ; libellés et catalogue local à confirmer |
| ACA-015 | Première/deuxième délibération et repêchage | direction/pédagogie | exemples de décisions et instructions applicables | Exemple 5e HP avec pourcentages et passage ; branches, seuil, effet sur le T.G. et décision finale à clarifier |

## 4. Décisions financières ouvertes

| ID | Question à trancher | Propriétaire attendu | Preuve nécessaire | État |
| --- | --- | --- | --- | --- |
| FIN-001 | Devise, précision monétaire, débiteur et payeurs autorisés | comptable | facture réelle anonymisée | Ouvert |
| FIN-002 | Priorité des tarifs et prorata en cours de période | comptable | exemples d’inscription | Ouvert |
| FIN-003 | Bourses, remises, cumul, plafonds et approbation | comptable/direction | exemples de remise | Ouvert |
| FIN-004 | Pénalités éventuelles : base, taux/montant, grâce et plafond | direction/comptable | exemple de retard ou refus explicite | Ouvert |
| FIN-005 | Affectation des paiements entre échéances, factures et enfants | comptable | exemple multi-factures | Ouvert |
| FIN-006 | Trop-perçu, crédits, remboursements et avoirs | comptable | exemples d’opérations inverses | Ouvert |
| FIN-007 | Numérotation des factures, reçus et avoirs | comptable | séquences de chaque site | Ouvert |
| FIN-008 | Ouverture/clôture de caisse, écarts et double approbation | comptable/direction | exemple de clôture | Ouvert |
| FIN-009 | Confirmation manuelle, rapprochement et canaux acceptés | comptable | références et justificatifs | Ouvert |
| FIN-010 | Relances et informations visibles par les familles | comptable/direction | modèles de communication | Ouvert |
| FIN-011 | Taxes éventuelles et exports comptables attendus | comptable/expert | références et format du destinataire | Ouvert |

## 5. Décisions RH et paie ouvertes

| ID | Question à trancher | Propriétaire attendu | Preuve nécessaire | État |
| --- | --- | --- | --- | --- |
| RH-001 | Types de contrats, fonctions, affectations et horaires | RH | contrats anonymisés | Ouvert |
| RH-002 | Types de congés, acquisition, comptage, report et approbation | RH/direction | exemples de soldes | Ouvert |
| RH-003 | Absences et incidences éventuelles sur la paie | RH/expert | exemples justifiés | Ouvert |
| PAY-001 | Période, date de clôture et calendrier de règlement | RH/expert local | calendrier validé | Ouvert |
| PAY-002 | Base de paie, temps partiel, entrées/sorties et prorata | expert local | bulletin recalculé | Ouvert |
| PAY-003 | Primes, retenues, avances et variables autorisées | RH/expert local | catalogue et justificatifs | Ouvert |
| PAY-004 | Cotisations et impôts applicables avec dates d’effet | expert local | références officielles | Bloquant avant paie réelle |
| PAY-005 | Arrondis, étapes de calcul et contrôles de cohérence | expert local | cas de frontière | Ouvert |
| PAY-006 | Préparation, validation, règlement et régularisation | direction/expert local | circuit nominatif et exemple | Ouvert |
| PAY-007 | Contenu du bulletin, destinataires et format d’export | RH/expert local | modèle anonymisé | Ouvert |

## 6. Décisions de sécurité, documents et exploitation ouvertes

| ID | Question à trancher | Propriétaire attendu | Preuve nécessaire | État |
| --- | --- | --- | --- | --- |
| SEC-001 | Rôles, permissions combinables et portées par site/classe/matière | direction | matrice validée | Ouvert |
| SEC-002 | Accès aux données médicales, disciplinaires, financières et RH | direction | scénarios d’accès | Ouvert |
| SEC-003 | Profils soumis à MFA et procédure de récupération | direction/administrateur | politique validée | Ouvert |
| SEC-004 | Séparation préparateur/validateur/payeur selon les opérations | direction | circuit nominatif | Ouvert |
| SEC-005 | Exports, motifs, approbations et destinataires externes | direction | inventaire des exports | Ouvert |
| SEC-006 | Conservation, archivage, suppression et demandes d’accès | direction | politique applicable validée | Ouvert |
| DOC-001 | Modèles, mentions, langues, signatures et paginations | propriétaires des documents | modèles anonymisés | Ouvert |
| DOC-002 | Publication, retrait de diffusion, rectification et authenticité | direction | procédure validée | Ouvert |
| OPS-001 | Volumes, postes, sites, connectivité et pics d’utilisation | direction/administrateur | mesures de l’établissement pilote | Ouvert |
| OPS-002 | Disponibilité, sauvegardes et objectifs de reprise adaptés au budget | direction/administrateur | arbitrage documenté | Ouvert |
| OPS-003 | Canaux externes réellement disponibles et contrats nécessaires | direction/administrateur | fournisseurs et conditions | Ouvert |

## 7. Questions transverses du moteur de règles

| ID | Question à trancher | Propriétaire attendu | État |
| --- | --- | --- | --- |
| RUL-001 | Ordre de priorité des héritages et traitement des adaptations concurrentes | direction + tech lead | Ouvert |
| RUL-002 | Portées autorisées pour chaque règle | propriétaires des domaines | Ouvert |
| RUL-003 | Date d’effet, fuseau et publication programmée | direction + tech lead | Versions datées et planification requises par DEC-007 ; fuseau, limites exactes et annulation à préciser |
| RUL-004 | Approbation à une ou plusieurs personnes par domaine | direction | Ouvert |
| RUL-005 | Duplication entre années/établissements et contrôles à refaire | propriétaires des domaines | Ouvert |
| RUL-006 | Jeu minimal de simulations obligatoires avant publication | propriétaires des domaines + QA | Ouvert |

## 8. Fiche de décision détaillée

Créer une entrée détaillée pour toute décision complexe ou contestée :

| Champ | À compléter |
| --- | --- |
| ID et titre | |
| Statut | |
| Problème à résoudre | |
| Options étudiées | |
| Décision et motif | |
| Source/référence officielle | |
| Établissement, sites et domaines concernés | |
| Date d’effet | |
| Impact sur les documents historiques | |
| Permissions nécessaires | |
| Exemples et tests associés | |
| Décideur et participants | |
| Date de validation | |
| Condition de révision | |

## 9. Validation globale du cadrage M0

| Fonction | Nom | Périmètre validé | Date | Réserves éventuelles |
| --- | --- | --- | --- | --- |
| Direction / product owner | à nommer | périmètre et arbitrages | | |
| Scolarité | à nommer | processus élèves et documents | | |
| Pédagogie/délibération | à nommer | notes et proclamation | | |
| Finance/caisse | à nommer | facturation et encaissements | | |
| RH | à nommer | personnel et congés | | |
| Expert paie local | à nommer | paie fictive et règles applicables | | |
| Tech lead | à nommer | faisabilité et ADR | | |
| Sécurité/confidentialité | à nommer | permissions et conservation | | |
