<?php

namespace Azuriom\Plugin\PlugiitTwitchLive\Models;

use Azuriom\Models\Traits\Loggable;
use Azuriom\Models\User;
use Azuriom\Plugin\PlugiitTwitchLive\Twitch\LiveStreams;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $login
 * @property int|null $user_id
 * @property bool $is_featured
 * @property string $status
 * @property string|null $display_name
 * @property string|null $avatar_url
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder approved()
 * @method static \Illuminate\Database\Eloquent\Builder pending()
 */
class Streamer extends Model
{
    use Loggable;

    public const CACHE_KEY = 'plugiit-twitch-live.streamers';

    public const STATUSES = ['approved', 'pending', 'rejected'];

    /**
     * Twitch logins: 4 to 25 letters, digits or underscores.
     */
    public const LOGIN_REGEX = '/^[a-zA-Z0-9_]{4,25}$/';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plugiit_twitch_live_streamers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login', 'user_id', 'is_featured', 'status', 'display_name', 'avatar_url',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        foreach (['saved', 'deleted'] as $event) {
            static::registerModelEvent($event, function () {
                Cache::forget(self::CACHE_KEY);
                Cache::forget(LiveStreams::CACHE_KEY);
            });
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setLoginAttribute(string $value): void
    {
        $this->attributes['login'] = strtolower(trim($value));
    }

    public function channelUrl(): string
    {
        return 'https://www.twitch.tv/'.$this->login;
    }

    public function displayName(): string
    {
        return $this->display_name ?: $this->login;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Scope a query to only include approved streamers.
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include streamers waiting for a validation.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /**
     * Get the approved streamers with their linked user (cached).
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function approvedStreamers(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            return self::approved()
                ->with('user')
                ->orderByDesc('is_featured')
                ->orderBy('login')
                ->get();
        });
    }
}
