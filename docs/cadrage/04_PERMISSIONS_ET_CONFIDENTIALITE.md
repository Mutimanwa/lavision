# Proposition de permissions et de confidentialité

**Statut :** proposition pour l’atelier A4
**Référence :** issue #4
**Validation attendue :** direction et propriétaires des domaines

## 1. Principes de conception proposés

1. **Refus par défaut :** toute action non accordée explicitement est refusée par l’API.
2. **Double contrôle :** vérifier à la fois la permission et la portée de la ressource.
3. **Isolation :** aucun accès implicite à un autre établissement, site, enfant, employé ou groupe.
4. **Moindre privilège :** ne donner que les opérations nécessaires à la fonction.
5. **Séparation des tâches :** préparer, approuver, publier et payer peuvent exiger des personnes distinctes selon le risque.
6. **Administration technique limitée :** gérer les comptes ou les intégrations ne donne pas automatiquement accès aux notes, dossiers médicaux, paiements ou salaires.
7. **Audit ciblé :** journaliser les opérations sensibles sans copier de secrets ou de contenus inutiles dans les logs.
8. **Refus côté serveur :** masquer un bouton ne constitue jamais une autorisation.

Ces principes sont des propositions de sécurité. La matrice nominative et les portées réelles doivent être validées avant l’implémentation M1.

## 2. Profils et portées à confirmer

| Profil | Portée candidate | Besoin principal | Restriction importante proposée |
| --- | --- | --- | --- |
| Administrateur technique | établissements explicitement gérés | comptes, intégrations, supervision | pas d’accès métier automatique aux contenus sensibles |
| Direction | établissement ou sites désignés | pilotage et validations | pas d’accès RH/paie détaillé sans permission explicite |
| Secrétariat/scolarité | sites et niveaux affectés | admissions, dossiers administratifs, inscriptions | pas de paie ni de contenu médical détaillé |
| Comptable | sites et domaines financiers affectés | tarifs, factures, créances et rapports | pas de notes ni de dossiers RH |
| Caissier | caisse et site affectés | encaissements et reçus de sa session | pas de modification des tarifs ni des règles |
| Responsable RH | employés des sites affectés | dossiers, contrats, congés et préparation RH | validation de paie séparée si requise |
| Enseignant | classes et matières assignées, périodes ouvertes | appels et notes | pas d’accès aux autres classes ou aux finances familiales |
| Professeur principal | enseignant + classe principale | suivi et préparation du conseil | pas de publication sans permission dédiée |
| Parent/responsable | enfants légalement rattachés | documents publiés et finances de son foyer | aucun autre élève ou responsable |
| Élève | son propre dossier | contenus publiés autorisés | aucun brouillon, incident privé ou donnée d’autrui |
| Auditeur/lecture seule | ressources et périodes autorisées | consultation et contrôle | aucune écriture et aucun contenu masqué dans son mandat |

**Décision requise :** préciser si ces profils sont des rôles fixes, des ensembles de permissions combinables, ou les deux. Les fonctions cumulées doivent être représentées sans contourner les séparations d’approbation.

## 3. Matrice d’actions proposée

La liste des rôles autorisés ci-dessous est un point de départ. Elle ne constitue pas une habilitation effective.

