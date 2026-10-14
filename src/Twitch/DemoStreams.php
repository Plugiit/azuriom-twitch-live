<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\Twitch;

use Illuminate\Support\Collection;

/**
 * Generates fake but stable live streams, to try the plugin without Twitch credentials.
 */
class DemoStreams
{
    protected const TITLES = [
        'Construction de la nouvelle capitale sur PixelCraft !',
        'Survie hardcore sur PixelCraft – jour 42 [FR]',
        'Skyblock PixelCraft : on vise le top 1 de l\'île',
        'Guerre de factions ce soir sur PixelCraft !',
        'Redstone et fermes automatiques – PixelCraft',
        'Chill & discussions avec la commu',
        'Event build : le spawn de Noël de PixelCraft',
        'Speedrun du End sur PixelCraft',
    ];

    /**
     * @param  \Illuminate\Support\Collection<int, \Azuriom\Plugin\PlugiitTwitchLive\Models\Streamer>  $streamers
     * @return array<int, array<string, mixed>>
     */
    public static function generate(Collection $streamers): array
    {
        // Stable during an hour, so the page does not change on every refresh.
        $slot = now()->format('YmdH');
        $streams = [];

        foreach ($streamers->values() as $index => $streamer) {
            $hash = crc32($streamer->login.'|'.$slot);

            if (! $streamer->is_featured && $hash % 10 < 4) {
                continue; // ~40% of the other streamers are offline
            }

            $chatting = ! $streamer->is_featured && $hash % 7 === 0;

            $streams[] = [
                'id' => 'demo-'.$streamer->id,
                'user_id' => 'demo-'.$streamer->id,
                'login' => $streamer->login,
                'name' => $streamer->displayName(),
                'game_id' => $chatting ? '509658' : Settings::MINECRAFT_GAME_ID,
                'game_name' => $chatting ? 'Just Chatting' : 'Minecraft',
                'title' => $chatting ? 'Discussions avant la session PixelCraft' : self::TITLES[$hash % count(self::TITLES)],
                'viewers' => ($streamer->is_featured ? 180 : 4) + $hash % 260,
                'started_at' => now()->startOfHour()->subMinutes(15 + $hash % 200)->toIso8601ZuluString(),
                'language' => 'fr',
                'thumbnail' => plugin_asset('plugiit-twitch-live', 'img/demo-'.(($index % 4) + 1).'.svg'),
            ];
        }

        return $streams;
    }
}
