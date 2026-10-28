# Cahier des charges — LaVision

**Produit :** LaVision, plateforme intégrée de gestion scolaire
**Version du document :** 1.2
**Date :** 28 octobre 2026
**Statut :** proposition détaillée à valider avec la direction, le secrétariat, la finance et les enseignants
**Périmètre :** web, desktop Tauri, API, données, sécurité et exploitation

---

## 1. Résumé exécutif

LaVision doit devenir une plateforme modulaire couvrant le cycle de vie complet de l’établissement :

- admission et inscription ;
- dossier élève et responsables légaux ;
- organisation pédagogique, présences, notes et proclamation ;
- frais scolaires, facturation, encaissements et suivi financier ;
- dossiers du personnel, contrats, présences, congés et paie ;
- communication avec les familles et portails dédiés ;
- services annexes comme la bibliothèque, le transport ou l’internat ;
- pilotage, reporting, audit et administration ;
- exploitation web et desktop avec une source de données maîtrisée.

La priorité n’est pas d’additionner des interfaces. LaVision doit garantir que **chaque information possède un propriétaire, une règle de validation, un historique et un niveau d’accès**. Une note publiée, un paiement encaissé ou un bulletin de paie doit être traçable et contrôlable.

Le socle actuel fournit déjà un frontend Next.js/TypeScript/Tailwind, un parcours académique de démonstration et un shell Tauri. La prochaine étape structurante est le passage à une architecture client-serveur sécurisée avec PostgreSQL et une API métier.

## 2. Contexte et point de départ

### 2.1 Acquis

- migration du prototype PHP/HTML vers Next.js, React, TypeScript et Tailwind CSS ;
- inscription, dossiers élèves, classes, matières et coefficients ;
- notes, bulletins, proclamation et présences en mode démonstration ;
- exports CSV, impression et sauvegarde JSON ;
- moteur académique testé ;
- export statique web et build Windows Tauri en CI ;
- documentation initiale et ancien code conservé dans `legacy/`.

### 2.2 Limites actuelles

- données stockées dans le navigateur avec `localStorage` ;
- absence de comptes, rôles et autorisations serveur ;
- absence de base centralisée et de transactions ;
- absence de gestion financière et de paie ;
- absence de stockage documentaire sécurisé ;
- absence de notifications et portails parents/élèves ;
- absence de synchronisation desktop et de mécanisme de reprise ;
- règles de notation et documents non encore validés par un établissement pilote.

### 2.3 Conséquence

Le prototype actuel ne doit pas recevoir de données réelles de mineurs ou de paiements. La mise en production exige les travaux des jalons M0 et M1 décrits dans le plan de réalisation.

## 3. Vision et objectifs

### 3.1 Vision

> Une seule plateforme pour suivre un élève, un paiement, un employé ou une décision pédagogique, sans ressaisie et avec une piste d’audit fiable.

### 3.2 Objectifs mesurables

| Objectif | Indicateur cible |
| --- | --- |
| Supprimer les doubles saisies | une donnée de référence partagée par tous les modules |
| Fiabiliser les inscriptions | 100 % des affectations historisées par année scolaire |
| Accélérer les encaissements | reçu disponible immédiatement après validation |
| Suivre les impayés | solde et échéancier calculés à partir des écritures |
| Sécuriser les résultats | publication réservée aux rôles autorisés et journalisée |
| Réduire les erreurs de bulletin | règles centralisées et tests de non-régression |
| Améliorer la disponibilité | objectif initial de 99,5 % hors maintenance planifiée |
| Préparer la reprise | RPO initial ≤ 24 h et RTO initial ≤ 4 h, à resserrer selon le budget |
| Faciliter l’adoption | parcours principaux utilisables sans formation technique approfondie |

## 4. Hypothèses de cadrage

Ces hypothèses structurent la conception mais doivent être confirmées pendant M0 :

- établissement francophone, avec possibilité d’ajouter l’anglais et le kirundi ;
- devise principale BIF, architecture permettant une autre devise ;
- année scolaire divisée en périodes configurables ;
- barème, coefficients et décisions paramétrables par niveau ;
- une première installation pour un établissement, avec possibilité de plusieurs sites ;
- présence des champs d’organisation dans le modèle pour ne pas bloquer une future offre multi-établissements ;
- desktop connecté à l’API dans un premier temps ; hors ligne complet traité séparément ;
- paiements mobiles et règles fiscales/paie intégrés seulement après validation des partenaires et obligations locales.

