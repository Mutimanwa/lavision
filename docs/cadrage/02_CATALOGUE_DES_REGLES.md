# Catalogue des règles configurables à valider

**Statut :** proposition de catalogue pour les ateliers M0
**Références :** issues #2 et #39

## 1. Convention de lecture

Chaque ligne décrit une décision métier, pas encore son implémentation. Les clés sont des identifiants techniques candidats destinés à éviter les ambiguïtés ; elles pourront être adaptées lors de la conception de l’API.

Le fonctionnement détaillé des portées, versions, simulations et snapshots est défini dans la [configuration métier avancée](10_CONFIGURATION_METIER_AVANCEE.md). Toute règle variable découverte dans un module doit être ajoutée au présent catalogue avant sa recette.

| Colonne | Signification |
| --- | --- |
| Type | nature contrôlée de la valeur, jamais une formule libre |
| Portée candidate | niveau auquel la valeur peut être adaptée |
| Point de départ | comportement du prototype ou proposition à discuter |
| Validation | propriétaire qui doit confirmer la règle |

**Types autorisés :** booléen, entier borné, décimal exact, montant en unité monétaire entière ou décimal exact, énumération, date, durée, liste structurée et objet à schéma fixe. Aucun script ou code utilisateur n’est accepté.

**Portées possibles :** établissement, site, année, niveau, programme, classe ou matière lorsque la règle le justifie. Une même clé ne doit pas autoriser des portées sans intérêt métier.

## 2. Principes transverses déjà arrêtés

Ces principes ont été explicitement retenus pour lavision et ne sont pas remis en cause par les ateliers de valeurs :

| Clé candidate | Principe |
| --- | --- |
| `rules.scope.organization` | chaque établissement peut adapter ses règles autorisées sans changer le code |
| `rules.catalog.coverage` | toute règle métier variable du périmètre est référencée ; aucune variable critique cachée dans un module |
| `rules.unknown_policy` | une règle obligatoire inconnue reste explicitement non configurée et bloque le résultat officiel concerné, sans repli silencieux |
| `rules.lifecycle` | statuts brouillon, en validation, publié, planifié et archivé, avec dates d’effet et contrôle des chevauchements |
| `rules.publication.immutable` | une version publiée est immutable ; toute modification crée une nouvelle version |
| `rules.publication.simulation_required` | une simulation sur des exemples précède la publication |
| `rules.documents.snapshot` | chaque document officiel conserve la version exacte des règles utilisée |
| `rules.security.domain_permissions` | les droits de modification et de publication sont distincts par domaine |
| `rules.security.no_arbitrary_code` | aucune formule ou code arbitraire fourni par un utilisateur n’est exécuté |
| `rules.audit` | auteur, date, motif, ancienne et nouvelle version sont historisés |

### Décisions techniques encore ouvertes

- ordre de priorité exact entre les portées d’héritage ;
- comportement si deux adaptations de même spécificité se chevauchent ;
- fuseau horaire utilisé pour les dates d’effet ;
- procédure et personne autorisées à annuler une publication future ;
- duplication d’une configuration entre années ou établissements et contrôles associés ;
- politique d’archivage d’une version encore référencée par des documents.

## 3. Référentiels et années scolaires

| Clé candidate | Type | Portée candidate | Point de départ à valider | Validation |
| --- | --- | --- | --- | --- |
| `calendar.year.period_structure` | liste structurée | établissement, année | trois trimestres dans le prototype ; structure réelle à confirmer | scolarité |
| `calendar.period.grading_window` | dates bornées | période | dates de saisie distinctes des dates pédagogiques, à confirmer | scolarité |
| `calendar.period.publication_window` | dates bornées | période | aucune fenêtre dans le prototype | direction |
| `calendar.year.closure_policy` | énumération | établissement | clôture puis réouverture autorisée et motivée | direction |
| `calendar.non_working_days` | liste de dates | site, année | calendrier à fournir | scolarité |
| `academic.class.capacity_enforcement` | énumération | niveau | bloquant ou avertissement à choisir | direction |
| `academic.student.unique_matricule_scope` | énumération | établissement | matricule unique dans l’établissement | secrétariat |
| `academic.enrollment.single_primary_class` | booléen | établissement | une affectation principale à une date donnée | secrétariat |

