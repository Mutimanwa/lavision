# Processus et questions de cadrage

**Statut :** schémas de travail à valider pendant les ateliers
**Référence :** issue #2

Pour chaque processus, compléter le propriétaire, l’approbateur, les pièces utilisées et les exceptions. Les diagrammes ci-dessous décrivent le périmètre attendu, pas une procédure déjà approuvée.

## 1. Admission, inscription et réinscription

**Propriétaire métier :** à nommer
**Approbateur :** à nommer
**Participants :** secrétariat, direction, famille, finance si un frais est exigé

```text
Demande ou réinscription
        │
        ▼
Vérification du dossier ── incomplet ──► complément demandé
        │ complet
        ▼
Décision d’admission ── refus ──► décision motivée et archivée
        │ accepté
        ▼
Création/récupération de l’élève et des responsables
        │
        ▼
Affectation annuelle à une classe
        │
        ├──────────────► tarification et échéancier éventuels
        ▼
Inscription confirmée et pièces remises
```

### Points à confirmer

- Qui peut admettre un élève et qui peut seulement enregistrer la demande ?
- Quelles pièces sont obligatoires, conditionnelles ou facultatives ?
- Une inscription peut-elle être confirmée avant paiement ? Si un acompte est requis, comment est-il calculé ?
- Comment éviter le doublon d’un élève ou d’un responsable déjà connu ?
- Comment gérer liste d’attente, capacité atteinte, refus et désistement ?
- Comment se déroulent réinscription, transfert interne, changement de classe et retour après interruption ?
- Quel événement déclenche la facturation et quel événement rend l’élève actif ?
- Quelles corrections restent possibles après confirmation et qui les approuve ?

### Exceptions à tester

Dossier incomplet, jumeaux avec les mêmes responsables, élève déjà inscrit l’année précédente, transfert en cours d’année, départ puis retour, classe complète, annulation avant le premier cours.

## 2. Organisation de l’année et des référentiels

**Propriétaire métier :** à nommer
**Approbateur :** à nommer

```text
Création de l’année → sites/niveaux → sections et options
       → programmes annuels → cours disponibles et cours propres aux options
       → maxima et schémas d’évaluation par cours → classes et affectations
       → périodes et calendrier → affectations enseignantes → activation
       → clôture des périodes → clôture de l’année
```

**Orientation confirmée le 29/10/2026 :** chaque établissement configure ses sections, ses options, les cours disponibles, les cours propres aux options et leurs maxima. Les formulaires du recueil 2024–2025, toujours utilisés cette année, servent de modèles de départ ; les valeurs locales restent versionnées et suivent préparation → validation pédagogique → approbation → publication. Les référentiels scolaires et les règles académiques peuvent avoir des validateurs différents.

### Points à confirmer

- Qui crée, vérifie et publie les sections, options et programmes de chaque établissement ?
- Quels cours sont communs, propres à une option ou réellement facultatifs pour les élèves ?
- Quel maximum et quel schéma d’évaluation chaque cours utilise-t-il dans chaque programme annuel ?
- Quels types de périodes sont utilisés : trimestres, semestres, séquences ou combinaison ?
- Une période possède-t-elle des dates de saisie et de publication distinctes de ses dates pédagogiques ?
- Quels référentiels varient selon le site, le niveau, la filière ou le programme ?
- Qui peut rouvrir une période ou une année clôturée, avec quel motif et quelle durée ?
- Comment traiter une matière ajoutée ou retirée alors que des notes existent déjà ?
- Les coefficients appartiennent-ils à la matière, au programme, à la classe ou à l’évaluation ?

## 3. Évaluations, notes et corrections

**Propriétaire métier :** à nommer
**Approbateur :** à nommer
**Participants :** enseignant, professeur principal, direction/scolarité

```text
Création de l’évaluation → saisie en brouillon → contrôle des absences
       → validation par matière → vérification du dossier de classe
       → clôture → publication éventuelle
                         │
                         └─ correction autorisée → nouvelle version + audit
```

