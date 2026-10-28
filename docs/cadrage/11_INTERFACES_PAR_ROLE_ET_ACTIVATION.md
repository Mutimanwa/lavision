# Interfaces par rôle et activation progressive

**Version :** 0.1 — 28 octobre 2026
**Références :** issues #4, #7 et #33 ; catalogue des rôles dans [Permissions et confidentialité](04_PERMISSIONS_ET_CONFIDENTIALITE.md)
**Statut :** orientation d’activation guidée proposée par le produit ; interfaces et prérequis détaillés à valider avec les représentants des rôles

## 1. Objectif et principes

Chaque rôle doit disposer d’un espace de travail qui répond immédiatement à trois questions :

1. **Que puis-je faire maintenant ?**
2. **Quelles tâches demandent mon attention ?**
3. **Quelle est la prochaine étape si une fonctionnalité n’est pas encore disponible ?**

La solution proposée n’est pas de créer treize applications distinctes. lavision conserve un socle de design et des composants partagés, puis adapte le tableau de bord, la navigation, les recherches, les actions et les aides au rôle et à la portée de l’utilisateur.

Principes retenus pour cette proposition :

- **un même écran n’affiche pas les mêmes actions à tous les rôles** ;
- **le menu est une aide à la navigation, jamais une barrière de sécurité** : l’API refuse toute URL ou requête non autorisée ;
- **l’activation ne dépend pas de la visite guidée** : elle repose sur de vraies permissions, affectations et configurations ;
- **pas de menus cadenassés en grand nombre** : une option non pertinente pour le rôle est masquée ; une option réellement activable est présentée avec sa raison et sa prochaine étape ;
- **le guide est non bloquant**, reprenable et rechargeable depuis le centre d’aide ;
- **les documents historiques restent consultables** selon les permissions, même si un module est ensuite désactivé pour les nouvelles opérations.

Cette conception précise les modules déjà prévus. Elle n’ajoute pas de nouveau domaine fonctionnel à la v1.

## 2. Espaces de travail communs

Quatre familles d’interface suffisent à couvrir les treize rôles, avec des variations par permissions et affectations.

| Espace | Rôles concernés | Caractéristiques communes |
| --- | --- | --- |
| **Console technique** | administrateur technique | établissements et sites gérés, comptes, intégrations, sessions, santé du service ; aucun indicateur métier sensible par défaut |
| **Back-office établissement** | direction, responsable pédagogique, scolarité, vie scolaire, enseignant, comptable, caissier, RH, auditeur | shell commun, navigation métier filtrée, tâches et recherches limitées à la portée |
| **Espace employé** | employé, seul ou combiné avec une fonction interne | profil, documents personnels, congés et bulletins de paie publiés ; aucune donnée d’un collègue |
| **Portails famille et élève** | parent/responsable et élève | consultation simplifiée des seules personnes rattachées et des seuls contenus publiés |

### Éléments communs du shell

Selon le rôle, le shell peut comprendre :

- un **accueil** avec les tâches prioritaires plutôt qu’un empilement d’indicateurs ;
- un **sélecteur d’espace et de site** limité aux rôles et établissements réellement attribués ;
- une **recherche scoped** qui ne révèle aucun dossier hors portée ;
- un centre de **notifications** lié aux tâches de l’utilisateur ;
- une entrée **Guide et aide** permettant de reprendre ou relancer le parcours ;
- le profil, la langue si cette option est retenue, les sessions et la déconnexion.

Un utilisateur qui cumule deux rôles ne reçoit pas automatiquement une navigation surchargée. Il peut basculer entre les espaces qui lui sont attribués, par exemple **Direction** et **Enseignant**. Ce changement de présentation ne crée aucune nouvelle permission.

## 3. Interfaces nécessaires par rôle

Les chemins entre backticks existent dans le prototype actuel. Les autres intitulés décrivent les interfaces cibles des jalons correspondants ; ils ne sont pas présentés comme déjà développés.

### 3.1 Administration et pilotage

