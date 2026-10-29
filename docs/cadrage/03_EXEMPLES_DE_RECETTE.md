# Exemples de recette métier

**Statut :** cas de discussion et futures fixtures de test
**Référence :** issue #2

Ces exemples utilisent uniquement des personnes et des montants fictifs. Les résultats marqués **Prototype** décrivent le comportement actuel. Les résultats **Hypothèse** servent à comparer des choix. Aucun des deux ne vaut validation officielle sans accord du propriétaire métier.

## 1. Bulletin pondéré et dossier incomplet

### Configuration de travail A

| Paramètre | Valeur de travail | Statut |
| --- | --- | --- |
| Barème | 0 à 20 | à valider |
| Moyenne | somme des `notes × coefficients` / somme des coefficients des matières notées | comportement du prototype |
| Affichage | deux décimales | à valider |
| Seuil de réussite | 10/20 | valeur actuelle, à valider |
| Dossier incomplet | pas de rang officiel ni de décision Admis/Ajourné | comportement du prototype ; rang provisoire séparé proposé le 29/10/2026 |

### Entrées communes

| Matière | Coefficient | Élève A | Élève B | Élève C | Élève D |
| --- | ---: | ---: | ---: | ---: | ---: |
| Mathématiques | 4 | 12 | 12 | 10 | 12 |
| Français | 3 | 14 | 14 | 10 | 14 |
| Sciences | 2 | 8 | 8 | 10 | 8 |
| Histoire | 1 | 16 | 16 | 10 | absente |

### Résultat à recalculer

Pour A et B :

```text
Total pondéré = 12×4 + 14×3 + 8×2 + 16×1 = 122
Coefficients  = 4 + 3 + 2 + 1 = 10
Moyenne       = 122 / 10 = 12,20
```

Pour C : `100 / 10 = 10,00`.

Pour D, si la moyenne provisoire est affichée avec le dénominateur des seules matières notées :

```text
Total pondéré = 106
Coefficients notés = 9
Moyenne provisoire = 106 / 9 = 11,777… → 11,78
```

| Élève | Complétude | Moyenne | Décision selon la configuration A |
| --- | --- | ---: | --- |
| A | complet | 12,20 | Admis |
| B | complet | 12,20 | Admis |
| C | complet | 10,00 | Admis, seuil atteint exactement |
| D | incomplet | 11,78 provisoire | Incomplet ; document provisoire/incomplet proposé |

### Décisions à obtenir

- La moyenne provisoire de D doit-elle être affichée, masquée ou calculée autrement ?
- Une absence de note peut-elle être remplacée par zéro, une dispense ou une récupération ? Dans quels cas ?
- Le seuil est-il franchi lorsque la moyenne est exactement égale ?
- Une classe entière est-elle bloquée par D, ou seulement sa publication individuelle ?

**Réponses du 29/10/2026 :** la moyenne provisoire est affichée sans transformer les notes manquantes en zéro. La diffusion d’un bulletin provisoire ou d’un relevé incomplet est proposée, sans décision annuelle définitive. Le rang provisoire est séparé du classement officiel ; les rattrapages et le modèle exact du document restent à confirmer.

**Résultat attendu validé par :** à compléter
**Date et éventuelles corrections :** à compléter

## 2. Ex æquo et rangs

Avec les résultats précédents, A et B ont la même moyenne et C a la moyenne immédiatement inférieure.

| Politique candidate | Rang A | Rang B | Rang C | Effet |
| --- | ---: | ---: | ---: | --- |
| Rang de compétition | 1 | 1 | 3 | comportement actuel du prototype |
| Rang dense | 1 | 1 | 2 | alternative à discuter |
| Rang ordinal déterministe | 1 | 2 | 3 | nécessite un critère de départage explicite |

**Question d’atelier :** quelle politique figure sur les documents officiels ? Si un départage est requis, quel critère est légitime et comment éviter un choix arbitraire ?

**Réponse du 29/10/2026 :** rang de compétition `1, 1, 3` et comparaison avant arrondi sélectionnés ; validation par la direction et exemple annuel restant à compléter.

## 3. Agrégation annuelle

Supposons trois moyennes périodiques déjà calculées sans nouvel arrondi intermédiaire : `12`, `14` et `16`.

