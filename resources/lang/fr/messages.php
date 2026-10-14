<?php

return [
    'title' => 'Streamers en direct',
    'subtitle' => 'Les membres de la communauté actuellement en live sur Twitch.',

    'live' => 'En direct',
    'viewers' => '{0} Aucun spectateur|{1} 1 spectateur|[2,*] :count spectateurs',
    'since' => 'Depuis :duration',
    'watch' => 'Regarder sur Twitch',
    'member' => 'Membre : :name',
    'featured' => 'À l\'affiche',
    'none' => 'Aucun streamer de la communauté n\'est en direct pour le moment. Revenez plus tard !',
    'offline_title' => 'Nos streamers',
    'offline' => 'Hors ligne',
    'updated' => 'Mis à jour :time',
    'stale' => 'Twitch est momentanément injoignable : affichage du dernier état connu.',
    'demo' => 'Mode démonstration : les lives affichés sont simulés.',
    'demo_player' => 'Le lecteur Twitch de :name s\'affichera ici en direct. Il n\'est pas chargé en mode démonstration.',
    'not_configured' => 'L\'intégration Twitch n\'est pas encore configurée.',

    'widget' => [
        'title' => 'En live maintenant',
        'more' => 'Voir tous les lives',
        'none' => 'Personne n\'est en live pour le moment.',
    ],

    'badge' => ':count en live',

    'fields' => [
        'login' => 'chaîne Twitch',
    ],

    'profile' => [
        'title' => 'Ma chaîne Twitch',
        'info' => 'Vous streamez sur le serveur ? Déclarez votre chaîne : après validation par l\'équipe, vos lives apparaîtront sur le site.',
        'login' => 'Identifiant de la chaîne',
        'login_help' => 'Le nom présent dans l\'adresse twitch.tv/votre_nom.',
        'submit' => 'Déclarer ma chaîne',
        'update' => 'Modifier',
        'remove' => 'Retirer ma chaîne',
        'submitted' => 'Votre chaîne a été envoyée, elle sera visible après validation par l\'équipe.',
        'removed' => 'Votre chaîne Twitch a été retirée.',
        'status' => [
            'approved' => 'Validée',
            'pending' => 'En attente de validation',
            'rejected' => 'Refusée',
        ],
    ],
];