## 5. Périmètre fonctionnel

### 5.1 Socle d’administration

**Fonctions attendues**

- identité et coordonnées de l’établissement ;
- sites, salles, niveaux, filières, options et départements ;
- années scolaires, périodes, calendriers et jours non ouvrés ;
- classes, capacités, groupes et affectations ;
- matières, coefficients, enseignants et programmes ;
- devises, langues, formats de date et numérotation des documents ;
- modèles de bulletins, reçus, attestations et communications ;
- paramètres par module avec historique des changements.

**Règles importantes**

- une année clôturée n’est plus modifiable sans réouverture autorisée ;
- les référentiels utilisés par des documents ne sont pas supprimés physiquement ;
- chaque modification de paramètre sensible indique l’auteur, la date et l’ancienne valeur.

### 5.1.1 Moteur de règles métier configurables

Les règles peuvent varier d’un établissement à un autre et évoluer au fil du temps. **Toute règle métier variable du périmètre prévu** doit donc être administrée depuis **Paramètres → Configuration scolaire**, sans modification du code source pour changer ses valeurs.

**Paramètres concernés**

- académique : barèmes, schémas avec ou sans examen, coefficients éventuels, agrégations, notes manquantes, arrondis ou troncatures, seuils, appréciations, calcul annuel, ex æquo, repêchage et décisions ;
- assiduité : types d’appel, tolérances, justificatifs et seuils d’alerte ;
- finance : devise, échéanciers, pénalités, remises, affectations, numérotation et seuils d’approbation ;
- personnel : temps de travail, congés, variables de paie et circuits de validation ;
- documents : modèles, mentions obligatoires, diffusion, rectification et publication ;
- autres domaines déjà prévus : toute variable de communication ou de service doit également être référencée dans le catalogue.

**Exigences de conception**

- un catalogue exhaustif des règles variables, avec paramètres typés et bornés validés par l’API ;
- des modèles de départ sourcés et duplicables en brouillon, sans valeur par défaut cachée ni adoption silencieuse des valeurs du prototype ;
- une configuration par établissement, avec héritage explicable et adaptations autorisées par site, année, niveau, section/option, programme, classe ou branche ;
- des statuts brouillon, en validation, publié, planifié et archivé, ainsi que des dates d’effet et des contrôles de chevauchement ;
- des états explicites pour une règle non configurée, non applicable ou en conflit ; une règle obligatoire manquante bloque le résultat officiel concerné ;
- une simulation sans effet métier comparant la version applicable et le brouillon avant publication ;
- des permissions distinctes de lecture, brouillon, simulation, validation et publication par domaine ;
- un historique complet, la comparaison des versions et la possibilité de dupliquer une configuration existante ;
- des stratégies de calcul prédéfinies et des tables structurées bornées, sans exécution de code ou de formule arbitraire fournie par un utilisateur.

**Versioning et historique**

Une règle publiée est immutable. Toute modification crée une nouvelle version qui ne s’applique qu’à partir de sa date d’effet. Une version future ne modifie pas les calculs actuels ; un retour à une ancienne politique crée une nouvelle version datée. Les bulletins, proclamations, factures et paies conservent le snapshot exact de la configuration utilisée. Un changement de configuration ne doit jamais modifier silencieusement un document officiel déjà publié.

La spécification détaillée des parcours, états, contrôles et critères d’acceptation figure dans [Configuration métier avancée](cadrage/10_CONFIGURATION_METIER_AVANCEE.md).

### 5.2 Utilisateurs, rôles et sécurité

**Fonctions attendues**

- connexion, déconnexion et récupération de compte ;
- invitation et désactivation d’un utilisateur ;
- authentification multifacteur pour les profils sensibles ;
- rôles et permissions fines appliqués par l’API ;
- portée des permissions : établissement, site, classe, matière ou fonction ;
- sessions actives et révocation ;
- journal d’audit consultable selon permission ;
- politique de confidentialité et gestion des consentements nécessaires.

**Profils de référence**

