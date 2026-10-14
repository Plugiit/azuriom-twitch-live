<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\View;

use Azuriom\Extensions\Plugin\UserProfileCardComposer;
use Azuriom\Plugin\PlugiitTwitchLive\Models\Streamer;
use Azuriom\Plugin\PlugiitTwitchLive\Twitch\Settings;
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
                'name' => trans('plugiit-twitch-live::messages.profile.title'),
                'view' => 'plugiit-twitch-live::profile.card',
                'data' => [
                    'streamer' => Streamer::where('user_id', Auth::id())->first(),
                ],
            ],
        ];
    }
}
