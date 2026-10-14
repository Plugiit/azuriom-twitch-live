<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\PlugiitTwitchLive\Models\Streamer;
use Azuriom\Plugin\PlugiitTwitchLive\Twitch\LiveStreams;
use Illuminate\Http\Request;

class TwitchLiveController extends Controller
{
    /**
     * Show the streamers currently live.
     */
    public function index(Request $request, LiveStreams $liveStreams)
    {
        $state = $liveStreams->state();
        $streams = collect($state['streams']);
        $liveLogins = $streams->pluck('login');

        return view('plugiit-twitch-live::index', [
            'state' => $state,
            'streams' => $streams,
            'featured' => $streams->firstWhere('featured', true),
            'offline' => Streamer::approvedStreamers()->reject(fn (Streamer $s) => $liveLogins->contains($s->login)),
            'parent' => $request->getHost(),
        ]);
    }
}
