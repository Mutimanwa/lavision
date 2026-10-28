# Configuration métier avancée

**Version :** 0.1 — 28 octobre 2026
**Écran cible :** Paramètres → Configuration scolaire
**Références :** issues #2 et #39, décision DEC-007
**Statut :** orientation produit validée ; spécification détaillée à consolider pendant M0/M1

## 1. Décision et objectif

LaVision doit permettre une **personnalisation poussée de toutes les règles métier variables du périmètre prévu**, car elles peuvent différer entre établissements et changer au fil du temps.

L’utilisateur ne doit pas deviner une règle pendant le cadrage pour permettre au développement d’avancer. Une information encore inconnue devient un paramètre documenté, avec un propriétaire et un état explicite. Elle ne devient une valeur officielle qu’après configuration et validation par une personne autorisée.

Cette exigence approfondit le moteur de règles déjà prévu. Elle n’ajoute pas de nouveau module métier à la v1.

### Conséquences directes

- aucune règle variable critique ne doit rester cachée dans le code ;
- les valeurs du prototype sont des propositions, pas des vérités nationales ou locales ;
- une règle inconnue est marquée **à configurer**, sans valeur de repli silencieuse ;
- une évolution crée une nouvelle version datée, sans réécrire l’historique ;
- les calculs officiels utilisent uniquement une configuration publiée, complète et applicable à leur contexte.

## 2. Ce qui est configurable et ce qui ne l’est pas

| Catégorie | Traitement |
| --- | --- |
| Barèmes, composantes, agrégations, arrondis, rangs, décisions | configurable par les portées autorisées |
| Tarifs, remises, paiements, caisse, numérotation | configurable avec permissions financières |
| Congés, temps de travail, variables et validations RH/paie | configurable avec permissions dédiées et validation compétente |
| Assiduité, alertes, documents et publication | configurable selon le catalogue du domaine |
| Valeurs techniques de sécurité ou d’infrastructure | configuration technique séparée, pas mêlée aux règles scolaires |
| Immuabilité des versions publiées | invariant non désactivable |
| Validation serveur, contrôle d’accès et audit | invariant non désactivable |
| Conservation du snapshot sur un document officiel | invariant non désactivable |
| Interdiction des scripts et formules arbitraires | invariant non désactivable |

Si une nouvelle variable métier est découverte pendant le développement, elle doit être ajoutée au catalogue avec son type, ses bornes et ses portées. La laisser en dur dans le code constitue un défaut de conformité au cahier des charges.

## 3. Concepts à distinguer

| Concept | Rôle |
| --- | --- |
| Définition de règle | clé stable, domaine, description, type, contraintes, portées et caractère obligatoire |
| Valeur de règle | contenu typé choisi par l’établissement pour une définition |
| Portée | établissement, site, année, niveau, programme, classe ou branche autorisée pour cette règle |
| Ensemble de règles | groupe cohérent de paramètres qui doivent être validés ensemble |
| Version de configuration | brouillon ou version publiée d’un ensemble, avec auteur, source et dates d’effet |
| Configuration résolue | valeurs effectivement applicables après héritage, à une date et dans une portée données |
| Modèle de départ | configuration duplicable, sourcée et proposée, jamais appliquée silencieusement |
| Snapshot documentaire | copie immutable de la configuration résolue utilisée par un calcul ou document officiel |

Une définition appartient au catalogue produit. Une version de configuration appartient à l’établissement. Cette séparation permet de faire évoluer le logiciel sans modifier les valeurs déjà publiées par une école.

## 4. Parcours dans Paramètres → Configuration scolaire

### 4.1 Recherche et contexte

L’utilisateur choisit :

1. le domaine : académique, assiduité, finance, RH/paie, documents ou autre domaine déjà prévu ;
2. l’établissement et, si nécessaire, le site ;
3. l’année ou la date à laquelle la règle doit s’appliquer ;
4. le niveau, la section/option, le programme, la classe ou la branche lorsque la définition l’autorise.

