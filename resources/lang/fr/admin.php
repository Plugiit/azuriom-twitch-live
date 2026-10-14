<?php

return [
    'nav' => [
        'title' => 'Twitch Live',
        'streamers' => 'Streamers',
        'settings' => 'Paramètres',
    ],

    'permission' => 'Gérer les streamers Twitch',

    'streamers' => [
        'title' => 'Streamers',
        'create' => 'Ajouter un streamer',
        'edit' => 'Modifier le streamer :login',
        'pending' => 'Chaînes déclarées par les joueurs',
        'pending_info' => 'Ces chaînes ont été déclarées depuis le profil des joueurs et attendent votre validation.',
        'empty' => 'Aucun streamer pour le moment.',
        'login' => 'Identifiant Twitch',
        'login_help' => 'Le nom présent dans l\'adresse twitch.tv/nom (4 à 25 lettres, chiffres ou _).',
        'member' => 'Membre du site lié',
        'member_help' => 'Pseudo exact du membre Azuriom (facultatif).',
        'featured' => 'Mis en avant',
        'featured_help' => 'Les streamers mis en avant passent en premier et leur lecteur Twitch est intégré à la page.',
        'status' => 'Statut',
        'live' => 'En live',
        'approve' => 'Valider',
        'reject' => 'Refuser',
        'approved' => 'La chaîne :login a été validée.',
        'rejected' => 'La chaîne :login a été refusée.',
        'declared' => 'Déclarée le',
    ],

    'settings' => [
        'title' => 'Paramètres Twitch Live',
        'credentials' => 'Application Twitch',
        'credentials_info' => 'Créez une application sur dev.twitch.tv/console/apps (URL de redirection : http://localhost, catégorie « Website Integration »), puis copiez son Client ID et un secret. Ils sont stockés chiffrés.',
        'client_id' => 'Client ID',
        'client_secret' => 'Client Secret',
        'secret_saved' => 'Un secret est enregistré. Laissez vide pour le conserver.',
        'secret_empty' => 'Aucun secret enregistré.',
        'remove_secret' => 'Supprimer le secret enregistré',
        'filters' => 'Filtres',
        'only_minecraft' => 'Afficher seulement les lives dont le jeu est Minecraft',
        'title_keyword' => 'Mot-clé obligatoire dans le titre du live',
        'title_keyword_help' => 'Par exemple « PixelCraft ». Laissez vide pour ne pas filtrer. Insensible à la casse.',
        'display' => 'Affichage',
        'cache_minutes' => 'Durée du cache',
        'cache_minutes_help' => 'Fréquence maximale des appels à l\'API Twitch. La tâche planifiée rafraîchit les lives dès que le cache expire.',
        'minutes' => 'minutes',
        'navbar_badge' => 'Afficher un compteur de lives à côté du lien « Streamers en direct » du menu',
        'player_submissions' => 'Permettre aux joueurs de déclarer leur chaîne depuis leur profil',
        'demo_mode' => 'Mode démonstration',
        'demo_mode_help' => 'Simule des lives à partir de la liste des streamers, sans appeler Twitch. À désactiver en production.',
        'demo_enabled' => 'Le mode démonstration est activé : les lives affichés sur le site sont simulés et l\'API Twitch n\'est pas appelée.',
        'widget' => 'Widget pour votre thème',
        'widget_info' => 'Affichez les lives en cours dans n\'importe quelle vue Blade :',
    ],

    'status' => [
        'title' => 'État',
        'live' => 'Lives en cours',
        'updated' => 'Dernière mise à jour',
        'never' => 'Jamais',
        'error' => 'Dernière erreur',
        'not_configured' => 'Identifiants Twitch manquants : aucun live ne peut être récupéré.',
        'scheduler' => 'La tâche planifiée « plugiit-twitch-live:refresh » rafraîchit les lives automatiquement si le planificateur Laravel est configuré (cron « php artisan schedule:run » chaque minute). Sinon, les lives sont rafraîchis à la demande lors des visites, selon la durée du cache.',
    ],

    'refresh' => [
        'button' => 'Rafraîchir maintenant',
        'success' => 'Lives rafraîchis : :count en direct.',
        'error' => 'Impossible de joindre Twitch : :error',
    ],

    'logs' => [
        'plugiit-twitch-live-streamers' => [
            'created' => 'Ajout du streamer #:id',
            'updated' => 'Mise à jour du streamer #:id',
            'deleted' => 'Suppression du streamer #:id',
        ],
        'settings' => 'Mise à jour des paramètres Twitch Live',
    ],
];
