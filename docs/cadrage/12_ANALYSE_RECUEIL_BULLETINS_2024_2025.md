# Analyse du recueil de bulletins RDC 2024–2025

**Consultation :** 29 octobre 2026

**Statut :** analyse de sources secondaires ; usage des formulaires cette année confirmé par l’utilisateur, structures et totaux locaux à faire confirmer

**Références :** issues #2 et #39

## 1. Sources et limites

Le document principal est le [recueil Scribd de 106 pages](https://www.scribd.com/document/886593638/Bulletins-MINEDUC-24-25-Toutes-Sections-Par-Ordre-Alphabetique) transmis par l’utilisateur. Des documents séparés ont permis de lire des séries dont l’extraction de la compilation était incomplète :

| Source | Modèles utilisés pour la comparaison |
| --- | --- |
| [Recueil toutes sections](https://www.scribd.com/document/886593638/Bulletins-MINEDUC-24-25-Toutes-Sections-Par-Ordre-Alphabetique) | primaire, 7e/8e CTEB, Agriculture, Construction, Foresterie, Industries agricoles, Latin–Philosophie et Latin–Grec |
| [Pédagogie générale — 4 pages](https://www.scribd.com/document/836268338/2-01-BULLETINS-PEDAGOGIE-GENERALE-1) | 1re, 2e, 3e et 4e Humanités, formulaires `/024` à `/027` |
| [Commerciale & Gestion — 4 pages](https://www.scribd.com/document/834505742/3-01-bulletins-Commerciale-Gestion-1) | 1re, 2e, 3e et 4e Humanités, formulaires `/042` à `/045` |
| [Mécanique générale — 4 pages](https://www.scribd.com/document/838226415/9-01-bulletins-Mecanique-Generale) | 1re, 2e, 3e et 4e Humanités techniques, formulaires `/052` à `/055` |
| [1re Humanités scientifiques — 1 page](https://www.scribd.com/document/834371530/Bulletin-1e-Scientifique-Mathematiques-Physique) | domaines et sous-domaines, formulaire se terminant par `/012` |
| [Degré moyen — 1 page](https://www.scribd.com/document/853390788/BULLETIN-DE-LA-3e-4e-ANNEE-DEGRE-MOYEN) | comparaison du modèle `IGE/P.S/005` avec le primaire et les Humanités |

Ces documents sont hébergés par Scribd et téléversés par des tiers. La présence d’un en-tête ministériel ou d’un code IGE ne prouve pas, à elle seule, l’authenticité, l’édition en vigueur ou l’applicabilité à lavision. L’extraction texte est par ailleurs dégradée sur certaines pages. Les codes et libellés doivent donc être contrôlés visuellement sur une source officielle ou un modèle fourni par l’établissement.

**Portée de l’analyse :** les modèles lisibles cités ci-dessus ont été comparés. Les 106 pages ne sont pas déclarées intégralement transcrites ou validées. Aucun document nominatif n’a été copié dans Git.

## 2. Principales familles de bulletins observées

| Famille | Structure observée | Particularités |
| --- | --- | --- |
| Primaire | 3 trimestres et 6 périodes | domaines, sous-totaux, pourcentage, place et décisions passe/double |
| Primaire terminal / enseignement spécial | mêmes périodes, avec résultat final | bloc `Moyenne école 50`, `ENAFEP 50`, `Total 100` sur le modèle consulté |
| 7e/8e CTEB | 2 semestres et 4 périodes | domaines et sous-domaines scientifiques, langues, univers social, arts et développement personnel |
| 8e CTEB | structure CTEB et résultat final distinct | décision du jury, option orientée et bloc `Moyenne école 50`, `TENASOSP 50`, `Total 100` |
| Humanités non terminales | 2 semestres, 4 périodes, examen par semestre et T.G. | groupes de maxima variables, repêchage, pourcentage, place, application et conduite |
| Humanités terminales | mêmes périodes, avec zone EXETAT | résultat `Diplôme` avec pourcentage ou `A échoué`, contrôle du chef de centre ; certaines sections techniques affichent aussi une zone `MEC 1000` |
| Humanités techniques | structure des Humanités | pratique professionnelle ou atelier, stages et branches propres à l’option selon l’année |

Le bloc `50 + 50 = 100` est donc observé dans des contextes précis de résultat final avec épreuve nationale. Il ne doit pas être transformé en pondération annuelle générique pour toutes les classes.

## 3. Différences de programmes et de formulaires

Les modèles examinés comprennent notamment :

- 7e et 8e CTEB, avec des branches et maxima qui évoluent entre les deux années ;
- 1re Humanités scientifiques, organisée par domaines et sous-domaines ;
- Agriculture générale, Construction, Foresterie et Industries agricoles ;
- Latin–Philosophie et Latin–Grec ;
- Pédagogie générale 1re–4e ;
- Commerciale & Gestion 1re–4e ;
- Mécanique générale 1re–4e.

La section seule ne suffit pas à déterminer un bulletin. Le programme varie selon **l’année des Humanités**, l’option et l’édition du formulaire. Par exemple :

- la 3e Pédagogie générale comprend Didactique, Pédagogie, Psychologie et Pratique d’enseignement ;
- la 4e Pédagogie générale ajoute notamment Philosophie et conserve une Pratique d’enseignement sans examen semestriel ;
- la 1re Commerciale & Gestion comprend notamment Comptabilité générale, Fiscalité, Documentation commerciale et Pratique professionnelle ;
- la 4e Commerciale & Gestion fait apparaître notamment Déontologie professionnelle, Finances publiques, Comptabilité analytique et une zone EXETAT ;
- la Mécanique générale fait varier les branches techniques entre les années et utilise une Pratique professionnelle en atelier.

## 4. Deux schémas d’agrégation à ne pas confondre

### Branche avec examen semestriel

Pour une branche dont le maximum de chaque période est `P` :

```text
Maximum de l’examen semestriel = 2 × P
Total du semestre              = P1 + P2 + Examen
Maximum du semestre            = P + P + 2P = 4P
Maximum annuel T.G.            = 8P
```

### Branche sans examen semestriel

Ce schéma est visible notamment pour Pratique d’enseignement, Pratique professionnelle et certaines activités complémentaires :

```text
Total du semestre              = P1 + P2
Maximum du semestre            = 2P
Maximum annuel T.G.            = 4P
```

Une branche sans examen ne doit donc recevoir ni une note d’examen obligatoire ni un maximum d’examen implicite. Les maxima visibles portent déjà le poids des branches dans les totaux ; aucun coefficient indépendant supplémentaire n’est visible sur les modèles analysés.

## 5. Totaux reconstitués de contrôle

Les valeurs suivantes sont des **déductions arithmétiques** à partir des branches et maxima lisibles. Elles ne constituent pas encore une configuration officielle de lavision. Pour la 8e CTEB, les maxima `1 680` par semestre et `3 360` au T.G. sont également lisibles dans le texte extrait, ce qui confirme la méthode de reconstruction.

| Modèle | Maximum d’une période | Maximum de l’examen semestriel | Maximum d’un semestre | Maximum annuel T.G. |
| --- | ---: | ---: | ---: | ---: |
| 7e CTEB | 400 | 800 | 1 600 | 3 200 |
| 8e CTEB | 420 | 840 | 1 680 | 3 360 |
| 1re Humanités scientifiques | 360 | 720 | 1 440 | 2 880 |
| 1re Pédagogie générale | 530 | 1 060 | 2 120 | 4 240 |
| 2e Pédagogie générale | 590 | 1 180 | 2 360 | 4 720 |
| 3e Pédagogie générale | 750 | 1 300 | 2 800 | 5 600 |
| 4e Pédagogie générale | 780 | 1 360 | 2 920 | 5 840 |
| 1re Commerciale & Gestion | 480 | 720 | 1 680 | 3 360 |
| 2e Commerciale & Gestion | 480 | 720 | 1 680 | 3 360 |
| 3e Commerciale & Gestion | 490 | 740 | 1 720 | 3 440 |
| 4e Commerciale & Gestion | 480 | 740 | 1 700 | 3 400 |

### Décomposition des maxima d’une période

| Modèle | Contrôle de la somme |
| --- | --- |
| 7e CTEB | `80 + 30 + 30 + 100 + 100 + 40 + 20 = 400` |
| 8e CTEB | `80 + 50 + 40 + 80 + 110 + 40 + 20 = 420` |
| 1re scientifique | `70 + 40 + 70 + 80 + 90 + 10 = 360` |
| 1re Pédagogie générale | `3×10 + 10×20 + 5×40 + 2×50 = 530` |
| 2e Pédagogie générale | `3×10 + 11×20 + 6×40 + 2×50 = 590` |
| 3e Pédagogie générale | `4×10 + 7×20 + 3×40 + 7×50 + 100 = 750` |
| 4e Pédagogie générale | `4×10 + 6×20 + 3×40 + 8×50 + 100 = 780` |
| 1re/2e Commerciale & Gestion | `6×10 + 5×20 + 20 + 2×30 + 2×40 + 60 + 100 = 480` |
| 3e Commerciale & Gestion | `8×10 + 20 + 6×20 + 30 + 2×40 + 60 + 100 = 490` |
| 4e Commerciale & Gestion | `9×10 + 10 + 4×20 + 30 + 3×40 + 50 + 100 = 480` |

Pour les séries Pédagogie 3e/4e, `100` correspond à la Pratique d’enseignement sans examen. Pour Commerciale & Gestion, les valeurs `20` ou `10` isolées correspondent aux activités sans examen et `100` à la Pratique professionnelle sans examen. Les maxima d’examen du tableau excluent ces branches.

## 6. Comparaisons et points de vigilance

### Bulletin 5e HP 2016–2017

Le bulletin rempli déjà analysé dans le [document 09](09_ANALYSE_BULLETIN_5E_HP.md) possède 22 branches, un maximum reconstitué de `580` par période, `2 120` par semestre et `4 240` au T.G. La 3e Pédagogie générale 2024–2025 possède aussi 22 branches, mais une répartition différente conduisant à `750`, `2 800` et `5 600` dans la présente reconstruction.

Ces deux documents ne doivent donc pas partager silencieusement le même programme. Un code de formulaire ne doit pas non plus servir de clé unique sans année et programme : le recueil 2024–2025 fait apparaître `/019` sur Latin–Grec, alors que le bulletin historique 5e HP porte aussi `IGE/P.S/019`.

### Variantes de Commerciale & Gestion

Une [autre variante tierce de 1re Commerciale & Gestion](https://www.scribd.com/document/887657418/Bulletin-Option-Commerciale-Gestion-1ere-2eme-Humanites), avec ancien en-tête ministériel et année laissée vide, affiche `350` par période, `1 400` par semestre et `2 800` au total. Elle ne contient pas les mêmes branches que le formulaire 2024–2025 `/042`, qui comprend notamment des activités et une Pratique professionnelle.

Il ne faut pas fusionner ces variantes ni choisir une valeur sur la base du seul titre donné par la plateforme.

### Décisions et repêchage

- Les modèles non terminaux consultés prévoient une zone de repêchage et les mentions passe/double ; ils ne donnent pas le seuil, le nombre de branches éligibles ni l’effet du repêchage sur le T.G.
- Les bulletins terminaux EXETAT distinguent le diplôme et l’échec, sans fournir à eux seuls les règles de délibération.
- Les rubriques Application et Conduite sont présentes, mais leur calcul et leur effet ne sont pas définis par les formulaires.
- La mention « bulletin sans valeur s’il est raturé ou surchargé » renforce la nécessité d’une rectification par nouvelle version plutôt que par réécriture silencieuse.

## 7. Conséquences pour lavision

1. Utiliser les formulaires toujours en cours comme modèles de départ sourcés, sans figer leurs listes dans le code.
2. Permettre à chaque établissement de configurer ses sections, options, cours disponibles et cours propres aux options.
3. Identifier un programme par **année scolaire, cycle, année d’étude, section, option et version du formulaire**, pas seulement par son intitulé court.
4. Configurer les branches et maxima par programme annuel, avec des schémas « avec examen » et « sans examen » par branche ; l’exemple Comptabilité `120` points reste local et configurable.
5. Représenter séparément domaines, sous-domaines et sous-totaux lorsque le modèle les exige.
6. Calculer les maxima généraux depuis les branches réellement présentes et contrôler chaque total avant publication.
7. Conserver ENAFEP, TENASOSP et EXETAT comme épreuves et résultats distincts du calcul scolaire interne ; leurs règles de combinaison doivent être configurées et validées par contexte.
8. Ne jamais appliquer le bloc `50/50` de la 8e CTEB ou du primaire terminal à toutes les classes.
9. Prévoir des modèles de documents distincts pour bulletin périodique, bulletin annuel, repêchage, résultat national et document provisoire/incomplet.
10. Conserver le résultat calculé, la décision administrative et la validation officielle séparés, conformément aux réponses M0 déjà enregistrées.
11. Publier une version locale du programme sans réécrire les configurations et documents historiques.

## 8. Réponses du 29 octobre 2026 et points restant à valider

### Réponses enregistrées

- Les formulaires du recueil 2024–2025 et des séries associées sont toujours utilisés pendant l’année en cours.
- Chaque établissement configure ses sections, ses options, les cours disponibles et les cours propres aux options.
- Le maximum est défini par cours dans le programme local. L’exemple donné est **Comptabilité : 120 points** en Commerciale & Gestion ; il ne constitue pas une valeur nationale ou un défaut pour les autres programmes.

### Points restant à valider

1. Qui crée, vérifie et publie les sections, options et programmes dans chaque établissement ?
2. Les totaux reconstitués sont-ils confirmés par les maxima généraux des formulaires utilisés et par les grilles locales publiées ?
3. Pratique d’enseignement, Pratique professionnelle et activités complémentaires sont-elles toujours sans examen dans les programmes retenus ?
4. Le bloc école/TENASOSP `50 + 50` est-il confirmé pour la 8e CTEB et quel est son fondement officiel ?
5. Que signifie la zone `MEC 1000` visible sur certaines terminales techniques et à quelles sections s’applique-t-elle ?
6. Quelles règles de repêchage, de deuxième délibération, d’Application et de Conduite sont applicables à chaque programme ?
7. Quels sous-totaux de domaines doivent apparaître sur les bulletins et quels cours sont réellement facultatifs pour les élèves ?

**Prochaine preuve attendue :** un brouillon de grille initiale créé par l’établissement à partir du formulaire en usage, avec sections, options, cours, maxima et schémas d’examen. L’utilisateur n’a pas à deviner ces valeurs pendant le développement ; elles sont renseignées et validées dans la configuration locale.
