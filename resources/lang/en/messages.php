<?php

return [
    'title' => 'Live streamers',
    'subtitle' => 'Community members currently live on Twitch.',

    'live' => 'Live',
    'viewers' => '{0} No viewers|{1} 1 viewer|[2,*] :count viewers',
    'since' => 'For :duration',
    'watch' => 'Watch on Twitch',
    'member' => 'Member: :name',
    'featured' => 'Featured',
    'none' => 'No community streamer is live right now. Come back later!',
    'offline_title' => 'Our streamers',
    'offline' => 'Offline',
    'updated' => 'Updated :time',
    'stale' => 'Twitch is temporarily unreachable: showing the last known state.',
    'demo' => 'Demo mode: the displayed streams are simulated.',
    'demo_player' => 'The Twitch player of :name will be displayed here live. It is not loaded in demo mode.',
    'not_configured' => 'The Twitch integration is not configured yet.',

    'widget' => [
        'title' => 'Live now',
        'more' => 'See all streams',
        'none' => 'Nobody is live right now.',
    ],

    'badge' => ':count live',

    'fields' => [
        'login' => 'Twitch channel',
    ],

    'profile' => [
        'title' => 'My Twitch channel',
        'info' => 'Do you stream on the server? Declare your channel: once validated by the staff, your streams will appear on the website.',
        'login' => 'Channel login',
        'login_help' => 'The name in the twitch.tv/your_name address.',
        'submit' => 'Declare my channel',
        'update' => 'Update',
        'remove' => 'Remove my channel',
        'submitted' => 'Your channel has been sent, it will be visible after validation by the staff.',
        'removed' => 'Your Twitch channel has been removed.',
        'status' => [
            'approved' => 'Approved',
            'pending' => 'Waiting for validation',
            'rejected' => 'Rejected',
        ],
    ],
];
