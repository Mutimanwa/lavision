# Questionnaire de l’établissement pilote

**Version :** 0.3 — 27 octobre 2026
**Statut :** à renseigner avant de figer les règles de calcul
**Références :** issues #2 et #39

## 1. Comment répondre

- Répondre avec les identifiants des questions, par exemple `Q-STR-01 : ...`.
- Il est possible de répondre en plusieurs fois. Les questions Q-STR, Q-ETAT, Q-EVAL, Q-DEC et Q-DOC sont prioritaires pour le parcours académique.
- « Je ne sais pas » et « à confirmer avec… » sont des réponses utiles : elles créent un point ouvert au lieu d’une hypothèse implicite.
- Donner un exemple concret lorsque la réponse concerne un calcul.
- Utiliser des exemples fictifs ou anonymisés. Ne pas transmettre de données réelles d’élèves ou d’employés dans Git.
- Une réponse donnée dans ce questionnaire reste **proposée** jusqu’à validation par le responsable métier concerné.

## 2. Premiers éléments recueillis

Ces éléments proviennent du premier échange. Ils sont reformulés pour vérification et ne sont pas encore des règles officielles validées.

| Élément compris | Statut | Clarification encore nécessaire |
| --- | --- | --- |
| La première description évoquait un concours d’État après 7E/8E, puis un certificat et le choix d’une section/option. | reformulé après consultation des sources RDC | les sources identifient le TENASOSP à la fin de 8E ; l’EXETAT est une étape distincte en fin d’humanités |
| Le barème ou la pondération peut dépendre du cours, notamment selon la section et l’option. | à confirmer | distinguer note maximale, coefficient, pourcentage du volume horaire et moyenne de synthèse |
| Le choix initial était trimestres + séquences ; le calendrier RDC 2026–2027 retient 2 semestres et 4 périodes pour le secondaire. | base officielle à confirmer pour le pilote | préciser le découpage par niveau et le sens local de « séquence » |
| Une note manquante permet une moyenne provisoire. | proposition sélectionnée | dénominateur, visibilité et blocage éventuel de la publication |
| Les ex æquo utilisent le rang de compétition `1, 1, 3`. | proposition sélectionnée | moyenne arrondie ou non et comportement annuel |
| Un modèle vierge de bulletin 1ère/2e secondaire a été fourni. | structure analysée | bulletin rempli, arrondis, pourcentage, décisions et repêchage à confirmer |

La [base officielle RDC](07_BASE_OFFICIELLE_RDC.md) répond déjà à plusieurs questions de structure et d’épreuves nationales. L’[analyse du bulletin fourni](08_ANALYSE_BULLETIN_1E_2E.md) répond partiellement à Q-EVAL-04, Q-EVAL-06, Q-EVAL-07, Q-EVAL-11, Q-EVAL-13, Q-EVAL-15 et Q-DEC-15. Les réponses restantes doivent surtout préciser l’application locale, les options offertes et les règles figurant sur les bulletins.

## Bloc 1 — Structure scolaire, cycles, sections et options

**À renseigner en premier. Ce bloc détermine le référentiel auquel les règles sont rattachées.**