| Rôle | Accueil après connexion | Menus et interfaces nécessaires | Actions prioritaires | Exclusions explicites |
| --- | --- | --- | --- | --- |
| **Administrateur technique** | état des établissements gérés, comptes à traiter et alertes techniques | établissements/sites, utilisateurs et invitations, rôles et portées, intégrations, sessions, journal technique, activation des modules selon mandat | inviter/désactiver, affecter une portée, révoquer une session, activer un module autorisé | notes, bulletins, données médicales, finances et paie sans habilitation métier séparée |
| **Direction** | décisions en attente, indicateurs autorisés et échéances | `/`, validations, `/proclamation/`, rapports de pilotage, configuration à approuver dans `/parametres/`, audit selon permission | examiner, approuver ou rejeter, publier après contrôles, déléguer | modification directe d’une opération préparée par un autre lorsque la séparation est requise ; paie/RH détaillés sans permission |
| **Auditeur / lecture seule** | mandats et rapports disponibles | catalogue des rapports autorisés, journal d’audit, historique des versions, recherche dans la portée du mandat | consulter, filtrer, comparer des versions | toute création ou modification ; export sans permission dédiée |

### 3.2 Pédagogie, scolarité et vie scolaire

| Rôle | Accueil après connexion | Menus et interfaces nécessaires | Actions prioritaires | Exclusions explicites |
| --- | --- | --- | --- | --- |
| **Responsable pédagogique** | notes à valider, configurations à contrôler et périodes à venir | `/classes/`, programmes et affectations, `/notes/` en contrôle, `/bulletins/`, préparation de `/proclamation/`, règles académiques et simulations | valider les notes, contrôler une configuration, lancer une simulation, soumettre pour publication | publication officielle sans délégation ; finances familiales et paie |
| **Secrétariat / scolarité** | admissions et dossiers incomplets, inscriptions du jour | `/eleves/`, `/inscriptions/`, familles et rattachements, documents administratifs, affectations dans `/classes/` | créer ou compléter un dossier, inscrire, affecter, produire un document administratif autorisé | validation des notes, encaissement sans rôle de caisse, contenus médicaux détaillés |
| **Vie scolaire** | absences et retards à traiter, justificatifs et alertes | `/presences/`, corrections d’appel, justificatifs, incidents disciplinaires autorisés, suivi par classe | contrôler un appel, enregistrer un justificatif, traiter une alerte | notes et moyennes non nécessaires, données médicales ou finances sans mandat |
| **Enseignant** | cours du jour, appels à faire et notes à compléter | mes classes et matières, `/presences/`, `/notes/`, `/bulletins/` limités à mes affectations ; onglet **Ma classe principale** si affectation | faire l’appel, saisir un brouillon de notes, consulter les résultats autorisés | autres classes/matières, clôture sans permission, publication, finances familiales |

Le professeur principal conserve le rôle **Enseignant**. Son affectation ajoute l’interface de suivi de sa classe et les actions explicitement déléguées, sans créer un espace distinct.

### 3.3 Finance et caisse

| Rôle | Accueil après connexion | Menus et interfaces nécessaires | Actions prioritaires | Exclusions explicites |
| --- | --- | --- | --- | --- |
| **Comptable** | créances, échéances et opérations à contrôler | tarifs, factures et échéanciers, bourses/remises selon permission, créances, avoirs et remboursements, rapports financiers, exports autorisés | facturer, contrôler un solde, proposer une remise, produire un rapport | notes, dossiers RH et paie sans mandat ; encaissement de caisse sans affectation |
| **Caissier** | session de caisse actuelle et paiements de sa session | ouverture/clôture de caisse, recherche de facture, encaissement, reçus, historique de ses opérations | ouvrir sa session, encaisser, imprimer un reçu, clôturer avec contrôle | tarifs et règles financières, caisse d’un collègue ou d’un autre site, suppression d’un paiement |

### 3.4 Personnel et self-service

| Rôle | Accueil après connexion | Menus et interfaces nécessaires | Actions prioritaires | Exclusions explicites |
| --- | --- | --- | --- | --- |
| **Responsable RH** | dossiers à compléter, congés et périodes à préparer | employés, contrats et affectations, présences du personnel, congés et validations, préparation de paie si permission | créer un dossier, gérer un contrat, traiter un congé, préparer une opération autorisée | notes des élèves et finances familiales ; validation de sa propre paie ou opération lorsque interdite |
| **Employé** | mes prochaines échéances et mes documents | mon profil, mes contrats et documents publiés, mes congés, mes bulletins de paie | consulter, télécharger un document autorisé, faire une demande de congé | dossier ou salaire d’un autre employé, administration RH |

