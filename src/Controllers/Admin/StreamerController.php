<?php

namespace Azuriom\Plugin\TwitchLive\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\TwitchLive\Models\Streamer;
use Azuriom\Plugin\TwitchLive\Requests\StreamerRequest;
use Azuriom\Plugin\TwitchLive\Twitch\LiveStreams;

class StreamerController extends Controller
{
    /**
     * Display the streamers.
     */
    public function index(LiveStreams $liveStreams)
    {
        $liveLogins = collect($liveStreams->cachedState()['streams'] ?? [])->pluck('login');

        return view('twitch-live::admin.streamers.index', [
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
        return view('twitch-live::admin.streamers.create');
    }

    public function store(StreamerRequest $request)
    {
        Streamer::create($request->streamerData());

        return to_route('twitch-live.admin.streamers.index')
            ->with('success', trans('messages.status.success'));
    }

    public function edit(Streamer $streamer)
    {
        return view('twitch-live::admin.streamers.edit', [
            'streamer' => $streamer->load('user'),
        ]);
    }

    public function update(StreamerRequest $request, Streamer $streamer)
    {
        $streamer->update($request->streamerData());

        return to_route('twitch-live.admin.streamers.index')
            ->with('success', trans('messages.status.success'));
    }

    public function destroy(Streamer $streamer)
    {
        $streamer->delete();

        return to_route('twitch-live.admin.streamers.index')
            ->with('success', trans('messages.status.success'));
    }

    public function approve(Streamer $streamer)
    {
        $streamer->update(['status' => 'approved']);

        return to_route('twitch-live.admin.streamers.index')
            ->with('success', trans('twitch-live::admin.streamers.approved', ['login' => $streamer->login]));
    }

    public function reject(Streamer $streamer)
    {
        $streamer->update(['status' => 'rejected']);

        return to_route('twitch-live.admin.streamers.index')
            ->with('success', trans('twitch-live::admin.streamers.rejected', ['login' => $streamer->login]));
    }
}