| ID | Question | Réponse |
| --- | --- | --- |
| Q-STR-01 | Quel est le nom officiel de chaque cycle et quelles classes contient-il ? | |
| Q-STR-02 | Quels sont les noms officiels et les abréviations des classes, notamment 7E et 8E ? | |
| Q-STR-03 | Les classes 7E et 8E appartiennent-elles au même cycle ? Quelle classe précède et quelle classe suit chacune ? | |
| Q-STR-04 | Combien de sites, sections, options et classes physiques faut-il gérer la première année ? | |
| Q-STR-05 | Quelle est la différence exacte entre **cycle**, **section**, **option**, **filière**, **programme** et **classe** dans votre organisation ? | |
| Q-STR-06 | La section est-elle choisie avant l’option ? Donner deux ou trois exemples réels de combinaisons section/option. | |
| Q-STR-07 | Une même option peut-elle exister dans plusieurs sections ? Son programme est-il alors identique ? | |
| Q-STR-08 | À quel moment l’élève choisit-il sa section et son option ? Qui valide ce choix ? | |
| Q-STR-09 | Peut-on changer de section ou d’option en cours d’année ? Comment reporter les notes déjà obtenues ? | |
| Q-STR-10 | Le programme des cours est-il défini par classe, section, option ou combinaison des trois ? | |
| Q-STR-11 | Un programme change-t-il selon l’année scolaire tout en conservant le même nom ? | |
| Q-STR-12 | Quels cours sont communs, obligatoires ou propres à une option ? | |
| Q-STR-13 | Un élève peut-il suivre un cours facultatif ou supplémentaire hors de son programme principal ? | |
| Q-STR-14 | Comment appelle-t-on l’enseignant responsable d’une classe et l’enseignant d’un cours ? | |
| Q-STR-15 | Plusieurs enseignants peuvent-ils donner le même cours à des groupes différents ? | |
| Q-STR-16 | Quelles capacités maximales et quels groupes ou salles doivent être respectés ? | |
| Q-STR-17 | Quelles langues sont utilisées pour les intitulés officiels des cycles, sections, options et cours ? | |
| Q-STR-18 | Existe-t-il un référentiel officiel ou interne qui décrit déjà cette structure ? Lequel et quelle version ? | |

## Bloc 2 — Épreuves nationales, certification et passage vers une section

**Ces questions distinguent le TENASOSP de l’EXETAT et ne présument pas d’une intégration externe.**

| ID | Question | Réponse |
| --- | --- | --- |
| Q-ETAT-01 | Quelles épreuves nationales concernent l’établissement : ENAFEP, TENASOSP, EXETAT ou autres ? | |
| Q-ETAT-02 | Pour chaque épreuve, quelle classe présente les candidats ? La source RDC situe le TENASOSP à la fin de 8E. | |
| Q-ETAT-03 | Quelles conditions l’établissement doit-il vérifier avant l’inscription à chaque épreuve ? | |
| Q-ETAT-04 | Qui inscrit les candidats et quelles pièces sont nécessaires ? | |
| Q-ETAT-05 | Quelles données chaque épreuve exige-t-elle : identité, numéro unique, école d’origine, cours, notes ou autres ? | |
| Q-ETAT-06 | LaVision doit-il seulement enregistrer la candidature, saisir un résultat officiel, importer un fichier ou échanger avec un système externe ? | |
| Q-ETAT-07 | Qui communique le résultat officiel et sous quelle forme ? | |
| Q-ETAT-08 | Quels résultats sont possibles : réussite, échec, ajournement, rattrapage ou autre ? | |
| Q-ETAT-09 | L’établissement calcule-t-il un résultat national ou se contente-t-il de reproduire une décision officielle externe ? | |
| Q-ETAT-10 | Quel certificat, brevet ou diplôme est délivré pour chaque épreuve, par quelle autorité et avec quel nom officiel ? | |
| Q-ETAT-11 | Qui prépare, signe et remet le document ? Existe-t-il un numéro ou registre officiel ? | |
| Q-ETAT-12 | Que se passe-t-il en cas d’échec, d’absence ou de réclamation ? | |
| Q-ETAT-13 | Le choix de la section et de l’option intervient-il avant ou après la publication du résultat ou du certificat ? | |
| Q-ETAT-14 | Le résultat du TENASOSP influence-t-il les sections ou options accessibles ? Selon quelles règles ? | |
| Q-ETAT-15 | Le résultat national doit-il apparaître sur le bulletin scolaire interne ou rester dans un dossier distinct ? | |
| Q-ETAT-16 | Quels documents officiels anonymisés peuvent servir de modèles pour ce parcours ? | |

## Bloc 3 — Cours, évaluations, barèmes et pondérations

