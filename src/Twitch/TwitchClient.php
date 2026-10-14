<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\Twitch;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Minimal client for the Twitch Helix API using an app access token (client credentials flow).
 */
class TwitchClient
{
    public const TOKEN_URL = 'https://id.twitch.tv/oauth2/token';

    public const API_URL = 'https://api.twitch.tv/helix/';

    /**
     * Maximum number of logins accepted per Helix request.
     */
    public const CHUNK_SIZE = 100;

    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
    ) {}

    /**
     * Get an app access token, cached until it expires.
     *
     * @throws \Azuriom\Plugin\PlugiitTwitchLive\Twitch\TwitchException
     */
    public function appToken(bool $fresh = false): string
    {
        $cacheKey = $this->tokenCacheKey();

        if (! $fresh && ($token = Cache::get($cacheKey)) !== null) {
            return $token;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(self::TOKEN_URL, [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                ]);
        } catch (ConnectionException $e) {
            throw new TwitchException('Unable to reach Twitch: '.$e->getMessage(), 0, $e);
        }

        if (! $response->successful() || ! is_string($response->json('access_token'))) {
            throw new TwitchException(sprintf(
                'Twitch authentication failed (HTTP %d): %s',
                $response->status(),
                $response->json('message', 'invalid response'),
            ));
        }

        $token = $response->json('access_token');
        // Keep a safety margin so an expiring token is never used.
        $ttl = max(60, (int) $response->json('expires_in', 3600) - 300);

        Cache::put($cacheKey, $token, now()->addSeconds($ttl));

        return $token;
    }

    /**
     * Get the live streams of the given logins (offline channels are not returned).
     *
     * @param  string[]  $logins
     * @return array<int, array<string, mixed>>
     *
     * @throws \Azuriom\Plugin\PlugiitTwitchLive\Twitch\TwitchException
     */
    public function streams(array $logins): array
    {
        $streams = [];

        foreach ($this->chunks($logins) as $chunk) {
            foreach ($this->get('streams', 'user_login', $chunk, ['first' => self::CHUNK_SIZE]) as $raw) {
                if (($raw['type'] ?? 'live') === 'live') {
                    $streams[] = self::normalizeStream($raw);
                }
            }
        }

        return $streams;
    }

    /**
     * Get the profile of the given logins, keyed by lowercase login.
     *
     * @param  string[]  $logins
     * @return array<string, array{id: string, login: string, display_name: string, avatar: string|null}>
     *
     * @throws \Azuriom\Plugin\PlugiitTwitchLive\Twitch\TwitchException
     */
    public function users(array $logins): array
    {
        $users = [];

        foreach ($this->chunks($logins) as $chunk) {
            foreach ($this->get('users', 'login', $chunk) as $raw) {
                $login = strtolower($raw['login'] ?? '');

                if ($login === '') {
                    continue;
                }

                $users[$login] = [
                    'id' => (string) ($raw['id'] ?? ''),
                    'login' => $login,
                    'display_name' => (string) ($raw['display_name'] ?? $login),
                    'avatar' => ($raw['profile_image_url'] ?? '') ?: null,
                ];
            }
        }

        return $users;
    }

    /**
     * Normalize a stream returned by GET /helix/streams.
     *
     * @return array<string, mixed>
     */
    public static function normalizeStream(array $raw): array
    {
        $thumbnail = (string) ($raw['thumbnail_url'] ?? '');

        return [
            'id' => (string) ($raw['id'] ?? ''),
            'user_id' => (string) ($raw['user_id'] ?? ''),
            'login' => strtolower((string) ($raw['user_login'] ?? '')),
            'name' => (string) ($raw['user_name'] ?? $raw['user_login'] ?? ''),
            'game_id' => (string) ($raw['game_id'] ?? ''),
            'game_name' => (string) ($raw['game_name'] ?? ''),
            'title' => (string) ($raw['title'] ?? ''),
            'viewers' => (int) ($raw['viewer_count'] ?? 0),
            'started_at' => $raw['started_at'] ?? null,
            'language' => (string) ($raw['language'] ?? ''),
            'thumbnail' => $thumbnail !== ''
                ? str_replace(['{width}', '{height}'], ['640', '360'], $thumbnail)
                : null,
        ];
    }

    /**
     * Run a GET request on a Helix endpoint with a repeated query parameter.
     *
     * @param  string[]  $values
     * @return array<int, array<string, mixed>>
     *
     * @throws \Azuriom\Plugin\PlugiitTwitchLive\Twitch\TwitchException
     */
    protected function get(string $endpoint, string $parameter, array $values, array $query = []): array
    {
        // Helix expects repeated parameters (user_login=a&user_login=b), not PHP arrays.
        $queryString = collect($values)
            ->map(fn (string $value) => $parameter.'='.rawurlencode($value))
            ->merge(collect($query)->map(fn ($value, $key) => $key.'='.rawurlencode((string) $value)))
            ->implode('&');

        $url = self::API_URL.$endpoint.'?'.$queryString;

        $response = $this->request($url, $this->appToken());

        if ($response->status() === 401) {
            // The token may have been revoked or expired early: retry once with a new one.
            $response = $this->request($url, $this->appToken(true));
        }

        if (! $response->successful()) {
            throw new TwitchException(sprintf(
                'Twitch API error on %s (HTTP %d): %s',
                $endpoint,
                $response->status(),
                $response->json('message', 'invalid response'),
            ));
        }

        $data = $response->json('data');

        if (! is_array($data)) {
            throw new TwitchException("Invalid Twitch API response on {$endpoint}.");
        }

        return $data;
    }

    protected function request(string $url, string $token): Response
    {
        try {
            return Http::withToken($token)
                ->withHeaders(['Client-Id' => $this->clientId])
                ->acceptJson()
                ->timeout(10)
                ->get($url);
        } catch (ConnectionException $e) {
            throw new TwitchException('Unable to reach Twitch: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * @param  string[]  $logins
     * @return array<int, string[]>
     */
    protected function chunks(array $logins): array
    {
        $logins = array_values(array_unique(array_filter(array_map(
            fn ($login) => strtolower(trim((string) $login)), $logins
        ))));

        return array_chunk($logins, self::CHUNK_SIZE);
    }

    protected function tokenCacheKey(): string
    {
        return 'plugiit-twitch-live.token.'.sha1($this->clientId.'|'.$this->clientSecret);
    }
}
