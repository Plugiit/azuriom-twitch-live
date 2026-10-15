<?php

namespace Azuriom\Plugin\TwitchLive\Tests;

use Azuriom\Plugin\TwitchLive\Twitch\TwitchClient;
use Azuriom\Plugin\TwitchLive\Twitch\TwitchException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Run from the Azuriom root:
 *   vendor/bin/phpunit plugins/twitch-live/tests
 */
class TwitchClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Never touch the real cache of the website.
        config(['cache.default' => 'array']);
        Cache::flush();
        Http::preventStrayRequests();
    }

    protected function client(): TwitchClient
    {
        return new TwitchClient('client-id', 'client-secret');
    }

    protected function tokenResponse(string $token = 'token-1')
    {
        return Http::response(['access_token' => $token, 'expires_in' => 5000000, 'token_type' => 'bearer']);
    }

    public function test_streams_are_parsed_from_helix(): void
    {
        Http::fake([
            'id.twitch.tv/*' => $this->tokenResponse(),
            'api.twitch.tv/helix/streams*' => Http::response([
                'data' => [[
                    'id' => '40952121085',
                    'user_id' => '101051819',
                    'user_login' => 'Kraftox_TV',
                    'user_name' => 'Kraftox_TV',
                    'game_id' => '27471',
                    'game_name' => 'Minecraft',
                    'type' => 'live',
                    'title' => 'Survie sur le serveur !',
                    'viewer_count' => 1234,
                    'started_at' => '2026-10-14T08:00:00Z',
                    'language' => 'fr',
                    'thumbnail_url' => 'https://static-cdn.jtvnw.net/previews-ttv/live_user_kraftox_tv-{width}x{height}.jpg',
                    'tags' => ['Français'],
                    'is_mature' => false,
                ]],
                'pagination' => [],
            ]),
        ]);

        $streams = $this->client()->streams(['kraftox_tv', 'offline_guy']);

        $this->assertCount(1, $streams);
        $this->assertSame('kraftox_tv', $streams[0]['login']);
        $this->assertSame('Kraftox_TV', $streams[0]['name']);
        $this->assertSame('27471', $streams[0]['game_id']);
        $this->assertSame('Minecraft', $streams[0]['game_name']);
        $this->assertSame(1234, $streams[0]['viewers']);
        $this->assertSame('2026-10-14T08:00:00Z', $streams[0]['started_at']);
        $this->assertSame('https://static-cdn.jtvnw.net/previews-ttv/live_user_kraftox_tv-640x360.jpg', $streams[0]['thumbnail']);

        Http::assertSent(function (Request $request) {
            return str_starts_with($request->url(), 'https://api.twitch.tv/helix/streams?')
                && str_contains($request->url(), 'user_login=kraftox_tv&user_login=offline_guy')
                && $request->hasHeader('Client-Id', 'client-id')
                && $request->hasHeader('Authorization', 'Bearer token-1');
        });
    }

    public function test_logins_are_sent_by_batches_of_100_and_token_is_cached(): void
    {
        Http::fake([
            'id.twitch.tv/*' => $this->tokenResponse(),
            'api.twitch.tv/helix/streams*' => Http::response(['data' => []]),
        ]);

        $logins = array_map(fn (int $i) => 'streamer_'.$i, range(1, 250));

        $this->assertSame([], $this->client()->streams($logins));
        $this->client()->streams(['another_one']);

        $streamRequests = collect(Http::recorded())->filter(fn ($pair) => str_contains($pair[0]->url(), '/helix/streams'));
        $tokenRequests = collect(Http::recorded())->filter(fn ($pair) => str_contains($pair[0]->url(), 'oauth2/token'));

        $this->assertCount(4, $streamRequests); // 100 + 100 + 50, then 1
        $this->assertCount(1, $tokenRequests); // token reused from the cache
        $this->assertSame(100, substr_count($streamRequests->first()[0]->url(), 'user_login='));
        $this->assertSame(50, substr_count($streamRequests->values()[2][0]->url(), 'user_login='));
    }

    public function test_users_are_parsed_with_avatars(): void
    {
        Http::fake([
            'id.twitch.tv/*' => $this->tokenResponse(),
            'api.twitch.tv/helix/users*' => Http::response([
                'data' => [[
                    'id' => '141981764',
                    'login' => 'noxiamc',
                    'display_name' => 'NoxiaMC',
                    'type' => '',
                    'broadcaster_type' => 'affiliate',
                    'description' => 'Streams Minecraft',
                    'profile_image_url' => 'https://static-cdn.jtvnw.net/jtv_user_pictures/noxia-profile_image-300x300.png',
                    'offline_image_url' => '',
                    'created_at' => '2016-12-14T20:32:28Z',
                ]],
            ]),
        ]);

        $users = $this->client()->users(['NoxiaMC']);

        $this->assertSame([
            'noxiamc' => [
                'id' => '141981764',
                'login' => 'noxiamc',
                'display_name' => 'NoxiaMC',
                'avatar' => 'https://static-cdn.jtvnw.net/jtv_user_pictures/noxia-profile_image-300x300.png',
            ],
        ], $users);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/helix/users?login=noxiamc'));
    }

    public function test_an_expired_token_is_renewed_once(): void
    {
        Http::fake([
            'id.twitch.tv/*' => Http::sequence()
                ->push(['access_token' => 'old-token', 'expires_in' => 3600])
                ->push(['access_token' => 'new-token', 'expires_in' => 3600]),
            'api.twitch.tv/helix/streams*' => Http::sequence()
                ->push(['error' => 'Unauthorized', 'status' => 401, 'message' => 'Invalid OAuth token'], 401)
                ->push(['data' => []]),
        ]);

        $this->assertSame([], $this->client()->streams(['kraftox_tv']));

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer new-token'));
    }

    public function test_api_errors_throw_a_twitch_exception(): void
    {
        Http::fake([
            'id.twitch.tv/*' => Http::response(['status' => 400, 'message' => 'invalid client'], 400),
        ]);

        $this->expectException(TwitchException::class);
        $this->expectExceptionMessage('invalid client');

        $this->client()->streams(['kraftox_tv']);
    }

    public function test_server_errors_throw_a_twitch_exception(): void
    {
        Http::fake([
            'id.twitch.tv/*' => $this->tokenResponse(),
            'api.twitch.tv/*' => Http::response('Service Unavailable', 503),
        ]);

        $this->expectException(TwitchException::class);

        $this->client()->streams(['kraftox_tv']);
    }
}
