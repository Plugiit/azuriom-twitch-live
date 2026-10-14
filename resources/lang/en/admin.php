<?php

return [
    'nav' => [
        'title' => 'Twitch Live',
        'streamers' => 'Streamers',
        'settings' => 'Settings',
    ],

    'permission' => 'Manage Twitch streamers',

    'streamers' => [
        'title' => 'Streamers',
        'create' => 'Add a streamer',
        'edit' => 'Edit streamer :login',
        'pending' => 'Channels declared by players',
        'pending_info' => 'These channels were declared from the players profile and are waiting for your validation.',
        'empty' => 'No streamer yet.',
        'login' => 'Twitch login',
        'login_help' => 'The name in the twitch.tv/name address (4 to 25 letters, digits or _).',
        'member' => 'Linked website member',
        'member_help' => 'Exact name of the Azuriom member (optional).',
        'featured' => 'Featured',
        'featured_help' => 'Featured streamers are displayed first and their Twitch player is embedded in the page.',
        'status' => 'Status',
        'live' => 'Live',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'approved' => 'The channel :login has been approved.',
        'rejected' => 'The channel :login has been rejected.',
        'declared' => 'Declared on',
    ],

    'settings' => [
        'title' => 'Twitch Live settings',
        'credentials' => 'Twitch application',
        'credentials_info' => 'Create an application on dev.twitch.tv/console/apps (redirect URL: http://localhost, category "Website Integration"), then copy its Client ID and a secret. They are stored encrypted.',
        'client_id' => 'Client ID',
        'client_secret' => 'Client Secret',
        'secret_saved' => 'A secret is saved. Leave empty to keep it.',
        'secret_empty' => 'No secret saved.',
        'remove_secret' => 'Delete the saved secret',
        'filters' => 'Filters',
        'only_minecraft' => 'Only display streams whose game is Minecraft',
        'title_keyword' => 'Required keyword in the stream title',
        'title_keyword_help' => 'For example "MyServer". Leave empty to disable. Case insensitive.',
        'display' => 'Display',
        'cache_minutes' => 'Cache duration',
        'cache_minutes_help' => 'Maximum frequency of the Twitch API calls. The scheduled task refreshes the streams as soon as the cache expires.',
        'minutes' => 'minutes',
        'navbar_badge' => 'Display a live counter next to the "Live streamers" navbar link',
        'player_submissions' => 'Allow players to declare their channel from their profile',
        'demo_mode' => 'Demo mode',
        'demo_mode_help' => 'Simulates streams from the streamers list, without calling Twitch. Disable it in production.',
        'demo_enabled' => 'Demo mode is enabled: the streams displayed on the website are simulated and the Twitch API is not called.',
        'widget' => 'Widget for your theme',
        'widget_info' => 'Display the current streams in any Blade view:',
    ],

    'status' => [
        'title' => 'Status',
        'live' => 'Current streams',
        'updated' => 'Last update',
        'never' => 'Never',
        'error' => 'Last error',
        'not_configured' => 'Missing Twitch credentials: no stream can be retrieved.',
        'scheduler' => 'The scheduled task "plugiit-twitch-live:refresh" refreshes the streams automatically when the Laravel scheduler is configured (cron "php artisan schedule:run" every minute). Otherwise, the streams are refreshed on demand during visits, according to the cache duration.',
    ],

    'refresh' => [
        'button' => 'Refresh now',
        'success' => 'Streams refreshed: :count live.',
        'error' => 'Unable to reach Twitch: :error',
    ],

    'logs' => [
        'plugiit-twitch-live-streamers' => [
            'created' => 'Added streamer #:id',
            'updated' => 'Updated streamer #:id',
            'deleted' => 'Deleted streamer #:id',
        ],
        'settings' => 'Updated Twitch Live settings',
    ],
];