### 3.5 Parent et élève

| Rôle | Accueil après connexion | Menus et interfaces nécessaires | Actions prioritaires | Exclusions explicites |
| --- | --- | --- | --- | --- |
| **Parent / responsable** | enfants rattachés, informations publiées et échéances du foyer | sélecteur d’enfant limité aux rattachements, bulletins et résultats publiés, assiduité autorisée, factures et reçus du foyer, communications | consulter un bulletin, vérifier une échéance, télécharger un reçu, contacter l’école selon le canal prévu | autre enfant ou foyer, brouillons, incidents privés non destinés au responsable |
| **Élève** | mon emploi du temps et mes contenus publiés | mon profil, mes classes, emploi du temps, devoirs si le module est activé, résultats et documents publiés | consulter mes informations, accéder à une ressource ou un devoir autorisé | autre élève, notes en brouillon, données du responsable ou du personnel |

## 4. Correspondance avec le prototype actuel

| Route actuelle | Usage actuel | Espaces cibles principaux |
| --- | --- | --- |
| `/` | tableau de bord scolaire de démonstration | accueil décliné par rôle ; aucun chiffre sensible affiché uniquement parce qu’il existe dans l’état local |
| `/eleves/` | répertoire et dossier élève | scolarité ; consultation limitée pour les autres rôles autorisés |
| `/inscriptions/` | inscription guidée en quatre étapes | scolarité |
| `/classes/` | classes et matières | scolarité pour les affectations ; responsable pédagogique pour les programmes et coefficients |
| `/presences/` | appel quotidien | enseignant et vie scolaire selon la portée |
| `/notes/` | saisie des notes | enseignant pour le brouillon ; responsable pédagogique pour le contrôle |
| `/bulletins/` | calcul, consultation et impression | enseignant/pédagogie/direction en interne ; parent et élève uniquement après publication |
| `/proclamation/` | classement et publication | responsable pédagogique pour la préparation ; direction ou délégataire pour la publication |
| `/parametres/` | établissement, règles simples et sauvegarde locale | interfaces séparées selon le domaine ; aucun accès global implicite pour l’administrateur technique |

Ces routes restent actuellement celles d’un prototype local sans authentification. Leur présence ne constitue ni une matrice d’accès effective ni une promesse que toutes les interfaces cibles sont déjà livrées.

## 5. Activation progressive des menus

### 5.1 Deux états à ne pas confondre

| État | Propriétaire | Signification |
| --- | --- | --- |
| **État du module pour l’établissement** | établissement et administrateurs autorisés | module désactivé, en configuration ou actif pour les opérations |
| **Progression du guide pour un utilisateur** | utilisateur | étapes de découverte ou tâches personnelles commencées, terminées ou ignorées |

Terminer une visite guidée ne change jamais l’état d’un module. Inversement, activer un module ne marque pas le guide comme terminé.

### 5.2 Cycle de vie d’un module

```text
Désactivé ── activation autorisée ──► En configuration ── prérequis validés ──► Actif
    ▲                                      │                                  │
    └──────────── désactivation explicite, sans suppression de l’historique ◄─┘
```

- **Désactivé :** aucune nouvelle opération du module ; le module apparaît seulement dans le catalogue d’activation accessible aux rôles mandatés.
- **En configuration :** les écrans de préparation sont accessibles aux rôles concernés ; les opérations qui exigent encore un prérequis restent indisponibles avec une explication.
- **Actif :** les menus sont proposés aux utilisateurs autorisés et affectés ; l’API continue de vérifier chaque action.
- **Désactivation ultérieure :** action explicite et auditée ; les données et documents historiques ne sont pas supprimés.

### 5.3 Présentation dans la navigation