### Points à confirmer

- Quels types d’évaluation entrent dans chaque moyenne ?
- Une note est-elle saisie sur un barème propre à l’évaluation puis convertie, ou directement sur le barème du bulletin ?
- Comment distinguer note absente, élève absent, dispense et zéro réellement obtenu ?
- Qui valide les notes et qui peut les rouvrir après clôture ?
- Une correction après publication entraîne-t-elle un nouveau bulletin, une mention de rectification ou une procédure externe ?
- Quels contrôles bloquent la publication : notes manquantes, effectif incohérent, moyenne hors limites, appréciation absente ?
- Les familles voient-elles les évaluations individuelles ou seulement le bulletin publié ?

## 4. Calcul du bulletin et de la proclamation

**Propriétaire métier :** à nommer
**Approbateur :** à nommer

```text
Notes validées + version publiée des règles
                    │
                    ▼
       Contrôle de complétude du dossier
                    │
          ┌─────────┴─────────┐
          ▼                   ▼
      Complet              Incomplet
          │                   │
          ▼                   ▼
 Moyennes/rangs/décision   traitement à définir
          │                   │
          └─────────┬─────────┘
                    ▼
       Simulation du conseil de classe
                    │
                    ▼
       Validation et version officielle figée
                    │
                    ▼
         Publication aux destinataires
```

### Points à confirmer

- Comment agréger les évaluations, les matières et les périodes ?
- À quel moment arrondit-on et avec quelle précision ?
- Comment classer les ex æquo et les dossiers incomplets ?
- Existe-t-il des notes éliminatoires, compensations, rattrapages ou conditions d’assiduité ?
- Comment décide-t-on passage, redoublement, ajournement ou orientation ?
- Le conseil peut-il déroger au calcul automatique ? Si oui, avec quel motif, quelle approbation et quelle trace ?
- Quels résultats figurent sur le bulletin périodique, le bulletin annuel et le procès-verbal ?

**Attention :** les comportements actuels du prototype — moyenne sur 20, trois trimestres, blocage des dossiers incomplets et rang de compétition — sont des hypothèses à valider, non des règles acquises.

## 5. Présences, retards et incidents

**Propriétaire métier :** à nommer
**Approbateur :** à nommer

```text
Appel → présent/absent/retard/autre statut → justificatif éventuel
     → validation ou rejet du justificatif → alerte éventuelle
     → correction autorisée et historisée → statistiques de période
```

### Points à confirmer

- L’appel est-il journalier, par séance, ou les deux selon le niveau ?
- Quels statuts sont nécessaires et lesquels comptent comme absence ?
- Un retard est-il aussi une présence ? Plusieurs retards peuvent-ils produire une absence ?
- Qui saisit et qui valide un justificatif ? Quel délai est admis ?
- Quels seuils déclenchent une alerte et par quel canal ?
- Qui peut consulter un incident de conduite ou une information médicale liée à une absence ?
- Comment corriger un appel après clôture sans effacer la valeur initiale ?

## 6. Tarification et facturation

**Propriétaire métier :** à nommer
**Approbateur :** à nommer
**Participants :** comptable, direction, secrétariat

```text
Référentiel de frais → tarif applicable → inscription/service
       → bourse/remise/avoir éventuel → facture ou échéancier
       → validation et numérotation → envoi au responsable
```

### Points à confirmer

- Qui est le débiteur : élève, famille, responsable précis ou organisme payeur ?
- Quels tarifs dépendent de l’année, du niveau, de la classe, du programme ou de services optionnels ?
- Comment cumuler plusieurs bourses ou remises sans appliquer deux fois la même réduction ?
- Les remises sont-elles fixes, proportionnelles, plafonnées ou soumises à approbation ?
- Quand une facture devient-elle officielle et comment la corriger : avoir, nouvelle facture ou autre pièce ?
- Quels événements créent une échéance et comment gérer une inscription en cours de période ?
- Quel est le format de numérotation par site et par année ?