| Action | Rôles candidats | Portée et conditions | Refus à tester |
| --- | --- | --- | --- |
| Inviter/désactiver un utilisateur | administrateur technique | établissement géré ; révocation des sessions | désactivation hors établissement |
| Attribuer une permission sensible | administrateur + approbation définie | permission et portée explicites ; audit obligatoire | auto-élévation de privilèges |
| Consulter un dossier administratif élève | secrétariat, direction autorisée | site/niveau affecté | secrétariat d’un autre site |
| Modifier une inscription | secrétariat | année ouverte et classe autorisée | modification d’une année clôturée |
| Consulter une information médicale | rôle désigné uniquement | finalité et élève précis ; accès audité | enseignant ou liste générale |
| Saisir une note en brouillon | enseignant | classe, matière, évaluation et fenêtre assignées | changement d’identifiant vers une autre classe |
| Valider/clôturer les notes | rôle pédagogique désigné | période et portée autorisées | enseignant sans permission de clôture |
| Rouvrir des notes validées | rôle autorisé distinct si retenu | motif, durée, audit | réouverture après proclamation sans procédure |
| Publier un bulletin/proclamation | direction ou délégataire | version des règles et dossiers contrôlés | enseignant ou parent |
| Consulter un résultat | enseignant autorisé, parent/élève rattaché | brouillon pour rôle interne ; publié seulement pour famille | enfant non rattaché |
| Corriger un appel | enseignant/vie scolaire selon délai | séance assignée ; ancienne valeur conservée | correction hors fenêtre sans approbation |
| Consulter un incident disciplinaire | rôles désignés | élève et portée autorisés | autre classe ou parent non concerné |
| Gérer tarifs et factures | comptable | site, année et état de la facture | caissier ou enseignant |
| Approuver une remise au-delà du seuil | approbateur financier désigné | montant et domaine | auto-approbation par le préparateur |
| Encaisser un paiement | caissier | session ouverte et caisse affectée | caisse d’un autre site ou session clôturée |
| Contre-passer un paiement | rôle financier autorisé | motif, référence et approbation éventuelle | suppression du paiement initial |
| Clôturer une caisse | caissier + contrôle défini | caisse affectée et écart traité | clôture d’une caisse d’un collègue |
| Consulter la finance d’un foyer | comptable, parent/payeur rattaché | factures du payeur ou enfants rattachés | factures d’un autre foyer |
| Consulter un dossier employé | RH, direction explicitement autorisée | site et champs autorisés | enseignant ou comptable scolaire |
| Approuver un congé | approbateur hiérarchique/RH | type, durée et employé concernés | auto-approbation |
| Préparer une paie | rôle RH désigné | période et employés autorisés | préparateur hors portée |
| Valider une paie | validateur désigné | séparation du préparateur si retenue | auto-validation interdite lorsque requise |
| Consulter un bulletin de paie | RH autorisé et employé concerné | bulletin et champs précis | autre employé ou parent d’élève |
| Modifier les règles académiques | rôle pédagogique désigné | brouillon et établissement/portée autorisés | comptable ou enseignant non délégué |
| Publier les règles académiques | direction/délégataire | simulation réussie et date d’effet valide | modification d’une version publiée |
| Modifier/publier les règles financières | rôles finance distincts | domaine finance uniquement | rôle académique ou RH |
| Modifier/publier les règles RH/paie | rôles RH/direction désignés | domaine RH uniquement | comptable scolaire sans mandat |
| Consulter le journal d’audit | auditeur ou rôle autorisé | types d’événements et périmètre du mandat | accès à des données hors mandat |
| Exporter des données | permission séparée par domaine | même portée que la consultation + justification éventuelle | export massif sans permission dédiée |

## 4. Permissions du moteur de règles

Proposition de séparation à valider :

| Permission | Objet |
| --- | --- |
| `rules.academic.read` | consulter les règles académiques accessibles |
| `rules.academic.draft` | créer ou modifier un brouillon académique |
| `rules.academic.simulate` | exécuter une simulation sans publier |
| `rules.academic.submit` | soumettre un brouillon contrôlé et simulé pour validation |
| `rules.academic.review` | vérifier la portée, les valeurs, la source et le rapport de simulation |
| `rules.academic.publish` | publier une version après contrôles |
| `rules.finance.*` | mêmes opérations pour le domaine financier |
| `rules.hr.*` | mêmes opérations pour congés et personnel |
| `rules.payroll.*` | permissions séparées pour la paie en raison de sa sensibilité |
| `rules.archive` | archiver sans supprimer les versions référencées |
| `rules.audit.read` | consulter l’historique autorisé des changements |

Aucune permission de brouillon ne permet de modifier une version publiée. La simulation ne doit pas créer d’effet métier ni diffuser de document.

## 5. Classification des données à valider