| Profil | Droits principaux |
| --- | --- |
| Administrateur technique | configuration, comptes, intégrations, supervision |
| Direction | pilotage, validations, rapports, proclamation |
| Secrétariat / scolarité | admissions, inscriptions, dossiers, documents |
| Comptable | frais, factures, créances, rapports financiers |
| Caissier | encaissements et reçus dans sa session de caisse |
| Responsable RH | personnel, contrats, congés, paie selon permission |
| Enseignant | classes et matières assignées, notes et appels |
| Professeur principal | suivi de sa classe et préparation du conseil |
| Parent / responsable | enfants rattachés, bulletins, factures et communications |
| Élève | emploi du temps, devoirs, notes publiées et documents autorisés |
| Auditeur / lecture seule | consultation des rapports et journaux autorisés |

Un rôle ne donne jamais implicitement accès à tous les établissements ou à tous les élèves.

### 5.3 Admissions, élèves et familles

**Fonctions attendues**

- demandes d’admission et liste d’attente ;
- inscription, réinscription et renouvellement d’année ;
- identité, matricule, photo et informations administratives ;
- responsables légaux, liens familiaux et autorisations de contact ;
- rattachement contrôlé d’un parent à ses enfants ;
- affectation à une classe et historique des affectations ;
- transfert interne, départ, retour et statut ancien élève ;
- documents requis, dates d’expiration et vérification ;
- bourses et exonérations visibles selon permission ;
- informations médicales strictement restreintes ;
- import CSV contrôlé avec rapport d’erreurs ;
- exports et attestations scolaires.

**Règles importantes**

- matricule unique dans l’établissement ;
- un élève actif possède au plus une affectation principale à une date donnée ;
- les coordonnées d’un responsable peuvent être partagées entre plusieurs enfants sans duplication ;
- une suppression se transforme généralement en archivage afin de conserver l’historique ;
- les données médicales ne sont jamais affichées dans les listes générales.

### 5.4 Organisation pédagogique

- programmes et référentiels par niveau ;
- matières, coefficients et règles de calcul ;
- attribution des enseignants aux classes et matières ;
- groupes et options ;
- emplois du temps avec détection des conflits de salle, classe et enseignant ;
- calendrier des cours, examens et événements ;
- remplacements et suivi du volume horaire ;
- séances réalisées et progression du programme.

### 5.5 Présences, conduite et vie scolaire

- appel par séance ou par journée selon configuration ;
- présent, absent, retard, excusé et départ anticipé ;
- justificatifs et validation par un rôle autorisé ;
- alertes d’absence aux responsables ;
- suivi de la conduite, incidents et sanctions avec confidentialité ;
- statistiques d’assiduité par élève, classe et période ;
- historique des corrections d’appel.

### 5.6 Devoirs, évaluations et notes

- devoirs et ressources pédagogiques ;
- types d’évaluation et périodes ;
- grilles de saisie par classe, matière et évaluation ;
- barèmes multiples, coefficients et notes maximales ;
- brouillon, validation et réouverture contrôlée ;
- historique des changements de notes ;
- import/export contrôlé ;
- commentaires et appréciations ;
- règles de rattrapage et compensation si applicables.

### 5.7 Bulletins, conseils et proclamation

- calcul des moyennes et rangs selon les règles validées ;
- bulletins périodiques et annuels ;
- appréciations du conseil de classe ;
- délibération avec contrôle des dossiers incomplets ;
- décisions d’admission, ajournement, passage ou redoublement selon contexte ;
- publication différée et gel d’une version officielle ;
- procès-verbal signé et archivé ;
- relevés de notes, attestations de réussite et diplômes ;
- vérification d’authenticité d’un document par identifiant ou QR code si retenu.

**Invariant métier :** une proclamation officielle référence une version figée des notes et des règles utilisées. Toute correction ultérieure exige une procédure de réouverture et reste auditable.

### 5.8 Finances scolaires

#### Référentiel et facturation

- types de frais : inscription, scolarité, transport, cantine, examen, etc. ;
- tarifs par année, niveau, classe, option ou élève ;
- échéanciers et paiements fractionnés ;
- factures et avoirs ;
- remises, bourses, exonérations et majorations ;
- taxes éventuelles configurables après validation métier ;
- numérotation séquentielle et non réutilisable des documents.

#### Encaissements

- paiement total ou partiel ;
- espèces, banque, chèque, virement, mobile money ou autre canal ;
- reçus et preuves de paiement ;
- affectation d’un paiement à une ou plusieurs factures ;
- sessions de caisse, ouverture, clôture et écart de caisse ;
- validation d’un paiement manuel par un rôle autorisé ;
- remboursement et annulation par opération inverse, jamais par effacement silencieux.

