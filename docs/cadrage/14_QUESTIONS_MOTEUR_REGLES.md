# Questions à traiter avant les calculs du moteur de règles

**Référence :** issue #39 — étape préalable aux simulations et aux calculs métier
**Statut :** checklist de validation, sans valeur par défaut implicite

Cette liste ne demande pas à l’établissement de deviner une valeur inconnue. Chaque réponse doit être associée à un propriétaire, une source et, pour un calcul, un exemple fictif ou anonymisé. Tant qu’une réponse obligatoire manque, la règle reste `à configurer` et le résultat officiel concerné est bloqué.

## 1. Résolution et cycle de publication

| ID | Question à faire valider | Preuve attendue |
| --- | --- | --- |
| RUL-001 | Quel est l’ordre de priorité entre organisation, site, année, niveau, programme, classe et matière ? Comment traiter deux adaptations de même spécificité ? | tableau de résolution et cas de conflit |
| RUL-002 | Quelles portées sont autorisées pour chaque définition de règle ? | matrice définition × portées |
| RUL-003 | Quel fuseau et quelles bornes exactes utilisent les dates d’effet ? Comment annuler une publication future ? | exemple de publication planifiée |
| RUL-004 | Qui valide pédagogiquement, approuve et publie dans chaque domaine ? Quelles séparations de personnes sont obligatoires ? | circuit nominatif et délégations |
| RUL-005 | Quels contrôles faut-il rejouer lors d’une duplication entre années ou établissements ? | exemple de duplication et différences |
| RUL-006 | Quels cas normaux, frontières et incomplets sont obligatoires avant publication ? | jeu minimal de simulations par domaine |

## 2. Règles académiques

Les entrées ACA-002 à ACA-017 du registre restent la référence détaillée. Il faut notamment valider :

- barèmes, pas de saisie, conversion, coefficients et schémas avec ou sans examen ;
- distinction note absente, non-saisie, dispense et rattrapage ;
- précision, mode et étapes d’arrondi ;
- ex æquo, dossiers incomplets et rang provisoire ;
- seuils, notes éliminatoires, compensation, appréciations et délibérations ;
- poids annuels, examens nationaux, éligibilité en cours d’année et décision administrative ;
- sections, options, programmes, cours propres aux options et maxima propres à chaque établissement.

L’exemple Comptabilité à `120` points en Commerciale & Gestion reste un exemple local configurable, jamais une valeur globale. Les périodes observées dans les formulaires ne créent pas de coefficient ou de pondération implicite.

## 3. Finance et paie

Les entrées FIN-001 à FIN-011 et PAY-001 à PAY-007 restent ouvertes. Avant tout calcul ou document réel, il faut les références locales, les dates d’effet et les exemples anonymisés correspondants. Aucune règle fiscale, cotisation, pénalité, formule de paie ou durée de conservation n’est déduite du prototype.

## 4. Critère de passage à la simulation

Une simulation ne pourra être décrite comme représentative d’un domaine que lorsque :

1. le catalogue et les portées nécessaires sont renseignés ;
2. les règles obligatoires et leurs valeurs ont un propriétaire et une source ;
3. les exemples et résultats attendus sont relus ;
4. le même résolveur déterministe est utilisé pour le brouillon et la version publiée ;
5. aucune simulation ne crée de note, facture, paiement, paie ou notification officielle.
