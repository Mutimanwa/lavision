# Registre des décisions de cadrage

**Version :** 0.12 — 30 octobre 2026
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
| DEC-008 | Les espaces de travail sont adaptés aux rôles, avec activation progressive des menus et guide de première utilisation contextualisé, non bloquant et rechargeable. | Validé — demande produit du 28/10/2026 | navigation calculée selon permissions, portées et prérequis ; activation séparée de la progression personnelle ; voir [spécification des interfaces](11_INTERFACES_PAR_ROLE_ET_ACTIVATION.md) |
| DEC-009 | Chaque établissement configure ses sections, options, programmes de cours, cours propres aux options et maxima. Les formulaires du recueil 2024–2025 restent utilisés cette année et servent de modèles de départ. | Validé — réponse produit du 29/10/2026 | référentiels et programmes locaux versionnés, simulation et publication ; exemple Comptabilité `120` points en Commerciale & Gestion sans valeur par défaut générale ni réécriture de l’historique |
| DEC-010 | La configuration suit quatre étapes : préparation, validation pédagogique, approbation et publication. Référentiels scolaires et règles académiques sont distincts et peuvent avoir des validateurs différents. | Validé — réponse produit du 29/10/2026 | permissions et circuits séparés ; aucune étape ne saute les contrôles ni ne modifie une version publiée |
| DEC-011 | Les huit ADR sont acceptés pour l’implémentation M1 et le démarrage de #5. | Validé — réponse produit du 29/10/2026 | revues tech lead/sécurité/DevOps et preuves de mise en œuvre toujours requises ; hébergement, RPO/RTO et production non validés |
| DEC-012 | La base des 13 rôles, les portées, le refus par défaut, l’administration technique limitée et les scénarios SEC-01 à SEC-20 servent de fondation à M1. | Validé — réponse produit du 29/10/2026 | habilitations nominatives, médical, conservation et validation des propriétaires restent à compléter ; aucune habilitation effective n’est créée par cette décision |
| DEC-013 | Toute information absente, incertaine ou non validée est traitée comme un paramètre configurable, sans valeur officielle inventée ni repli silencieux. | Validé — demande produit du 30/10/2026 | le paramètre reste « à configurer » jusqu’à saisie et validation ; les invariants de sécurité, d’audit et d’immuabilité ne sont pas désactivables ; les calculs officiels restent bloquants si leurs prérequis manquent |

## 3. Décisions académiques ouvertes

