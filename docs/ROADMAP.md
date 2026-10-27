# Feuille de route — LaVision

La planification opérationnelle est désormais centralisée dans les documents suivants afin d’éviter les doublons :

- **[Cahier des charges complet](CAHIER_DES_CHARGES.md)** : périmètre fonctionnel, règles, données, sécurité et architecture cible ;
- **[Plan de réalisation](PLAN_DE_REALISATION.md)** : jalons, estimations, dépendances, équipe et critères de sortie ;
- **[Backlog GitHub](BACKLOG.md)** : liens directs vers les 38 issues #2 à #39.

## Socle déjà réalisé

- migration du prototype PHP/HTML vers Next.js, TypeScript et Tailwind CSS ;
- parcours de démonstration inscription, élèves, classes, notes, bulletins et proclamation ;
- présences, paramètres, exports CSV et sauvegarde JSON ;
- moteur académique testé ;
- export web statique et build desktop Tauri Windows en CI.

Ces acquis restent un prototype local. Ils ne remplacent pas les prérequis de sécurité, de base centralisée et de recette du plan.

**Prochaine étape :** conduire les ateliers M0 à partir du [dossier de cadrage métier](cadrage/README.md), faire valider les exemples et les permissions, puis finaliser les ADR avant l’initialisation de l’API.

## Cible par jalons

| Jalon | Finalité | Issues |
| --- | --- | --- |
| M0 — Cadrage et architecture | règles métier, ADR, permissions et confidentialité | #2–#4 |
| M1 — Socle production et sécurité | API, PostgreSQL, authentification, RBAC, règles configurables et environnements | #5–#9 et #39 |
| M2 — Élèves et pédagogie | inscription à proclamation sur données centralisées | #10–#14 |
| M3 — Finances scolaires | facturation, paiements, caisse, impayés et contrôle | #15–#20 |
| M4 — Personnel et paie | employés, contrats, congés, présences et paie | #21–#24 |
| M5 — Portails et communication | accès familles/élèves, notifications et devoirs | #25–#28 |
| M6 — Recette et mise en production | reporting, desktop, qualité, sauvegardes et bascule | #29–#33 |
| M7 — Services annexes et hors ligne | bibliothèque, inventaire, transport, internat et synchronisation | #34–#38 |

## Versions cibles

- `0.2` : socle API, sécurité et règles configurables sur staging ;
- `0.3` : élèves et pédagogie centralisés ;
- `0.4` : finance scolaire ;
- `0.5` : personnel et paie validée ;
- `0.6` : portails et communication ;
- `1.0` : noyau en production après recette ;
- `1.x` : services annexes, intégrations et hors ligne évolué.

Les durées, responsabilités et conditions de validation sont détaillées dans le plan de réalisation.