## 4. Notes, moyennes et bulletins

| Clé candidate | Type | Portée candidate | Point de départ à valider | Validation |
| --- | --- | --- | --- | --- |
| `academic.grading.scale_min` | décimal exact | programme, matière | `0` dans le prototype | pédagogie |
| `academic.grading.scale_max` | décimal exact | programme, matière | `20` dans le prototype ; autres barèmes à prévoir | pédagogie |
| `academic.grading.input_increment` | décimal exact | programme | pas de limite de pas explicite dans le prototype | pédagogie |
| `academic.grading.conversion_mode` | énumération | programme | aucune conversion ; définir si plusieurs barèmes coexistent | pédagogie |
| `academic.subject.coefficient` | décimal positif borné | programme, matière | aucun coefficient séparé visible sur les deux bulletins ; ne pas doubler la pondération portée par les maxima | pédagogie |
| `academic.evaluation.weight` | décimal positif borné | type d’évaluation | absent du prototype | pédagogie |
| `academic.evaluation.term_schema` | énumération + paramètres fixes | programme, matière | schémas « périodes + examen » et « périodes sans examen » observés ; valeurs à valider | pédagogie |
| `academic.average.subject_aggregation` | énumération | programme | moyenne pondérée des notes par matière à préciser | pédagogie |
| `academic.average.report_aggregation` | énumération | niveau, programme | moyenne pondérée dans le prototype ; bulletins à base de sommes de points et de maxima à prendre en charge | pédagogie |
| `academic.report.grouping_schema` | liste structurée | programme, document | groupes de maxima ou domaines/sous-domaines avec sous-totaux observés ; ordre et regroupements à valider | pédagogie |
| `academic.average.missing_policy` | énumération | niveau, programme | note absente différente de zéro ; les tirets du bulletin 5e HP exigent des statuts explicites | pédagogie |
| `academic.average.exemption_policy` | énumération | programme | absent du prototype ; ne pas réduire un maximum sans règle validée | pédagogie |
| `academic.average.provisional_display` | booléen | niveau | moyenne provisoire visible sur dossier incomplet dans le moteur actuel | pédagogie |
| `academic.rounding.calculation_precision` | entier borné | établissement | deux décimales dans le prototype | pédagogie |
| `academic.rounding.display_precision` | entier borné | document | deux décimales dans le prototype ; une décimale observée en 5e HP | pédagogie |
| `academic.rounding.mode` | énumération | établissement | arrondi arithmétique du prototype ; troncature candidate à confirmer pour le bulletin 5e HP | pédagogie |
| `academic.rounding.stage` | énumération | niveau, programme | à définir : ligne, matière, période ou résultat final | pédagogie |
| `academic.rank.tie_policy` | énumération | niveau | rang de compétition `1, 1, 3` dans le prototype | pédagogie |
| `academic.rank.incomplete_policy` | énumération | niveau | rang provisoire après les complets proposé le 29/10/2026, exclu du classement officiel ; numérotation à valider | pédagogie |
| `academic.appreciation.scale` | liste structurée bornée | niveau, programme | seuils Excellent/Très bien/Bien/Passable du prototype | pédagogie |
| `academic.pass.mark` | décimal dans le barème | niveau, programme | seuil unique configurable, valeur initiale `10/20` | direction |
| `academic.pass.eliminatory_rules` | liste structurée | niveau, programme | absent du prototype ; à fournir si applicable | direction |
| `academic.pass.compensation_policy` | énumération + paramètres fixes | niveau, programme | absent du prototype | direction |
| `academic.pass.resit_policy` | liste structurée | niveau, programme | absent du prototype | direction |
| `academic.year.aggregation` | énumération + poids bornés | niveau, programme | structure séparée des poids versionnés proposée le 29/10/2026 ; aucune valeur par défaut, validation par l’autorité compétente requise | direction |
| `academic.year.decision_policy` | liste structurée | niveau, section, programme | passage et redoublement proposés comme décisions finales, ajournement comme état avant délibération ; textes applicables à vérifier | direction |
| `academic.national_exam.result_block` | objet à schéma fixe | programme, document | blocs ENAFEP, TENASOSP et EXETAT distincts observés ; maxima, combinaison et portée à valider, sans `50/50` par défaut | direction/scolarité |
| `academic.year.midyear_policy` | énumération + règles bornées | niveau, programme | calcul sur les seules périodes présentes proposé le 29/10/2026 ; éligibilité annuelle à valider | scolarité/direction |
| `academic.decision.attendance_policy` | liste structurée bornée | niveau, programme | effet configurable proposé le 29/10/2026, sans règle ni seuil par défaut | direction |
| `academic.decision.conduct_policy` | liste structurée bornée | niveau, programme | effet configurable proposé le 29/10/2026, sans règle ni seuil par défaut | direction |
| `academic.council.override_policy` | énumération | établissement | dérogation non tranchée ; résultat calculé, décision administrative et validation officielle doivent rester distincts | direction |
| `academic.publication.incomplete_policy` | énumération | niveau | bulletin provisoire ou relevé incomplet proposé le 29/10/2026 ; aucune note ni décision annuelle inventée | direction |
| `academic.publication.correction_policy` | énumération | établissement | circuit distinct avec validation pédagogique, autorisation administrative et nouvelle version proposé le 29/10/2026 | direction |
| `academic.publication.family_visibility` | énumération | niveau | résultats publiés et document provisoire/incomplet explicitement marqué proposés le 29/10/2026 ; modèle à préciser | direction |

