# ADR-005 — Worker et tâches asynchrones

- **Date :** 29 octobre 2026
- **Statut :** proposé
- **Décideurs attendus :** tech lead et DevOps

## Contexte

Les imports, exports volumineux, notifications et antivirus ne doivent pas bloquer une requête HTTP. Un simple traitement dans le processus API serait perdu au redémarrage et pourrait dupliquer un effet lors d’un retry.

## Décision proposée

Utiliser un **worker séparé**, **Redis/BullMQ** pour les files et une **outbox transactionnelle PostgreSQL**. L’écriture métier et l’événement outbox sont confirmés dans la même transaction. Un relais publie ensuite les jobs ; les consommateurs sont idempotents.

Règles :

- petite opération synchrone pour la décision immédiate, asynchrone uniquement pour les effets différables ;
- clé d’idempotence et identifiant d’agrégat dans chaque job, sans données personnelles inutiles ;
- retries bornés avec backoff, délai aléatoire et file d’échec inspectable ;
- verrou ou contrôle de version pour empêcher deux workers d’appliquer deux fois le même effet ;
- aucune notification ou transaction externe considérée comme « exactly once » : l’état externe et les identifiants de remise sont enregistrés ;
- planification centralisée pour éviter que chaque réplique déclenche le même traitement ;
- métriques de profondeur, âge du plus ancien job, échecs et durée de traitement.

Les requêtes de paiement et les publications gardent une écriture PostgreSQL synchrone. Le worker peut ensuite produire un reçu, notifier ou actualiser une projection sans changer rétroactivement la décision confirmée.

## Alternatives évaluées

- **jobs en mémoire dans NestJS** : rejeté en production pour la perte au redémarrage et l’absence de reprise fiable ;
- **file uniquement dans PostgreSQL** : solution de repli à réévaluer si Redis est trop coûteux, mais le cahier des charges retient Redis/BullMQ pour les besoins prévus ;
- **événement publié avant validation SQL** : rejeté, car il peut annoncer une opération annulée ;
- **microservice par consommateur** : rejeté ; le worker partage le code et le déploiement du monolithe.

## Conséquences

- Redis devient un composant exploité et supervisé ;
- l’outbox ajoute un relais et un nettoyage à gérer ;
- les effets sont idempotents et observables plutôt que supposés uniques ;
- l’interface affiche les états `en attente`, `en cours`, `réussi` et `échoué` sans prétendre qu’un job est terminé trop tôt.

## Critères de vérification

- arrêt du worker pendant un traitement puis reprise sans double effet ;
- perte temporaire de Redis sans perte de l’écriture métier ou de l’outbox ;
- un job en échec définitif est visible et rejouable par un rôle autorisé ;
- les logs ne contiennent ni notes, ni token, ni contenu de fichier sensible.
