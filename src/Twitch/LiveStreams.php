<?php

namespace Azuriom\Plugin\TwitchLive\Twitch;

use Azuriom\Plugin\TwitchLive\Models\Streamer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Retrieves the live streams of the community, with caching and a fallback on the last known state.
 */
class LiveStreams
{
    /**
     * Current state, expires after the configured cache duration.
     */
    public const CACHE_KEY = 'twitch-live.live';

    /**
     * Last successfully fetched state, kept forever as a fallback.
     */
    public const LAST_KEY = 'twitch-live.last';

    /**
     * Twitch profiles (display name and avatar) of the streamers.
     */
    public const USERS_KEY = 'twitch-live.users';

    public function __construct(protected ?TwitchClient $client = null) {}

    /**
     * Get the current state, refreshing it when the cache has expired.
     *
     * @return array{streams: array, updated_at: string|null, demo: bool, configured: bool, stale: bool, error: string|null}
     */
    public function state(): array
    {
        return Cache::get(self::CACHE_KEY) ?? $this->refresh();
    }

    /**
     * Get the cached state without ever calling Twitch (for global elements like the navbar badge).
     */
    public function cachedState(): ?array
    {
        return Cache::get(self::CACHE_KEY) ?? Cache::get(self::LAST_KEY);
    }

    public function liveCount(): int
    {
        return count($this->cachedState()['streams'] ?? []);
    }

    public function isFresh(): bool
    {
        return Cache::has(self::CACHE_KEY);
    }

    /**
     * Fetch the live streams now and store them in the cache.
     */
    public function refresh(): array
    {
        $streamers = Streamer::approvedStreamers();
        $minutes = Settings::cacheMinutes();

        if (Settings::demoMode()) {
            $state = $this->makeState($this->decorate(DemoStreams::generate($streamers), $streamers), demo: true);

            Cache::put(self::CACHE_KEY, $state, now()->addMinutes($minutes));

            return $state;
        }

        $client = $this->client();

        if ($client === null) {
            $state = $this->makeState([], configured: false);

            Cache::put(self::CACHE_KEY, $state, now()->addMinutes($minutes));

            return $state;
        }

        try {
            $logins = $streamers->pluck('login')->all();
            $streams = $logins !== [] ? $client->streams($logins) : [];
            $profiles = $this->profiles($client, $streamers);

            $state = $this->makeState($this->decorate($streams, $streamers, $profiles));

            Cache::put(self::CACHE_KEY, $state, now()->addMinutes($minutes));
            Cache::forever(self::LAST_KEY, $state);

            return $state;
        } catch (Throwable $e) {
            Log::warning('[Plugiit Twitch Live] Unable to refresh the live streams: '.$e->getMessage());

            $last = Cache::get(self::LAST_KEY);
            $state = $last ?? $this->makeState([]);
            $state['stale'] = true;
            $state['error'] = Str::limit($e->getMessage(), 250);

            // Retry sooner than the normal cache duration, without hammering the API.
            Cache::put(self::CACHE_KEY, $state, now()->addMinute());

            return $state;
        }
    }

    /**
     * Refresh only when the cached state has expired.
     */
    public function refreshIfStale(): array
    {
        return $this->isFresh() ? Cache::get(self::CACHE_KEY) : $this->refresh();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Apply the filters, attach the community data and sort the streams.
     *
     * @param  \Illuminate\Support\Collection<int, \Azuriom\Plugin\TwitchLive\Models\Streamer>  $streamers
     */
    public function decorate(array $streams, Collection $streamers, array $profiles = []): array
    {
        $byLogin = $streamers->keyBy('login');
        $onlyMinecraft = Settings::onlyMinecraft();
        $keyword = Settings::titleKeyword();

        return collect($streams)
            ->filter(fn (array $stream) => $byLogin->has($stream['login']))
            ->filter(fn (array $stream) => ! $onlyMinecraft
                || $stream['game_id'] === Settings::MINECRAFT_GAME_ID
                || strcasecmp($stream['game_name'], 'Minecraft') === 0)
            ->filter(fn (array $stream) => $keyword === null || Str::contains($stream['title'], $keyword, true))
            ->map(function (array $stream) use ($byLogin, $profiles) {
                /** @var \Azuriom\Plugin\TwitchLive\Models\Streamer $streamer */
                $streamer = $byLogin->get($stream['login']);
                $profile = $profiles[$stream['login']] ?? null;

                return $stream + [
                    'streamer_id' => $streamer->id,
                    'featured' => $streamer->is_featured,
                    'avatar' => $profile['avatar'] ?? $streamer->avatar_url ?? $streamer->user?->getAvatar(),
                    'member' => $streamer->user?->name,
                ];
            })
            ->sortBy([
                fn (array $a, array $b) => $b['featured'] <=> $a['featured'],
                fn (array $a, array $b) => $b['viewers'] <=> $a['viewers'],
            ])
            ->values()
            ->all();
    }

    /**
     * Get the Twitch profiles, refreshed once a day, and keep them on the streamers.
     *
     * @param  \Illuminate\Support\Collection<int, \Azuriom\Plugin\TwitchLive\Models\Streamer>  $streamers
     */
    protected function profiles(TwitchClient $client, Collection $streamers): array
    {
        $profiles = Cache::get(self::USERS_KEY, []);
        $missing = $streamers->pluck('login')->diff(array_keys($profiles))->values()->all();

        if ($missing === []) {
            return $profiles;
        }

        try {
            $profiles = array_merge($profiles, $client->users($missing));
        } catch (TwitchException $e) {
            // Avatars are optional: the live streams are still displayed.
            Log::notice('[Plugiit Twitch Live] Unable to fetch the Twitch profiles: '.$e->getMessage());

            return $profiles;
        }

        Cache::put(self::USERS_KEY, $profiles, now()->addDay());

        foreach ($streamers as $streamer) {
            $profile = $profiles[$streamer->login] ?? null;

            if ($profile !== null && ($streamer->avatar_url !== $profile['avatar'] || $streamer->display_name !== $profile['display_name'])) {
                Streamer::whereKey($streamer->id)->update([
                    'avatar_url' => $profile['avatar'],
                    'display_name' => Str::limit($profile['display_name'], 50, ''),
                ]);
            }
        }

        return $profiles;
    }

    protected function client(): ?TwitchClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        if (! Settings::hasCredentials()) {
            return null;
        }

        return $this->client = new TwitchClient(Settings::clientId(), Settings::clientSecret());
    }

    protected function makeState(array $streams, bool $demo = false, bool $configured = true): array
    {
        return [
            'streams' => $streams,
            'updated_at' => now()->toIso8601String(),
            'demo' => $demo,
            'configured' => $configured,
            'stale' => false,
            'error' => null,
        ];
    }

    /**
     * Format the duration since the stream started, e.g. "2 h 05".
     */
    public static function duration(?string $startedAt): ?string
    {
        if ($startedAt === null) {
            return null;
        }

        $minutes = max(0, (int) Carbon::parse($startedAt)->diffInMinutes(now(), true));

        return $minutes >= 60
            ? sprintf('%d h %02d', intdiv($minutes, 60), $minutes % 60)
            : sprintf('%d min', $minutes);
    }
}