**Bloc bloquant pour les calculs de bulletins. Répondre avec au moins un cours commun et un cours d’option.**

| ID | Question | Réponse |
| --- | --- | --- |
| Q-EVAL-01 | Pour un cours donné, que signifie exactement le nombre associé au cours : note maximale, coefficient, crédit, pourcentage ou autre ? | |
| Q-EVAL-02 | Donner un exemple complet pour un cours commun : intitulé, section/option, barème, coefficient et évaluations. | |
| Q-EVAL-03 | Donner le même exemple pour un cours d’option dont la règle est différente. | |
| Q-EVAL-04 | La note maximale est-elle propre à chaque évaluation, à chaque cours ou à chaque programme ? | |
| Q-EVAL-05 | Les notes sont-elles saisies sur leur barème brut puis converties ? Si oui, vers quel barème et avec quelle formule déterminée ? | |
| Q-EVAL-06 | Le coefficient d’un cours peut-il changer selon la section, l’option, la période ou l’année ? | |
| Q-EVAL-07 | Le calendrier RDC 2026–2027 prévoit 2 semestres/4 périodes au secondaire et 3 trimestres/6 périodes au primaire. Quel découpage chaque niveau de l’école applique-t-il ? | |
| Q-EVAL-08 | Que représente localement une « séquence » : séance didactique de 50 minutes, période du calendrier, évaluation ou ensemble d’évaluations ? | |
| Q-EVAL-09 | Quels types d’évaluations existent : interrogations, devoirs, travaux pratiques, examens ou autres ? | |
| Q-EVAL-10 | Comment obtient-on la note d’une séquence pour un cours ? Donner un exemple chiffré. | |
| Q-EVAL-11 | Comment obtient-on la note trimestrielle d’un cours à partir des séquences et de l’examen éventuel ? | |
| Q-EVAL-12 | Chaque composante possède-t-elle un poids ou un pourcentage ? Leur total doit-il être égal à une valeur précise ? | |
| Q-EVAL-13 | Comment calcule-t-on la moyenne générale d’un trimestre à partir des cours ? | |
| Q-EVAL-14 | Les cours facultatifs ou supplémentaires entrent-ils dans la moyenne générale ? | |
| Q-EVAL-15 | Comment calcule-t-on le résultat annuel : moyenne des trimestres, cumul des points, examen annuel ou autre ? | |
| Q-EVAL-16 | Les trimestres ont-ils des poids différents ? | |
| Q-EVAL-17 | Existe-t-il une note minimale par cours, une note éliminatoire ou une compensation entre cours ? | |
| Q-EVAL-18 | Quel est le seuil de réussite et s’applique-t-il au cours, au trimestre, à l’année ou à plusieurs niveaux ? | |
| Q-EVAL-19 | Quelles appréciations ou mentions existent et quelles sont leurs limites ? | |
| Q-EVAL-20 | Quand arrondit-on : évaluation, cours, séquence, trimestre, moyenne générale ou résultat annuel ? | |
| Q-EVAL-21 | Combien de décimales faut-il conserver dans les calculs et afficher sur chaque document ? | |
| Q-EVAL-22 | Quelle règle applique-t-on exactement à mi-chemin, par exemple pour `12,345` affiché avec deux décimales ? | |
| Q-EVAL-23 | Les enseignants saisissent-ils des points bruts, des pourcentages ou les deux ? | |
| Q-EVAL-24 | Qui peut créer une évaluation, modifier son barème et verrouiller les notes ? | |

## Bloc 4 — Notes manquantes, classement et décisions

