<?php

namespace Azuriom\Plugin\TwitchLive\View;

use Azuriom\Extensions\Plugin\UserProfileCardComposer;
use Azuriom\Plugin\TwitchLive\Models\Streamer;
use Azuriom\Plugin\TwitchLive\Twitch\Settings;
use Illuminate\Support\Facades\Auth;

class ProfileCardComposer extends UserProfileCardComposer
{
    /**
     * Get the cards to add to the user profile.
     */
    public function getCards(): array
    {
        if (! Settings::playerSubmissions() || Auth::guest()) {
            return [];
        }

        return [
            [
                'name' => trans('twitch-live::messages.profile.title'),
                'view' => 'twitch-live::profile.card',
                'data' => [
                    'streamer' => Streamer::where('user_id', Auth::id())->first(),
                ],
            ],
        ];
    }
}