#### Suivi et contrôle

- solde par élève et par famille ;
- créances, retards et relances ;
- rapprochement avec les références bancaires ou mobiles ;
- journal chronologique des opérations ;
- rapport des encaissements par caissier, canal et période ;
- balance des comptes de caisse ;
- dépenses, catégories, justificatifs et approbation ;
- budget prévisionnel et réalisé ;
- exports comptables et pièces justificatives.

**Limites de la première version financière**

La finance scolaire ne sera pas présentée comme une comptabilité générale certifiée. La première version couvre facturation, encaissements, créances, caisse, dépenses et exports. Une comptabilité en partie double complète ou une intégration à un logiciel comptable fera l’objet d’une validation dédiée.

**Invariants financiers**

- montants stockés en unités monétaires entières ou décimales exactes, jamais en flottants approximatifs ;
- solde d’une facture = total + majorations − remises − avoirs − paiements affectés ;
- aucun paiement confirmé n’est supprimé physiquement ;
- toute écriture comporte auteur, date, canal, devise et référence ;
- un rapprochement ou une clôture validée ne peut pas être modifié sans contre-opération.

### 5.9 Personnel et ressources humaines

- dossier employé, matricule, coordonnées et documents ;
- enseignants, personnel administratif et personnel de service ;
- contrats, fonctions, départements, dates et historique ;
- qualifications et matières enseignables ;
- affectations et charge horaire ;
- présences et absences du personnel ;
- demandes et approbation de congés ;
- évaluations et objectifs ;
- recrutement et candidatures en option ;
- remplacements ;
- variables de paie, primes, retenues et avances ;
- calcul et validation des bulletins de paie ;
- export bancaire et registre des paiements ;
- archivage des documents et contrôle d’accès RH.

**Précaution :** les cotisations, impôts et règles de paie doivent être paramétrés et validés par un spécialiste local avant usage réel.

### 5.10 Portails parents et élèves

- accès limité aux personnes rattachées ;
- profil et préférences de notification ;
- bulletins et résultats publiés uniquement ;
- échéancier, factures et reçus ;
- assiduité et justificatifs ;
- emploi du temps, devoirs et ressources ;
- messagerie ou demandes adressées à l’école ;
- consentements et autorisations si nécessaires ;
- téléchargement sécurisé des documents.

### 5.11 Communication et notifications

- annonces ciblées par établissement, classe ou groupe ;
- modèles de messages et traductions ;
- e-mail dans un premier canal, SMS/WhatsApp via fournisseurs validés ;
- files de traitement, tentatives et accusés d’échec ;
- préférences et désinscription lorsque applicable ;
- relances d’échéances et alertes d’absence ;
- historique des envois sans exposer les données à des destinataires non autorisés.

### 5.12 Bibliothèque et ressources

- catalogue des ouvrages et exemplaires ;
- adhérents, prêts, retours et réservations ;
- retards et pénalités éventuelles liées à la finance ;
- inventaire et état des ressources ;
- statistiques d’utilisation.

### 5.13 Inventaire et patrimoine

- articles, catégories, numéros de série et emplacements ;
- affectation à un site, une salle ou un employé ;
- entrées, sorties, transferts et inventaires ;
- maintenance et historique des incidents ;
- fournisseurs et pièces justificatives ;
- amortissement comptable laissé à un module ultérieur ou à l’outil comptable.

### 5.14 Transport scolaire

- véhicules, chauffeurs et documents d’assurance ;
- lignes, arrêts et horaires ;
- affectation des élèves ;
- tarifs et facturation liés au module finance ;
- suivi des présences dans le transport en option ;
- alertes aux familles selon les capacités du fournisseur.

### 5.15 Internat, cantine et autres services

- chambres, lits et affectations pour l’internat ;
- repas, menus, tarifs et consommations pour la cantine ;
- forfaits et paiements intégrés à la facture familiale ;
- incidents et autorisations de sortie ;
- infirmerie avec accès très restreint ;
- activités, clubs et événements.

Ces services sont planifiés après les modules indispensables et peuvent être activés séparément.

### 5.16 Reporting et pilotage