| ID | Question | Réponse |
| --- | --- | --- |
| Q-DEC-01 | Une moyenne provisoire utilise-t-elle seulement les cours notés ou tous les coefficients du programme ? | |
| Q-DEC-02 | Comment distingue-t-on une note non saisie, une absence, une dispense et un zéro obtenu ? | |
| Q-DEC-03 | Qui voit la moyenne provisoire : enseignant, direction, secrétariat, parent ou élève ? | |
| Q-DEC-04 | Une note obligatoire manquante bloque-t-elle le bulletin individuel, toute la classe ou seulement la décision finale ? | |
| Q-DEC-05 | Que se passe-t-il après une absence justifiée ou un rattrapage ? | |
| Q-DEC-06 | Le rang de compétition `1, 1, 3` s’applique-t-il aux trimestres et à l’année ? | |
| Q-DEC-07 | Les ex æquo sont-ils déterminés avant ou après arrondi de la moyenne ? | |
| Q-DEC-08 | Les élèves incomplets apparaissent-ils dans le classement et, si oui, à quel endroit ? | |
| Q-DEC-09 | Quelles décisions officielles existent : passage, redoublement, ajournement, exclusion, orientation ou autres ? | |
| Q-DEC-10 | La décision est-elle entièrement calculée ou le conseil peut-il la modifier ? Avec quel motif et quelle approbation ? | |
| Q-DEC-11 | L’assiduité ou la conduite influence-t-elle la décision académique ? Selon quelle règle ? | |
| Q-DEC-12 | Comment gérer un élève arrivé ou parti en cours d’année dans les moyennes et le classement ? | |
| Q-DEC-13 | Qui peut rouvrir une période ou une proclamation publiée et selon quelle procédure ? | |
| Q-DEC-14 | Une rectification produit-elle une nouvelle version du document et comment les destinataires sont-ils informés ? | |
| Q-DEC-15 | Quelles règles s’appliquent à la première délibération, au repêchage et à la deuxième délibération prévus dans le calendrier secondaire ? | |

## Bloc 5 — Bulletins et autres documents académiques

| ID | Question | Réponse |
| --- | --- | --- |
| Q-DOC-01 | Quels documents sont produits par période et par année ? | |
| Q-DOC-02 | Quels champs figurent sur un bulletin : école, élève, classe, section, option, cours, notes, coefficients, rangs, appréciations et signatures ? | |
| Q-DOC-03 | Le bulletin affiche-t-il les notes brutes, converties, pondérées ou plusieurs représentations ? | |
| Q-DOC-04 | Faut-il afficher le détail des séquences et évaluations ou seulement la synthèse ? | |
| Q-DOC-05 | Quelles mentions obligatoires, signatures, cachets, langues et mises en page faut-il respecter ? | |
| Q-DOC-06 | Quel document atteste de la réussite au concours d’État et quel document atteste de la scolarité interne ? | |
| Q-DOC-07 | Quel est le contenu du procès-verbal de délibération ? | |
| Q-DOC-08 | Qui prépare, vérifie, valide, signe et diffuse chaque document ? | |
| Q-DOC-09 | Quand un parent ou un élève peut-il consulter ou télécharger chaque document ? | |
| Q-DOC-10 | Peut-on fournir un modèle anonymisé de chaque document avec un exemple recalculé ? | |

## Bloc 6 — Admission, inscription et dossiers

**À valider avant les issues #10 et #11 ; les réponses peuvent suivre le premier atelier académique.**

| ID | Question | Réponse |
| --- | --- | --- |
| Q-ADM-01 | Quelles sont les étapes réelles de la demande d’admission à l’inscription confirmée ? | |
| Q-ADM-02 | Qui peut accepter, refuser ou mettre un candidat en liste d’attente ? | |
| Q-ADM-03 | Quelles pièces sont obligatoires selon le niveau ou la situation de l’élève ? | |
| Q-ADM-04 | Comment attribue-t-on le matricule et reste-t-il identique pendant toute la scolarité ? | |
| Q-ADM-05 | Comment fonctionne la réinscription d’une année à la suivante ? | |
| Q-ADM-06 | Comment rattache-t-on plusieurs enfants à un même responsable sans créer de doublons ? | |
| Q-ADM-07 | Qui sont les payeurs et responsables autorisés à récupérer l’élève ou recevoir ses documents ? | |
| Q-ADM-08 | Comment gérer transfert, changement de classe, interruption, départ et retour ? | |
| Q-ADM-09 | Une inscription ou un acompte financier est-il nécessaire avant l’affectation définitive ? | |
| Q-ADM-10 | Quelles informations médicales sont réellement nécessaires et qui peut y accéder ? | |