| Situation de l’utilisateur | Présentation proposée |
| --- | --- |
| module actif + permission + affectation | lien normal dans le menu |
| module en configuration + utilisateur responsable d’une étape | lien visible avec badge **À configurer**, ouvrant la checklist ou l’écran de préparation |
| module désactivé + utilisateur pouvant l’activer | entrée dans **Modules à activer**, avec bénéfice, prérequis et responsable ; pas d’encombrement du menu quotidien |
| module désactivé + utilisateur ne pouvant pas l’activer | module absent du menu ; une demande peut être faite depuis l’aide si ce processus est retenu |
| permission ou portée absente | menu absent et accès direct refusé par l’API, sans révéler de données |
| action précise encore indisponible dans un écran accessible | action désactivée avec raison courte et lien vers le prérequis, si l’utilisateur peut le traiter |

### 5.4 Exemples de prérequis fonctionnels

Ces prérequis sont des propositions de UX à confronter aux règles métier validées ; ils ne remplacent pas les contrôles de l’API.

| Interface | Conditions minimales avant les opérations concernées |
| --- | --- |
| Inscriptions | année ouverte, classe disponible, permission de scolarité et champs obligatoires configurés |
| Appel / présences | élèves affectés, enseignant ou vie scolaire assigné, date ouverte et statuts configurés |
| Saisie de notes | affectation classe/matière, élèves inscrits, période ouverte et barème applicable publié |
| Bulletins en brouillon | élèves, matières et règles applicables disponibles ; les dossiers incomplets restent explicitement signalés |
| Publication d’un bulletin | contrôles de complétude réussis, version des règles figée et permission de publication |
| Proclamation | notes validées selon le processus retenu, dossiers contrôlés et approbateur autorisé |
| Facturation | module finance activé, année et tarifs applicables publiés, permission finance |
| Encaissement | facture accessible, caisse affectée et session ouverte ; permission de caisse |
| Paie | référentiels du personnel et règles locales validés, période ouverte et permissions de préparation/validation séparées |
| Portail parent ou élève | compte actif, rattachement vérifié et contenu effectivement publié |

## 6. Guide de première utilisation

### 6.1 Deux progressions complémentaires

1. **Checklist de l’établissement**, partagée par les administrateurs concernés : référentiels, règles, affectations et activation des modules.
2. **Guide personnel**, propre à chaque utilisateur : découverte de son espace et premières actions dans sa portée.

Cette séparation évite qu’un enseignant voie « configurer les tarifs » ou que deux administrateurs refassent indépendamment la même tâche globale.

### 6.2 Parcours proposé à la première connexion

1. **Bienvenue et contexte** — nom de l’établissement, rôle(s), site(s) et finalité de l’espace ; aucun choix de rôle non attribué.
2. **Repères rapides** — trois à cinq points maximum : accueil, navigation, recherche autorisée, notifications et aide.
3. **Première tâche utile** — une action adaptée au rôle ou une checklist si l’établissement n’est pas prêt.
4. **Progression enregistrée** — possibilité de **Continuer plus tard**, avec reprise au bon endroit sur un autre appareil lorsque le backend sera disponible.
5. **Fin de guide** — accès à l’espace de travail et carte discrète **Premiers pas** tant que des tâches restent utiles.

Le guide ne doit pas transformer une configuration critique en simple animation. Si une règle officielle ou une affectation est manquante, l’écran indique précisément ce qui reste à faire et qui peut le faire.

### 6.3 Première tâche par rôle

| Rôle | Première tâche ou checklist proposée |
| --- | --- |
| Administrateur technique | vérifier l’établissement, inviter les responsables et contrôler leurs portées |
| Direction | examiner les validations attendues et les paramètres à approuver |
| Responsable pédagogique | vérifier classes, matières, affectations et configuration académique applicable |
| Secrétariat / scolarité | vérifier les classes disponibles et repérer le parcours dossier élève → inscription |
| Vie scolaire | vérifier les classes suivies et le traitement d’un appel ou justificatif |
| Enseignant | vérifier ses affectations, puis repérer l’appel et la saisie de notes |
| Comptable | vérifier les tarifs applicables et le parcours facture → solde |
| Caissier | vérifier sa caisse et le parcours session → paiement → reçu |
| Responsable RH | vérifier les dossiers du personnel et les circuits de validation |
| Employé | vérifier son profil et retrouver ses documents |
| Parent / responsable | vérifier les enfants rattachés et ouvrir un contenu publié |
| Élève | vérifier son profil et retrouver son emploi du temps ou ses résultats publiés |
| Auditeur | vérifier le périmètre du mandat et ouvrir un rapport autorisé |

