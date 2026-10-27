# Analyse du bulletin 1ère/2e année secondaire

**Document reçu le :** 27 octobre 2026
**Source fournie :** [Bulletin Scolaire Vierge RDC 2023 — Scribd](https://www.scribd.com/document/684123123/Bulletin-1e-et-2e)
**Copie de travail :** [image du bulletin](assets/bulletin-1e-2e-rdc-2023.png)
**Statut :** modèle vierge analysé ; version officielle actuelle et règles de délibération à confirmer

## 1. Valeur et limites du document

Le modèle porte la mention « Bulletin de la 1ère, 2ème (1) année secondaire » et le code `IGE/P.S/005`. Il correspond donc aux classes pour lesquelles les programmes nationaux utilisent aussi les nomenclatures 7e et 8e de l’Éducation de Base.

Le document est très utile pour comprendre la structure du bulletin. Toutefois :

- il est hébergé par un tiers sur Scribd, pas sur le site du Ministère ;
- sa mention « 2023 » correspond au titre de publication en ligne, pas nécessairement à l’édition réglementaire du formulaire ;
- le pied de page contient encore une année `19…` et un lieu préimprimé « Mwene-Ditu », alors que l’en-tête mentionne une école de Beni ;
- il ne contient aucun exemple de notes permettant de vérifier les arrondis et décisions.

Il faut donc l’utiliser comme **modèle de structure**, puis confirmer l’édition applicable avec un bulletin récent ou une instruction officielle.

## 2. Structure des colonnes

| Zone | Colonnes relevées |
| --- | --- |
| Premier semestre | travail journalier 1ère P, travail journalier 2e P, examen, total |
| Second semestre | travail journalier 3e P, travail journalier 4e P, examen, total |
| Synthèse annuelle | T.G. — total général |
| Repêchage | pourcentage et signature du professeur |
| Synthèse de classe | maxima généraux, totaux, pourcentage, place/nombre d’élèves |
| Autres rubriques | application, conduite, signature du responsable |

Le modèle confirme une organisation en **deux semestres et quatre périodes**, cohérente avec le calendrier secondaire RDC 2026–2027 consulté.

## 3. Maxima par groupe de branches

### Groupe A — maximum de 20 par période

Huit branches figurent dans ce groupe :

- Religion ;
- Éducation civique et morale ;
- Éducation à la vie ;
- Informatique ;
- Anglais ;
- Dessin ;
- Éducation physique ;
- Musique.

| Période | Examen | Total semestre | T.G. annuel |
| ---: | ---: | ---: | ---: |
| 20 | 40 | 80 | 160 |

### Groupe B — maximum de 30 par période

Quatre branches figurent dans ce groupe :

- Géographie ;
- Histoire ;
- Sciences ;
- Technologie.

| Période | Examen | Total semestre | T.G. annuel |
| ---: | ---: | ---: | ---: |
| 30 | 60 | 120 | 240 |

### Groupe C — maximum de 60 par période

Deux branches figurent dans ce groupe :

- Français ;
- Mathématique.

| Période | Examen | Total semestre | T.G. annuel |
| ---: | ---: | ---: | ---: |
| 60 | 120 | 240 | 480 |

**Constat important :** le bulletin ne présente pas de colonne de coefficient indépendante. Les maxima attribués aux branches déterminent déjà leur poids dans les totaux. Il ne faut donc pas ajouter automatiquement un second coefficient qui doublerait cette pondération.

## 4. Formules directement suggérées par le tableau

Pour chaque branche :

```text
Total du semestre 1 = note P1 + note P2 + note examen 1
Total du semestre 2 = note P3 + note P4 + note examen 2
Total général       = total semestre 1 + total semestre 2
```

Pour les trois groupes, le maximum de l’examen est égal à deux fois le maximum d’une période :

```text
Maximum semestre = maximum période × 4
Maximum annuel   = maximum période × 8
```

Cela implique, si aucune autre règle ne modifie les totaux :

| Composante | Part du semestre | Part de l’année |
| --- | ---: | ---: |
| Chaque période | 25 % | 12,5 % |
| Chaque examen semestriel | 50 % | 25 % |
| Chaque semestre | — | 50 % |

Ces pourcentages sont une conséquence arithmétique des maxima du modèle, pas une règle générale à appliquer à toutes les classes sans vérification.

## 5. Maxima généraux théoriques du modèle

Si les 14 branches imprimées sont toutes utilisées, les maxima peuvent être reconstitués comme suit.

### Par période

```text
8 branches × 20 + 4 branches × 30 + 2 branches × 60
= 160 + 120 + 120
= 400
```

### Par examen semestriel

```text
8 branches × 40 + 4 branches × 60 + 2 branches × 120
= 320 + 240 + 240
= 800
```

### Par semestre et par année

| Agrégat | Calcul | Maximum théorique |
| --- | --- | ---: |
| Semestre | 400 + 400 + 800 | 1 600 |
| Année | 1 600 + 1 600 | 3 200 |
| Groupe A à l’année | 8 × 160 | 1 280 |
| Groupe B à l’année | 4 × 240 | 960 |
| Groupe C à l’année | 2 × 480 | 960 |
| Contrôle annuel | 1 280 + 960 + 960 | 3 200 |

La ligne « MAXIMA GÉNÉRAUX » étant vierge, ces valeurs doivent être vérifiées sur un bulletin rempli ou auprès de l’établissement. Elles constituent néanmoins un excellent cas de test pour valider le futur moteur.

## 6. Pourcentage, rang et appréciations

Le modèle contient une ligne « POURCENTAGE » sous chaque agrégat et une ligne « PLACE/NBRE ÉLÈVES ». La structure suggère un pourcentage calculé à partir des points obtenus et des maxima :

```text
Pourcentage = points obtenus / maximum correspondant × 100
```

Cette formule reste à confirmer pour chaque colonne. Il faut également préciser :

- le nombre de décimales et le mode d’arrondi ;
- si le rang utilise le pourcentage exact ou affiché ;
- le traitement des ex æquo ;
- le dénominateur lorsqu’une note manque ;
- si les rubriques Application et Conduite interviennent dans les totaux ou restent des appréciations séparées.

## 7. Repêchage et décisions visibles

Le modèle comporte une zone d’examen de repêchage avec un pourcentage et une signature. Les décisions imprimées comprennent :

- passage conditionné par la réussite d’un repêchage dans une ou plusieurs branches à indiquer ;
- passage dans la classe supérieure ;
- redoublement ;
- échec avec réorientation vers une autre voie.

Une zone distincte mentionne aussi « Passe », « Double » et « A échoué ».

### Règles encore absentes du modèle

Le document ne précise pas :

1. le pourcentage de réussite requis ;
2. les conditions qui obligent un élève au repêchage ;
3. les branches concernées et leur nombre maximal ;
4. la note maximale du repêchage ;
5. si la note de repêchage remplace une note existante ou reste séparée ;
6. son incidence éventuelle sur le total général et le pourcentage final ;
7. les règles de première et deuxième délibération ;
8. les seuils qui distinguent passage, redoublement et réorientation.

Ces règles ne doivent pas être inventées à partir de la seule mise en page.

## 8. Champs du document à prévoir

### Établissement

- identifiant national et code de l’école ;
- province éducationnelle, ville et commune ;
- nom de l’établissement ;
- lieu et date d’émission ;
- sceau, nom et signature du chef d’établissement.

### Élève

- nom complet et sexe ;
- lieu et date de naissance ;
- classe ;
- numéro permanent ;
- signature de l’élève et signature du responsable.

### Résultats et suivi

- année scolaire ;
- notes par période et examen ;
- totaux et pourcentage ;
- rang et effectif de la classe ;
- application et conduite ;
- repêchage et décision finale.

Les identifiants nationaux et numéros permanents doivent bénéficier d’un accès restreint. Ils ne doivent pas apparaître dans des exports ou des liens publics non protégés.

## 9. Conséquence pour les corrections

La note « le bulletin est sans valeur s’il est raturé ou surchargé » renforce le besoin de produire un document propre et contrôlé.

Dans LaVision, une correction après publication ne doit donc pas simuler une rature. Elle doit suivre une procédure autorisée, créer une nouvelle version traçable et conserver le document précédent ainsi que le motif de rectification.

## 10. Prochaine preuve nécessaire

Pour transformer ce modèle en règles de calcul validées, il faut encore obtenir :

- un bulletin rempli et anonymisé de la même classe ;
- le détail du calcul d’un élève, des notes jusqu’au pourcentage et à la décision ;
- les instructions sur les notes manquantes, les arrondis et le rang ;
- les conditions et le calcul du repêchage ;
- la confirmation que les 14 branches et leurs maxima correspondent bien au programme actuellement suivi ;
- un modèle équivalent pour une option des humanités afin de comparer les maxima.