| Catégorie | Exemples | Sensibilité proposée | Mesures minimales candidates |
| --- | --- | --- | --- |
| Identité et contact | noms, naissance, adresse, téléphone | personnelle | portée stricte, export contrôlé, chiffrement en transit et au repos |
| Scolarité | inscriptions, notes, résultats | personnelle sensible pour les mineurs | publication contrôlée, historique des modifications |
| Médical | allergies, traitements, justificatifs | très sensible | accès nominatif restreint, pas de liste générale, audit de consultation |
| Discipline | incidents, sanctions | très sensible | visibilité limitée et rétention spécifique |
| Finance famille | factures, paiements, créances | sensible | accès payeur/finance seulement, exports protégés |
| RH et contrats | identité employé, contrat, absences | sensible | domaine RH isolé |
| Paie | salaire, retenues, coordonnées bancaires | très sensible | permissions dédiées, chiffrement et journalisation renforcée |
| Authentification | secrets, jetons, second facteur | secret de sécurité | hachage/chiffrement approprié, jamais dans les logs |
| Audit | acteur, action, ressource, date | sensible | append-only, accès restreint, contenu minimisé |

## 6. Conservation, droits et destinataires

Aucune durée n’est inscrite par défaut dans ce dossier. La direction doit faire valider la politique applicable et distinguer obligation de conservation, besoin opérationnel et suppression.

| Sujet | Décision attendue |
| --- | --- |
| Durées | durée par catégorie, point de départ et règle d’archivage |
| Fin de scolarité | sortie d’un élève, retour éventuel et dossiers officiels |
| Fin de contrat | conservation du dossier employé et de la paie |
| Documents sources | justificatifs d’inscription, paiement, absence et paie |
| Journal d’audit | rétention, accès et protection contre l’altération |
| Sauvegardes | inclusion, expiration et suppression cohérente avec la politique |
| Demandes d’accès | responsable, vérification d’identité, délai et procédure |
| Rectification | correction de la donnée sans effacer les documents officiels historiques |
| Destinataires externes | banque, prestataire SMS/e-mail, autorités ou comptable : liste et finalité |
| Incident | personnes à alerter, procédure de confinement et preuve à conserver |

## 7. Scénarios de test d’autorisation

Chaque scénario deviendra un test automatisé négatif ou positif dans M1.

| ID | Scénario | Résultat attendu |
| --- | --- | --- |
| SEC-01 | un enseignant modifie l’identifiant de classe dans une requête de notes | refus si la classe n’est pas assignée |
| SEC-02 | un parent demande le bulletin d’un autre enfant | refus sans indiquer l’existence du dossier |
| SEC-03 | un caissier modifie un tarif | refus, même avec une session de caisse ouverte |
| SEC-04 | un comptable consulte un salaire | refus sans permission paie explicite |
| SEC-05 | un administrateur technique consulte une information médicale | refus sans habilitation métier séparée |
| SEC-06 | un utilisateur désactivé utilise une ancienne session | refus et révocation |
| SEC-07 | un rôle de brouillon tente de modifier une règle publiée | refus ; création d’une nouvelle version seulement |
| SEC-08 | un rôle académique publie une règle financière | refus |
| SEC-09 | un préparateur approuve sa propre opération alors que la séparation est requise | refus et événement de sécurité |
| SEC-10 | un auditeur tente une exportation ou une modification | refus sans permission dédiée |
| SEC-11 | un utilisateur change d’établissement dans l’URL ou le corps JSON | refus par portée serveur |
| SEC-12 | une année clôturée est modifiée sans réouverture autorisée | refus et conservation de l’historique |
| SEC-13 | un document publié est recalculé après une nouvelle version des règles | aucune modification du document historique |
| SEC-14 | un export contient des champs médicaux ou RH non autorisés | champs exclus ou export refusé |
| SEC-15 | un log d’erreur est inspecté après une authentification échouée | aucun mot de passe, jeton ou second facteur |

## 8. Validation de la matrice

| Domaine | Représentant | Matrice relue le | Décisions associées | Statut |
| --- | --- | --- | --- | --- |
| Administration/comptes | à nommer | | | en attente |
| Scolarité/pédagogie | à nommer | | | en attente |
| Assiduité/discipline/médical | à nommer | | | en attente |
| Finance/caisse | à nommer | | | en attente |
| RH/paie | à nommer | | | en attente |
| Parent/élève | à nommer | | | en attente |
| Audit/conservation | à nommer | | | en attente |
