# Analyse du bulletin de 5e HP — Pédagogie générale

**Document reçu le :** 27 octobre 2026
**Source fournie :** [Bulletin scolaire — Scribd](https://www.scribd.com/document/898450716/Bulletin-scolaire), puis deux images jointes par l’utilisateur
**Document observé :** bulletin rempli de l’année scolaire 2016–2017, code `IGE/P.S/019`
**Statut :** structure et incohérences relevées ; règles actuelles de l’établissement à confirmer

## 1. Identification, confidentialité et limites

L’utilisateur précise que **5e HP signifie 5e des Humanités pédagogiques**. Le formulaire porte l’intitulé « Bulletin de la 5e année Pédagogie générale ». Ces deux libellés sont conservés sans déduire, du seul scan, l’intitulé administratif exact de la section.

Les images contiennent des données nominatives et des identifiants. Les originaux ne sont donc **pas copiés dans Git**. Cette analyse ne reprend ni nom, ni date ou lieu de naissance, ni numéro d’identification, ni signature. Les quelques contrôles arithmétiques ci-dessous sont dissociés de l’identité de l’élève.

Le document est utile pour observer un bulletin réellement rempli, mais il ne constitue pas à lui seul une instruction officielle actuelle :

- il concerne une année scolaire ancienne, 2016–2017 ;
- il est diffusé par un tiers et non par le site du Ministère ;
- l’en-tête reprend l’ancienne appellation du ministère ;
- plusieurs reports manuscrits ne sont pas arithmétiquement cohérents ;
- aucune légende ne définit les tirets, les arrondis ou les seuils de décision.

Il faut donc distinguer les **faits visibles**, les **calculs de vérification** et les **règles encore à valider**.

## 2. Structure des colonnes

| Zone | Colonnes relevées |
| --- | --- |
| Premier semestre | travaux journaliers 1ère P et 2e P, examen, total |
| Second semestre | travaux journaliers 3e P et 4e P, examen, total |
| Synthèse annuelle | T.G. — total général |
| Repêchage | pourcentage et signature du professeur |
| Synthèse de classe | maxima généraux, totaux, pourcentage, place/nombre d’élèves |
| Autres rubriques | application, conduite, signature du responsable |

Le modèle confirme une nouvelle fois l’organisation en **deux semestres et quatre périodes**. La Pratique d’enseignement constitue toutefois une exception : ses cellules d’examen sont neutralisées et son total semestriel ne repose que sur deux périodes.

## 3. Branches et maxima relevés

Le bulletin comporte **22 branches**, réparties en cinq groupes de maxima.

| Groupe | Branches | Maximum par période | Examen semestriel | Total semestre | T.G. annuel |
| --- | --- | ---: | ---: | ---: | ---: |
| A — 4 branches | Religion ; Ed. civ. & morale ; Education à la vie (1) ; Informatique (1) | 10 | 20 | 40 | 80 |
| B — 13 branches | Biologie ; Education physique ; Initiation esthétique ; Géographie ; Histoire ; Physique ; Pédagogie ; Psychologie ; Dessin pédagogique ; Act. man./Écriture ; Ed. musicale/Théâtre ; Didactique générale ; Didact. des disciplines | 20 | 40 | 80 | 160 |
| C — 2 branches | Mathématiques ; Langues nationales | 40 | 80 | 160 | 320 |
| D — 2 branches | Français ; Anglais | 50 | 100 | 200 | 400 |
| E — 1 branche | Pratique d’enseignement | 100 | **sans examen** | 200 | 400 |

Les libellés abrégés sont conservés lorsque le formulaire ne donne pas leur développement. La présence d’une branche sur ce modèle ne prouve pas qu’elle est encore enseignée avec le même intitulé ou le même barème.

**Constat important :** comme sur le bulletin 1ère/2e, il n’y a pas de coefficient indépendant visible. Les maxima des branches déterminent leur poids dans les totaux. Il ne faut pas ajouter automatiquement une seconde pondération.

## 4. Maxima généraux

### Maximum d’une période — reconstitué

```text
4 × 10 + 13 × 20 + 2 × 40 + 2 × 50 + 1 × 100
= 40 + 260 + 80 + 100 + 100
= 580
```

### Maximum d’un examen semestriel — reconstitué

La Pratique d’enseignement n’entre pas dans ce calcul puisqu’elle n’a pas d’examen sur le formulaire.

```text
4 × 20 + 13 × 40 + 2 × 80 + 2 × 100
= 80 + 520 + 160 + 200
= 960
```

### Contrôle des maxima de semestre et d’année

| Agrégat | Calcul | Maximum | Preuve sur le document |
| --- | --- | ---: | --- |
| Une période | somme des maxima des 22 branches | 580 | valeur reconstituée |
| Un examen semestriel | somme des examens des 21 branches concernées | 960 | valeur reconstituée |
| Un semestre | 580 + 580 + 960 | **2 120** | valeur également inscrite |
| Une année | 2 120 + 2 120 | **4 240** | valeur également inscrite |

Les cellules de maxima généraux des périodes et des examens ne sont pas remplies. Les valeurs `580` et `960` sont donc des contrôles arithmétiques, non des chiffres directement imprimés dans la synthèse.

## 5. Deux schémas d’agrégation visibles

### Branches avec examen semestriel

```text
Total du semestre 1 = P1 + P2 + Examen 1
Total du semestre 2 = P3 + P4 + Examen 2
T.G.                = Total semestre 1 + Total semestre 2
```

Pour une branche dont le maximum de période est `P` :

```text
Maximum examen   = 2 × P
Maximum semestre = 4 × P
Maximum annuel   = 8 × P
```

### Pratique d’enseignement, sans examen

```text
Total du semestre 1 = P1 + P2
Total du semestre 2 = P3 + P4
T.G.                = Total semestre 1 + Total semestre 2
```

Avec `P = 100` sur ce modèle :

```text
Maximum semestre = 2 × P = 200
Maximum annuel   = 4 × P = 400
```

| Composante | Part du semestre — branche avec examen | Part du semestre — Pratique d’enseignement |
| --- | ---: | ---: |
| Chaque période | 25 % | 50 % |
| Examen semestriel | 50 % | sans objet |

Ces parts découlent des maxima du formulaire. Elles ne doivent pas être généralisées à une autre classe sans vérification.

## 6. Recalculs et incohérences à ne pas masquer

Plusieurs lignes sont cohérentes avec les formules précédentes. Par exemple, les totaux semestriels `30 + 30`, `62 + 54`, `130 + 130` et `130 + 150` donnent respectivement les T.G. `60`, `116`, `260` et `280`.

Mais le bulletin contient aussi des écarts visibles :

- sur une ligne, les totaux semestriels `65` et `41` devraient produire `106`, alors que le T.G. reporté est `74` ;
- la somme des totaux du premier semestre lus sur les 22 lignes donne `1 214`, alors que le total général manuscrit semble être `1 114` ;
- le total annuel `2 254` correspond à `1 114 + 1 140`, mais pas à la somme de toutes les lignes si les autres lectures sont exactes.

Ces écarts peuvent provenir d’une erreur de calcul, de report ou de lecture du scan. **Il ne faut pas choisir silencieusement une correction.** Ils doivent être arbitrés avec une copie plus nette ou le détail de calcul validé.

### Conséquence pour LaVision

Le futur moteur doit pouvoir contrôler au minimum :

```text
total semestriel = somme des composantes prévues par le schéma de la branche
T.G.             = total semestre 1 + total semestre 2
total général    = somme des lignes du programme
maximum général  = somme des maxima des lignes du programme
```

Une divergence doit être signalée avant publication. Une rectification autorisée doit créer une nouvelle version traçable ; elle ne doit pas réécrire silencieusement le document historique.

## 7. Pourcentages : hypothèse de troncature à confirmer

La lecture de travail des valeurs globales manuscrites est la suivante : `1 114 / 2 120`, `1 140 / 2 120` et `2 254 / 4 240`, avec les pourcentages `52,5`, `53,7` et `53,1`.

| Calcul | Valeur exacte | Arrondi arithmétique à 1 décimale | Troncature à 1 décimale | Valeur lue |
| --- | ---: | ---: | ---: | ---: |
| 1 114 / 2 120 × 100 | 52,547… % | 52,5 % | 52,5 % | 52,5 % |
| 1 140 / 2 120 × 100 | 53,773… % | 53,8 % | 53,7 % | 53,7 % |
| 2 254 / 4 240 × 100 | 53,160… % | 53,2 % | 53,1 % | 53,1 % |

Les deux derniers cas sont **compatibles avec une troncature à une décimale**, et non avec un arrondi arithmétique classique. Cette observation reste une hypothèse : certains chiffres sont manuscrits et le premier total ne se réconcilie pas avec la somme des lignes. Elle doit être confirmée par l’établissement avant de choisir `academic.rounding.mode`.

## 8. Tirets et notes manquantes

Des tirets apparaissent notamment :

- dans une note de première période dont le total semestriel additionne seulement les autres composantes ;
- dans un examen dont le total semestriel correspond à la somme des deux périodes ;
- dans toutes les composantes du second semestre d’une branche, tandis que le T.G. conserve le seul total du premier semestre.

Dans ces exemples, le tiret **n’ajoute pas de points** et le maximum général du programme reste inchangé. Cela ne suffit pas à déterminer sa signification métier : note non saisie, absence, dispense, cours non suivi ou zéro.

LaVision doit donc conserver un statut distinct de la valeur numérique. Il ne faut pas transformer automatiquement tout tiret en zéro définitif ni réduire le dénominateur sans règle validée.

## 9. Repêchage, rang et décisions

Le formulaire prévoit :

- une condition de passage après réussite d’un examen de repêchage dans des branches à indiquer ;
- les décisions passe, double, échec et réorientation ;
- un pourcentage et une signature du professeur par branche repêchée ;
- une place sur le nombre d’élèves, renseignée ici dans la synthèse annuelle.

Des pourcentages entiers de repêchage sont visibles sur quatre lignes. La décision « passe » est cochée. Toutefois :

- la liste manuscrite des branches soumises au repêchage ne semble pas correspondre exactement aux lignes comportant un pourcentage ;
- les signatures de la zone de repêchage ne sont pas renseignées ;
- sur les lignes recalculables, le T.G. reste la somme des semestres et ne semble pas remplacé par le résultat du repêchage ;
- aucun seuil de réussite du repêchage n’est imprimé.

Cet exemple plaide pour conserver séparément les résultats initiaux, l’épreuve de repêchage et la décision finale. Il ne permet pas de déduire le seuil, le nombre maximal de branches, la compensation ou la règle de deuxième délibération.

Les rubriques Application et Conduite utilisent notamment les codes `B` et `TB`. Leur signification et leur incidence éventuelle sur la décision ne sont pas définies sur le document.

## 10. Comparaison avec le bulletin 1ère/2e secondaire

| Élément | 1ère/2e secondaire | 5e HP — Pédagogie générale |
| --- | --- | --- |
| Code du formulaire | `IGE/P.S/005` | `IGE/P.S/019` |
| Document analysé | modèle vierge | bulletin rempli, année 2016–2017 |
| Nombre de branches | 14 | 22 |
| Maxima par période | 20, 30 ou 60 | 10, 20, 40, 50 ou 100 |
| Schéma semestriel | deux périodes + examen pour toutes les branches | deux périodes + examen, sauf Pratique d’enseignement |
| Maximum général d’un semestre | 1 600, reconstitué | 2 120, reconstitué et inscrit |
| Maximum général annuel | 3 200, reconstitué | 4 240, reconstitué et inscrit |
| Exemple de pourcentage | aucun | valeurs compatibles avec une troncature, à confirmer |
| Exemple de repêchage | zone vierge | pourcentages et décision visibles, règles non déductibles |
| Cohérence arithmétique | non testable sur un modèle vierge | plusieurs écarts à arbitrer |

La comparaison confirme qu’un barème ou une formule ne peut pas être appliqué uniformément à tout le secondaire. La configuration doit être rattachée au **programme annuel et à la branche**, avec une date d’effet et une version publiée.

## 11. Conséquences pour la configuration LaVision

1. Représenter la 5e HP et Pédagogie générale dans le référentiel section/option/programme, après validation des libellés exacts.
2. Configurer les maxima par programme et par branche, sans coefficient supplémentaire implicite.
3. Prévoir un catalogue fermé de schémas, notamment « périodes + examen » et « périodes sans examen ».
4. Calculer les maxima généraux à partir des branches réellement incluses dans le programme.
5. Distinguer note absente, absence, dispense, cours non suivi et zéro.
6. Configurer séparément précision, étape et mode d’arrondi ou de troncature.
7. Contrôler les sommes par ligne et par synthèse avant publication.
8. Historiser première délibération, repêchage et deuxième délibération sans écraser les résultats initiaux.
9. Conserver sur chaque bulletin le modèle, les règles et leur version exacte.

## 12. Questions à traiter avec l’utilisateur et l’établissement

1. **Libellés :** « Humanités pédagogiques » est-il le nom de la section et « Pédagogie générale » celui de l’option dans votre référentiel ?
2. **Programme :** les 22 branches et leurs maxima correspondent-ils encore à la 5e HP actuellement suivie ? Quelles différences existe-t-il par année ou option ?
3. **Pratique :** l’absence d’examen semestriel en Pratique d’enseignement est-elle une règle actuelle ? Comment obtient-on chaque note de période sur 100 ?
4. **Tirets :** que signifie chaque tiret et quand un manque de note vaut-il zéro, dispense ou dossier incomplet ? Le maximum général reste-t-il toujours inchangé ?
5. **Écarts de calcul :** pouvez-vous confirmer les totaux globaux et fournir le détail corrigé ou une copie plus nette ? Quelle valeur fait foi en cas de divergence ?
6. **Pourcentages :** faut-il tronquer ou arrondir à une décimale ? L’arrondi intervient-il seulement à l’affichage ou aussi dans les calculs suivants ?
7. **Repêchage :** quel est le seuil de réussite, quelles branches sont éligibles et combien de branches peuvent être repêchées ?
8. **Après repêchage :** le pourcentage obtenu reste-t-il séparé, remplace-t-il une note ou modifie-t-il le T.G. et le pourcentage annuel ?
9. **Décisions :** comment enregistrez-vous la première décision conditionnelle puis la décision finale de deuxième délibération ?
10. **Classement :** le rang annuel utilise-t-il le pourcentage exact ou affiché, et comment traite-t-on les ex æquo et dossiers incomplets ?

## 13. Prochaine preuve nécessaire

Pour convertir cette analyse en règles validées, il faut encore :

- la confirmation des libellés section/option et du programme actuel ;
- un modèle vierge ou une instruction officielle récente de 5e HP ;
- un exemple recalculé et corrigé, anonymisé, avec les mêmes entrées et résultats attendus ;
- la règle de pourcentage et de troncature/arrondi ;
- la signification des tirets et la politique de publication des dossiers incomplets ;
- les instructions de première délibération, repêchage et deuxième délibération.

L’analyse du modèle 1ère/2e reste disponible dans [08_ANALYSE_BULLETIN_1E_2E.md](08_ANALYSE_BULLETIN_1E_2E.md).