| Politique candidate | Calcul | Moyenne annuelle |
| --- | --- | ---: |
| Périodes équivalentes | `(12 + 14 + 16) / 3` | `14,00` |
| Poids `1, 1, 2` | `(12×1 + 14×1 + 16×2) / 4` | `14,50` |
| Autre règle de l’établissement | à documenter | à calculer |

### Points à verrouiller

- poids de chaque période ;
- présence éventuelle d’un examen annuel distinct ;
- arrondi des moyennes périodiques avant agrégation ou seulement à la fin ;
- traitement d’une période incomplète ou d’une arrivée en cours d’année ;
- liens éventuels avec compensation, assiduité ou conseil de classe.

**Réponse du 29/10/2026 :** le résultat annuel combine les périodes et un examen annuel distinct. La structure du calcul est séparée de ses paramètres de pondération : ceux-ci seront versionnés et configurés après validation par l’autorité scolaire compétente en RDC. Aucune valeur par défaut n’est retenue ; les politiques ci-dessus ne sont pas des formules annuelles complètes.

## 4. Arrondis et frontières

Cas théorique : une valeur exacte de `12,345` doit être affichée avec deux décimales.

| Mode candidat | Résultat | Remarque |
| --- | ---: | --- |
| Arrondi arithmétique vers le haut à mi-chemin | 12,35 | proposition courante à confirmer |
| Arrondi au pair à mi-chemin | 12,34 | alternative donnant un résultat différent |
| Troncature | 12,34 | alternative à ne pas confondre avec l’arrondi |

Il faut également tester `scale_max`, `scale_min`, un dénominateur nul, une note juste sous le seuil, une note exactement au seuil et une note juste au-dessus. La précision de calcul et la précision d’affichage doivent être distinguées.

**Mode, étape et précision validés :** à compléter

## 5. Deux configurations d’établissement

Ce cas vérifie l’exigence de configurabilité sans choisir les valeurs d’un établissement réel.

| Paramètre | Configuration A | Configuration B |
| --- | --- | --- |
| Barème | 0–20 | 0–100 |
| Seuil de réussite | 10 | 50 |
| Précision affichée | 2 | 1 |
| Ex æquo | compétition | dense |
| Périodes | 3 | 2 |

**Résultat attendu structurel :** les mêmes scénarios fonctionnels peuvent être exécutés avec A et B sans modification du code. Les résultats restent rattachés à la configuration utilisée et un document publié avec A ne change pas lorsque B est créée ou lorsque A est révisée.

Les valeurs exactes de recette seront ajoutées après validation des règles de conversion entre barèmes.

### Variation d’une règle dans le temps

Cas entièrement fictif : une configuration publiée applique une troncature à une décimale jusqu’à la fin de l’année A, puis une nouvelle version applique un arrondi arithmétique à deux décimales à partir de l’année B.

**Résultats attendus :**

- les deux versions coexistent sans chevauchement de dates ;
- la nouvelle version est simulée avant publication ;
- les calculs de l’année A continuent d’utiliser l’ancienne version ou le snapshot du document ;
- les calculs de l’année B utilisent la nouvelle version à partir de sa date d’effet ;
- un retour à l’ancienne politique crée une troisième version au lieu de modifier l’historique ;
- une règle obligatoire non configurée pour une nouvelle portée produit une erreur explicite, sans repli caché sur le prototype.

Ces valeurs illustrent uniquement le versionnement et ne constituent pas une recommandation académique.

## 6. Facture, remise et paiements partiels

### Hypothèses de travail

- devise fictive de test libellée BIF, sans préjuger de la configuration réelle ;
- facture au responsable d’un élève ;
- remise fixe de 50 000, sans taxe ni pénalité ;
- deux paiements confirmés et affectés à cette seule facture.

| Écriture | Montant |
| --- | ---: |
| Frais de scolarité | 300 000 |
| Frais d’inscription | 50 000 |
| Total brut | 350 000 |
| Remise fixe | −50 000 |
| Net facturé | 300 000 |
| Paiement 1 | −120 000 |
| Paiement 2 | −80 000 |
| **Solde attendu** | **100 000** |

Invariant :

```text
Solde = total + majorations − remises − avoirs − paiements affectés
      = 350 000 + 0 − 50 000 − 0 − 200 000
      = 100 000
```

**Cas complémentaires à faire valider :** remise supérieure au montant éligible, devise différente, second paiement en double, annulation du paiement 2, avoir après paiement, élève transféré et clôture déjà réalisée.

**Résultat validé par le comptable :** à compléter