| ID | Question à trancher | Propriétaire attendu | Preuve nécessaire | État |
| --- | --- | --- | --- | --- |
| ACA-001 | Structure des années, périodes, fenêtres de saisie et de publication | scolarité | calendrier réel | Structure par cycle sélectionnée le 29/10/2026 : 2 semestres/4 périodes au secondaire, 3 trimestres/6 périodes au primaire ; validation locale et fenêtres à confirmer |
| ACA-002 | Barèmes, pas de saisie et conversion éventuelle entre barèmes | pédagogie | exemples de notes | Note maximale et définition par programme de cours sélectionnées le 29/10/2026 ; recueil 2024–2025 analysé avec maxima variables et totaux reconstitués dans le document 12 ; maximum de chaque cours configuré par l’établissement ; exemple Comptabilité `120` points en Commerciale & Gestion donné le 29/10/2026, sans adoption générale ; pas de saisie et conversions à confirmer |
| ACA-003 | Coefficients et agrégation des évaluations/matières | pédagogie | bulletin recalculé | Moyenne pondérée par coefficient distinct et agrégation variable par programme sélectionnées le 29/10/2026 ; coefficients et poids des composantes avec ou sans examen à confirmer |
| ACA-004 | Traitement des notes absentes, dispenses, absences et rattrapages | pédagogie | cas incomplets | Distinction note/absence/non-saisi et moyenne provisoire sur les seuls cours notés sélectionnées le 29/10/2026 ; diffusion provisoire/incomplète proposée, sans note inventée ni décision annuelle définitive ; rattrapages à confirmer |
| ACA-005 | Précision, mode et étapes d’arrondi | pédagogie | cas de frontière | Arrondi de chaque cours avant la moyenne, à une décimale et au pair le plus proche, sélectionné le 29/10/2026 ; précision interne et arrondi annuel à confirmer |
| ACA-006 | Classement des ex æquo et des dossiers incomplets | direction | exemple de classement | Compétition `1, 1, 3`, comparaison avant arrondi et rang provisoire des dossiers incomplets après les complets sélectionnés le 29/10/2026 ; exclusion du classement officiel, application annuelle et numérotation provisoire à confirmer sur exemple |
| ACA-007 | Seuil, notes éliminatoires, compensation et appréciations | direction | exemples de décision | Seuil de 50 % par cours et sur la moyenne générale sélectionné le 29/10/2026 comme règle de départ ; cas moyenne générale ≥ 50 % avec cours < 50 % envoyé à la délibération le 29/10/2026 ; passage et redoublement proposés comme décisions finales, ajournement comme état avant délibération ; textes applicables, compensation et mentions à confirmer |
| ACA-008 | Calcul annuel et règles de passage/redoublement | direction | exemple annuel | Combinaison des périodes et d’un examen annuel distinct sélectionnée le 29/10/2026 ; structure séparée des poids versionnés, sans valeur par défaut, à configurer après validation de l’autorité scolaire compétente en RDC ; résultat calculé, décision administrative et validation officielle séparés ; bloc école/TENASOSP `50 + 50` observé sur la seule 8e CTEB du recueil, sans adoption générique ; T.G. et seuils à confirmer sur exemple |
| ACA-009 | Conditions de blocage et de publication des bulletins | direction | cas complet et incomplet | Proposé le 29/10/2026 — publier un bulletin provisoire ou un relevé incomplet, sans note inventée ni décision annuelle définitive ; modèle, mentions et validation de la Direction à confirmer |
| ACA-010 | Réouverture, dérogation du conseil et rectification après publication | direction | procédure et approbateurs | Pour la configuration, préparation → validation pédagogique → approbation → publication validées le 29/10/2026, avec circuits distincts pour référentiels et règles ; pour les résultats, validation pédagogique puis autorisation administrative conservées ; approbateurs nommés, délégations, réouverture et notification à définir ; dérogation du conseil non tranchée et textes par niveau/section à vérifier |
| ACA-011 | Appel, statuts, retards, justificatifs et alertes d’assiduité | vie scolaire | cas quotidien et par séance | Effet de l’assiduité et de la conduite configurable par programme proposé le 29/10/2026, sans seuil par défaut ; appel, justificatifs, alertes et règles applicables à confirmer |
| ACA-012 | Contenu exact des bulletins et procès-verbaux | pédagogie/direction | modèles anonymisés | Formulaires `/005` et `/019` inventoriés ; recueil 2024–2025 : `/007`, `/008`, `/012`, `/024`–`/027`, `/042`–`/045`, `/052`–`/055` et autres modèles comparés dans le document 12 ; utilisation des formulaires du recueil pendant l’année en cours confirmée par la réponse du 29/10/2026 ; authenticité de chaque source et procès-verbaux à confirmer |
| ACA-013 | Parcours 7E/8E, TENASOSP/EXETAT, résultat et certification | direction/scolarité | base RDC sourcée + documents récents anonymisés | Choix section/option à l’entrée des Humanités après 8e/TENASOSP sélectionné le 29/10/2026 ; bloc de résultat final école/TENASOSP observé sur le modèle 8e CTEB du recueil ; fondement, certification et séquence exacte à confirmer |
| ACA-014 | Organisation des sections, options et programmes de cours | scolarité/pédagogie | grille initiale et circuit de publication de chaque établissement | Configuration locale des sections, options, cours disponibles, cours propres aux options et maxima confirmée le 29/10/2026 ; hiérarchie année → établissement → classe académique conservée ; responsables et valeurs initiales à renseigner par établissement |
| ACA-015 | Première/deuxième délibération et repêchage | direction/pédagogie | exemples de décisions et instructions applicables | Cas ACA-PROP-001 orienté vers la délibération le 29/10/2026 ; zones de repêchage observées sur plusieurs modèles non terminaux 2024–2025, sans règles chiffrées ; première/deuxième délibération, seuils et effet sur le T.G. à clarifier |
| ACA-016 | Niveaux inclus dans le premier pilote | direction/scolarité | liste des classes, sections et options participantes | Tout le secondaire — 7e/8e et Humanités — sélectionné le 29/10/2026 ; classes, sections et options initiales configurées par l’établissement pilote, sans liste figée dans le code |
| ACA-017 | Arrivées, départs et transferts en cours d’année | scolarité/direction | exemple de calcul et règle d’éligibilité | Calcul sur les seules périodes présentes proposé le 29/10/2026, sans inventer les autres ; résultat annuel officiel conditionné à une règle minimale de présence validée ; normalisation des poids et départ/retour à confirmer sur exemple |

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
| SEC-001 | Rôles, permissions combinables et portées par site/classe/matière | direction | matrice validée | Base des 13 rôles, rôles combinables, portées et scénarios SEC validée par le représentant produit le 29/10/2026 ; matrice nominative et propriétaires à valider |
| SEC-002 | Accès aux données médicales, disciplinaires, financières et RH | direction | scénarios d’accès | Incidents de la classe principale accessibles au professeur principal sélectionné le 29/10/2026 ; médical à confirmer, restrictions finance/RH à valider |
| SEC-003 | Profils soumis à MFA et procédure de récupération | direction/administrateur | politique validée | MFA pour profils sensibles sélectionnée le 29/10/2026 ; liste exacte, portails et récupération à confirmer |
| SEC-004 | Séparation préparateur/validateur/payeur selon les opérations | direction | circuit nominatif | Validation pédagogique et autorisation administrative proposées pour les résultats le 29/10/2026 ; personnes distinctes et autres domaines à confirmer |
| SEC-005 | Exports, motifs, approbations et destinataires externes | direction | inventaire des exports | Permission séparée et approbation avec motif pour exports sensibles sélectionnées le 29/10/2026 ; catégories et destinataires à inventorier |
| SEC-006 | Conservation, archivage, suppression et demandes d’accès | direction | politique applicable validée | Validation par la direction avec avis juridique, administratif et informatique sélectionnée le 29/10/2026 ; durées et procédures à fournir |
| DOC-001 | Modèles, mentions, langues, signatures et paginations | propriétaires des documents | modèles anonymisés | Structures et champs de plusieurs formulaires 2024–2025 inventoriés dans le document 12 ; formulaires du recueil toujours utilisés cette année selon la réponse du 29/10/2026 ; adaptations locales, mentions et signataires à valider |
| DOC-002 | Publication, retrait de diffusion, rectification et authenticité | direction | procédure validée | Circuit pédagogique/administratif et circuit distinct de rectification proposés le 29/10/2026 ; retrait, authenticité et signataires à confirmer |
| OPS-001 | Volumes, postes, sites, connectivité et pics d’utilisation | direction/administrateur | mesures de l’établissement pilote | Ouvert |
| OPS-002 | Disponibilité, sauvegardes et objectifs de reprise adaptés au budget | direction/administrateur | arbitrage documenté | Ouvert |
| OPS-003 | Canaux externes réellement disponibles et contrats nécessaires | direction/administrateur | fournisseurs et conditions | Ouvert |
| UX-001 | Interfaces, responsables d’activation et premiers parcours propres à chaque rôle | direction + représentants des domaines | parcours et maquettes validés | Orientation DEC-008 validée ; matrice et prérequis détaillés proposés dans le document 11, validation par rôle restante |

## 7. Questions transverses du moteur de règles

| ID | Question à trancher | Propriétaire attendu | État |
| --- | --- | --- | --- |
| RUL-001 | Ordre de priorité des héritages et traitement des adaptations concurrentes | direction + tech lead | Ouvert |
| RUL-002 | Portées autorisées pour chaque règle | propriétaires des domaines | Ouvert |
| RUL-003 | Date d’effet, fuseau et publication programmée | direction + tech lead | Versions datées et planification requises par DEC-007 ; fuseau, limites exactes et annulation à préciser |
| RUL-004 | Approbation à une ou plusieurs personnes par domaine | direction | Quatre étapes validées pour la configuration le 29/10/2026 ; identité des approbateurs et séparation éventuelle des personnes à préciser par domaine |
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
