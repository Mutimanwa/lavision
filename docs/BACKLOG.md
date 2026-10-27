# Backlog GitHub — LaVision

**38 issues ouvertes — #2 à #39**
Généré à partir des issues réelles du dépôt le 27 octobre 2026.

Le [plan de réalisation](PLAN_DE_REALISATION.md) décrit les dépendances, les conditions de sortie et la stratégie. Le jalon, le domaine, la priorité et l’estimation figurent aussi dans le corps de chaque issue.

## Ordre de lecture

1. traiter les P0 dans l’ordre des jalons ;
2. terminer les P1 du noyau avant la recette M6 ;
3. activer les P2 selon les besoins après validation des dépendances.

## M0 — Cadrage et architecture

- **[#2 — Cadrer les processus et règles métier avec l’établissement pilote](https://github.com/Mutimanwa/lavision/issues/2)**
  `P0-critique` · `cadrage` · 8–12 jours-personnes · état : OPEN
- **[#3 — Valider l’architecture cible et rédiger les ADR](https://github.com/Mutimanwa/lavision/issues/3)**
  `P0-critique` · `cadrage` · 4–6 jours-personnes · état : OPEN
- **[#4 — Définir les personas, la matrice des permissions et la politique de confidentialité](https://github.com/Mutimanwa/lavision/issues/4)**
  `P0-critique` · `securite` · 5–8 jours-personnes · état : OPEN

## M1 — Socle production et sécurité

- **[#5 — Initialiser l’API modulaire, PostgreSQL et les migrations Prisma](https://github.com/Mutimanwa/lavision/issues/5)**
  `P0-critique` · `socle` · 6–9 jours-personnes · état : OPEN
- **[#6 — Implémenter l’authentification, les sessions et la MFA des profils sensibles](https://github.com/Mutimanwa/lavision/issues/6)**
  `P0-critique` · `securite` · 7–10 jours-personnes · état : OPEN
- **[#7 — Appliquer le RBAC, les portées et le journal d’audit côté serveur](https://github.com/Mutimanwa/lavision/issues/7)**
  `P0-critique` · `securite` · 8–12 jours-personnes · état : OPEN
- **[#8 — Créer les référentiels d’établissement, années scolaires et périodes](https://github.com/Mutimanwa/lavision/issues/8)**
  `P0-critique` · `socle` · 5–8 jours-personnes · état : OPEN
- **[#9 — Mettre en place les environnements Docker, la CI/CD et l’observabilité](https://github.com/Mutimanwa/lavision/issues/9)**
  `P0-critique` · `devops` · 6–9 jours-personnes · état : OPEN
- **[#39 — Créer le moteur de règles scolaires configurables et versionnées](https://github.com/Mutimanwa/lavision/issues/39)**
  `P0-critique` · `socle` · 7–11 jours-personnes · état : OPEN

## M2 — Élèves et pédagogie

- **[#10 — Implémenter les admissions, réinscriptions et affectations historisées](https://github.com/Mutimanwa/lavision/issues/10)**
  `P0-critique` · `eleves` · 8–12 jours-personnes · état : OPEN
- **[#11 — Centraliser les élèves, responsables et documents avec import contrôlé](https://github.com/Mutimanwa/lavision/issues/11)**
  `P0-critique` · `eleves` · 8–12 jours-personnes · état : OPEN
- **[#12 — Migrer les notes, bulletins et proclamations vers l’API avec règles versionnées](https://github.com/Mutimanwa/lavision/issues/12)**
  `P0-critique` · `pedagogie` · 10–15 jours-personnes · état : OPEN
- **[#13 — Gérer les présences, justificatifs et incidents de vie scolaire](https://github.com/Mutimanwa/lavision/issues/13)**
  `P1-important` · `pedagogie` · 6–9 jours-personnes · état : OPEN
- **[#14 — Créer les emplois du temps, affectations enseignantes et calendrier pédagogique](https://github.com/Mutimanwa/lavision/issues/14)**
  `P1-important` · `pedagogie` · 8–12 jours-personnes · état : OPEN

## M3 — Finances scolaires

- **[#15 — Créer les tarifs, factures, échéanciers, bourses et avoirs](https://github.com/Mutimanwa/lavision/issues/15)**
  `P0-critique` · `finance` · 8–12 jours-personnes · état : OPEN
- **[#16 — Implémenter les paiements, reçus et sessions de caisse transactionnels](https://github.com/Mutimanwa/lavision/issues/16)**
  `P0-critique` · `finance` · 10–15 jours-personnes · état : OPEN
- **[#17 — Gérer remboursements, dépenses et rapprochement financier](https://github.com/Mutimanwa/lavision/issues/17)**
  `P1-important` · `finance` · 7–10 jours-personnes · état : OPEN
- **[#18 — Produire les soldes, impayés, relances et rapports financiers](https://github.com/Mutimanwa/lavision/issues/18)**
  `P1-important` · `finance` · 6–9 jours-personnes · état : OPEN
- **[#19 — Ajouter les exports comptables et les tests d’invariants financiers](https://github.com/Mutimanwa/lavision/issues/19)**
  `P0-critique` · `finance` · 6–9 jours-personnes · état : OPEN
- **[#20 — Intégrer un canal de paiement externe avec idempotence et mode de secours](https://github.com/Mutimanwa/lavision/issues/20)**
  `P2-planifie` · `finance` · 8–15 jours-personnes · état : OPEN

## M4 — Personnel et paie

- **[#21 — Créer les dossiers du personnel, contrats et affectations](https://github.com/Mutimanwa/lavision/issues/21)**
  `P0-critique` · `personnel` · 7–10 jours-personnes · état : OPEN
- **[#22 — Gérer les présences, congés et validations RH](https://github.com/Mutimanwa/lavision/issues/22)**
  `P1-important` · `personnel` · 6–9 jours-personnes · état : OPEN
- **[#23 — Implémenter les variables de paie, bulletins et règlements](https://github.com/Mutimanwa/lavision/issues/23)**
  `P1-important` · `personnel` · 12–18 jours-personnes · état : OPEN
- **[#24 — Ajouter évaluations, recrutement et remplacements du personnel](https://github.com/Mutimanwa/lavision/issues/24)**
  `P2-planifie` · `personnel` · 7–12 jours-personnes · état : OPEN

## M5 — Portails et communication

- **[#25 — Livrer les portails parent et élève avec accès restreint](https://github.com/Mutimanwa/lavision/issues/25)**
  `P1-important` · `communication` · 10–14 jours-personnes · état : OPEN
- **[#26 — Créer les annonces, modèles et notifications e-mail transactionnelles](https://github.com/Mutimanwa/lavision/issues/26)**
  `P1-important` · `communication` · 6–9 jours-personnes · état : OPEN
- **[#27 — Intégrer SMS/WhatsApp et files de notification avec suivi des échecs](https://github.com/Mutimanwa/lavision/issues/27)**
  `P2-planifie` · `communication` · 7–12 jours-personnes · état : OPEN
- **[#28 — Ajouter devoirs, ressources pédagogiques et remises de travaux](https://github.com/Mutimanwa/lavision/issues/28)**
  `P2-planifie` · `communication` · 8–12 jours-personnes · état : OPEN

## M6 — Recette et mise en production

- **[#29 — Construire les tableaux de bord transversaux et exports réglementaires](https://github.com/Mutimanwa/lavision/issues/29)**
  `P1-important` · `reporting` · 7–11 jours-personnes · état : OPEN
- **[#30 — Durcir le desktop Tauri, la signature et les mises à jour](https://github.com/Mutimanwa/lavision/issues/30)**
  `P1-important` · `desktop` · 6–10 jours-personnes · état : OPEN
- **[#31 — Exécuter les tests E2E, sécurité, performance et recette métier](https://github.com/Mutimanwa/lavision/issues/31)**
  `P0-critique` · `qualite` · 12–18 jours-personnes · état : OPEN
- **[#32 — Automatiser sauvegardes, restauration et déploiement de production](https://github.com/Mutimanwa/lavision/issues/32)**
  `P0-critique` · `devops` · 7–11 jours-personnes · état : OPEN
- **[#33 — Préparer migration, formation, documentation et bascule pilote](https://github.com/Mutimanwa/lavision/issues/33)**
  `P0-critique` · `devops` · 8–14 jours-personnes · état : OPEN

## M7 — Services annexes et hors ligne évolué

- **[#34 — Implémenter la bibliothèque, les prêts et les retours](https://github.com/Mutimanwa/lavision/issues/34)**
  `P2-planifie` · `services` · 7–10 jours-personnes · état : OPEN
- **[#35 — Gérer l’inventaire, le patrimoine et la maintenance](https://github.com/Mutimanwa/lavision/issues/35)**
  `P2-planifie` · `services` · 8–12 jours-personnes · état : OPEN
- **[#36 — Gérer le transport scolaire et sa facturation associée](https://github.com/Mutimanwa/lavision/issues/36)**
  `P2-planifie` · `services` · 8–12 jours-personnes · état : OPEN
- **[#37 — Ajouter l’internat, la cantine et les activités scolaires](https://github.com/Mutimanwa/lavision/issues/37)**
  `P2-planifie` · `services` · 10–15 jours-personnes · état : OPEN
- **[#38 — Ajouter un mode hors ligne contrôlé avec synchronisation et conflits](https://github.com/Mutimanwa/lavision/issues/38)**
  `P2-planifie` · `desktop` · 15–25 jours-personnes · état : OPEN

## Voir sur GitHub

- [Toutes les issues](https://github.com/Mutimanwa/lavision/issues)
- [Issues ouvertes](https://github.com/Mutimanwa/lavision/issues?q=is%3Aissue+is%3Aopen)
- [Cahier des charges](CAHIER_DES_CHARGES.md)
- [Plan de réalisation](PLAN_DE_REALISATION.md)
