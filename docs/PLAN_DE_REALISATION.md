# Plan de réalisation — LaVision

**Version :** 1.1 — 27 octobre 2026
**Document associé :** [Cahier des charges](CAHIER_DES_CHARGES.md)
**Backlog GitHub :** [Index des 38 issues](BACKLOG.md) · [Vue GitHub](https://github.com/Mutimanwa/lavision/issues)

> Les durées sont des estimations de cadrage, pas des engagements contractuels. Elles supposent une équipe pluridisciplinaire et des utilisateurs métier disponibles pour répondre et tester.

## 1. Stratégie

Le projet est découpé en huit jalons. Chaque jalon produit une version démontrable et vérifiable.

La stratégie repose sur quatre règles :

1. **sécuriser les fondations avant les modules métier** ;
2. **livrer le noyau élèves–pédagogie–finance–personnel avant les services annexes** ;
3. **ne jamais faire de l’interface la seule source de vérité** : règles et contrôles vivent aussi dans l’API et PostgreSQL ;
4. **faire tester chaque module par son utilisateur métier** avant de passer au suivant.

## 2. Équipe cible recommandée

| Fonction | Engagement indicatif | Responsabilité |
| --- | ---: | --- |
| Product owner / représentant de la direction | 0,5 ETP | priorités, arbitrages, recette |
| Référents scolarité, finance et RH | temps planifié chaque semaine | règles, exemples, tests et validation |
| Tech lead / architecte | 1 ETP | architecture, sécurité, revues |
| Développeurs full-stack | 2 à 3 ETP | API, frontend, migrations et tests |
| UX/UI designer | 0,5 ETP | parcours, prototypes, accessibilité |
| QA / recette | 1 ETP | automatisation, non-régression, recette |
| DevOps | 0,2 à 0,4 ETP | CI/CD, environnements, sauvegardes, supervision |
| Expert paie/fiscalité locale | à la demande | validation des paramètres légaux |

Avec une seule personne développeuse, le périmètre complet doit être fortement séquencé et le calendrier réévalué. La paie, la sécurité et la finance ne doivent pas être validées sans compétences métier externes.

## 3. Vue d’ensemble des jalons

| Jalon | Durée indicative | Résultat | Condition de sortie |
| --- | ---: | --- | --- |
| M0 — Cadrage et architecture | 2–3 sem. | processus, règles et architecture validés | décisions signées et exemples métier disponibles |
| M1 — Socle production et sécurité | 5–6 sem. | API, PostgreSQL, comptes, RBAC et environnements | accès serveur testés et staging déployé |
| M2 — Élèves et pédagogie | 6–8 sem. | inscription à proclamation sur données centralisées | parcours scolarité validé par le secrétariat et les enseignants |
| M3 — Finances scolaires | 6–8 sem. | facturation, caisse, impayés et rapports | scénarios financiers et invariants validés |
| M4 — Personnel et paie | 5–6 sem. | dossiers, congés, présences et paie | paie d’exemple validée par RH/expert local |
| M5 — Portails et communication | 4–6 sem. | accès famille/élève et notifications | permissions et envois testés de bout en bout |
| M6 — Recette et mise en production | 4–6 sem. | noyau durci, desktop, sauvegardes et bascule | recette signée et restauration réussie |
| M7 — Services annexes et hors ligne | 8–12 sem. | modules optionnels et synchronisation évoluée | activation indépendante par service |

**Noyau en production :** environ 32 à 41 semaines avec l’équipe cible et les validations disponibles.
**Suite complète :** environ 9 à 12 mois si certains travaux M7 sont parallélisés.
**Charge brute du backlog :** environ 292 à 433 jours-personnes, incluant développement et recette mais pas les temps d’attente. Cette charge ne se convertit pas directement en durée calendaire.
Le calendrier est revu à la fin de M0 et de chaque jalon.

## 4. Backlog détaillé

Les priorités ont le sens suivant :

- **P0 — critique :** obligatoire pour la première production ;
- **P1 — important :** nécessaire pour une exploitation complète du domaine ;
- **P2 — optionnel planifié :** à activer après le noyau ou selon le besoin.

Les estimations sont exprimées en jours-personnes de développement/recette, hors temps d’attente des validations.

### M0 — Cadrage et architecture

| Issue | Priorité | Estimation | Résultat attendu |
| --- | --- | ---: | --- |
| Cadrer les processus et règles métier avec l’établissement pilote | P0 | 8–12 j | processus et exemples signés |
| Valider l’architecture cible et rédiger les ADR | P0 | 4–6 j | architecture et choix techniques traçables |
| Définir les personas, la matrice des permissions et la politique de confidentialité | P0 | 5–8 j | rôles, portées et règles de conservation validés |

**Sortie :** aucune règle critique ne reste implicite ; les modèles de documents et les cas limites sont disponibles.

### M1 — Socle production et sécurité

| Issue | Priorité | Estimation | Résultat attendu |
| --- | --- | ---: | --- |
| Initialiser l’API modulaire, PostgreSQL et les migrations Prisma | P0 | 6–9 j | API versionnée et schéma initial |
| Implémenter l’authentification, les sessions et la MFA des profils sensibles | P0 | 7–10 j | comptes et sessions sécurisés |
| Appliquer le RBAC, les portées et le journal d’audit côté serveur | P0 | 8–12 j | permissions centralisées et audit |
| Créer les référentiels d’établissement, années scolaires et périodes | P0 | 5–8 j | configuration structurée et historisée |
| Créer le moteur de règles scolaires configurables et versionnées | P0 | 7–11 j | règles par établissement, simulation et historique préservé |
| Mettre en place les environnements Docker, la CI/CD et l’observabilité | P0 | 6–9 j | staging reproductible et supervision initiale |

**Sortie :** un utilisateur ne peut effectuer que les opérations autorisées par l’API ; la base, les migrations et les logs sont exploitables sur staging. Les règles métier sont configurables par établissement et leurs versions publiées sont immuables.

### M2 — Élèves et pédagogie

| Issue | Priorité | Estimation | Résultat attendu |
| --- | --- | ---: | --- |
| Implémenter les admissions, réinscriptions et affectations historisées | P0 | 8–12 j | cycle annuel complet sans perte d’historique |
| Centraliser les élèves, responsables et documents avec import contrôlé | P0 | 8–12 j | dossiers et fichiers sécurisés, imports vérifiables |
| Migrer les notes, bulletins et proclamations vers l’API avec règles versionnées | P0 | 10–15 j | calculs serveur et publications figées |
| Gérer les présences, justificatifs et incidents de vie scolaire | P1 | 6–9 j | assiduité et corrections auditées |
| Créer les emplois du temps, affectations enseignantes et calendrier pédagogique | P1 | 8–12 j | planification avec conflits détectés |

**Sortie :** le parcours inscription → notes → bulletin → proclamation fonctionne sur PostgreSQL, avec rôles et historique. Un référentiel enseignant minimal est disponible, puis enrichi dans M4.

### M3 — Finances scolaires

| Issue | Priorité | Estimation | Résultat attendu |
| --- | --- | ---: | --- |
| Créer les tarifs, factures, échéanciers, bourses et avoirs | P0 | 8–12 j | facturation configurable et exacte |
| Implémenter les paiements, reçus et sessions de caisse transactionnels | P0 | 10–15 j | encaissement, reçu et clôture de caisse |
| Gérer remboursements, dépenses et rapprochement financier | P1 | 7–10 j | opérations inverses et contrôle des justificatifs |
| Produire les soldes, impayés, relances et rapports financiers | P1 | 6–9 j | créances et indicateurs financiers |
| Ajouter les exports comptables et les tests d’invariants financiers | P0 | 6–9 j | exports contrôlés et non-régression |
| Intégrer un canal de paiement externe avec idempotence et mode de secours | P2 | 8–15 j | connecteur sandbox puis production après contrat |

**Sortie :** les montants sont exacts, chaque opération sensible est traçable, les soldes sont recalculables et les tests de concurrence passent.

### M4 — Personnel et paie

| Issue | Priorité | Estimation | Résultat attendu |
| --- | --- | ---: | --- |
| Créer les dossiers du personnel, contrats et affectations | P0 | 7–10 j | dossier employé historisé et sécurisé |
| Gérer les présences, congés et validations RH | P1 | 6–9 j | workflow d’absence et soldes de congés |
| Implémenter les variables de paie, bulletins et règlements | P1 | 12–18 j | paie configurable et validée sur exemples |
| Ajouter évaluations, recrutement et remplacements du personnel | P2 | 7–12 j | suivi RH complémentaire |

**Sortie :** les droits RH sont isolés, les calculs sont reproductibles et les règles locales ont été validées avant toute paie réelle.

### M5 — Portails et communication

| Issue | Priorité | Estimation | Résultat attendu |
| --- | --- | ---: | --- |
| Livrer les portails parent et élève avec accès restreint | P1 | 10–14 j | consultation des seules personnes rattachées |
| Créer les annonces, modèles et notifications e-mail transactionnelles | P1 | 6–9 j | communications ciblées et traçables |
| Intégrer SMS/WhatsApp et files de notification avec suivi des échecs | P2 | 7–12 j | canaux externes résilients |
| Ajouter devoirs, ressources pédagogiques et remises de travaux | P2 | 8–12 j | échanges pédagogiques dans les portails |

**Sortie :** un parent ne voit que ses enfants et seulement les informations publiées ; les envois sont journalisés et testés avec des comptes de démonstration.

### M6 — Recette et mise en production

| Issue | Priorité | Estimation | Résultat attendu |
| --- | --- | ---: | --- |
| Construire les tableaux de bord transversaux et exports réglementaires | P1 | 7–11 j | pilotage consolidé et documents validés |
| Durcir le desktop Tauri, la signature et les mises à jour | P1 | 6–10 j | installateur testé et mécanisme de mise à jour |
| Exécuter les tests E2E, sécurité, performance et recette métier | P0 | 12–18 j | rapports de qualité et anomalies corrigées |
| Automatiser sauvegardes, restauration et déploiement de production | P0 | 7–11 j | production supervisée et restauration prouvée |
| Préparer migration, formation, documentation et bascule pilote | P0 | 8–14 j | utilisateurs formés et mise en service contrôlée |

**Sortie :** mise en production du noyau uniquement après recette signée, restauration réussie et plan de retour arrière disponible.

### M7 — Services annexes et hors ligne évolué

| Issue | Priorité | Estimation | Résultat attendu |
| --- | --- | ---: | --- |
| Implémenter la bibliothèque, les prêts et les retours | P2 | 7–10 j | module bibliothèque activable |
| Gérer l’inventaire, le patrimoine et la maintenance | P2 | 8–12 j | actifs et mouvements historisés |
| Gérer le transport scolaire et sa facturation associée | P2 | 8–12 j | lignes, affectations et frais intégrés |
| Ajouter l’internat, la cantine et les activités scolaires | P2 | 10–15 j | services et consommations liés aux familles |
| Ajouter un mode hors ligne contrôlé avec synchronisation et conflits | P2 | 15–25 j | offline limité à des scénarios explicitement validés |

**Sortie :** chaque service peut être activé séparément sans modifier les invariants du noyau. Le hors ligne n’est livré qu’après tests de conflits et de sécurité.

## 5. Dépendances structurantes

```text
M0 Cadrage
   │
   ▼
M1 API + sécurité + règles configurables
   │
   ▼
M2 Élèves + pédagogie
   │
   ├──────────────► M3 Finance
   │                    │
   │                    ▼
   └──────────────► M4 Personnel et paie
                        │
                        ▼
                   M5 Portails et communication
                        │
                        ▼
                   M6 Production du noyau
                        │
                        ▼
                   M7 Services optionnels
```

Quelques travaux UX, tests et documentation avancent en parallèle. Aucun module ne contourne ces dépendances de données et de sécurité.

## 6. Organisation du travail

### 6.1 Cadence

- sprint de deux semaines ;
- revue de démonstration avec les utilisateurs concernés ;
- point métier court chaque semaine ;
- revue des risques et du périmètre à chaque jalon ;
- priorisation des anomalies avant les options ;
- aucune nouvelle fonctionnalité majeure pendant la recette finale sans arbitrage explicite.

### 6.2 Branches et revues

- une branche par issue ou petit groupe cohérent ;
- Pull Request liée à l’issue ;
- revue obligatoire pour sécurité, finance, paie et migrations ;
- CI web et desktop verte avant fusion ;
- migrations testées sur une copie anonymisée ;
- déploiement staging automatique, production manuellement approuvée.

## 7. Definition of Ready

Une issue est prête lorsque :

- l’objectif et les utilisateurs concernés sont identifiés ;
- les règles et exemples sont disponibles ;
- les maquettes nécessaires sont validées ;
- les permissions sont précisées ;
- les critères d’acceptation sont testables ;
- les dépendances et données nécessaires sont connues ;
- l’estimation reste compatible avec un sprint, sinon l’issue est découpée.

## 8. Definition of Done

Une issue est terminée lorsque :

- le code et la revue sont fusionnés ;
- les tests unitaires et d’intégration pertinents passent ;
- les autorisations serveur sont testées ;
- les migrations et erreurs sont documentées ;
- l’accessibilité et les principaux formats d’écran sont vérifiés ;
- les logs ne contiennent pas de données sensibles ;
- la documentation utilisateur/technique est mise à jour ;
- la démonstration staging est acceptée ;
- aucune anomalie critique ou majeure connue ne reste ouverte ;
- les critères de l’issue sont cochés avec des preuves.

## 9. Qualité et sécurité transversales

Chaque jalon comprend :

- tests de permissions positives et négatives ;
- vérification des données fictives et absence de secrets ;
- mise à jour de la matrice de menaces pour les opérations sensibles ;
- revue des performances des requêtes principales ;
- sauvegarde avant migration risquée ;
- tests d’impression pour les documents ajoutés ;
- suivi des décisions dans un ADR lorsque l’architecture change.

## 10. Stratégie de versions

| Version | Contenu |
| --- | --- |
| `0.2` | socle API, sécurité et règles configurables sur staging |
| `0.3` | élèves et pédagogie centralisés |
| `0.4` | finance scolaire |
| `0.5` | personnel et paie validée |
| `0.6` | portails et communication |
| `1.0` | noyau en production après recette |
| `1.x` | services annexes, intégrations et hors ligne évolué |

## 11. Indicateurs de suivi

- vélocité et capacité réelle par sprint ;
- pourcentage d’issues P0 terminées ;
- couverture des règles critiques et des permissions ;
- anomalies par sévérité et âge ;
- temps de build et taux d’échec CI ;
- scénarios de recette acceptés / total ;
- résultats des tests de restauration ;
- adoption par rôle après le pilote ;
- temps de traitement des opérations critiques.

## 12. Gestion des changements

Toute demande nouvelle est évaluée selon :

1. valeur utilisateur et obligation réglementaire ;
2. impact sur les données et permissions ;
3. dépendances et risque de retard ;
4. coût de test et d’exploitation ;
5. possibilité de l’activer comme option après la version 1.0.

Le cahier des charges reste la référence. Une modification de périmètre met à jour le document, les issues et l’estimation du jalon concerné.