### Contraintes minimales attendues de l’API

- `scale_max > scale_min` et seuils compris dans le barème ;
- coefficients et poids strictement positifs lorsqu’ils participent à une moyenne ;
- dénominateur nul interdit et traité par une règle explicite ;
- bornes des appréciations ordonnées, sans chevauchement ni trou non intentionnel ;
- poids d’agrégation compatibles avec les périodes réellement définies ;
- une pondération annuelle obligatoire absente produit `non calculable`, jamais un poids égal ou un pourcentage implicite ;
- un document provisoire/incomplet et son rang provisoire sont explicitement séparés des résultats officiels et de la décision annuelle ;
- chaque branche utilise un schéma explicite ; une branche sans examen ne doit ni exiger ni recevoir une note d’examen ;
- totaux de semestre, T.G., totaux généraux et maxima généraux réconciliés avant publication, sans correction silencieuse des écarts ;
- aucune règle de compensation ne doit produire une note ou une décision hors domaine ;
- résultat académique calculé, décision administrative et validation officielle sont distincts ; aucun libellé officiel n’est appliqué sans référence vérifiée pour le niveau et la section ;
- une période antérieure à l’inscription n’est pas une note nulle ; le résultat annuel officiel exige une règle d’éligibilité validée ;
- assiduité et conduite ne modifient un résultat que par une règle bornée, publiée et applicable au programme ;
- le résultat simulé doit être reproductible avec la même version et les mêmes entrées.

## 5. Assiduité et vie scolaire

| Clé candidate | Type | Portée candidate | Point de départ à valider | Validation |
| --- | --- | --- | --- | --- |
| `attendance.capture.mode` | énumération | niveau | appel quotidien dans le prototype | vie scolaire |
| `attendance.status.catalog` | liste structurée | établissement | présent, absent, retard, excusé dans le prototype | vie scolaire |
| `attendance.status.counts_absent` | liste d’énumérations | niveau | incidence de chaque statut à définir | vie scolaire |
| `attendance.late.threshold_minutes` | entier borné | niveau | absent du prototype | vie scolaire |
| `attendance.late.to_absence_policy` | énumération + entier borné | niveau | absent du prototype ; ne pas inventer de conversion | vie scolaire |
| `attendance.justification.required` | liste d’énumérations | établissement | justificatif selon statut à définir | vie scolaire |
| `attendance.justification.deadline` | durée bornée | établissement | absent du prototype | vie scolaire |
| `attendance.alert.thresholds` | liste structurée | niveau | absent du prototype | direction |
| `attendance.correction.window` | durée bornée | établissement | correction après clôture à encadrer | direction |
| `conduct.incident.visibility` | énumération | établissement | accès restreint ; rôles exacts à définir | direction |