Une recherche par libellé et par clé permet de retrouver une règle. Des filtres affichent les règles obligatoires, non configurées, adaptées, héritées, en conflit ou prochainement modifiées.

### 4.2 Lecture d’une règle

Chaque fiche présente :

- son nom, sa description et son domaine ;
- sa valeur actuelle et son unité ;
- son état : héritée, adaptée, non configurée, non applicable ou en conflit ;
- la source exacte de la valeur héritée ;
- les portées autorisées et les programmes concernés ;
- les règles dépendantes et les contraintes à respecter ;
- la version publiée, la date d’effet et les versions futures ;
- l’historique des auteurs, validateurs, motifs et justificatifs.

L’interface doit répondre à la question : **« pourquoi cette valeur s’applique-t-elle ici et à cette date ? »**, sans exiger la lecture du code.

### 4.3 Modification

1. créer un brouillon à partir de zéro, d’un modèle sourcé ou d’une version existante ;
2. modifier les valeurs dans des champs adaptés à leur type ;
3. renseigner le motif, la source, la portée et la date d’effet ;
4. laisser l’API contrôler les valeurs et les dépendances ;
5. simuler le résultat et examiner les différences ;
6. faire vérifier puis publier selon les permissions du domaine.

Une version publiée ne possède pas de bouton de modification. « Revenir à une ancienne règle » crée une nouvelle version datée qui reprend les valeurs choisies.

## 5. Héritage et résolution des conflits

L’héritage évite de recopier les mêmes valeurs tout en permettant des adaptations locales.

### Exigences

- chaque définition limite les portées qu’elle accepte ;
- la résolution suit un ordre déterministe, documenté et testé ;
- la valeur la plus précise applicable est choisie selon l’ordre validé ;
- l’origine de chaque valeur et la chaîne d’héritage sont visibles ;
- deux adaptations incompatibles de même priorité produisent un **conflit explicite**, jamais un choix aléatoire ;
- les chevauchements de dates et dépendances incohérentes sont détectés avant publication ;
- la simulation utilise exactement le même résolveur que les calculs officiels.

L’ordre détaillé entre site, année, niveau, section/option, programme, classe et branche reste à consolider dans l’ADR de l’issue #3. Il ne doit pas être dispersé dans les écrans ou les modules.

## 6. Changements dans le temps

Chaque version de configuration possède :

- une date ou un instant de début d’effet ;
- une fin d’effet facultative si elle est connue ;
- le fuseau de l’établissement utilisé pour interpréter ces limites ;
- un état : brouillon, en validation, publié, planifié ou archivé ;
- une source et un motif de changement.

### Comportements attendus

- préparer une future année sans modifier l’année en cours ;
- interdire deux versions incompatibles qui couvrent la même portée et la même période ;
- dupliquer une année précédente en **brouillon**, puis revoir les règles impactées ;
- recalculer un cas historique avec la version applicable à cette date ou avec son snapshot ;
- conserver une version archivée tant qu’elle est référencée ;
- ne jamais recalculer silencieusement un document publié avec une règle plus récente.

Une rectification d’un document suit sa propre procédure : nouvelle version du document, motif, approbation et lien vers la version remplacée.

## 7. Règles inconnues, obligatoires et valeurs par défaut

### États distingués

| État | Signification |
| --- | --- |
| Non configurée | aucune valeur applicable n’a été validée |
| Brouillon incomplet | des valeurs sont saisies mais des prérequis manquent |
| Non applicable | choix explicite et motivé lorsque la définition le permet |
| Configurée | une valeur publiée couvre le contexte |
| En conflit | plusieurs valeurs candidates ne peuvent pas être départagées |

Un champ vide, zéro, « non applicable » et « je ne sais pas » ne sont jamais synonymes.

### Valeurs par défaut et modèles

- un modèle national ou produit indique toujours sa source, sa date et ses limites ;
- une valeur issue du prototype est identifiée comme **proposée** ;
- copier un modèle crée un brouillon et ne vaut pas validation ;
- aucune valeur par défaut cachée ne peut combler une règle obligatoire lors d’un calcul officiel ;
- une règle facultative doit avoir un comportement explicite lorsqu’elle est désactivée.