## 7. Pénalité de retard optionnelle

Ce cas n’est actif que si l’établissement décide d’appliquer une majoration et que sa base est validée.

À partir du solde de 100 000 ci-dessus, **si** la règle retenue est une majoration de 5 % sur le solde en retard, sans autre écriture :

```text
Majoration = 100 000 × 5 % = 5 000
Nouveau solde = 105 000
```

L’atelier doit préciser si la base est plutôt le montant initial, l’échéance échue, le solde restant ou une autre base, ainsi que la date de calcul, le délai de grâce, le plafond et les exclusions. Le taux de 5 % est uniquement un exemple arithmétique, **pas une valeur par défaut recommandée**.

**Règle réelle :** à compléter ou à marquer « sans pénalité »

## 8. Affectation d’un paiement à plusieurs factures

Deux factures d’une même famille sont ouvertes : une ancienne de 100 000 et une récente de 150 000. Un paiement de 120 000 est confirmé.

Si la politique « échéance la plus ancienne d’abord » est retenue :

| Facture | Avant | Affectation | Solde après |
| --- | ---: | ---: | ---: |
| Ancienne | 100 000 | 100 000 | 0 |
| Récente | 150 000 | 20 000 | 130 000 |
| **Total** | **250 000** | **120 000** | **130 000** |

Comparer avec l’affectation choisie par le caissier et avec une priorité par type de frais. Vérifier également le cas de plusieurs enfants et la possibilité de modifier l’affectation après clôture de caisse.

**Politique retenue :** à compléter

## 9. Trop-perçu, annulation et contre-opération

### Trop-perçu

Facture de 100 000, paiement reçu de 120 000 : excédent de 20 000. Trois résultats candidats sont à comparer : rejet avant confirmation, crédit disponible de 20 000, ou demande de remboursement de 20 000. Aucun ne doit produire un solde négatif non expliqué.

### Annulation

Un paiement confirmé de 50 000 est contre-passé avec une référence à l’opération initiale :

- l’opération initiale reste visible ;
- la contre-opération est datée, motivée et autorisée ;
- le solde augmente de 50 000 ;
- le reçu initial n’est pas réécrit silencieusement ;
- un justificatif d’annulation est produit selon la procédure validée.

**Résultats attendus et approbateurs :** à compléter

## 10. Numérotation et clôture de caisse

Scénario minimal :

1. ouvrir une caisse avec un fonds constaté ;
2. confirmer plusieurs paiements sur des canaux différents ;
3. calculer les totaux attendus par canal ;
4. saisir le montant compté et l’écart éventuel ;
5. faire approuver l’écart selon le seuil applicable ;
6. clôturer et verrouiller la session ;
7. tenter une modification après clôture et vérifier le refus ;
8. vérifier qu’aucun numéro de reçu annulé n’est réutilisé.

**Montants, fonds de caisse, seuils et résultats réels :** à fournir par la finance

## 11. Congés et paie : trame obligatoire

Aucun exemple chiffré de paie n’est proposé avant l’atelier avec l’expert local. Le cas de recette devra contenir au minimum :

| Rubrique | Donnée à fournir |
| --- | --- |
| Contrat et période | type, dates, temps de travail |
| Base | montant ou taux, unité et source |
| Présences/absences | événements et justificatifs retenus |
| Variables | primes, retenues, avances et approbations |
| Cotisations et impôts | règles applicables, date d’effet et références officielles |
| Arrondis | étape, précision et mode |
| Résultat intermédiaire | brut et chaque retenue, selon les termes validés |
| Net et règlement | résultat attendu, devise et preuve |
| Permissions | préparateur, validateur et destinataire |
| Version | version exacte des règles conservée avec le bulletin |

Le test doit inclure une entrée ou sortie en cours de période, une absence, une régularisation et une seconde exécution avec les mêmes données pour vérifier la reproductibilité.

## 12. Inventaire des documents officiels

Joindre une version anonymisée ou inventorier les champs sans copier de données réelles.

