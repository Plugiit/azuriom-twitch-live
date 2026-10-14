<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\PlugiitTwitchLive\Models\Streamer;
use Azuriom\Plugin\PlugiitTwitchLive\Requests\StreamerRequest;
use Azuriom\Plugin\PlugiitTwitchLive\Twitch\LiveStreams;

class StreamerController extends Controller
{
    /**
     * Display the streamers.
     */
    public function index(LiveStreams $liveStreams)
    {
        $liveLogins = collect($liveStreams->cachedState()['streams'] ?? [])->pluck('login');

        return view('plugiit-twitch-live::admin.streamers.index', [
            'pending' => Streamer::pending()->with('user')->latest()->get(),
            'streamers' => Streamer::where('status', '!=', 'pending')
                ->with('user')
                ->orderByDesc('is_featured')
                ->orderBy('login')
                ->paginate(50),
            'liveLogins' => $liveLogins,
        ]);
    }

    public function create()
    {
        return view('plugiit-twitch-live::admin.streamers.create');
    }

    public function store(StreamerRequest $request)
    {
        Streamer::create($request->streamerData());

        return to_route('plugiit-twitch-live.admin.streamers.index')
            ->with('success', trans('messages.status.success'));
    }

    public function edit(Streamer $streamer)
    {
        return view('plugiit-twitch-live::admin.streamers.edit', [
            'streamer' => $streamer->load('user'),
        ]);
    }

    public function update(StreamerRequest $request, Streamer $streamer)
    {
        $streamer->update($request->streamerData());

        return to_route('plugiit-twitch-live.admin.streamers.index')
            ->with('success', trans('messages.status.success'));
    }

    public function destroy(Streamer $streamer)
    {
        $streamer->delete();

        return to_route('plugiit-twitch-live.admin.streamers.index')
            ->with('success', trans('messages.status.success'));
    }

    public function approve(Streamer $streamer)
    {
        $streamer->update(['status' => 'approved']);

        return to_route('plugiit-twitch-live.admin.streamers.index')
            ->with('success', trans('plugiit-twitch-live::admin.streamers.approved', ['login' => $streamer->login]));
    }

    public function reject(Streamer $streamer)
    {
        $streamer->update(['status' => 'rejected']);

        return to_route('plugiit-twitch-live.admin.streamers.index')
            ->with('success', trans('plugiit-twitch-live::admin.streamers.rejected', ['login' => $streamer->login]));
    }
}
