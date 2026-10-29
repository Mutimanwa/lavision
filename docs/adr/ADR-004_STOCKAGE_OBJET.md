# ADR-004 — Stockage objet et fichiers

- **Date :** 29 octobre 2026
- **Statut :** proposé
- **Décideurs attendus :** tech lead, sécurité et DevOps

## Contexte

Les dossiers d’élèves, documents du personnel et justificatifs ne doivent pas être exposés par une URL permanente. Les fichiers peuvent contenir un malware, un type différent de celui annoncé ou des données hors de la portée de l’utilisateur.

## Décision proposée

Utiliser un **stockage S3 compatible privé**, chiffré par le fournisseur, avec métadonnées et statut de sécurité dans PostgreSQL. Les buckets publics et l’accès direct avec des identifiants S3 côté client sont interdits.

Flux proposé :

1. l’API vérifie permission, portée, taille et catégorie de document ;
2. elle crée une clé opaque, un checksum attendu et une URL de téléversement signée de courte durée ;
3. le fichier arrive dans une zone de **quarantaine** avec le statut `pending` ;
4. le worker contrôle taille réelle, signature du type, checksum et antivirus ;
5. seul un fichier `available` peut recevoir une URL de téléchargement signée après une nouvelle vérification d’accès ;
6. les statuts `rejected`, `quarantined` et `deleted` restent traçables sans exposer le contenu.

Les noms d’origine sont traités comme du texte non fiable. Les clés sont aléatoires et ne contiennent pas de nom d’élève. Les exports sensibles suivent en plus le motif et l’approbation définis dans SEC-20.

## Alternatives évaluées

- **disque local de l’API** : réservé au développement ; rejeté en production répliquée pour la durabilité et le partage avec le worker ;
- **bucket public** : rejeté ;
- **fichiers binaires dans PostgreSQL** : non retenu par défaut pour les gros documents, tout en gardant les métadonnées transactionnelles en base ;
- **téléversement direct immédiatement visible** : rejeté tant que l’antivirus et les contrôles ne sont pas terminés.

## Conséquences

- besoin d’un stockage S3 local pour les tests et d’un fournisseur de production à sélectionner ;
- disponibilité différée d’un fichier pendant l’analyse ;
- cycle de vie, chiffrement et sauvegarde à configurer selon la politique de conservation validée ;
- aucune durée de rétention ou de validité des URLs n’est fixée par cet ADR.

## Critères de vérification

- un utilisateur hors portée ne peut ni créer ni utiliser une URL signée ;
- un fichier en quarantaine ou rejeté n’est pas téléchargeable ;
- un changement d’extension ne contourne pas les contrôles ;
- la suppression logique, l’expiration et la restauration sont testées sans fuite vers un bucket public.
