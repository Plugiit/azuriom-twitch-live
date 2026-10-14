# Plugiit Twitch Live

Plugin Azuriom qui affiche les streamers de votre communauté actuellement en direct sur Twitch.

## Fonctionnalités

- Page publique `/plugiit-twitch-live` : cartes des lives (miniature, titre, spectateurs, durée, jeu, membre lié),
  lecteur Twitch intégré du streamer **mis en avant** (embed `player.twitch.tv` avec `parent=` = domaine courant),
  liste des streamers hors ligne.
- Widget « En live maintenant » incluable dans un thème.
- Compteur de lives optionnel dans la barre de navigation, ajouté par un middleware à côté de tout lien
  du menu vers la page des lives (et sur le menu déroulant parent).
- Les joueurs peuvent déclarer leur chaîne depuis leur profil (carte « Ma chaîne Twitch ») ; un administrateur
  la valide ou la refuse.
- Administration (permission `plugiit-twitch-live.admin`) :
  - identifiants de l'application Twitch (Client ID / Client Secret **chiffrés** en base avec `encrypt()`) ;
  - liste des streamers : login Twitch, membre Azuriom lié, mis en avant, statut (validé / en attente / refusé) ;
  - filtres : seulement les lives Minecraft et/ou mot-clé obligatoire dans le titre ;
  - durée du cache, compteur dans le menu, déclarations des joueurs ;
  - **mode démonstration** (désactivé par défaut, signalé en admin et sur la page) qui simule des lives sans appeler Twitch ;
  - état (nombre de lives, dernière mise à jour, dernière erreur) et bouton « Rafraîchir maintenant ».

## Intégration Twitch

- Jeton d'application via *client credentials* (`POST https://id.twitch.tv/oauth2/token`), mis en cache jusqu'à
  son expiration (moins 5 minutes) et renouvelé automatiquement si Twitch répond 401.
- `GET /helix/streams?user_login=…` par lots de 100 logins.
- `GET /helix/users?login=…` pour les noms et avatars (mis en cache 24 h et enregistrés sur les streamers).
- Erreurs réseau ou API : la page reste fonctionnelle et affiche le **dernier état connu** avec un avertissement ;
  l'erreur est journalisée et visible dans l'administration. Nouvel essai au bout d'une minute.

## Rafraîchissement

- Tâche planifiée `plugiit-twitch-live:refresh` enregistrée dans le planificateur Laravel (chaque minute,
  sans chevauchement). Elle n'appelle Twitch que lorsque le cache a expiré (`--force` pour forcer).
  Nécessite le cron Azuriom habituel : `* * * * * php /chemin/azuriom/artisan schedule:run`.
- Sans cron, les lives sont rafraîchis à la demande lors des visites, au plus une fois par durée de cache.
- Le compteur du menu n'appelle jamais Twitch : il lit uniquement le cache.

## Installation

1. Copiez le dossier dans `plugins/plugiit-twitch-live` et activez le plugin dans **Admin › Extensions**.
2. Créez une application sur <https://dev.twitch.tv/console/apps> (catégorie « Website Integration »,
   URL de redirection `http://localhost`), puis renseignez le Client ID et un secret dans
   **Admin › Twitch Live › Paramètres**.
3. Ajoutez vos streamers dans **Admin › Twitch Live › Streamers**.
4. Ajoutez la page au menu : **Admin › Navigation**, type « Plugin », route « Streamers en direct ».

## Widget pour les thèmes

```blade
@include('plugiit-twitch-live::widget')
@include('plugiit-twitch-live::widget', ['limit' => 3, 'hideWhenEmpty' => true])
```

## Tests

Tests PHPUnit du client Helix avec `Http::fake()` (parsing des réponses, lots de 100, cache et
renouvellement du jeton, erreurs) :

```sh
vendor/bin/phpunit plugins/plugiit-twitch-live/tests
```

## URL

| Page | URL |
|---|---|
| Lives | `/plugiit-twitch-live` |
| Déclaration de chaîne (POST/DELETE, connecté) | `/plugiit-twitch-live/channel` |
| Administration | `/admin/plugiit-twitch-live/streamers`, `/admin/plugiit-twitch-live/settings` |