Aucune donnée réelle ne doit être créée uniquement pour terminer le guide. Les environnements de démonstration utilisent des données fictives ; la production propose une action réelle seulement lorsque son contexte est prêt.

### 6.4 Comportement et accessibilité

- actions **Passer**, **Reprendre** et **Relancer depuis l’aide** ;
- navigation clavier, focus visible et restitution par lecteur d’écran ;
- pas de visite imposée à chaque connexion ni de réinitialisation systématique après une mise à jour ;
- sur mobile, étapes en panneau inférieur sans masquer l’action expliquée ;
- aide contextuelle dans les états vides plutôt qu’une longue visite générale ;
- guide versionné : une nouvelle étape peut être proposée sans effacer l’historique de l’utilisateur ;
- en web et desktop, même progression via le compte utilisateur lorsque l’API de production sera disponible.

## 7. Conséquences techniques pour l’implémentation

Lors du développement M1 et des jalons métier :

1. l’API fournit les espaces, permissions, portées et états de modules après authentification ;
2. `components/layout/app-shell.tsx` consomme une navigation calculée, au lieu de dupliquer des conditions de rôle dans chaque page ;
3. les features métier déclarent leurs prérequis de navigation sans pouvoir s’accorder elles-mêmes une permission ;
4. une future feature `onboarding` pourra contenir les pages, hooks et services du guide ; une feature `navigation` ne sera créée que si sa logique dépasse le shell partagé ;
5. les contrôles d’accès restent côté serveur, y compris pour les exports, recherches et accès directs par URL ;
6. les états de module et la progression sont audités ou historisés selon leur sensibilité.

Aucun faux sélecteur de rôle ne doit être ajouté au prototype local pour simuler une sécurité qui n’existe pas encore.

## 8. Scénarios de recette proposés

| ID | Scénario | Résultat attendu |
| --- | --- | --- |
| UX-01 | première connexion d’un enseignant | guide court limité à ses classes, appels et notes ; aucune étape finance ou administration |
| UX-02 | utilisateur avec rôles Direction et Enseignant | seuls ses deux espaces attribués sont proposés ; le changement d’espace ne crée aucun droit |
| UX-03 | guide passé puis rouvert depuis l’aide | application utilisable immédiatement et reprise au bon endroit |
| UX-04 | module actif mais prérequis incomplet | badge **À configurer**, raison et prochaine étape ; aucune activation fictive après la visite |
| UX-05 | utilisateur non autorisé saisit directement l’URL d’un module | refus serveur et absence de données sensibles dans la réponse |
| UX-06 | administrateur active un module | module en configuration jusqu’aux prérequis, puis disponible aux seuls rôles et périmètres concernés |
| UX-07 | module désactivé après utilisation | nouvelles opérations bloquées ; historique conservé et consultable selon les permissions |
| UX-08 | parent ou élève ouvre le portail | seules les personnes rattachées et les publications apparaissent |
| UX-09 | guide utilisé au clavier et sur mobile | toutes les étapes sont lisibles, fermables et actionnables sans piège de focus |
| UX-10 | utilisateur change d’appareil | progression personnelle retrouvée via le compte ; aucune duplication de la checklist globale de l’établissement |

## 9. Points à valider avec les représentants

| Sujet | Proposition de départ | Décision encore attendue |
| --- | --- | --- |
| Activation d’un module | administrateur technique ou direction selon un mandat par domaine | responsables exacts et nécessité d’une double approbation |
| Menus indispensables au pilote | noyau scolarité/pédagogie d’abord ; finance, RH et portails selon leur jalon | ordre de mise à disposition retenu |
| Guide obligatoire ou non | visite toujours ignorable ; prérequis métier toujours obligatoires pour l’action concernée | éventuelles étapes de sécurité réellement bloquantes |
| Cumul de rôles | sélecteur entre espaces attribués | espace affiché par défaut et règles de présentation |
| Checklist établissement | partagée entre responsables de chaque domaine | propriétaire de chaque tâche et notifications attendues |
| Contenu du guide | trois à cinq repères puis une première action | durée et exemples validés avec chaque rôle |