## 6. Finance scolaire

| Clé candidate | Type | Portée candidate | Point de départ à valider | Validation |
| --- | --- | --- | --- | --- |
| `finance.currency.code` | énumération ISO | établissement | BIF comme hypothèse de cadrage | comptable |
| `finance.currency.minor_units` | entier borné | devise | précision réelle à confirmer ; aucun flottant | comptable |
| `finance.invoice.debtor_policy` | énumération | établissement | élève, famille ou payeur à définir | comptable |
| `finance.tariff.applicability` | liste ordonnée | établissement | année/niveau/programme/service ; priorité à définir | comptable |
| `finance.invoice.proration_policy` | énumération + paramètres fixes | type de frais | absent du prototype | comptable |
| `finance.discount.stacking_policy` | énumération | établissement | cumul et plafonds à définir | comptable |
| `finance.discount.calculation_base` | énumération | type de remise | base avant/après autres remises à définir | comptable |
| `finance.discount.approval_thresholds` | liste de montants | site | seuils et approbateurs à définir | direction |
| `finance.late_fee.enabled` | booléen | établissement | désactivé tant qu’aucune règle n’est validée | direction |
| `finance.late_fee.calculation_base` | énumération | type de frais | base à définir si pénalités autorisées | comptable |
| `finance.late_fee.rate_or_amount` | décimal ou montant borné | type de frais | aucune valeur par défaut proposée | direction |
| `finance.late_fee.grace_period` | durée bornée | type de frais | aucune valeur par défaut proposée | comptable |
| `finance.payment.allocation_order` | liste ordonnée | établissement | affectation manuelle ou automatique à définir | comptable |
| `finance.payment.overpayment_policy` | énumération | établissement | crédit, rejet ou remboursement à définir | comptable |
| `finance.payment.manual_confirmation` | énumération + seuils | site | rôles et justificatifs à définir | comptable |
| `finance.payment.cancellation_policy` | énumération | établissement | contre-opération, jamais effacement silencieux | comptable |
| `finance.cash.opening_required` | booléen | site | session de caisse requise pour encaisser | comptable |
| `finance.cash.variance_approval` | liste de montants | site | seuils d’écart et approbation à définir | direction |
| `finance.document.numbering_scope` | énumération | établissement | séquence par site/année/type à définir | comptable |
| `finance.document.numbering_reset` | énumération | établissement | aucune réutilisation ; rythme de réinitialisation à définir | comptable |
| `finance.dunning.schedule` | liste structurée | établissement | relances et contenus à définir | comptable |

### Contraintes minimales attendues de l’API

- montants exacts et devise cohérente sur toute une opération ;
- total, remises, majorations, avoirs et paiements réconciliables ;
- numéros officiels uniques et jamais réutilisés ;
- aucune suppression physique d’un paiement confirmé ou d’une clôture validée ;
- aucune remise ou pénalité négative déguisée ; utiliser le type d’écriture approprié ;
- affectation d’un paiement limitée au montant disponible selon la règle de trop-perçu ;
- configuration fiscale inactive tant qu’elle n’a pas été validée par le responsable compétent.

## 7. Personnel, congés et paie

Les valeurs de cette section restent volontairement sans proposition chiffrée. Elles exigent les documents de l’établissement et, pour la paie, la validation d’un spécialiste local.