Le brouillon et la simulation peuvent montrer les informations manquantes. La production du résultat officiel concerné est refusée tant qu’une règle obligatoire applicable reste absente ou incohérente.

## 8. Personnalisation sans code arbitraire

La configuration avancée utilise un **catalogue fermé de constructions sûres** :

- booléens, entiers bornés et décimales exactes ;
- dates, durées, devises et énumérations ;
- listes structurées et objets à schéma fixe ;
- tables de décision bornées avec opérateurs autorisés ;
- stratégies de calcul prédéfinies, sélectionnées par identifiant ;
- paramètres numériques de ces stratégies, validés par l’API.

Par exemple, « périodes + examen » et « périodes sans examen » sont deux stratégies explicites. L’utilisateur choisit la stratégie et ses maxima ; il ne saisit pas une expression libre à exécuter.

Une nouvelle stratégie de calcul peut nécessiter une évolution développée et testée du catalogue. Une fois publiée, ses paramètres sont configurables par les établissements autorisés. Aucun JavaScript, SQL, script, formule textuelle ou appel réseau fourni par un utilisateur n’est exécuté.

## 9. Validation et simulation avant publication

### Contrôles automatiques

- type, bornes, précision et unité ;
- champs obligatoires et valeurs non ambiguës ;
- dépendances entre règles et compatibilité des stratégies ;
- périodes cohérentes et absence de chevauchement interdit ;
- dénominateurs et domaines de calcul valides ;
- portée et permissions de l’utilisateur ;
- présence des exemples de simulation exigés pour le domaine.

### Simulation sans effet métier

La simulation doit :

- fonctionner sur des exemples fictifs ou anonymisés ;
- comparer la version actuellement applicable et le brouillon ;
- montrer les valeurs héritées et la configuration résolue ;
- afficher les résultats, avertissements, règles manquantes et conflits ;
- identifier les programmes, classes, branches et types de documents impactés ;
- inclure les cas normaux, frontières et dossiers incomplets définis pour le domaine ;
- ne créer ni note officielle, ni facture, ni paiement, ni paie, ni notification.

Le rapport de simulation est conservé avec la demande de publication. Les mêmes entrées et la même version doivent toujours produire le même résultat.

## 10. Couverture des domaines existants

| Domaine | Exemples de règles variables à couvrir |
| --- | --- |
| Référentiels et calendrier | années, périodes, fenêtres, capacités, jours non ouvrés |
| Académique | barèmes, schémas avec/sans examen, coefficients éventuels, agrégation, notes manquantes, arrondis, rangs, repêchage, décisions |
| Assiduité et vie scolaire | modes d’appel, statuts, retards, justificatifs, alertes, visibilité des incidents |
| Finance | devises, tarifs, remises, échéanciers, affectations, avoirs, caisse, numérotation, approbations |
| Personnel et paie | contrats, horaires, congés, variables, périodes, validations, cotisations et impôts validés avec leurs dates d’effet |
| Documents | champs, modèles, signatures, diffusion, rectification et conservation |
| Communication et services déjà prévus | modèles, canaux, alertes, tarifs et règles propres au service concerné |

Chaque module doit déclarer ses dépendances au catalogue avant sa recette. Un module ne peut pas contourner le moteur parce qu’une valeur est plus simple à coder en dur.

## 11. Permissions, approbation et audit

Les permissions de lecture, brouillon, simulation, soumission, validation, publication et archivage sont séparées par domaine et par portée.

- un enseignant non délégué ne modifie pas les règles académiques ;
- un rôle académique ne publie pas une règle financière ou de paie ;
- les opérations sensibles appliquent la séparation préparateur/validateur requise ;
- l’administrateur technique ne reçoit pas implicitement les droits métier ;
- la simulation ne donne aucun droit de publication ;
- une permission de brouillon ne permet jamais de modifier une version publiée.

