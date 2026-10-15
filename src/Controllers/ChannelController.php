<?php

namespace Azuriom\Plugin\TwitchLive\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\TwitchLive\Models\Streamer;
use Azuriom\Plugin\TwitchLive\Twitch\Settings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Lets the players declare their own Twitch channel (validated by an admin).
 */
class ChannelController extends Controller
{
    public function store(Request $request)
    {
        abort_unless(Settings::playerSubmissions(), 403);

        $user = $request->user();
        $current = Streamer::where('user_id', $user->id)->first();

        $request->merge(['login' => strtolower(trim((string) $request->input('login')))]);

        $validated = $request->validate([
            'login' => [
                'required', 'string', 'regex:'.Streamer::LOGIN_REGEX,
                Rule::unique('twitch_live_streamers', 'login')->ignore($current?->id),
            ],
        ], [], ['login' => trans('twitch-live::messages.fields.login')]);

        if ($current !== null && $current->login === $validated['login'] && ! $current->isPending()) {
            return redirect()->route('profile.index');
        }

        if ($current !== null) {
            $current->update(['login' => $validated['login'], 'status' => 'pending', 'display_name' => null, 'avatar_url' => null]);
        } else {
            Streamer::create([
                'login' => $validated['login'],
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('profile.index')
            ->with('success', trans('twitch-live::messages.profile.submitted'));
    }

    public function destroy(Request $request)
    {
        Streamer::where('user_id', $request->user()->id)->delete();

        return redirect()->route('profile.index')
            ->with('success', trans('twitch-live::messages.profile.removed'));
    }
}
