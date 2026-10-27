# Base officielle RDC pour le cadrage académique

**Date de consultation :** 27 octobre 2026
**Source principale :** site du Ministère de l’Éducation Nationale et Nouvelle Citoyenneté de la République démocratique du Congo
**Statut :** référentiel national à confirmer pour l’établissement pilote

## 1. Objet et limites

Ce document rassemble les informations du site [edu-nc.gouv.cd](https://edu-nc.gouv.cd/) utiles à LaVision. Il évite de demander à l’utilisateur des réponses déjà publiées par le Ministère.

Les informations ci-dessous décrivent le référentiel national consulté. Elles ne prouvent pas encore :

- que l’établissement pilote relève bien de ce référentiel pour chacun de ses niveaux ;
- quelles sections et options il organise réellement ;
- quelles règles locales de saisie, de publication ou de délibération il applique ;
- quels maxima, coefficients ou formules figurent sur ses bulletins officiels.

Les dates et règles d’une année scolaire doivent rester versionnées. Elles ne doivent jamais être écrites en dur dans le code.

## 2. Structure du parcours scolaire

| Étape | Correspondance retenue dans les sources | Conséquence pour LaVision |
| --- | --- | --- |
| Primaire | six années, de la 1re à la 6e | niveau distinct du CTEB |
| Éducation de Base | huit années : primaire + Cycle terminal de l’Éducation de Base | le parcours ne se limite pas au primaire |
| CTEB | 7e et 8e années de l’Éducation de Base | deux classes d’un même cycle terminal |
| Correspondance secondaire | 7e = 1ère secondaire ; 8e = 2e secondaire dans les programmes nationaux | prévoir des alias officiels sans créer deux classes différentes |
| Humanités | parcours après l’Éducation de Base | section/option et programme deviennent structurants |
| Humanités générales et techniques | quatre années selon les pages et programmes consultés | cycle long à distinguer du professionnel court |
| Humanités professionnelles | trois années selon la page des niveaux | ne pas imposer quatre années à toutes les filières |

Le programme de mathématiques 8e EB présente également les humanités générales comme quatre années, avec deux années de cycle moyen et deux années de cycle supérieur, ainsi que des sections subdivisées en options.

### Exemples de sections/options cités dans le programme 2019

Les sources mentionnent notamment des sections pédagogique, littéraire et scientifique, ainsi que des options telles que Pédagogie générale, Latin-Philosophie, Latin-Grec, Mathématique-Physique et Chimie-Biologie. Cette liste est **illustrative et non exhaustive**. Elle ne constitue pas le catalogue à activer automatiquement dans une école.

## 3. Ne pas confondre les trois épreuves nationales

| Épreuve | Niveau concerné | Résultat indiqué par les pages officielles |
| --- | --- | --- |
| ENAFEP | fin de 6e primaire | certificat de fin d’études primaires et accès au CTEB |
| TENASOSP | fin de 8e EB / fin du CTEB | certificat de fin de l’Éducation de Base et orientation vers les humanités |
| EXETAT | fin des humanités générales, techniques ou professionnelles | diplôme d’État |

Le **TENASOSP** est le Test National de Sélection et d’Orientation Scolaire et Professionnelle. Il ne doit pas être enregistré dans LaVision comme l’Examen d’État de fin de secondaire.

Le programme 8e EB de 2019 emploie aussi le terme **brevet** pour le test sanctionnant le secondaire général/CTEB. La page actuelle des épreuves certificatives parle de **certificat de fin de l’Éducation de Base**. Le modèle exact du document et sa dénomination à afficher doivent donc être confirmés avec un document officiel récent.

### Conséquence fonctionnelle

LaVision doit pouvoir distinguer :

- une candidature à une épreuve nationale ;
- les données d’inscription exigées pour l’édition concernée ;
- le résultat officiel publié par l’autorité compétente ;
- le document ou certificat associé ;
- l’orientation ou le choix de filière qui en découle ;
- les résultats scolaires internes de l’établissement.

Aucune intégration technique avec une plateforme ministérielle n’est supposée à ce stade. Le mode de reprise des résultats — saisie, import ou échange futur — reste une décision du projet.

## 4. Calendrier officiel 2026–2027

Le calendrier signé le 26 juin 2026 fixe la rentrée au **1er septembre 2026** et la clôture au **2 juillet 2027**. Il prévoit **222 jours ouvrables de classe** pour les enseignements primaire, secondaire et technique.

### Différence importante entre primaire et secondaire

| Niveau | Découpage publié pour 2026–2027 |
| --- | --- |
| Primaire | 3 trimestres et 6 périodes |
| Secondaire et technique | 2 semestres et 4 périodes |

Pour le secondaire et le technique :

| Élément | Date ou période publiée |
| --- | --- |
| Premier semestre | 1er septembre 2026 au 17 février 2027 |
| Deuxième semestre | 22 février au 2 juillet 2027 |
| Examens du premier semestre | 9 au 17 février 2027 |
| Examens du deuxième semestre | 24 mai au 1er juin 2027 |
| Première délibération | 10 juin 2027 |
| Examens de repêchage | 15 et 16 juin 2027 |
| Deuxième délibération | 18 juin 2027 |
| Proclamation officielle et clôture | 2 juillet 2027 |
| TENASOSP | 3 et 4 juin 2027 |
| EXETAT — session ordinaire | 21 au 24 juin 2027 |

Ces dates sont un exemple de configuration annuelle issue du calendrier national. Elles ne constituent pas des valeurs permanentes pour les années suivantes.

### Terminologie à ne pas mélanger

- Le calendrier emploie **période** pour les subdivisions de l’année.
- Le programme 8e EB indique qu’une **séquence didactique** dure cinquante minutes.
- Une séquence didactique n’est donc pas automatiquement équivalente à une période d’évaluation ou à un bulletin.

L’établissement doit préciser le sens qu’il donne localement à « séquence » avant de modéliser les écrans de notes.

## 5. Programmes, cours et pondération

La page des programmes nationaux fournit des programmes et guides par année, notamment pour :

- Mathématiques ;
- Sciences de la Vie et de la Terre ;
- Sciences Physiques, Technologie et TIC.

Elle utilise les correspondances suivantes :

| Nomenclature EB | Nomenclature secondaire | Nomenclature des humanités scientifiques |
| --- | --- | --- |
| 7e EB | 1ère secondaire | — |
| 8e EB | 2e secondaire | — |
| 3e secondaire | — | 1ère année des humanités |
| 4e secondaire | — | 2e année des humanités |
| 5e secondaire | — | 3e année des humanités |
| 6e secondaire | — | 4e année des humanités |

Le programme 8e EB de mathématiques présente un régime pédagogique avec les disciplines, les heures hebdomadaires et leur pourcentage du volume horaire. Il indique un volume hebdomadaire total de 36 heures pour la 7e et la 8e dans ce document de 2019.

**Attention :** un pourcentage du volume horaire n’est pas automatiquement le coefficient utilisé pour calculer une moyenne. Les sources consultées jusqu’ici ne suffisent pas à définir les maxima de notes, les cotes par cours ou les formules des bulletins. Ces informations doivent être relevées sur les bulletins et instructions officielles applicables.

Le même programme distingue :

- l’évaluation prédictive, notamment pour l’orientation ;
- l’évaluation formative, incluant les interrogations et examens semestriels ;
- l’évaluation certificative pour les examens et tests de fin de cycle.

## 6. Conséquences pour le modèle de configuration

Le référentiel doit permettre de représenter, sans les confondre :

| Concept | Rôle |
| --- | --- |
| Cycle | primaire, CTEB, humanités générales/techniques/professionnelles |
| Année d’études | 7e EB, 8e EB, 3e secondaire, etc., avec alias éventuels |
| Filière | générale, technique ou professionnelle |
| Section | regroupement pédagogique applicable aux humanités |
| Option | spécialisation dans une section |
| Programme annuel | ensemble des cours et règles applicables à une année d’études et une option |
| Cours du programme | discipline, volume horaire, enseignant et schéma d’évaluation applicables |
| Schéma d’évaluation | maxima, poids et règles de calcul à valider séparément du volume horaire |
| Calendrier annuel | semestres/trimestres, périodes, examens, délibérations et clôtures |
| Épreuve nationale | ENAFEP, TENASOSP, EXETAT ou autre épreuve officielle configurée |
| Résultat officiel | décision externe datée et référencée, distincte du calcul interne |

Les programmes nationaux doivent pouvoir servir de **modèles importables ou reproductibles**, tandis que l’établissement conserve ses classes physiques, enseignants et adaptations autorisées. Toute adaptation reste soumise au moteur de règles versionnées de l’issue #39.

## 7. Points qui restent à faire valider

1. L’établissement applique-t-il bien le calendrier secondaire à deux semestres et quatre périodes pour 2026–2027 ?
2. Que signifie « séquence » dans sa pratique : séance de 50 minutes, période du calendrier ou évaluation ?
3. Quelles filières, sections et options sont réellement ouvertes dans chaque site ?
4. Quel programme national et quelle année d’édition utilise-t-il pour chaque cours ?
5. Quels maxima et coefficients apparaissent sur les bulletins pour un cours commun et un cours d’option ?
6. Comment calcule-t-on les résultats de période, de semestre et d’année ?
7. Quelles conditions déterminent passage, échec, repêchage et deuxième délibération ?
8. Comment l’école reçoit-elle et conserve-t-elle les résultats TENASOSP et EXETAT ?
9. Quel document récent confirme la dénomination du certificat ou brevet de fin d’Éducation de Base ?
10. Quelles données d’identification nationale doivent réellement être conservées et qui peut y accéder ?

Un modèle vierge de bulletin 1ère/2e secondaire fourni par l’utilisateur fait l’objet d’une [analyse séparée](08_ANALYSE_BULLETIN_1E_2E.md). Il confirme la structure à deux semestres et quatre périodes, mais ne remplace pas une instruction officielle sur les calculs et le repêchage.

## 8. Sources officielles consultées

- [Accueil du MINEDU-NC](https://edu-nc.gouv.cd/)
- [Documentation et réformes](https://edu-nc.gouv.cd/documentation)
- [Niveaux d’enseignement](https://edu-nc.gouv.cd/levels)
- [Épreuves certificatives](https://edu-nc.gouv.cd/certifying-exams)
- [TENASOSP et ENAFEP](https://edu-nc.gouv.cd/tenasosp-enafep)
- [EXETAT](https://edu-nc.gouv.cd/exetat)
- [Programmes nationaux](https://edu-nc.gouv.cd/national-programmes)
- [Calendrier scolaire 2026–2027](https://edu-nc.gouv.cd/docs/1784193331_CALENDRIER%20SCOLAIRE%202026%20%E2%80%93%202027%20F_copy.pdf)
- [Programme éducatif de mathématiques — 8e année de l’Éducation de Base, 2019](https://edu-nc.gouv.cd/wp-content/uploads/2022/09/PE8-MATH-16062019_DIPROMAD_MEPSP.pdf)
- [Programme SPTIC — 2e année des Humanités Scientifiques, 2021](https://edu-nc.gouv.cd/wp-content/uploads/2022/09/MEPST_Programmes-Educatifs_4e-Sec_2-HS_SPTIC.pdf)

En cas de divergence entre une page de présentation, un programme ancien et une instruction récente, l’instruction applicable à l’année concernée doit être identifiée et validée avant implémentation.