## 7. Encaissement, caisse et remboursement

**Propriétaire métier :** à nommer
**Approbateur :** à nommer
**Participants :** caissier, comptable, direction selon seuil

```text
Ouverture de caisse → identification du payeur et des factures
       → saisie du paiement → contrôle → confirmation transactionnelle
       → affectation aux factures → reçu → clôture et rapprochement
```

### Points à confirmer

- Quels canaux sont acceptés et quelles références sont obligatoires ?
- Qui peut confirmer un paiement manuel ou un paiement sans référence externe ?
- Comment répartir un paiement entre plusieurs factures ou enfants ?
- Comment traiter trop-perçu, avoir disponible, paiement rejeté et remboursement ?
- Une annulation est-elle une contre-opération ? Qui l’autorise ?
- Comment ouvrir et clôturer une caisse et traiter un écart ?
- Quels seuils nécessitent une seconde approbation ?

## 8. Personnel, présences et congés

**Propriétaire métier :** à nommer
**Approbateur :** à nommer

```text
Dossier employé → contrat et affectation → calendrier/temps de travail
       → présence ou absence → demande de congé → approbation
       → solde mis à jour → variables transmises à la paie si applicable
```

### Points à confirmer

- Quels types de contrats, fonctions et affectations coexistent ?
- Quelles données sont accessibles à la direction, à RH et aux responsables hiérarchiques ?
- Les congés sont-ils comptés en jours ouvrés, ouvrables ou calendaires ?
- Quelles sont les règles d’acquisition, de report, d’anticipation et de solde négatif ?
- Qui approuve selon le type d’absence et la durée ?
- Quelles absences ont une incidence sur la paie et comment cette incidence est-elle justifiée ?

## 9. Préparation, validation et règlement de la paie

**Propriétaire métier :** à nommer
**Approbateur :** direction + expert paie local à nommer

```text
Contrat applicable + présences + variables approuvées
       → préparation du brouillon → contrôles → calcul avec règles versionnées
       → validation indépendante → bulletin figé → règlement
       → export/preuve de paiement → archivage restreint
```

### Points à confirmer

- Quelle est la période de paie et quelle date fige les variables ?
- Comment calculer entrée, sortie, temps partiel, absence et régularisation ?
- Quelles primes, retenues, avances et cotisations existent réellement ?
- Quels éléments sont imposables ou soumis à cotisation selon la réglementation applicable ?
- Quels arrondis sont appliqués, à quelle étape et avec quelle autorité de référence ?
- Quelle séparation entre préparateur, validateur et personne qui règle ?
- Comment corriger une paie validée sans réécrire le bulletin historique ?

**Aucune formule fiscale ou sociale ne doit être développée pour un usage réel avant validation documentée par le spécialiste local.**

## 10. Publication et diffusion des documents officiels

**Propriétaire métier :** à nommer
**Approbateur :** à nommer

```text
Données validées + modèle + version des règles
       → prévisualisation → contrôle → publication → diffusion
       → archivage de la version exacte → rectification éventuelle tracée
```

### Points à confirmer

- Quels documents exigent signature, cachet, mention ou identifiant officiel ?
- Quelle langue et quelle mise en page sont obligatoires ?
- Qui peut publier, retirer de la diffusion ou rectifier chaque document ?
- Que voit exactement chaque destinataire et à partir de quelle date ?
- Comment vérifier l’authenticité sans exposer publiquement les données du document ?
- Combien de temps conserver chaque catégorie et qui autorise l’archivage ou la destruction ?

## 11. Fiche de validation d’un processus

À dupliquer pour chaque parcours retenu :

| Champ | À compléter |
| --- | --- |
| Processus | |
| Propriétaire métier | |
| Approbateur | |
| Participants et rôles | |
| Document ou source de référence | |
| Parcours normal validé le | |
| Exceptions validées le | |
| Exemples associés | |
| Permissions associées | |
| Décisions associées | |
| Points encore ouverts | |
| Validateur et date | |
