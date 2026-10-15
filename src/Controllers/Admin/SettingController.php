<?php

namespace Azuriom\Plugin\TwitchLive\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Setting;
use Azuriom\Plugin\TwitchLive\Twitch\LiveStreams;
use Azuriom\Plugin\TwitchLive\Twitch\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function show(LiveStreams $liveStreams)
    {
        return view('twitch-live::admin.settings', [
            'clientId' => Settings::clientId(),
            'hasSecret' => Settings::clientSecret() !== null,
            'demoMode' => Settings::demoMode(),
            'onlyMinecraft' => Settings::onlyMinecraft(),
            'titleKeyword' => Settings::titleKeyword(),
            'cacheMinutes' => Settings::cacheMinutes(),
            'navbarBadge' => Settings::navbarBadge(),
            'playerSubmissions' => Settings::playerSubmissions(),
            'state' => $liveStreams->cachedState(),
        ]);
    }

    /**
     * Update the settings.
     */
    public function save(Request $request, LiveStreams $liveStreams)
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'string', 'max:100', 'alpha_num:ascii'],
            'client_secret' => ['nullable', 'string', 'max:100', 'alpha_num:ascii'],
            'title_keyword' => ['nullable', 'string', 'max:50'],
            'cache_minutes' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $settings = [
            'twitch-live.client_id' => Settings::encrypt($validated['client_id'] ?? null),
            'twitch-live.title_keyword' => $validated['title_keyword'] ?? null,
            'twitch-live.cache_minutes' => $validated['cache_minutes'],
            'twitch-live.demo_mode' => $request->boolean('demo_mode'),
            'twitch-live.only_minecraft' => $request->boolean('only_minecraft'),
            'twitch-live.navbar_badge' => $request->boolean('navbar_badge'),
            'twitch-live.player_submissions' => $request->boolean('player_submissions'),
        ];

        // An empty secret field keeps the current secret, unless removal is requested.
        if ($request->boolean('remove_secret') || empty($validated['client_id'])) {
            $settings['twitch-live.client_secret'] = null;
        } elseif (! empty($validated['client_secret'])) {
            $settings['twitch-live.client_secret'] = Settings::encrypt($validated['client_secret']);
        }

        Setting::updateSettings($settings);

        // Credentials or filters may have changed: drop the cached results and profiles.
        $liveStreams->forget();
        Cache::forget(LiveStreams::USERS_KEY);

        ActionLog::log('twitch-live.settings.updated');

        return to_route('twitch-live.admin.settings')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Refresh the live streams now.
     */
    public function refresh(LiveStreams $liveStreams)
    {
        $state = $liveStreams->refresh();

        if ($state['error'] !== null) {
            return to_route('twitch-live.admin.settings')
                ->with('error', trans('twitch-live::admin.refresh.error', ['error' => $state['error']]));
        }

        return to_route('twitch-live.admin.settings')
            ->with('success', trans('twitch-live::admin.refresh.success', ['count' => count($state['streams'])]));
    }
}