| Document | Propriétaire | Modèle reçu | Champs inventoriés | Exemple recalculé | Validation |
| --- | --- | --- | --- | --- | --- |
| Demande/dossier d’inscription | secrétariat | non | non | non | en attente |
| Bulletin périodique | pédagogie | non | non | non | en attente |
| Bulletin annuel/relevé | pédagogie | oui : 1ère/2e vierge et 5e HP rempli | oui | contrôles 5e HP effectués, écarts à arbitrer | en attente |
| Procès-verbal de délibération | direction | non | non | non | en attente |
| Attestation/diplôme | direction | non | non | non | en attente |
| Facture et échéancier | finance | non | non | non | en attente |
| Reçu et avoir | finance | non | non | non | en attente |
| Clôture de caisse | finance | non | non | non | en attente |
| Contrat et dossier employé | RH | non | non | non | en attente |
| Bulletin de paie | RH/expert | non | non | non | en attente |

Pour chaque modèle, relever : titre, identifiants, mentions obligatoires, langue, signatures, cachets, pied de page, règles de pagination, destinataires, format papier, durée de conservation et procédure de rectification.

Les structures reçues sont détaillées dans les analyses des bulletins [1ère/2e](08_ANALYSE_BULLETIN_1E_2E.md) et [5e HP](09_ANALYSE_BULLETIN_5E_HP.md). Les valeurs réelles ne deviennent des fixtures de réussite qu’après arbitrage des écarts et validation métier ; les incohérences peuvent en revanche préparer des tests de détection et de blocage.

## 13. Cas ACA-PROP-001 — pondération, arrondi et double seuil

**Statut :** exemple entièrement fictif préparé le 29/10/2026 à partir des réponses d’atelier ; calcul et décision encore à faire valider.

### Configuration candidate

- notes saisies en points bruts sur 20 pour les trois cours ;
- maximum distinct du coefficient ;
- pourcentage de chaque cours arrondi à une décimale, au pair le plus proche, avant la moyenne générale ;
- moyenne générale pondérée par les coefficients `2, 1, 1` ;
- affichage final à une décimale avec le même mode d’arrondi ;
- seuil de départ de 50 % pour chaque cours et pour la moyenne générale.

| Cours | Points bruts | Maximum | Pourcentage exact | Pourcentage arrondi | Coefficient |
| --- | ---: | ---: | ---: | ---: | ---: |
| Mathématiques | 13,45 | 20 | 67,25 % | 67,2 % | 2 |
| Français | 15,5 | 20 | 77,5 % | 77,5 % | 1 |
| Sciences | 9 | 20 | 45,0 % | 45,0 % | 1 |

```text
Moyenne = (67,2×2 + 77,5×1 + 45,0×1) / (2 + 1 + 1)
        = 64,225 %
Affichage au pair à une décimale = 64,2 %
```

| Contrôle | Résultat calculé |
| --- | --- |
| Moyenne générale supérieure ou égale à 50 % | oui : 64,2 % |
| Tous les cours supérieurs ou égaux à 50 % | non : Sciences à 45,0 % |
| Décision finale | dossier à délibérer sélectionné le 29/10/2026 ; validation de la direction restante |

**Réponse du 29/10/2026 :** présenter le dossier au conseil sans décision finale automatique, en affichant la réussite de la moyenne générale et le cours sous le seuil.

**Validateur métier, date et décision :** à compléter

## 14. Cas ACA-PROP-002 — publication incomplète et rang provisoire

**Statut :** cas structurel fictif préparé le 29/10/2026 ; modèle de document et numérotation du rang provisoire à faire valider.

| Dossier | État des notes | Document diffusable proposé | Classement |
| --- | --- | --- | --- |
| Élève A | complet | bulletin de la période après circuit de publication | rang officiel |
| Élève B | complet | bulletin de la période après circuit de publication | rang officiel |
| Élève C | une note obligatoire manquante | bulletin provisoire **ou** relevé incomplet, avec mention visible | rang provisoire après les dossiers complets, exclu du classement officiel |

**Résultats attendus :**

- la note manquante reste `non saisie` ou `absente` selon les faits ; elle ne devient jamais zéro par défaut ;
- la moyenne provisoire utilise uniquement les cours notés et signale les éléments manquants ;
- le document porte un statut `provisoire` ou `incomplet`, sans décision annuelle définitive ;
- le rang provisoire est présenté après les dossiers complets et n’est pas mélangé au classement officiel ;
- la complétion des notes déclenche une nouvelle évaluation du dossier et, si déjà diffusé, le circuit distinct de rectification ;
- les versions précédemment diffusées restent traçables.

**Points encore ouverts :** choix ou coexistence des deux modèles, mentions et signatures, numérotation exacte du rang provisoire, destinataires et notification.

**Validateur métier, date et décision :** à compléter