| Clé candidate | Type | Portée candidate | Décision attendue | Validation |
| --- | --- | --- | --- | --- |
| `hr.contract.types` | liste structurée | établissement | types de contrats et champs obligatoires | RH |
| `hr.work_schedule.pattern` | liste structurée | site, fonction | horaires, jours travaillés et exceptions | RH |
| `hr.attendance.capture_mode` | énumération | site, fonction | mode de pointage et corrections | RH |
| `hr.leave.catalog` | liste structurée | établissement | types de congés et justificatifs | RH |
| `hr.leave.accrual_policy` | énumération + paramètres fixes | type de contrat | acquisition et prorata | RH/expert |
| `hr.leave.day_counting` | énumération | type de congé | jours ouvrés, ouvrables ou calendaires | RH |
| `hr.leave.carryover_policy` | entier/durée bornés | type de congé | report et expiration | RH/direction |
| `hr.leave.negative_balance_policy` | énumération + borne | type de congé | autorisation et approbation | direction |
| `hr.leave.approval_workflow` | liste structurée | type de congé | approbateurs selon durée et fonction | direction |
| `payroll.periodicity` | énumération | établissement | période et date de clôture | RH/expert |
| `payroll.base.calculation` | énumération | type de contrat | base mensuelle, horaire ou autre réellement utilisée | expert local |
| `payroll.proration_policy` | énumération + paramètres fixes | type de contrat | entrée, sortie, absence et temps partiel | expert local |
| `payroll.variable.catalog` | liste structurée | établissement | primes, retenues, avances et justificatifs | RH/expert |
| `payroll.contribution.catalog` | liste structurée | établissement, date d’effet | cotisations applicables et références officielles | expert local |
| `payroll.tax.catalog` | liste structurée | établissement, date d’effet | impôts applicables et références officielles | expert local |
| `payroll.rounding.policy` | énumération + précision | établissement | étape et mode d’arrondi | expert local |
| `payroll.validation.workflow` | liste structurée | établissement | préparation, contrôle, validation et règlement | direction |
| `payroll.correction_policy` | énumération | établissement | régularisation sans réécriture du bulletin historique | expert local |
| `payroll.payment.export_format` | liste structurée | banque/prestataire | format réel du destinataire | finance/expert |

## 8. Documents et communication

| Clé candidate | Type | Portée candidate | Point de départ à valider | Validation |
| --- | --- | --- | --- | --- |
| `document.template.versioning` | énumération | type de document | modèle versionné et figé sur publication | direction |
| `document.required_fields` | liste structurée | type de document | inventaire à partir des modèles officiels | propriétaire du document |
| `document.template.identity` | objet à schéma fixe | type de document, année, programme | intitulé, code de formulaire, édition et programme ; le code seul ne suffit pas à identifier une version | propriétaire du document |
| `document.publication.workflow` | liste structurée | type de document | validation pédagogique puis autorisation administrative proposées pour les résultats ; préparateur, signataire et délégations à confirmer | direction |
| `document.language` | énumération | type de document | français et autres langues à confirmer | direction |
| `document.authenticity.mode` | énumération | type de document | identifiant/QR éventuel sans donnée publique sensible | direction |
| `document.rectification_policy` | énumération | type de document | nouvelle version, mentions et diffusion | direction |
| `notification.absence.enabled` | booléen | établissement | canal et destinataires à confirmer | direction |
| `notification.dunning.enabled` | booléen | établissement | canal et contenu à confirmer | finance |
| `notification.retry_policy` | liste structurée bornée | canal | tentatives et délais techniques à définir | administrateur |

## 9. Fiche de validation d’une règle

À renseigner pour chaque règle critique ou non triviale :

| Champ | Valeur |
| --- | --- |
| Clé et intitulé | |
| Source métier ou document officiel | |
| Valeur et unité | |
| Portée et héritage | |
| Date d’effet | |
| Valeurs limites et erreurs attendues | |
| Exemples de recette associés | |
| Rôles autorisés à modifier | |
| Rôles autorisés à publier | |
| Propriétaire métier | |
| Statut | À discuter / Proposée / Validée / Refusée / Reportée |
| Validateur et date | |
