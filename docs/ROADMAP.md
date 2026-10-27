# Feuille de route vers la production

## Phase 1 — Socle migré et parcours académique (cette itération)

- [x] audit du prototype PHP/HTML ;
- [x] Next.js + TypeScript + Tailwind CSS ;
- [x] inscription et dossier élève ;
- [x] classes, matières et coefficients ;
- [x] notes, bulletins et proclamation ;
- [x] présences et paramètres ;
- [x] exports CSV, impression et sauvegarde JSON ;
- [x] tests, lint et build statique ;
- [x] socle Tauri et build Windows en CI ;
- [x] préparation au déploiement web.

## Phase 2 — Backend et sécurité

- [ ] choisir l’API et la base (recommandation : PostgreSQL) ;
- [ ] créer les migrations et les contraintes d’intégrité ;
- [ ] authentification sécurisée et récupération de compte ;
- [ ] rôles direction, secrétariat, enseignant et consultation ;
- [ ] autorisations serveur sur chaque opération ;
- [ ] journal d’audit des notes, décisions et publications ;
- [ ] import initial des élèves depuis les fichiers existants ;
- [ ] sauvegardes automatiques et test de restauration.

## Phase 3 — Couverture administrative complète

- [ ] années scolaires et historisation des affectations ;
- [ ] enseignants, personnel et attribution des cours ;
- [ ] emplois du temps ;
- [ ] documents d’inscription et stockage de fichiers ;
- [ ] frais de scolarité, paiements et reçus ;
- [ ] notifications aux responsables ;
- [ ] rapports académiques et administratifs configurables ;
- [ ] règles annuelles et conseils de fin d’année.

## Phase 4 — Desktop production

- [ ] repository SQLite local via commandes Rust si hors ligne requis ;
- [ ] synchronisation avec le backend et gestion des conflits ;
- [ ] mises à jour signées ;
- [ ] signature de code Windows ;
- [ ] tests d’installation Windows 10/11 et WebView2 ;
- [ ] procédure de sauvegarde/restauration sur poste.

## Décisions métier à confirmer avec l’établissement

- barème officiel : 20, 100 ou autre ;
- périodes : trimestres, semestres ou sessions ;
- formule de moyenne annuelle ;
- règles d’admission, de redoublement et d’ex æquo ;
- mentions et appréciations officielles ;
- modèle réglementaire du bulletin et du procès-verbal ;
- langues du bulletin et matières par niveau ;
- niveau d’accès de chaque profil utilisateur.

La version actuelle utilise volontairement des valeurs configurables et des règles simples : notes sur 20, trois trimestres, seuil par défaut de 10/20 et dossier complet obligatoire.