## 15. Cas ACA-PROP-003 — circuits de publication et de rectification

**Statut :** circuit proposé le 29/10/2026 ; fonctions nommées et délégations à faire valider par la Direction.

### Publication initiale

```text
Préparation du dossier
  → validation pédagogique
  → autorisation administrative
  → publication contrôlée
  → diffusion et journal d’audit
```

### Rectification après publication

```text
Demande motivée de rectification
  → validation pédagogique de la correction
  → autorisation administrative de la rectification
  → nouvelle version publiée, sans réécriture de l’ancienne
  → diffusion corrective et journal d’audit
```

Les deux circuits sont distincts. Chaque étape enregistre l’auteur, la date, la version, le motif et le résultat du contrôle. L’identité des approbateurs, les délégations, l’éventuelle impossibilité d’auto-approbation et les notifications restent à définir ; elles ne sont pas déduites automatiquement de ce schéma.

## 16. Cas ACA-PROP-004 — pondération annuelle non configurée

**Statut :** cas de blocage explicite préparé le 29/10/2026.

**Entrées :** des résultats de périodes et un examen annuel existent, mais la version applicable ne contient pas encore les poids validés.

**Résultat attendu :**

```text
Résultat annuel officiel : non calculable — pondération validée manquante
Décision annuelle        : non disponible
```

Le moteur peut exposer la structure du calcul et les paramètres requis, mais il ne doit appliquer ni poids égaux, ni répartition `50/50`, ni valeur du prototype par défaut. Après publication d’une configuration complète par l’autorité compétente, un exemple chiffré sera recalculé et accepté avant de devenir une fixture de réussite.

## 17. Cas ACA-PROP-005 — résultat, décision et validation officielle

**Statut :** cas structurel préparé le 29/10/2026 ; les libellés officiels restent soumis aux textes applicables au niveau et à la section.

| Couche | Contenu attendu | Ce qui est interdit |
| --- | --- | --- |
| Résultat académique calculé | moyennes, cours sous seuil, complétude et indicateurs de délibération | présenter ce résultat comme une décision officielle |
| Décision administrative | état et décision choisis par le processus autorisé, avec motif et version | écraser ou falsifier le résultat calculé |
| Validation officielle | publication après le circuit pédagogique et administratif applicable | considérer une moyenne comme une validation officielle |

Dans la proposition du 29/10/2026, **Passage** et **Redoublement** sont les deux décisions annuelles finales candidates. **Ajournement** représente un état avant délibération, pas une décision annuelle finale. Ce catalogue n’est pas activé comme vérité officielle sans vérification des textes du niveau et de la section concernés.

La possibilité d’une dérogation du conseil reste ouverte. Si elle est autorisée plus tard, le résultat calculé initial, la décision administrative, le motif, les validations et la version publiée devront rester des objets distincts et traçables.

## 18. Cas ACA-PROP-006 — arrivée en cours d’année

**Statut :** cas structurel fictif préparé le 29/10/2026 ; règle minimale de présence à faire valider.

**Entrées :** un élève rejoint la classe après la première période. Les périodes réellement suivies et l’examen annuel ont des résultats. Les poids annuels sont configurés, mais la règle d’éligibilité liée à la présence minimale n’est pas encore validée.

**Résultats attendus :**

- la période antérieure à l’inscription est `non applicable`, jamais zéro ni note manquante à rattraper ;
- les bulletins des périodes présentes restent calculables et publiables selon leur propre circuit ;
- le calcul annuel provisoire n’utilise que les périodes présentes et les poids applicables ;
- le résultat annuel officiel et le rang annuel restent indisponibles tant que l’éligibilité n’est pas validée ;
- une fois la règle d’éligibilité publiée, le moteur applique cette version sans inventer de seuil ni recalculer les périodes non suivies.

La normalisation des poids sur les périodes présentes et le traitement d’un départ puis retour restent à valider sur un exemple chiffré.

**Validateur métier, date et décision :** à compléter

## 19. Fiche d’acceptation d’un exemple

| Champ | À compléter |
| --- | --- |
| Identifiant du cas | |
| Domaine et processus | |
| Version des règles utilisée | |
| Entrées | |
| Résultat attendu | |
| Résultat observé pendant l’atelier | |
| Écart éventuel | |
| Décision associée | |
| Validateur métier | |
| Date | |
| Autorisation de transformer le cas en test automatisé | |