## Bloc 7 — Assiduité et vie scolaire

| ID | Question | Réponse |
| --- | --- | --- |
| Q-ASS-01 | L’appel est-il fait par jour, par cours, par séance ou différemment selon le cycle ? | |
| Q-ASS-02 | Quels statuts sont nécessaires et lesquels comptent comme présence ou absence ? | |
| Q-ASS-03 | À partir de quand un élève est-il en retard et qui enregistre ce retard ? | |
| Q-ASS-04 | Quels justificatifs sont acceptés et qui les valide ? | |
| Q-ASS-05 | Quels seuils déclenchent une alerte et qui doit être prévenu ? | |
| Q-ASS-06 | Qui peut corriger un appel après validation ou clôture ? | |
| Q-ASS-07 | Quels incidents de conduite doivent être enregistrés et qui peut les consulter ? | |
| Q-ASS-08 | Les présences ou incidents apparaissent-ils sur le bulletin ? Sous quelle forme ? | |

## Bloc 8 — Finances scolaires

**À valider avec la finance avant M3. Ne pas utiliser les exemples du dossier comme valeurs officielles.**

| ID | Question | Réponse |
| --- | --- | --- |
| Q-FIN-01 | Quels frais existent et lesquels dépendent du niveau, de la section, de l’option ou d’un service ? | |
| Q-FIN-02 | Qui est facturé : élève, famille, responsable ou organisme ? | |
| Q-FIN-03 | Quelle devise et quelle précision sont utilisées sur chaque document ? | |
| Q-FIN-04 | Quand la facture est-elle créée et devient-elle officielle ? | |
| Q-FIN-05 | Quels paiements échelonnés et quelles dates d’échéance sont possibles ? | |
| Q-FIN-06 | Quelles bourses, remises ou exonérations existent et qui les approuve ? | |
| Q-FIN-07 | Plusieurs réductions peuvent-elles se cumuler ? Dans quel ordre ? | |
| Q-FIN-08 | Existe-t-il des majorations de retard ? Sur quelle base et avec quelles exceptions ? | |
| Q-FIN-09 | Comment répartir un paiement entre plusieurs factures ou enfants ? | |
| Q-FIN-10 | Comment traiter trop-perçu, avoir, remboursement et annulation ? | |
| Q-FIN-11 | Quels canaux de paiement et justificatifs sont utilisés ? | |
| Q-FIN-12 | Qui ouvre et clôture la caisse, et comment traite-t-on un écart ? | |
| Q-FIN-13 | Quelle numérotation officielle appliquer aux factures, reçus et avoirs ? | |
| Q-FIN-14 | Quels rapports, exports et seuils d’approbation sont nécessaires ? | |

## Bloc 9 — Personnel, congés et paie

**À valider avec RH et un expert local avant tout usage réel de la paie.**

| ID | Question | Réponse |
| --- | --- | --- |
| Q-RH-01 | Quels types de personnel, contrats, fonctions et affectations existent ? | |
| Q-RH-02 | Quels horaires et calendriers s’appliquent à chaque catégorie ? | |
| Q-RH-03 | Comment enregistre-t-on les présences et absences du personnel ? | |
| Q-RH-04 | Quels congés existent et comment acquiert-on, compte-t-on et reporte-t-on les soldes ? | |
| Q-RH-05 | Qui approuve chaque type d’absence ou de congé ? | |
| Q-RH-06 | Quelle est la périodicité de paie et quand les variables sont-elles clôturées ? | |
| Q-RH-07 | Quelles bases, primes, retenues et avances sont réellement utilisées ? | |
| Q-RH-08 | Comment gérer temps partiel, entrée, sortie et absence en cours de période ? | |
| Q-RH-09 | Quelles cotisations, taxes et règles d’arrondi sont applicables, avec quelles références officielles ? | |
| Q-RH-10 | Qui prépare, vérifie, valide et règle la paie ? | |
| Q-RH-11 | Comment corriger une paie déjà validée sans modifier son historique ? | |
| Q-RH-12 | Quel modèle anonymisé de bulletin et quel export de règlement faut-il reproduire ? | |

