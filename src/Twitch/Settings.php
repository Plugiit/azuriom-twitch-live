<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\Twitch;

use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Typed access to the plugin settings. Twitch credentials are stored encrypted.
 */
class Settings
{
    public const PREFIX = 'plugiit-twitch-live.';

    /**
     * Twitch category id of Minecraft.
     */
    public const MINECRAFT_GAME_ID = '27471';

    public static function clientId(): ?string
    {
        return self::decrypted('client_id');
    }

    public static function clientSecret(): ?string
    {
        return self::decrypted('client_secret');
    }

    public static function hasCredentials(): bool
    {
        return self::clientId() !== null && self::clientSecret() !== null;
    }

    public static function demoMode(): bool
    {
        return (bool) setting(self::PREFIX.'demo_mode', false);
    }

    public static function onlyMinecraft(): bool
    {
        return (bool) setting(self::PREFIX.'only_minecraft', false);
    }

    public static function titleKeyword(): ?string
    {
        $keyword = trim((string) setting(self::PREFIX.'title_keyword', ''));

        return $keyword !== '' ? $keyword : null;
    }

    public static function cacheMinutes(): int
    {
        return max(1, min(60, (int) setting(self::PREFIX.'cache_minutes', 3)));
    }

    public static function navbarBadge(): bool
    {
        return (bool) setting(self::PREFIX.'navbar_badge', true);
    }

    public static function playerSubmissions(): bool
    {
        return (bool) setting(self::PREFIX.'player_submissions', true);
    }

    public static function encrypt(?string $value): ?string
    {
        return $value !== null && $value !== '' ? encrypt($value, false) : null;
    }

    protected static function decrypted(string $key): ?string
    {
        $value = setting(self::PREFIX.$key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $decrypted = decrypt($value, false);
        } catch (DecryptException) {
            return null;
        }

        return is_string($decrypted) && $decrypted !== '' ? $decrypted : null;
    }
}