- effectifs et répartition par classe, niveau, sexe et statut ;
- réussite, échec, assiduité et progression ;
- encaissements, impayés, créances et prévisions ;
- masse salariale et charges du personnel ;
- occupation des salles et charge enseignante ;
- tableaux de bord filtrés par période et site ;
- exports CSV/XLSX/PDF ;
- journaux d’audit et rapports réglementaires validés ;
- historique des indicateurs plutôt que chiffres écrasés.

## 6. Parcours métier de référence

### 6.1 Admission à proclamation

1. la demande est enregistrée et vérifiée ;
2. l’élève et ses responsables sont créés ou rapprochés de dossiers existants ;
3. l’inscription est affectée à une année et une classe ;
4. les frais sont facturés selon le tarif applicable ;
5. les enseignants saisissent présences et évaluations ;
6. les notes sont validées puis le conseil délibère ;
7. la proclamation est gelée et publiée ;
8. les documents officiels deviennent visibles aux profils autorisés.

### 6.2 Facture à encaissement

1. une facture est générée avec un échéancier ;
2. le caissier sélectionne la facture ou scanne une référence ;
3. le paiement est validé dans une session de caisse ouverte ;
4. une écriture et un reçu sont créés dans une transaction ;
5. le solde et les relances sont recalculés ;
6. la session est clôturée et rapprochée ;
7. les écritures sont exportées ou intégrées à la comptabilité.

### 6.3 Embauche à paie

1. le dossier et le contrat sont validés ;
2. la fonction et les affectations sont enregistrées ;
3. présences, congés et variables sont collectés ;
4. la paie est calculée en brouillon ;
5. un responsable contrôle et valide ;
6. les bulletins sont publiés et le règlement est enregistré ;
7. les écritures sont archivées et exportées.

## 7. Modèle de données conceptuel

### 7.1 Entités principales

- `Organization`, `Site`, `AcademicYear`, `Period` ;
- `RuleSet`, `RuleVersion`, `RuleValue`, `RulePublication` ;
- `User`, `Role`, `Permission`, `UserScope`, `Session` ;
- `Student`, `Guardian`, `StudentGuardian`, `Enrollment`, `StudentDocument` ;
- `Program`, `Level`, `Subject`, `SchoolClass`, `ClassAssignment` ;
- `Employee`, `Contract`, `TeachingAssignment`, `TimetableEntry` ;
- `AttendanceSession`, `AttendanceEntry`, `BehaviorIncident` ;
- `Assessment`, `Grade`, `GradeValidation`, `ReportCard`, `Proclamation` ;
- `FeeType`, `Tariff`, `Invoice`, `InvoiceLine`, `Payment`, `PaymentAllocation` ;
- `Scholarship`, `CreditNote`, `Refund`, `CashSession`, `Expense` ;
- `LeaveRequest`, `PayrollRun`, `Payslip`, `PayrollVariable` ;
- `Notification`, `MessageTemplate`, `FileObject`, `AuditEvent`.

### 7.2 Exigences d’intégrité

- clés étrangères et contraintes d’unicité explicites ;
- dates de validité pour les affectations et tarifs ;
- archivage logique des données référencées ;
- montants et taux en décimales exactes ;
- version optimiste des documents sensibles pour éviter les écrasements concurrents ;
- version de règle obligatoire sur chaque calcul ou document officiel concerné ;
- aucune moyenne, aucun solde et aucune paie ne doit être accepté comme vérité envoyée par le navigateur sans recalcul serveur.

## 8. Architecture technique cible

### 8.1 Principe

Conserver un **monolithe modulaire** au départ : plus simple à déployer et à tester qu’une architecture de microservices, tout en gardant des frontières claires entre domaines.

```text
Web Next.js ───────────────┐
                           │
Desktop Tauri ─────────────┼── HTTPS ── API métier ── PostgreSQL
                           │                 │
Portails parents/élèves ───┘                 ├── Stockage objet S3 compatible
                                             ├── Worker / file de tâches
                                             └── Fournisseurs e-mail, SMS, paiement
```

### 8.2 Recommandation technologique

| Couche | Recommandation |
| --- | --- |
| Web | Next.js, React, TypeScript, Tailwind, en conservant les composants existants |
| API | NestJS en monolithe modulaire, REST documenté par OpenAPI |
| Base | PostgreSQL avec migrations Prisma |
| Validation | schémas partagés et validation systématique côté serveur |
| Fichiers | stockage S3 compatible, liens temporaires et antivirus selon le fournisseur |
| Tâches | worker et file Redis/BullMQ lorsque notifications ou imports lourds l’exigent |
| Desktop | Tauri connecté à l’API ; cache local chiffré si nécessaire |
| Tests | Vitest, tests d’intégration PostgreSQL et Playwright |
| Exploitation | Docker, staging, production, sauvegardes et supervision centralisée |