## Bloc 10 — Utilisateurs, accès et confidentialité

| ID | Question | Réponse |
| --- | --- | --- |
| Q-SEC-01 | Quelles personnes ou fonctions utilisent LaVision et sur quels sites ? | |
| Q-SEC-02 | Qui peut consulter ou modifier chaque type de dossier ? | |
| Q-SEC-03 | Quelles classes et matières un enseignant peut-il voir en dehors de ses affectations ? | |
| Q-SEC-04 | Qui peut publier les résultats, valider une remise, clôturer une caisse ou valider une paie ? | |
| Q-SEC-05 | Quelles opérations exigent deux personnes distinctes ? | |
| Q-SEC-06 | Qui peut modifier et publier les règles académiques, financières et RH ? | |
| Q-SEC-07 | Quels profils doivent utiliser une authentification multifacteur ? | |
| Q-SEC-08 | Quelles données sont particulièrement sensibles et qui peut exporter chaque catégorie ? | |
| Q-SEC-09 | Quelles durées de conservation et quelles procédures d’accès ou de rectification faut-il appliquer ? | |
| Q-SEC-10 | Quels prestataires externes reçoivent des données et pour quelle finalité ? | |

## Bloc 11 — Volumes, reprise des données et exploitation

| ID | Question | Réponse |
| --- | --- | --- |
| Q-OPS-01 | Combien d’élèves, responsables, enseignants, employés et utilisateurs simultanés faut-il prévoir ? | |
| Q-OPS-02 | Quel est le volume approximatif de notes, paiements et documents par année ? | |
| Q-OPS-03 | Quelles données historiques faut-il reprendre et sous quels formats ? | |
| Q-OPS-04 | Quelles données peuvent rester dans les archives existantes sans migration détaillée ? | |
| Q-OPS-05 | Quels postes, systèmes et navigateurs sont utilisés, notamment pour Windows/Tauri ? | |
| Q-OPS-06 | Quelle est la qualité de la connexion et quelles opérations doivent continuer en cas de coupure ? | |
| Q-OPS-07 | Quels canaux d’hébergement, e-mail, SMS, paiement ou stockage sont déjà disponibles ? | |
| Q-OPS-08 | Qui administrera le système, assurera le support et formera les utilisateurs ? | |
| Q-OPS-09 | Quelles sauvegardes et quels délais de reprise sont nécessaires ? | |
| Q-OPS-10 | Quelle période pilote et quels utilisateurs peuvent participer à la recette ? | |

## Ordre de traitement recommandé

1. **Maintenant :** Q-STR, Q-ETAT, Q-EVAL, Q-DEC et Q-DOC.
2. **Avant les développements élèves :** Q-ADM et les accès associés de Q-SEC.
3. **Avant chaque domaine :** Q-ASS, Q-FIN et Q-RH avec leurs propriétaires respectifs.
4. **Avant l’architecture finale et la mise en production :** Q-SEC et Q-OPS.

Pour le prochain échange, utiliser le bulletin fourni et obtenir en priorité : un exemplaire rempli anonymisé, les règles de pourcentage/arrondi/rang, les conditions et le calcul du repêchage (**Q-DEC-15**), ainsi qu’un modèle pour une option des humanités. Confirmer aussi que les branches et maxima relevés correspondent au programme actuel. Les réponses générales déjà publiées par le Ministère ne doivent pas être redemandées sans nécessité.
