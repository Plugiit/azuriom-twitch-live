<div class="card h-100 ptl-card @if($stream['featured']) ptl-featured @endif">
    <a href="https://www.twitch.tv/{{ $stream['login'] }}" class="ptl-thumb ratio ratio-16x9 card-img-top" target="_blank" rel="noopener noreferrer"
       title="{{ trans('twitch-live::messages.watch') }}">
        <div>
            @if($stream['thumbnail'])
                <img src="{{ $stream['thumbnail'] }}" alt="{{ $stream['title'] }}" loading="lazy">
            @endif
            <span class="badge ptl-live"><span class="ptl-dot bg-white me-1"></span>{{ trans('twitch-live::messages.live') }}</span>
            <span class="badge ptl-viewers"><i class="bi bi-person-fill"></i> {{ trans_choice('twitch-live::messages.viewers', $stream['viewers'], ['count' => number_format($stream['viewers'], 0, ',', ' ')]) }}</span>
            @if($duration = \Azuriom\Plugin\TwitchLive\Twitch\LiveStreams::duration($stream['started_at']))
                <span class="badge ptl-duration"><i class="bi bi-clock"></i> {{ $duration }}</span>
            @endif
        </div>
    </a>
    <div class="card-body d-flex gap-2">
        @include('twitch-live::_avatar', ['avatar' => $stream['avatar'], 'name' => $stream['name']])
        <div class="min-w-0">
            <h3 class="h6 mb-1 ptl-title" title="{{ $stream['title'] }}">{{ $stream['title'] }}</h3>
            <div class="small">
                <a href="https://www.twitch.tv/{{ $stream['login'] }}" target="_blank" rel="noopener noreferrer" class="fw-semibold">{{ $stream['name'] }}</a>
                @if($stream['featured'])
                    <span class="badge text-bg-warning ms-1"><i class="bi bi-star-fill"></i> {{ trans('twitch-live::messages.featured') }}</span>
                @endif
            </div>
            <div class="small text-body-secondary">
                @if($stream['game_name'] !== '')
                    <i class="bi bi-controller"></i> {{ $stream['game_name'] }}
                @endif
                @if($stream['member'])
                    · <i class="bi bi-person"></i> {{ trans('twitch-live::messages.member', ['name' => $stream['member']]) }}
                @endif
            </div>
        </div>
    </div>
</div>
