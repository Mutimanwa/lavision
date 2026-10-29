# Supervision initiale

`prometheus.yml` et `alerts.yml` sont des configurations à monter dans une instance Prometheus administrée par l’exploitant. Le token de métriques est lu depuis `/run/secrets/lavision_metrics_token`, jamais depuis un fichier versionné.

Les cibles sont internes au réseau Compose : `api:3001` et `worker:3002`. Ne publiez pas ces ports sur Internet. Les règles couvrent la disponibilité des deux processus, les dépendances Redis/PostgreSQL vues par le worker et le taux de 5xx.

Aucun Alertmanager, fournisseur d’erreurs externes ou destinataire n’est configuré ici : ces choix, leurs secrets et leurs obligations de confidentialité restent à valider avant la production. En l’état, les événements d’erreur structurés et les métriques sont disponibles, mais une alerte n’est pas réputée envoyée tant que cette chaîne externe n’a pas été testée.