L’audit conserve l’auteur, le validateur, la date, le motif, la source, la portée, l’ancienne et la nouvelle version, ainsi que le rapport de simulation associé. Il ne contient pas de secret ni de données personnelles inutiles.

## 12. Modèle conceptuel pour l’issue #39

| Objet conceptuel | Contenu essentiel |
| --- | --- |
| `RuleDefinition` | clé, version du catalogue, domaine, type, schéma, contraintes, portées, obligation |
| `RuleSet` | ensemble cohérent, domaine et finalité |
| `RuleSetVersion` | valeurs, statut, dates d’effet, source, motif, auteur et validations |
| `RuleBinding` | portée précise à laquelle la version s’applique |
| `RuleResolution` | résultat déterministe, origine des valeurs, avertissements et conflits |
| `RuleSimulation` | entrées anonymisées, version candidate, résultats et comparaison |
| `RulePublication` | approbations, date de publication et rapport de simulation |
| `DocumentRuleSnapshot` | configuration résolue immutable, version et empreinte associées au document |

Les noms physiques des tables restent à définir dans l’ADR et le schéma Prisma. Les calculs sont effectués côté serveur ; le navigateur ne peut pas imposer une configuration résolue ou un résultat déjà calculé.

## 13. Critères d’acceptation

1. Deux programmes peuvent appliquer des maxima différents pendant la même année sans modifier le code.
2. Une branche sans examen n’exige ni n’accepte de note d’examen.
3. Deux années peuvent utiliser des modes d’arrondi différents, chacun dans sa période d’effet.
4. Une règle obligatoire inconnue bloque le résultat officiel concerné et explique précisément ce qui manque.
5. Aucune valeur du prototype ne devient une règle publiée sans action autorisée.
6. L’utilisateur voit l’origine de chaque valeur héritée et les conflits éventuels.
7. Une valeur hors borne ou une formule libre est refusée par l’API, même si l’interface est contournée.
8. La simulation ne modifie aucune donnée officielle et utilise le même résolveur que le calcul réel.
9. Une version publiée ne peut pas être modifiée ou supprimée.
10. Une version future ne change ni les calculs actuels ni les documents historiques.
11. La duplication d’une année crée un brouillon soumis aux mêmes contrôles.
12. Un bulletin, une facture ou une paie conserve la configuration exacte ayant produit ses résultats.
13. Un retour à une ancienne configuration crée une nouvelle version datée et auditée.
14. Une personne sans permission du domaine ne peut ni modifier ni publier la règle, quelle que soit la requête envoyée.
15. Toute règle variable découverte dans un module est référencée dans le catalogue avant la recette du module.

## 14. Découpage de réalisation proposé

L’issue #39 peut être découpée en incréments vérifiables, sans créer de nouveau jalon :

1. catalogue, définitions typées et validation API ;
2. portées, dates d’effet et résolution déterministe ;
3. brouillons, différences, conflits et duplication ;
4. simulation et exemples de recette ;
5. permissions, approbations, publication et audit ;
6. snapshots et intégration aux documents ;
7. écrans de configuration, recherche et explication de l’héritage.

Les modules M2, M3 et M4 branchent ensuite leurs calculs sur ces versions publiées. Leur recette doit vérifier qu’ils ne possèdent pas de deuxième moteur ou de valeur de repli concurrente.

## 15. Points encore à consolider

Ces points techniques et organisationnels ne bloquent pas la définition du catalogue :

- ordre détaillé des portées pour chaque famille de règles ;
- fuseau et traitement exact des limites de dates ;
- politique d’annulation d’une publication planifiée ;
- nombre et diversité des approbateurs par domaine ;
- jeu minimal de simulations par processus ;
- politique d’archivage et de duplication entre établissements ;
- format de preuve des justificatifs attachés à une version.

Ils sont suivis dans le registre des décisions et les ADR. Les valeurs académiques encore inconnues restent quant à elles des paramètres à renseigner par les responsables de l’établissement, sans être inventées pendant le développement.