Le choix définitif est entériné par des ADR pendant M0. Aucune migration de base ne sera effectuée sans environnement de test et procédure de retour arrière.

### 8.3 Desktop et hors ligne

- **Étape 1 :** desktop en ligne, même API que le web, export et impression ;
- **Étape 2 :** cache de lecture et files d’actions clairement identifiées ;
- **Étape 3 :** SQLite local uniquement si le besoin hors ligne est confirmé ;
- synchronisation avec identifiants idempotents, horodatage, version et résolution des conflits ;
- interdiction de publier une proclamation ou de valider une paie hors ligne sans procédure spécifique ;
- mises à jour et exécutables signés avant diffusion générale.

## 9. Exigences non fonctionnelles

### 9.1 Sécurité

- TLS pour tous les échanges ;
- moindre privilège et deny-by-default côté API ;
- MFA pour administrateurs, direction, finance et RH ;
- sessions sécurisées, expiration et rotation des jetons ;
- hachage Argon2id si des mots de passe sont gérés directement ;
- protection CSRF/XSS/injection et validation de chaque entrée ;
- limitation de débit sur les endpoints sensibles ;
- secrets hors Git et rotation documentée ;
- chiffrement des sauvegardes et des fichiers sensibles selon l’hébergement ;
- audit des notes, publications, paiements, paie et permissions ;
- revue OWASP et tests d’autorisation horizontale entre élèves/familles.

### 9.2 Confidentialité

- minimisation des données collectées ;
- information des responsables et base légale validée localement ;
- accès restreint aux données médicales, disciplinaires et RH ;
- durées de conservation et procédure d’archivage/suppression documentées ;
- export ou suppression d’un compte selon les obligations applicables ;
- aucun envoi de données réelles à un fournisseur externe sans validation.

### 9.3 Performance et capacité

Cibles initiales à confirmer après estimation des volumes :

- 100 utilisateurs simultanés sans dégradation bloquante ;
- p95 < 2 s pour les lectures courantes et < 3 s pour les écritures hors tâches lourdes ;
- imports et rapports lourds exécutés en arrière-plan ;
- pagination et index sur les recherches principales ;
- tests de volume avec plusieurs années et au moins 10 000 dossiers élèves.

### 9.4 Accessibilité et expérience utilisateur

- parcours critiques compatibles clavier et lecteurs d’écran ;
- objectif WCAG 2.2 AA sur les formulaires, tableaux et documents ;
- libellés français clairs et formats locaux ;
- responsive web, tablettes et desktop ;
- aucune action critique déclenchée sans confirmation explicite ;
- états vides, erreurs et confirmations homogènes ;
- impressions A4 contrôlées sur les navigateurs cibles.

### 9.5 Exploitation

- environnements développement, staging et production séparés ;
- sauvegardes automatiques chiffrées et restauration testée ;
- logs structurés sans données sensibles ;
- métriques, alertes et suivi des erreurs ;
- migration réversible et plan de rollback ;
- documentation d’exploitation et astreinte à définir ;
- objectifs initiaux RPO ≤ 24 h, RTO ≤ 4 h, à adapter à la valeur métier.

## 10. Migration et intégrations

### 10.1 Migration des données

- inventaire des fichiers Excel/CSV et de l’ancien SQLite éventuel ;
- nettoyage et dictionnaire des données ;
- import en staging avec rapport ligne par ligne ;
- déduplication des élèves et responsables ;
- échantillonnage et validation par le secrétariat ;
- sauvegarde avant bascule et possibilité de retour arrière ;
- interdiction d’écraser une production sans validation écrite.

### 10.2 Intégrations possibles

- e-mail transactionnel ;
- SMS et WhatsApp via fournisseurs disponibles dans le pays ;
- mobile money et banques après contrat et documentation officielle ;
- logiciel comptable par export ou API ;
- stockage S3 compatible ;
- signature électronique ou QR de vérification si nécessaire.

Chaque intégration dispose d’un mode dégradé, d’un journal, d’une idempotence et d’une procédure de reprise.

