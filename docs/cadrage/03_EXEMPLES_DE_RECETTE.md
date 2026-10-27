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
| Dossier incomplet | pas de rang ni de décision Admis/Ajourné | comportement du prototype |

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
| D | incomplet | 11,78 provisoire ou masquée | Incomplet |

### Décisions à obtenir

- La moyenne provisoire de D doit-elle être affichée, masquée ou calculée autrement ?
- Une absence de note peut-elle être remplacée par zéro, une dispense ou une récupération ? Dans quels cas ?
- Le seuil est-il franchi lorsque la moyenne est exactement égale ?
- Une classe entière est-elle bloquée par D, ou seulement sa publication individuelle ?

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

**Décision validée :** à compléter

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

**Configuration retenue et exemple validé :** à compléter

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
| Bulletin annuel/relevé | pédagogie | non | non | non | en attente |
| Procès-verbal de délibération | direction | non | non | non | en attente |
| Attestation/diplôme | direction | non | non | non | en attente |
| Facture et échéancier | finance | non | non | non | en attente |
| Reçu et avoir | finance | non | non | non | en attente |
| Clôture de caisse | finance | non | non | non | en attente |
| Contrat et dossier employé | RH | non | non | non | en attente |
| Bulletin de paie | RH/expert | non | non | non | en attente |

Pour chaque modèle, relever : titre, identifiants, mentions obligatoires, langue, signatures, cachets, pied de page, règles de pagination, destinataires, format papier, durée de conservation et procédure de rectification.

## 13. Fiche d’acceptation d’un exemple

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