## 11. Stratégie de tests et recette

### 11.1 Tests techniques

- unitaires pour les règles académiques, financières et RH ;
- intégration avec PostgreSQL pour transactions et contraintes ;
- contrats OpenAPI et compatibilité du client ;
- E2E Playwright des parcours critiques ;
- tests d’autorisation pour chaque rôle et chaque portée ;
- tests de concurrence sur paiements, notes et publications ;
- tests d’impression et exports ;
- tests de charge et de sécurité avant production.

### 11.2 Recette métier

Les scénarios suivants sont obligatoires :

1. inscrire puis réinscrire un élève sans perdre son historique ;
2. facturer avec remise et paiement partiel, puis produire un reçu ;
3. clôturer une caisse avec écart et contre-opération ;
4. saisir, corriger, valider et publier des notes ;
5. empêcher un enseignant d’accéder à une classe non assignée ;
6. empêcher un parent de consulter un enfant non rattaché ;
7. calculer une paie avec absence, prime et retenue validées ;
8. restaurer une sauvegarde sur un environnement vierge ;
9. vérifier le desktop, l’impression et la mise à jour ;
10. faire signer la recette par direction, scolarité, finance et RH.

## 12. Livrables attendus

- cahier des charges validé et glossaire métier ;
- maquettes et parcours utilisateurs ;
- ADR et architecture déployable ;
- schéma de base et migrations ;
- API OpenAPI et frontend par module ;
- jeux de données de test anonymisés ;
- tests automatisés et rapports de recette ;
- guides administrateur, enseignant, finance, RH et famille ;
- pipelines CI/CD et environnements ;
- politique de sauvegarde et restauration ;
- installateurs desktop et documentation de mise à jour ;
- procès-verbal de recette et plan de bascule.

## 13. Hors périmètre initial

Sauf décision contraire explicite :

- microservices et multi-tenancy SaaS commercial complet ;
- comptabilité générale certifiée ;
- paie réglementaire sans validation d’un spécialiste local ;
- applications mobiles natives séparées ;
- visioconférence et LMS complet concurrent d’outils spécialisés ;
- synchronisation hors ligne de toutes les décisions sensibles ;
- intelligence artificielle pour la notation automatique ;
- intégration de paiement sans contrat ni accès sandbox du fournisseur.

## 14. Risques et mesures de maîtrise

| Risque | Mesure |
| --- | --- |
| règles métier mal connues | ateliers et exemples signés avant développement |
| périmètre trop large | jalons et activation modulaire |
| erreurs financières | décimales exactes, transactions, invariants et audit |
| accès indu à des mineurs | RBAC serveur, tests horizontaux et journalisation |
| paie non conforme | paramétrage validé localement et recette RH |
| perte de données | sauvegardes, restauration testée et archivage |
| adoption faible | utilisateurs pilotes, formation et retours courts |
| complexité du hors ligne | desktop en ligne d’abord, offline progressif |
| dépendance fournisseur | adaptateurs, idempotence et mode manuel de secours |
| calendrier irréaliste | équipe dédiée et revue de capacité à chaque jalon |

## 15. Décisions à faire valider pendant M0

1. établissement pilote et volumes réels ;
2. langues, devises et sites ;
3. catalogue des périodes, barèmes, coefficients et règles de passage, avec les limites de leurs adaptations par établissement ;
4. modèles officiels de bulletins et proclamations ;
5. tarifs, bourses, remises et règles de remboursement ;
6. canaux de paiement et processus de caisse ;
7. règles de congés et paie ;
8. rôles précis et approbateurs ;
9. obligations de confidentialité et conservation ;
10. nécessité réelle du hors ligne et des services annexes ;
11. fournisseurs d’hébergement, e-mail, SMS et stockage ;
12. budget, équipe et date cible de mise en service.

Le [dossier de cadrage métier](cadrage/README.md) fournit les supports d’atelier, les exemples à recalculer et le registre dans lequel consigner ces décisions.

## 16. Critère global de réussite

LaVision sera considéré comme prêt pour une première production lorsque les parcours élèves, pédagogie, finance et personnel retenus auront été validés sur staging par les utilisateurs concernés, que les tests de sécurité et de restauration seront concluants, et que la direction disposera d’un plan de bascule, de formation et de support.

Les services annexes pourront ensuite être activés sans remettre en cause le socle ni les données déjà validées.
